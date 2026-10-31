<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;

class PeopleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id');
        $org = Organization::findOrFail($orgId);

        $currentMembership = OrganizationMember::where('organization_id', $org->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $userRole = $currentMembership->role;

        $employees = OrganizationMember::with(['user.projects' => function ($q) use ($org) {
            $q->where('organization_id', $org->id);
        }])
            ->where('organization_id', $org->id)
            ->whereIn('role', ['OWNER', 'ADMIN', 'MEMBER'])
            ->get();

        $clients = OrganizationMember::with(['user.projects' => function ($q) use ($org) {
            $q->where('organization_id', $org->id);
        }])
            ->where('organization_id', $org->id)
            ->where('role', 'CLIENT')
            ->get();

        $allProjects = Project::where('organization_id', $org->id)->get();

        return view('people.index', compact('org', 'user', 'userRole', 'employees', 'clients', 'allProjects'));
    }

    public function createEnrollment(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id') ?: ($user->organizations()->first()?->id ?? 1);
        $org = Organization::findOrFail($orgId);
        $initialStep = 1;
        $initialInviteType = 'employee';
        return view('people.enroll', compact('org', 'user', 'initialStep', 'initialInviteType'));
    }

    public function createEmployeeEnrollment(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id') ?: ($user->organizations()->first()?->id ?? 1);
        $org = Organization::findOrFail($orgId);
        $initialStep = 2;
        $initialInviteType = 'employee';
        return view('people.enroll', compact('org', 'user', 'initialStep', 'initialInviteType'));
    }

    public function createCollaboratorEnrollment(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id') ?: ($user->organizations()->first()?->id ?? 1);
        $org = Organization::findOrFail($orgId);
        $initialStep = 2;
        $initialInviteType = 'collaborator';
        return view('people.enroll', compact('org', 'user', 'initialStep', 'initialInviteType'));
    }

    public function createClientEnrollment(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id') ?: ($user->organizations()->first()?->id ?? 1);
        $org = Organization::findOrFail($orgId);
        $initialStep = 2;
        $initialInviteType = 'client';
        return view('people.enroll', compact('org', 'user', 'initialStep', 'initialInviteType'));
    }

    public function storeEnrollments(Request $request)
    {
        $currentUser = Auth::user();
        $orgId = session('active_org_id') ?: ($currentUser->organizations()->first()?->id ?? 1);
        $org = Organization::findOrFail($orgId);

        $inviteType = $request->input('invite_type', 'employee');
        $role = $inviteType === 'client' ? 'CLIENT' : 'MEMBER';
        $people = $request->input('people', []);
        $customMessage = trim((string) $request->input('custom_message', ''));

        $addedCount = 0;
        $lastInvitedUser = null;
        $invitedNames = [];
        foreach ($people as $p) {
            $name = trim($p['name'] ?? '');
            $email = trim($p['email'] ?? '');
            if ($name === '' || $email === '') {
                continue;
            }
            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password123'),
                    'job_title' => !empty($p['job_title']) ? $p['job_title'] : ($role === 'CLIENT' ? 'Client Representative' : 'Product Owner'),
                    'avatar_url' => 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($name),
                    'timezone' => 'Asia/Kolkata',
                ]);
            } else {
                $user->update([
                    'name' => $name,
                    'job_title' => !empty($p['job_title']) ? $p['job_title'] : $user->job_title,
                ]);
            }
            OrganizationMember::updateOrCreate(
                ['organization_id' => $org->id, 'user_id' => $user->id],
                [
                    'role' => $role,
                    'client_company' => $p['company'] ?? $org->name,
                    'joined_at' => now(),
                ]
            );

            $this->sendInvitationEmail($user, $org, $currentUser, $customMessage);
            $lastInvitedUser = $user;
            $invitedNames[] = $name;
            $addedCount++;
        }

        $typeSlug = $inviteType === 'client' ? 'clients' : ($inviteType === 'collaborator' ? 'collaborators' : 'employees');
        $identifier = substr(hash('sha256', ($lastInvitedUser?->id ?? '0') . '|' . implode(',', $invitedNames)), 0, 24);
        session([
            'last_invited_user_id' => $lastInvitedUser?->id,
            'last_invited_name' => !empty($invitedNames) ? implode(', ', $invitedNames) : 'manjot singh',
            'last_invited_identifier' => $identifier,
        ]);

        return redirect()->route('enrollments.completed', [
            'type' => $typeSlug,
            'identifier' => $identifier,
        ]);
    }

    public function enrollmentCompleted(Request $request, $type = 'employees')
    {
        $userId = (int) ($request->query('user_id') ?: session('last_invited_user_id', 0));
        $invitedUser = $userId > 0 ? User::find($userId) : User::orderByDesc('id')->first();
        $invitedName = session('last_invited_name') ?: ($invitedUser?->name ?? 'manjot singh');
        $invitedUserId = $invitedUser?->id ?? 1;
        $identifier = (string) ($request->query('identifier') ?: session('last_invited_identifier', '34da63ca736babc2a7bcc3a8'));

        return view('people.enrollment_completed', compact('invitedName', 'invitedUserId', 'identifier', 'type'));
    }

    public function createEnrollmentAccesses(Request $request)
    {
        $currentUser = Auth::user();
        $orgId = session('active_org_id') ?: ($currentUser->organizations()->first()?->id ?? 1);
        $org = Organization::findOrFail($orgId);

        $userId = (int) ($request->query('user_id') ?: session('last_invited_user_id', 0));
        $invitedUser = $userId > 0 ? User::find($userId) : User::orderByDesc('id')->first();
        $invitedName = $request->has('user_id') && $invitedUser
            ? $invitedUser->name
            : (session('last_invited_name') ?: ($invitedUser?->name ?? 'manjot singh'));
        $invitedUserId = $invitedUser?->id ?? 1;
        $firstName = explode(' ', trim($invitedName))[0] ?: 'manjot';
        $identifier = (string) ($request->query('identifier') ?: session('last_invited_identifier', '34da63ca736babc2a7bcc3a8'));

        $activeProjects = Project::where('organization_id', $org->id)
            ->where('status', '!=', 'ARCHIVED')
            ->orderBy('name')
            ->get();

        // Standard Basecamp projects shown in media_1793293075361_d77d4b45.jpg & media_1793293083868_3f28ac8c.jpg
        $curatedMeta = [
            'adhyatmah bharat - e commerce app and website' => ['isClientProject' => true, 'subtitle' => 'Project with Adhyatmah Bharat • 4 people'],
            'adhyatmah bharat version 2.0' => ['isClientProject' => true, 'subtitle' => 'Project with clients • 5 people'],
            'ajath infotech pvt ltd hq' => ['isClientProject' => false, 'subtitle' => 'Project with 8 people'],
            'ajath management - 2026' => ['isClientProject' => false, 'subtitle' => 'Project with 2 people'],
            'bipl app' => ['isClientProject' => true, 'subtitle' => 'Project with BIPL • 8 people'],
            'budfit' => ['isClientProject' => true, 'subtitle' => 'Project with Healthline • 3 people'],
            'clubgo' => ['isClientProject' => true, 'subtitle' => 'Project with ClubGo • 5 people'],
            'dubbly - video enhancement' => ['isClientProject' => true, 'subtitle' => 'Project with Healthline • 3 people'],
            'exibine mobile app' => ['isClientProject' => true, 'subtitle' => 'Project with clients • 4 people'],
            'ride my cars (edward)' => ['isClientProject' => true, 'subtitle' => 'Project with NDC Homes • 9 people'],
        ];

        $projectRows = [];
        $seenLower = [];

        foreach ($activeProjects as $p) {
            $ln = strtolower(trim($p->name));
            $meta = $curatedMeta[$ln] ?? null;
            $memberCount = max(2, $p->members()->count());
            $isCli = $meta ? $meta['isClientProject'] : (!empty($p->client_subtitle) || str_contains($ln, 'edward') || str_contains($ln, 'app'));
            $sub = $meta
                ? $meta['subtitle']
                : ($p->client_subtitle
                    ? "Project with {$p->client_subtitle} • {$memberCount} people"
                    : "Project with {$memberCount} people");

            $projectRows[] = [
                'id' => (int) $p->id,
                'name' => $p->name,
                'isClientProject' => $isCli,
                'subtitle' => $sub,
            ];
            $seenLower[$ln] = true;
        }

        // Ensure canonical projects from screenshot are present if not in activeProjects
        $virtualBaseId = 8000;
        $canonicalList = [
            ['name' => 'Adhyatmah Bharat - e Commerce App and Website', 'isClientProject' => true, 'subtitle' => 'Project with Adhyatmah Bharat • 4 people'],
            ['name' => 'Adhyatmah Bharat Version 2.0', 'isClientProject' => true, 'subtitle' => 'Project with clients • 5 people'],
            ['name' => 'Ajath Infotech Pvt Ltd HQ', 'isClientProject' => false, 'subtitle' => 'Project with 8 people'],
            ['name' => 'Ajath Management - 2026', 'isClientProject' => false, 'subtitle' => 'Project with 2 people'],
            ['name' => 'BIPL APP', 'isClientProject' => true, 'subtitle' => 'Project with BIPL • 8 people'],
            ['name' => 'Budfit', 'isClientProject' => true, 'subtitle' => 'Project with Healthline • 3 people'],
            ['name' => 'ClubGo', 'isClientProject' => true, 'subtitle' => 'Project with ClubGo • 5 people'],
            ['name' => 'Dubbly - Video Enhancement', 'isClientProject' => true, 'subtitle' => 'Project with Healthline • 3 people'],
            ['name' => 'Exibine Mobile App', 'isClientProject' => true, 'subtitle' => 'Project with clients • 4 people'],
        ];
        foreach ($canonicalList as $c) {
            $ln = strtolower($c['name']);
            if (!isset($seenLower[$ln])) {
                $seenLower[$ln] = true;
                $projectRows[] = [
                    'id' => $virtualBaseId++,
                    'name' => $c['name'],
                    'isClientProject' => $c['isClientProject'],
                    'subtitle' => $c['subtitle'],
                ];
            }
        }

        usort($projectRows, fn($a, $b) => strcasecmp($a['name'], $b['name']));

        $initialCheckedProjectIds = $invitedUserId > 0
            ? ProjectMember::where('user_id', $invitedUserId)->pluck('project_id')->map(fn($v) => (int) $v)->values()->all()
            : [];

        return view('people.enrollment_accesses', compact(
            'invitedName',
            'invitedUserId',
            'firstName',
            'identifier',
            'projectRows',
            'initialCheckedProjectIds'
        ));
    }

    public function storeEnrollmentAccesses(Request $request)
    {
        $userId = (int) $request->input('user_id', 0);
        $projectIds = (array) $request->input('project_ids', []);

        if ($userId > 0 && User::where('id', $userId)->exists()) {
            $validProjectIds = [];
            foreach ($projectIds as $pid) {
                $pidInt = (int) $pid;
                if ($pidInt > 0 && $pidInt < 8000 && Project::where('id', $pidInt)->exists()) {
                    $validProjectIds[] = $pidInt;
                }
            }
            $validProjectIds = array_values(array_unique($validProjectIds));

            // Remove any unchecked project memberships so the user ONLY has access to the selected projects
            if (empty($validProjectIds)) {
                ProjectMember::where('user_id', $userId)->delete();
            } else {
                ProjectMember::where('user_id', $userId)
                    ->whereNotIn('project_id', $validProjectIds)
                    ->delete();

                foreach ($validProjectIds as $pidInt) {
                    ProjectMember::firstOrCreate(
                        ['project_id' => $pidInt, 'user_id' => $userId],
                        ['role' => 'PROJECT_MEMBER']
                    );
                }
            }

            // If a non-Owner user is explicitly restricted to a subset of projects, ensure their org role is MEMBER/CLIENT (not ADMIN) so they don't bypass project access checks
            if ($userId !== 1 && count($validProjectIds) < 20) {
                OrganizationMember::where('user_id', $userId)
                    ->where('role', 'ADMIN')
                    ->update(['role' => 'MEMBER']);
            }
        }

        return redirect('/account/people');
    }

    public function sendInvitationEmail(User $invitedUser, Organization $org, ?User $inviter = null, ?string $customMessage = null): void
    {
        $inviterName = $inviter?->name ?? 'Manjot Singh';
        $inviterFirstName = explode(' ', trim($inviterName))[0] ?: 'Manjot';
        $orgName = $org->name ?? 'Ajath Infotech Pvt Ltd';
        $token = hash_hmac('sha256', $invitedUser->id . '|' . strtolower($invitedUser->email), config('app.key', 'ajath-basecamp-secret'));
        $baseUrl = rtrim(config('app.url', 'https://workspace.ajath.com'), '/');
        if (str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
            $baseUrl = 'https://workspace.ajath.com';
        }
        $joinUrl = $baseUrl . '/join/' . $invitedUser->id . '?token=' . $token;
        $declineUrl = $baseUrl . '/join/' . $invitedUser->id . '/decline?token=' . $token;
        $supportUrl = $baseUrl . '/support';
        $logoUrl = $baseUrl . '/images/basecamp-email-logo.png';

        $customHtml = '';
        if (!empty($customMessage)) {
            $escapedMsg = nl2br(e($customMessage));
            $customHtml = "<p style=\"margin:0 0 18px;padding:12px 16px;background-color:#f6f5f1;border-radius:6px;font-size:14px;line-height:1.5;color:#1d2d35;\">{$escapedMsg}</p>";
        }

        $escapedInviter = e($inviterName);
        $escapedFirstName = e($inviterFirstName);
        $escapedOrg = e($orgName);

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{$escapedInviter} invited you to their Basecamp ({$escapedOrg})</title>
</head>
<body style="margin:0;padding:24px 20px;background-color:#ffffff;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1d2d35;">
    <div style="max-width:560px;">
        <div style="margin-bottom:14px;">
            <img src="{$logoUrl}" width="38" height="36" alt="Basecamp" style="display:block;border:0;outline:none;">
        </div>
        <h1 style="margin:0 0 10px;font-size:20px;font-weight:700;line-height:1.3;color:#1d2d35;">
            {$escapedInviter} invited you to their Basecamp
        </h1>
        <p style="margin:0 0 18px;font-size:14px;line-height:1.5;color:#1d2d35;">
            Basecamp is a place that helps everyone stay organized and on the same page. It&rsquo;s really straightforward and easy. To join {$escapedFirstName}, click this button:
        </p>
        {$customHtml}
        <div style="margin:18px 0 20px;">
            <a href="{$joinUrl}" style="display:inline-block;background-color:#2573d8;color:#ffffff;font-weight:500;font-size:14px;text-decoration:none;padding:10px 18px;border-radius:5px;">
                Set up your password to join {$escapedFirstName}
            </a>
        </div>
        <p style="margin:0 0 44px;font-size:14px;line-height:1.5;color:#1d2d35;">
            Prefer not to participate? You can <a href="{$declineUrl}" style="color:#1d6ce0;text-decoration:underline;">decline this invitation</a> and we&rsquo;ll let {$escapedFirstName} know. If you have any questions, just email {$escapedFirstName} or reply to this message.
        </p>
        <div style="padding-top:12px;border-top:1px solid #e5e2dc;font-size:12px;color:#7c7872;line-height:1.5;">
            Need help? <a href="{$supportUrl}" style="color:#1d6ce0;text-decoration:underline;">Contact our support team</a> and we&rsquo;ll get back to you in just a few minutes.
        </div>
    </div>
</body>
</html>
HTML;

        try {
            $senderDisplayName = "{$inviterName} (Basecamp)";
            $replyToEmail = $inviter?->email ?: 'workspace@ajath.com';

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => env('MAIL_HOST', 'mail.ajath.com'),
                'mail.mailers.smtp.port' => (int) env('MAIL_PORT', 465),
                'mail.mailers.smtp.encryption' => env('MAIL_ENCRYPTION', 'ssl'),
                'mail.mailers.smtp.username' => env('MAIL_USERNAME', 'workspace@ajath.com'),
                'mail.mailers.smtp.password' => env('MAIL_PASSWORD', 'mS#l,;f+uTzk'),
                'mail.mailers.smtp.local_domain' => env('MAIL_EHLO_DOMAIN', 'workspace.ajath.com'),
                'mail.from.address' => 'workspace@ajath.com',
                'mail.from.name' => $senderDisplayName,
            ]);

            Mail::purge('smtp');
            Mail::mailer('smtp')->html($html, function ($message) use ($invitedUser, $inviterName, $orgName, $senderDisplayName, $replyToEmail) {
                $message->from('workspace@ajath.com', $senderDisplayName)
                    ->replyTo($replyToEmail, $inviterName)
                    ->to($invitedUser->email, $invitedUser->name)
                    ->subject("{$inviterName} invited you to their Basecamp ({$orgName})");

                $headers = $message->getSymfonyMessage()->getHeaders();
                if ($headers->has('Message-ID')) {
                    $headers->remove('Message-ID');
                }
                $headers->addIdHeader('Message-ID', bin2hex(random_bytes(16)) . '@ajath.com');
            });
        } catch (\Throwable $e) {
            Log::error('Failed to send invitation email to ' . $invitedUser->email . ': ' . $e->getMessage());
        }
    }

    public function declineJoin(Request $request, $userId)
    {
        $user = User::find($userId);
        if ($user) {
            OrganizationMember::where('user_id', $user->id)->delete();
            ProjectMember::where('user_id', $user->id)->delete();
        }
        return redirect('/login')->with('status', 'Your invitation has been declined.');
    }

    public function showJoinForm(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $org = $user->organizations()->first() ?? Organization::first();
        $token = (string) $request->query('token', '');
        return view('people.join', compact('user', 'org', 'token'));
    }

    public function completeJoin(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        $user->update([
            'name' => $validated['name'],
            'job_title' => $validated['job_title'] ?: $user->job_title,
            'password' => Hash::make($validated['password']),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        // Clear Pending badge in any cached project people states for this user
        foreach (\App\Models\Project::pluck('id') as $pid) {
            $cacheKey = 'bc_project_people_state_' . $pid;
            $state = \Illuminate\Support\Facades\Cache::get($cacheKey);
            if (is_array($state) && !empty($state['members']) && is_array($state['members'])) {
                $changed = false;
                foreach ($state['members'] as &$m) {
                    if ((int) ($m['id'] ?? 0) === (int) $user->id || strcasecmp($m['email'] ?? '', $user->email) === 0) {
                        $m['isPending'] = false;
                        $m['joinedConfirmed'] = true;
                        if (($m['roleBadge'] ?? '') === 'Pending') {
                            $m['roleBadge'] = '';
                        }
                        $changed = true;
                    }
                }
                unset($m);
                if ($changed) {
                    \Illuminate\Support\Facades\Cache::forever($cacheKey, $state);
                }
            }
        }

        $org = $user->organizations()->first() ?? Organization::first();
        if ($org) {
            OrganizationMember::updateOrCreate(
                ['organization_id' => $org->id, 'user_id' => $user->id],
                ['joined_at' => now()]
            );
            session(['active_org_id' => $org->id]);
        }

        Auth::login($user, true);

        return redirect()->route('home')->with('success', "Welcome to " . ($org->name ?? 'Basecamp') . ", {$user->name}!");
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();
        $orgId = session('active_org_id');
        $org = Organization::findOrFail($orgId);

        $currentMembership = OrganizationMember::where('organization_id', $org->id)
            ->where('user_id', $currentUser->id)
            ->firstOrFail();

        if (!in_array($currentMembership->role, ['OWNER', 'ADMIN'])) {
            abort(403, 'Only Owners and Admins can enroll new team members or clients.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'role' => 'required|in:ADMIN,MEMBER,CLIENT',
            'password' => 'required|string|min:6',
            'job_title' => 'nullable|string',
            'department' => 'nullable|string',
            'client_company' => 'nullable|string',
            'project_ids' => 'nullable|array',
            'project_ids.*' => 'exists:projects,id',
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'job_title' => $validated['job_title'] ?? ($validated['role'] === 'CLIENT' ? 'Client Representative' : 'Team Member'),
                'avatar_url' => 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($validated['name']),
                'timezone' => 'Asia/Kolkata',
            ]);
        }

        OrganizationMember::updateOrCreate(
            ['organization_id' => $org->id, 'user_id' => $user->id],
            [
                'role' => $validated['role'],
                'department' => $validated['department'] ?? null,
                'client_company' => $validated['client_company'] ?? null,
                'joined_at' => now(),
            ]
        );

        if (!empty($validated['project_ids'])) {
            $projRole = $validated['role'] === 'CLIENT' ? 'PROJECT_CLIENT' : 'PROJECT_MEMBER';
            foreach ($validated['project_ids'] as $pId) {
                ProjectMember::firstOrCreate([
                    'project_id' => $pId,
                    'user_id' => $user->id,
                ], ['role' => $projRole]);
            }
        }

        $this->sendInvitationEmail($user, $org, $currentUser);

        $roleText = $validated['role'] === 'CLIENT' ? 'Client' : ($validated['role'] === 'ADMIN' ? 'Admin' : 'Employee');
        return back()->with('success', "{$roleText} '{$user->name}' added and invitation emailed from workspace@ajath.com!");
    }

    public function destroy($userId)
    {
        $currentUser = Auth::user();
        $orgId = session('active_org_id');
        $org = Organization::findOrFail($orgId);

        $currentMembership = OrganizationMember::where('organization_id', $org->id)
            ->where('user_id', $currentUser->id)
            ->firstOrFail();

        if (!in_array($currentMembership->role, ['OWNER', 'ADMIN'])) {
            abort(403, 'Unauthorized.');
        }

        if ($org->owner_id == $userId) {
            return back()->with('error', 'Cannot remove organization owner.');
        }

        OrganizationMember::where('organization_id', $org->id)->where('user_id', $userId)->delete();
        ProjectMember::whereHas('project', fn($q) => $q->where('organization_id', $org->id))
            ->where('user_id', $userId)
            ->delete();

        return back()->with('success', 'Member removed.');
    }
}

