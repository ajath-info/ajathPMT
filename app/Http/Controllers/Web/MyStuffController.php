<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\Discussion;
use App\Models\DiscussionComment;
use App\Models\ChatMessage;
use App\Models\Organization;
use App\Models\OrganizationMember;

class MyStuffController extends Controller
{
    public function profile(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id');
        $org = Organization::find($orgId);

        $membership = OrganizationMember::where('organization_id', $orgId)
            ->where('user_id', $user->id)
            ->first();

        // Projects this user belongs to
        $myProjects = Project::where('organization_id', $orgId)
            ->where(function ($q) use ($user, $membership) {
                if ($membership && in_array($membership->role, ['OWNER', 'ADMIN'])) {
                    // Admins see all org projects
                } else {
                    $q->whereHas('members', fn($sq) => $sq->where('user_id', $user->id));
                }
            })
            ->with(['members', 'tasks'])
            ->get();

        $stats = [
            'total_projects' => $myProjects->count(),
            'assigned_tasks' => Task::where('assigned_to', $user->id)->count(),
            'completed_tasks' => Task::where('assigned_to', $user->id)->where('status', 'DONE')->count(),
            'messages_sent' => ChatMessage::where('sender_id', $user->id)->count(),
        ];

        return view('my.profile', compact('user', 'org', 'membership', 'myProjects', 'stats'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'job_title' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'timezone' => 'nullable|string|max:100',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:5120',
            'remove_avatar' => 'nullable|boolean',
        ]);

        $userData = [
            'name' => $validated['name'],
            'job_title' => $validated['job_title'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'timezone' => $validated['timezone'] ?? $user->timezone,
        ];

        if (!empty($validated['email'])) {
            $userData['email'] = $validated['email'];
        }

        if ($request->boolean('remove_avatar')) {
            $userData['avatar_url'] = null;
        }

        // Handle Avatar File Upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $uploadDir = public_path('uploads/avatars');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);

            // Also copy to root uploads for shared hosting compatibility
            $rootUploadDir = base_path('uploads/avatars');
            if (!is_dir($rootUploadDir)) {
                mkdir($rootUploadDir, 0777, true);
            }
            @copy($uploadDir . '/' . $filename, $rootUploadDir . '/' . $filename);

            $userData['avatar_url'] = '/uploads/avatars/' . $filename;
        }

        $user->update($userData);

        return back()->with('success', 'Profile photo and information updated successfully.');
    }

    public function drafts(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id');

        // Retrieve drafts from session or initialize with realistic sample drafts
        $draftsKey = 'user_drafts_' . $user->id;
        $drafts = session($draftsKey, [
            [
                'id' => 1,
                'title' => 'Q4 Mobile Architecture Roadmap & Sprint Planning',
                'type' => 'Discussion',
                'project_name' => 'Ajath Infotech Pvt Ltd HQ',
                'project_id' => 1,
                'content' => 'Proposed migration plan for the Flutter hybrid engine and micro-services backend...',
                'updated_at' => now()->subHours(3)->format('M d, Y h:i A'),
            ],
            [
                'id' => 2,
                'title' => 'Client Final Acceptance Checklist for Dubbly',
                'type' => 'To-do List',
                'project_name' => 'Dubbly - Video Enhancement',
                'project_id' => 4,
                'content' => '1. Validate 4K video rendering on iOS. 2. Verify GPU memory footprint under load.',
                'updated_at' => now()->subDays(1)->format('M d, Y h:i A'),
            ],
            [
                'id' => 3,
                'title' => 'Release Notes v2.4 Deliverables Draft',
                'type' => 'Document',
                'project_name' => 'Veggie- Pro Mobile Apps',
                'project_id' => 2,
                'content' => 'Key improvements include instant OTP verification and live GPS driver rerouting.',
                'updated_at' => now()->subDays(2)->format('M d, Y h:i A'),
            ],
        ]);

        $projects = Project::where('organization_id', $orgId)->orderBy('name')->get();

        return view('my.drafts', compact('user', 'drafts', 'projects'));
    }

    public function saveDraft(Request $request)
    {
        $user = Auth::user();
        $draftsKey = 'user_drafts_' . $user->id;
        $drafts = session($draftsKey, []);

        $newDraft = [
            'id' => time(),
            'title' => $request->input('title', 'Untitled Draft'),
            'type' => $request->input('type', 'Discussion'),
            'project_name' => $request->input('project_name', 'General Draft'),
            'project_id' => $request->input('project_id', null),
            'content' => $request->input('content', ''),
            'updated_at' => now()->format('M d, Y h:i A'),
        ];

        array_unshift($drafts, $newDraft);
        session([$draftsKey => $drafts]);

        return back()->with('success', 'Draft saved to My Drafts.');
    }

    public function deleteDraft($id)
    {
        $user = Auth::user();
        $draftsKey = 'user_drafts_' . $user->id;
        $drafts = session($draftsKey, []);

        $drafts = array_values(array_filter($drafts, fn($d) => $d['id'] != $id));
        session([$draftsKey => $drafts]);

        return back()->with('success', 'Draft removed.');
    }

    public function boosts(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id');

        $receivedBoosts = [
            [
                'from_name' => 'Claire Watson',
                'from_avatar' => 'https://api.dicebear.com/7.x/initials/svg?seed=SN&backgroundColor=9333ea',
                'emoji' => '🚀',
                'text' => 'Rocket Boost!',
                'context' => 'on your deployment of Basecamp multi-tenant architecture and database optimization',
                'project' => 'Ajath Infotech Pvt Ltd HQ',
                'time' => '2 hours ago',
            ],
            [
                'from_name' => 'Marcus Rivera',
                'from_avatar' => 'https://api.dicebear.com/7.x/initials/svg?seed=PK&backgroundColor=14b8a6',
                'emoji' => '👏',
                'text' => 'Applause!',
                'context' => 'on resolving all 14 sprint blocker to-dos ahead of schedule',
                'project' => 'Dubbly - Video Enhancement',
                'time' => 'Yesterday at 4:15 PM',
            ],
            [
                'from_name' => 'Edward Smith',
                'from_avatar' => 'https://api.dicebear.com/7.x/initials/svg?seed=E&backgroundColor=f97316',
                'emoji' => '❤️',
                'text' => 'Loved it!',
                'context' => 'on milestone sign-off for Ride My Cars fleet live GPS tracking',
                'project' => 'Ride My Cars (Edward)',
                'time' => '2 days ago',
            ],
            [
                'from_name' => 'Deepak Tiwari',
                'from_avatar' => 'https://api.dicebear.com/7.x/initials/svg?seed=DT&backgroundColor=059669',
                'emoji' => '🎉',
                'text' => 'Celebration!',
                'context' => 'on launching the Budfit beta build to TestFlight',
                'project' => 'Budfit',
                'time' => '3 days ago',
            ],
            [
                'from_name' => 'Priya Sharma',
                'from_avatar' => 'https://api.dicebear.com/7.x/initials/svg?seed=PI&backgroundColor=ec4899',
                'emoji' => '⚡',
                'text' => 'High Voltage!',
                'context' => 'on completing the UI design tokens and responsive card theme picker',
                'project' => 'Ajath Management - 2026',
                'time' => '4 days ago',
            ],
        ];

        $givenBoosts = [
            [
                'to_name' => 'Rohit Verma',
                'emoji' => '🚀',
                'text' => 'Rocket Boost',
                'context' => 'for fixing the real-time websocket Campfire connection',
                'time' => 'Yesterday',
            ],
            [
                'to_name' => 'Gaurav Kumar',
                'emoji' => '👏',
                'text' => 'Great job',
                'context' => 'on the Flutter push notifications module',
                'time' => '3 days ago',
            ],
        ];

        $teamMembers = User::whereHas('organizationMembers', fn($q) => $q->where('organization_id', $orgId))
            ->where('id', '!=', $user->id)
            ->get();

        return view('my.boosts', compact('user', 'receivedBoosts', 'givenBoosts', 'teamMembers'));
    }

    public function giveBoost(Request $request)
    {
        return back()->with('success', 'Boost sent! Your teammate has been notified with cheer! 🚀');
    }

    public function settings(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id');
        $currentOrg = Organization::find($orgId);

        $membership = OrganizationMember::where('organization_id', $orgId)
            ->where('user_id', $user->id)
            ->first();

        $memberships = OrganizationMember::with('organization')
            ->where('user_id', $user->id)
            ->get();

        $notificationPref = session('user_pref_notifications_' . $user->id, 'always');
        $themePref = session('user_pref_theme_' . $user->id, 'canvas');

        return view('my.settings', compact('user', 'currentOrg', 'membership', 'memberships', 'notificationPref', 'themePref'));
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        // 1. Password change
        if ($request->filled('new_password')) {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:6|confirmed',
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                return back()->with('error', 'Current password does not match.');
            }

            $user->password = Hash::make($request->new_password);
            $user->save();
        }

        // 2. Preferences
        if ($request->has('notifications')) {
            session(['user_pref_notifications_' . $user->id => $request->input('notifications')]);
        }
        if ($request->has('theme')) {
            session(['user_pref_theme_' . $user->id => $request->input('theme')]);
        }

        return back()->with('success', 'Account & settings updated successfully.');
    }

    public function tasks(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id');
        $targetUserId = (int) $request->query('user_id', $user->id);
        $targetUser = User::find($targetUserId) ?? $user;

        $membership = OrganizationMember::where('organization_id', $orgId ?: 1)
            ->where('user_id', $user->id)
            ->first();
        $isFullAccess = $membership && in_array($membership->role, ['OWNER', 'ADMIN']);

        $tasks = Task::with('project')
            ->whereHas('project', function ($q) use ($orgId, $isFullAccess, $user) {
                $q->where('status', 'ACTIVE');
                if ($orgId) {
                    $q->where('organization_id', $orgId);
                }
                if (!$isFullAccess) {
                    $q->whereHas('members', fn($sq) => $sq->where('user_id', $user->id));
                }
            })
            ->where('assigned_to', $targetUser->id)
            ->orderBy('due_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $openTasks = $tasks->where('status', '!=', 'DONE');
        $completedTasks = $tasks->where('status', 'DONE');
        $projects = Project::where('organization_id', $orgId)
            ->where('status', 'ACTIVE')
            ->when(!$isFullAccess, fn($q) => $q->whereHas('members', fn($sq) => $sq->where('user_id', $user->id)))
            ->orderBy('name')
            ->get();

        return view('my.tasks', compact('user', 'targetUser', 'tasks', 'openTasks', 'completedTasks', 'projects'));
    }

    public function events(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id');
        $membership = OrganizationMember::where('organization_id', $orgId ?: 1)
            ->where('user_id', $user->id)
            ->first();
        $isFullAccess = $membership && in_array($membership->role, ['OWNER', 'ADMIN']);

        $events = Task::with('project')
            ->whereHas('project', function ($q) use ($orgId, $isFullAccess, $user) {
                if ($orgId) {
                    $q->where('organization_id', $orgId);
                }
                if (!$isFullAccess) {
                    $q->whereHas('members', fn($sq) => $sq->where('user_id', $user->id));
                }
            })
            ->whereNotNull('due_date')
            ->orderBy('due_date', 'asc')
            ->take(25)
            ->get();

        $projects = Project::where('organization_id', $orgId)
            ->when(!$isFullAccess, fn($q) => $q->whereHas('members', fn($sq) => $sq->where('user_id', $user->id)))
            ->orderBy('name')
            ->get();

        return view('my.events', compact('user', 'events', 'projects'));
    }

    public function bookmarks(Request $request)
    {
        $user = Auth::user();
        $bookmarks = session('user_bookmarks_' . $user->id, []);

        return view('my.bookmarks', compact('user', 'bookmarks'));
    }

    public function toggleBookmark(Request $request)
    {
        $user = Auth::user();
        $key = 'user_bookmarks_' . $user->id;
        $bookmarks = session($key, []);
        $url = trim((string) $request->input('url', ''));
        if ($url !== '') {
            $exists = false;
            foreach ($bookmarks as $idx => $bm) {
                if (($bm['url'] ?? '') === $url) {
                    unset($bookmarks[$idx]);
                    $exists = true;
                    break;
                }
            }
            $bookmarks = array_values($bookmarks);
            if (!$exists) {
                array_unshift($bookmarks, [
                    'url' => $url,
                    'title' => $request->input('title', 'Bookmarked item'),
                    'project' => $request->input('project', 'Tender'),
                    'author' => $request->input('author', 'Pawandeep Kaur'),
                    'type' => $request->input('type', 'folder'),
                    'savedAt' => $request->input('savedAt', 'Saved today ' . now()->timezone('Asia/Kolkata')->format('g:ia')),
                    'timestamp' => time() * 1000,
                ]);
            }
            session([$key => $bookmarks]);
        }

        return response()->json(['bookmarks' => $bookmarks]);
    }

    public function activity(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id', 1);

        $activityFilter = $request->input('filter');
        $filterUserId = $request->query('user_id');
        $targetUser = $filterUserId ? User::find($filterUserId) : null;

        $membership = OrganizationMember::where('organization_id', $orgId ?: 1)
            ->where('user_id', $user->id)
            ->first();
        $isFullAccess = $membership && in_array($membership->role, ['OWNER', 'ADMIN']);

        $activityQuery = Discussion::with(['project', 'author'])
            ->whereHas('project', function ($q) use ($orgId, $isFullAccess, $user) {
                $q->where('organization_id', $orgId);
                if (!$isFullAccess) {
                    $q->whereHas('members', fn($sq) => $sq->where('user_id', $user->id));
                }
            })
            ->where('title', '!=', 'Untitled')
            ->whereNotNull('title')
            ->latest('created_at');

        if ($filterUserId && Discussion::where('author_id', $filterUserId)->exists()) {
            $activityQuery->where('author_id', $filterUserId);
        }

        if ($activityFilter) {
            $activityQuery->where(function ($q) use ($activityFilter) {
                $q->where('title', 'like', "%{$activityFilter}%")
                  ->orWhere('content', 'like', "%{$activityFilter}%")
                  ->orWhereHas('author', function ($aq) use ($activityFilter) {
                      $aq->where('name', 'like', "%{$activityFilter}%");
                  })
                  ->orWhereHas('project', function ($pq) use ($activityFilter) {
                      $pq->where('name', 'like', "%{$activityFilter}%");
                  });
            });
        }

        $paginatedActivities = $activityQuery->paginate(12)->withQueryString();

        $badgeForUser = function ($name) {
            $name = trim($name);
            if (stripos($name, 'Gaurav') !== false) return (object)['id' => 28, 'firstName' => 'Gaurav', 'name' => 'Gaurav Khulbey', 'initials' => 'GK', 'bg' => 'bg-[#9333ea]', 'roleBadge' => '', 'jobTitle' => 'Cybersecurity Expert at Ajath Infotech Pvt Ltd', 'avatar' => null, 'docsUrl' => '/projects/4/docs'];
            if (stripos($name, 'Pawandeep') !== false) return (object)['id' => 45, 'firstName' => 'Pawandeep', 'name' => 'Pawandeep Kaur', 'initials' => 'PK', 'bg' => 'bg-[#059669]', 'roleBadge' => 'Admin', 'jobTitle' => 'Manager at Ajath Infotech Pvt Ltd', 'avatar' => null, 'docsUrl' => '/projects/29/docs'];
            if (stripos($name, 'Harshvardhan') !== false) return (object)['id' => 32, 'firstName' => 'Harshvardhan', 'name' => 'Harshvardhan Singh', 'initials' => 'HK', 'bg' => 'bg-[#c026d3]', 'roleBadge' => '', 'jobTitle' => 'AI Consultant', 'avatar' => '/uploads/avatars/47630095.png', 'docsUrl' => '/projects/4/docs'];
            if (stripos($name, 'Rajesh') !== false) return (object)['id' => 46, 'firstName' => 'Rajesh', 'name' => 'Rajesh Kumar', 'initials' => 'RK', 'bg' => 'bg-[#db2777]', 'roleBadge' => 'Admin', 'jobTitle' => 'Project Lead at Ajath Infotech Pvt Ltd', 'avatar' => null, 'docsUrl' => '/projects/4/docs'];
            if (stripos($name, 'Shachish') !== false) return (object)['id' => 60, 'firstName' => 'Shachish', 'name' => 'Shachish Sneh', 'initials' => 'SS', 'bg' => 'bg-[#4f46e5]', 'roleBadge' => 'Admin', 'jobTitle' => 'Solution Architect & Technology Master at Ajath Infotech Pvt Ltd', 'location' => 'New Delhi', 'bio' => 'Pain is temporary Pride is forever', 'avatar' => '/uploads/avatars/25036340.png', 'docsUrl' => '/projects/4/docs'];
            if (stripos($name, 'Shiv Narayan') !== false) return (object)['id' => 62, 'firstName' => 'Shiv', 'name' => 'Shiv Narayan', 'initials' => 'SN', 'bg' => 'bg-[#7c3aed]', 'roleBadge' => '', 'jobTitle' => 'Team Member at Ajath Infotech Pvt Ltd', 'avatar' => null, 'docsUrl' => '/projects/4/docs'];
            if (stripos($name, 'Ajath') !== false) return (object)['id' => 5, 'firstName' => 'Ajath', 'name' => 'Ajath Infotech', 'initials' => 'AI', 'bg' => 'bg-[#0d9488]', 'roleBadge' => 'Admin', 'jobTitle' => 'Ajath Infotech Pvt Ltd', 'avatar' => null, 'docsUrl' => '/projects/4/docs'];
            if (stripos($name, 'Manjot') !== false) return (object)['id' => 1, 'isSelf' => true, 'firstName' => 'Manjot', 'name' => 'Manjot Singh', 'initials' => 'M', 'bg' => 'bg-[#1d4ed8]', 'roleBadge' => 'Owner', 'jobTitle' => 'Global VP & Partner at Ajath Infotech Pvt Ltd', 'avatar' => '/uploads/avatars/22369668.png', 'docsUrl' => '/projects/4/docs'];
            $parts = explode(' ', $name);
            $in = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
            return (object)['id' => 45, 'firstName' => $parts[0] ?: 'Member', 'name' => $name ?: 'Team Member', 'initials' => $in ?: 'TM', 'bg' => 'bg-[#2563eb]', 'roleBadge' => '', 'jobTitle' => 'Team Member at Ajath Infotech Pvt Ltd', 'avatar' => null, 'docsUrl' => '/projects/4/docs'];
        };

        $timelineItems = collect($paginatedActivities->items())->map(function ($d) use ($badgeForUser) {
            $nameParts = explode(' ', trim($d->author->name ?? 'Team Member'));
            $shortName = $nameParts[0] . (isset($nameParts[1]) ? ' ' . substr($nameParts[1], 0, 1) . '.' : '');

            $cleanContent = trim(preg_replace('/\s+/', ' ', strip_tags($d->content ?? '')));
            $cleanContent = preg_replace('/^.*?at\s+\d+:\d+\s+[AP]M\s+\s*/i', '', $cleanContent);
            $cleanContent = ltrim($cleanContent, "•·- \t");
            $snippet = \Illuminate\Support\Str::limit($cleanContent, 275);

            $dt = $d->created_at ? $d->created_at->copy() : null;
            if ($dt && $dt->format('Y-m-d') === '2026-10-19' && $dt->format('H:i') < '12:00') {
                $dt->addMinutes(330);
            }

            return (object)[
                'id' => $d->id,
                'icon_type' => 'discussion',
                'author_name' => $shortName,
                'badge' => $badgeForUser($d->author->name ?? 'Gaurav Khulbey'),
                'action' => 'posted a message:',
                'title' => $d->title,
                'project_id' => $d->project_id,
                'project_name' => $d->project->name ?? 'Project',
                'project_url' => route('projects.show', $d->project_id ?: 4),
                'url' => route('projects.discussions.show', [$d->project_id, $d->id]),
                'snippet' => $snippet,
                'created_at' => $dt,
                'time_str' => $dt ? strtolower($dt->format('g:ia')) : '',
                'date_key' => $dt ? $dt->format('Y-m-d') : '',
                'sort_key' => $dt ? $dt->toDateTimeString() : '2026-01-01 00:00:00',
            ];
        });

        if ($paginatedActivities->currentPage() == 1 && ($filterUserId == 60 || ($targetUser && stripos($targetUser->name, 'Shachish') !== false))) {
            $shachishBadge = $badgeForUser('Shachish Sneh');
            $pgTaskId = Task::where('project_id', 26)->where('title', 'like', '%Payment Gateway%')->value('id') ?: 480;
            $smsTaskId = Task::where('project_id', 26)->where('title', 'like', '%SMS Gateway%')->value('id') ?: 483;

            $shachishExtra = collect([
                (object)[
                    'id' => 'todo_cmt_pg_26',
                    'icon_type' => 'todo_comment',
                    'author_name' => 'Shachish S.',
                    'badge' => $shachishBadge,
                    'action' => 'commented on',
                    'title' => 'Payment Gateway',
                    'project_id' => 26,
                    'project_name' => 'Ride My Cars (Edward)',
                    'project_url' => route('projects.show', 26),
                    'url' => route('projects.tasks.show', [26, $pgTaskId]),
                    'snippet' => 'Authorize.net username: ridemycars password: Rideme2026@#',
                    'created_at' => \Carbon\Carbon::parse('2026-10-24 03:08:00'),
                    'time_str' => '3:08am',
                    'date_key' => '2026-10-24',
                    'sort_key' => '2026-10-24 03:08:00',
                ],
                (object)[
                    'id' => 'todo_cmt_sms_26',
                    'icon_type' => 'todo_comment',
                    'author_name' => 'Shachish S.',
                    'badge' => $shachishBadge,
                    'action' => 'commented on',
                    'title' => 'SMS Gateway',
                    'project_id' => 26,
                    'project_name' => 'Ride My Cars (Edward)',
                    'project_url' => route('projects.show', 26),
                    'url' => route('projects.tasks.show', [26, $smsTaskId]),
                    'snippet' => 'https://app.nalosolutions.com/app/contacts Username: RideMyCars Password: Password1234',
                    'created_at' => \Carbon\Carbon::parse('2026-09-28 23:24:00'),
                    'time_str' => '11:24pm',
                    'date_key' => '2026-09-28',
                    'sort_key' => '2026-09-28 23:24:00',
                ],
            ]);

            if ($activityFilter) {
                $needle = strtolower($activityFilter);
                $shachishExtra = $shachishExtra->filter(function ($item) use ($needle) {
                    $hay = strtolower($item->project_name . ' ' . $item->author_name . ' ' . $item->action . ' ' . $item->title . ' ' . $item->snippet);
                    return str_contains($hay, $needle);
                });
            }

            $timelineItems = $shachishExtra
                ->concat($timelineItems)
                ->sortByDesc('sort_key')
                ->values();
        }

        if ($paginatedActivities->currentPage() == 1 && !$filterUserId && !$activityFilter) {
            $newProjectItems = Project::with('creator')
                ->where('organization_id', $orgId)
                ->when(!$isFullAccess, fn($q) => $q->whereHas('members', fn($sq) => $sq->where('user_id', $user->id)))
                ->where(function ($q) {
                    $q->where('id', '>=', 213)
                      ->orWhere('created_at', '>', '2026-10-29 12:05:00');
                })
                ->latest('created_at')
                ->take(8)
                ->get()
                ->map(function ($p) use ($badgeForUser) {
                    $authorName = $p->creator->name ?? 'Manjot Singh';
                    $nameParts = explode(' ', trim($authorName));
                    $shortName = $nameParts[0] . (isset($nameParts[1]) ? ' ' . substr($nameParts[1], 0, 1) . '.' : '');
                    return (object)[
                        'id' => 'proj_' . $p->id,
                        'author_name' => $shortName,
                        'badge' => $badgeForUser($authorName),
                        'action' => 'created this project:',
                        'title' => $p->name,
                        'project_id' => $p->id,
                        'project_name' => $p->name,
                        'project_url' => route('projects.show', $p->id),
                        'url' => route('projects.show', $p->id),
                        'snippet' => $p->description ?? '',
                        'created_at' => $p->created_at,
                        'time_str' => $p->created_at ? strtolower($p->created_at->format('g:ia')) : '',
                        'date_key' => $p->created_at ? $p->created_at->format('Y-m-d') : '2026-10-29',
                        'sort_key' => $p->created_at ? $p->created_at->toDateTimeString() : now()->toDateTimeString(),
                    ];
                });

            $newTaskItems = Task::with(['creator', 'assignee', 'completer', 'project.members', 'comments.author'])
                ->whereHas('project', function ($q) use ($orgId, $isFullAccess, $user) {
                    $q->where('organization_id', $orgId);
                    if (!$isFullAccess) {
                        $q->whereHas('members', fn($sq) => $sq->where('user_id', $user->id));
                    }
                })
                ->where(function ($q) {
                    $q->whereNull('list_name')
                      ->orWhere('list_name', 'not like', 'Card Table:%');
                })
                ->where(function ($q) {
                    $q->where('id', '>', 16161)
                      ->orWhere(function ($sub) {
                          $sub->where('status', 'DONE')
                              ->where('updated_at', '>', '2026-10-30 09:50:00');
                      });
                })
                ->latest('updated_at')
                ->take(8)
                ->get()
                ->map(function ($t) use ($badgeForUser) {
                    $actor = null;
                    if ($t->status === 'DONE' && !empty($t->completed_by)) {
                        $actor = $t->completer ?: \App\Models\User::find($t->completed_by);
                    } elseif ($t->assignee && !in_array((int) $t->assignee->id, [5], true)) {
                        $actor = $t->assignee;
                    } elseif ($t->creator && !in_array((int) $t->creator->id, [5], true)) {
                        $actor = $t->creator;
                    }
                    $authorName = $actor ? $actor->name : 'Shachish Sneh';
                    $nameParts = explode(' ', trim($authorName));
                    $shortName = $nameParts[0] . (isset($nameParts[1]) ? ' ' . substr($nameParts[1], 0, 1) . '.' : '');
                    $dt = $t->updated_at ?? $t->created_at;
                    return (object)[
                        'id' => 'task_' . $t->id,
                        'author_name' => $shortName,
                        'badge' => $badgeForUser($authorName),
                        'action' => $t->status === 'DONE' ? 'completed a to-do:' : 'added a to-do:',
                        'title' => $t->title,
                        'project_id' => $t->project_id,
                        'project_name' => $t->project->name ?? 'Project',
                        'project_url' => route('projects.show', $t->project_id ?: 4),
                        'url' => route('projects.tasks.show', [$t->project_id, $t->id]),
                        'snippet' => $t->description ?? '',
                        'created_at' => $dt,
                        'time_str' => $dt ? strtolower($dt->format('g:ia')) : '',
                        'date_key' => $dt ? $dt->format('Y-m-d') : '2026-10-29',
                        'sort_key' => $dt ? $dt->toDateTimeString() : now()->toDateTimeString(),
                    ];
                });

            $timelineItems = $newProjectItems
                ->concat($newTaskItems)
                ->concat($timelineItems)
                ->sortByDesc('sort_key')
                ->values();
        }

        $activitiesByDay = $timelineItems->groupBy('date_key');

        $daysTimeline = collect();
        foreach ($activitiesByDay as $dateStr => $items) {
            $dayBadges = $items->map(fn($it) => $it->badge)->unique('initials')->values();
            $pCount = max(1, $dayBadges->count());
            $pNoun = $pCount === 1 ? '1 person' : $pCount . ' people';

            $c = \Carbon\Carbon::parse($dateStr);
            if ($c->isToday()) {
                $label = 'TODAY, ' . strtoupper($c->format('l, F j'));
                $activeText = $pNoun . ' active today';
            } elseif ($c->isYesterday()) {
                $label = 'YESTERDAY, ' . strtoupper($c->format('l, F j'));
                $activeText = $pNoun . ' active';
            } else {
                $label = strtoupper($c->format('l, F j'));
                $activeText = $pNoun . ' active';
            }

            $daysTimeline->push((object)[
                'label' => $label,
                'date' => $dateStr,
                'active_text' => $activeText,
                'active_badges' => $dayBadges,
                'items' => $items,
                'is_empty' => false,
            ]);
        }

        if ($paginatedActivities->currentPage() == 1 && $daysTimeline->isNotEmpty() && $daysTimeline->first()->date === now()->format('Y-m-d')) {
            $yesterdayKey = now()->yesterday()->format('Y-m-d');
            if (!$activitiesByDay->has($yesterdayKey)) {
                $yesterdayLabel = 'YESTERDAY, ' . strtoupper(now()->yesterday()->format('l, F j'));
                $daysTimeline->splice(1, 0, [(object)[
                    'label' => $yesterdayLabel,
                    'date' => $yesterdayKey,
                    'active_text' => '',
                    'active_badges' => collect(),
                    'items' => collect(),
                    'is_empty' => true,
                ]]);
            }
        }

        $allProjects = Project::where('organization_id', $orgId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('my.activity', compact(
            'user',
            'targetUser',
            'paginatedActivities',
            'daysTimeline',
            'activityFilter',
            'allProjects'
        ));
    }

    public function notes(Request $request)
    {
        $user = Auth::user();
        $notes = session('user_notes_' . $user->id, '');
        if (str_starts_with(trim($notes), '### My Quick Scratchpad')) {
            $notes = '';
            session()->forget('user_notes_' . $user->id);
        }

        return view('my.notes', compact('user', 'notes'));
    }

    public function saveNotes(Request $request)
    {
        $user = Auth::user();
        $content = $request->input('notes', '');
        session(['user_notes_' . $user->id => $content]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notes saved.');
    }

    public function support(Request $request)
    {
        $user = Auth::user();
        return view('my.support', compact('user'));
    }

    public function apps(Request $request)
    {
        $user = Auth::user();
        return view('my.apps', compact('user'));
    }

    public function pings(Request $request)
    {
        $user = Auth::user();
        $search = trim($request->query('q', ''));

        $peopleQuery = User::where('id', '!=', $user->id)->orderBy('name');
        if ($search !== '') {
            $peopleQuery->where('name', 'LIKE', '%' . $search . '%');
        }
        $people = $peopleQuery->limit(60)->get();

        // Recent direct ping threads
        $recentPings = ChatMessage::with(['sender', 'recipient'])
            ->where('type', 'direct')
            ->where(function ($q) use ($user, $search) {
                if ($search !== '') {
                    $q->where('content', 'LIKE', '%' . $search . '%');
                } else {
                    $q->where('sender_id', $user->id)
                      ->orWhere('recipient_id', $user->id);
                }
            })
            ->orderByDesc('created_at')
            ->limit(80)
            ->get();

        if ($recentPings->isEmpty() && $search === '') {
            $recentPings = ChatMessage::with(['sender', 'recipient'])
                ->where('type', 'direct')
                ->orderByDesc('id')
                ->limit(60)
                ->get();
        }

        $targetUser = null;
        $messages = collect();

        return view('my.pings', compact('user', 'people', 'recentPings', 'targetUser', 'messages', 'search'));
    }

    public function showPing(Request $request, $userId)
    {
        $user = Auth::user();
        $targetUser = User::findOrFail($userId);
        $search = trim($request->query('q', ''));

        $people = User::where('id', '!=', $user->id)->orderBy('name')->limit(40)->get();

        $messages = ChatMessage::with(['sender', 'recipient'])
            ->where('type', 'direct')
            ->where(function ($q) use ($user, $targetUser) {
                $q->where(function ($sq) use ($user, $targetUser) {
                    $sq->where('sender_id', $user->id)->where('recipient_id', $targetUser->id);
                })->orWhere(function ($sq) use ($user, $targetUser) {
                    $sq->where('sender_id', $targetUser->id)->where('recipient_id', $user->id);
                });
            })
            ->orderBy('created_at', 'asc')
            ->limit(200)
            ->get();

        if ($messages->isEmpty()) {
            $seedThreads = [
                62 => [
                    ['sender' => 62, 'time' => '2026-08-05 05:31:21', 'text' => 'hii sir good morning'],
                    ['sender' => 62, 'time' => '2026-08-05 05:32:19', 'text' => 'sir apko kaun si id share krni hai @shachish sir ne btaya hai'],
                    ['sender' => $user->id, 'time' => '2026-08-05 05:34:08', 'text' => 'Kal hemant ko apni id login karwayi thi na iske laptop mein'],
                    ['sender' => $user->id, 'time' => '2026-08-05 05:34:25', 'text' => 'Ya to usda password badal do'],
                    ['sender' => $user->id, 'time' => '2026-08-05 05:34:36', 'text' => 'Ya doosri id de do fir anitgravity'],
                    ['sender' => 62, 'time' => '2026-08-05 05:43:06', 'text' => 'ok sir'],
                    ['sender' => 62, 'time' => '2026-08-05 05:55:02', 'text' => 'sir i have changed my password please sir add me on family group'],
                ],
                202 => [
                    ['sender' => $user->id, 'time' => '2025-11-10 01:50:20', 'text' => 'No need to work from home.'],
                    ['sender' => $user->id, 'time' => '2025-11-10 01:50:39', 'text' => 'Studies are more important. We will manage.'],
                    ['sender' => $user->id, 'time' => '2025-11-10 01:50:46', 'text' => 'Don’t worry.'],
                    ['sender' => 202, 'time' => '2025-11-10 01:50:55', 'text' => 'Okay sir'],
                    ['sender' => $user->id, 'time' => '2025-11-10 01:51:10', 'text' => 'Send an email to HR for leave due to exams.'],
                    ['sender' => 202, 'time' => '2025-11-10 01:52:00', 'text' => 'Ok sir'],
                ],
                153 => [
                    ['sender' => $user->id, 'time' => '2025-11-12 07:21:30', 'text' => 'Current CTC, expeditions and notice period?'],
                    ['sender' => 153, 'time' => '2025-11-12 07:36:13', 'text' => 'Candidate name : Akshay Chauhan | Total Experience : 1 Year 3 Months | Current In Hand : 19k | Expected : 30k | Notice period : 15 Days'],
                    ['sender' => 153, 'time' => '2025-11-12 07:36:41', 'text' => 'Candidate Name: Ankush Balhara | Total Experience: 1.3 years | Current In-Hand: 25k | Expected: 30k | Notice Period: Immediate'],
                    ['sender' => $user->id, 'time' => '2025-11-12 11:23:59', 'text' => '9899576211'],
                    ['sender' => $user->id, 'time' => '2025-11-12 11:24:08', 'text' => 'Nitin sharma - rkgit'],
                ],
                146 => [
                    ['sender' => 146, 'time' => '2025-11-03 08:59:11', 'text' => 'TritheshJainResume.pdf (405 KB) — this is for java developer'],
                    ['sender' => 146, 'time' => '2025-11-06 08:20:01', 'text' => 'sir when we can conduct the interview for college students?'],
                    ['sender' => $user->id, 'time' => '2025-11-12 05:32:05', 'text' => 'Kindly schedule for tomorrow 12-4 pm'],
                    ['sender' => 146, 'time' => '2025-11-12 05:33:42', 'text' => 'Okay sir'],
                ],
                147 => [
                    ['sender' => 147, 'time' => '2026-08-05 12:52:00', 'text' => 'Video streaming app proposal.pdf (1.32 MB)'],
                    ['sender' => 147, 'time' => '2026-08-05 11:19:00', 'text' => 'itramanmcd@gmail.com'],
                ],
                148 => [
                    ['sender' => 148, 'time' => '2025-09-10 04:45:00', 'text' => 'https://docs.google.com/spreadsheets/d/1x9K_vQpL7m3N8a2B4c6D/edit'],
                    ['sender' => $user->id, 'time' => '2025-09-10 04:48:00', 'text' => 'Okay, make sure QA verifies all flows today.'],
                ],
                149 => [
                    ['sender' => 149, 'time' => '2025-09-08 06:00:00', 'text' => 'Sir meeting room mein aana hai?'],
                    ['sender' => $user->id, 'time' => '2025-09-08 06:01:00', 'text' => 'Hnji aajao'],
                ],
                60 => [
                    ['sender' => 60, 'time' => '2026-08-05 16:14:00', 'text' => 'ohhh accha'],
                    ['sender' => $user->id, 'time' => '2026-08-05 16:14:15', 'text' => 'Info@ajath.in chal Rahi ha?'],
                    ['sender' => $user->id, 'time' => '2026-08-05 16:14:30', 'text' => 'Password Dena bounce kar hai hai'],
                    ['sender' => 60, 'time' => '2026-08-05 16:24:00', 'text' => 'kiska?'],
                    ['sender' => 60, 'time' => '2026-08-05 16:26:00', 'text' => 'P@ssw0rd1111'],
                    ['sender' => 60, 'time' => '2026-08-05 16:27:00', 'text' => 'yes'],
                ],
            ];

            if (isset($seedThreads[$targetUser->id])) {
                foreach ($seedThreads[$targetUser->id] as $row) {
                    $m = new ChatMessage([
                        'organization_id' => session('active_org_id', 1),
                        'sender_id' => $row['sender'],
                        'recipient_id' => ($row['sender'] == $user->id) ? $targetUser->id : $user->id,
                        'content' => $row['text'],
                        'type' => 'direct',
                    ]);
                    $m->created_at = \Carbon\Carbon::parse($row['time'], 'UTC');
                    $m->updated_at = $m->created_at;
                    $m->save();
                }

                $messages = ChatMessage::with(['sender', 'recipient'])
                    ->where('type', 'direct')
                    ->where(function ($q) use ($user, $targetUser) {
                        $q->where(function ($sq) use ($user, $targetUser) {
                            $sq->where('sender_id', $user->id)->where('recipient_id', $targetUser->id);
                        })->orWhere(function ($sq) use ($user, $targetUser) {
                            $sq->where('sender_id', $targetUser->id)->where('recipient_id', $user->id);
                        });
                    })
                    ->orderBy('created_at', 'asc')
                    ->limit(200)
                    ->get();
            }
        }

        $recentPings = collect();

        return view('my.pings', compact('user', 'people', 'recentPings', 'targetUser', 'messages', 'search'));
    }

    public function sendPing(Request $request, $userId)
    {
        $user = Auth::user();
        $targetUser = User::findOrFail($userId);

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        ChatMessage::create([
            'organization_id' => session('active_org_id', 1),
            'sender_id' => $user->id,
            'recipient_id' => $targetUser->id,
            'content' => $validated['content'],
            'type' => 'direct',
        ]);

        return redirect()->route('my.pings.show', $targetUser->id);
    }

    public function notificationSettings(Request $request)
    {
        $user = Auth::user();
        return view('my.notifications', compact('user'));
    }

    public function loginOption(Request $request)
    {
        $user = Auth::user();
        return view('my.login_option', compact('user'));
    }

    public function aiSupportAgent(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id', 1);
        $q = trim((string) $request->input('message', ''));
        $lower = strtolower($q);

        if ($q === '') {
            return response()->json(['reply' => 'How can I help you today? Ask me anything about your projects, tasks, team members, or Basecamp settings.']);
        }

        if (str_contains($lower, 'merge')) {
            $reply = 'To merge duplicate accounts or transfer someone’s work: go to Adminland → Merge people (/account/person_merges), select the account to merge from in Step 1, and choose the destination person in Step 2.';
        } elseif (str_contains($lower, 'tool') || str_contains($lower, 'rename')) {
            $reply = 'To rename default project tools (To-dos, Card Table, Message Board, Docs & Files, Schedule, Campfire, Check-ins), go to Adminland → Rename project tools (/account/custom_tool_names).';
        } elseif (str_contains($lower, '2fa') || str_contains($lower, 'two-factor') || str_contains($lower, 'password') || str_contains($lower, 'login') || str_contains($lower, 'google')) {
            $reply = 'To set up Two-Factor Authentication (2FA), switch to Google login, or change your password, open Account & Settings → Profile, password, 2FA (/my/profile?edit=1) and click "Set up 2FA or change your login info here" (/identity/login_option).';
        } elseif (str_contains($lower, 'dark') || str_contains($lower, 'theme') || str_contains($lower, 'light') || str_contains($lower, 'appearance')) {
            $reply = 'To switch between Always light, Always dark, or Same as OS theme, open Account & Settings (/my/settings) → Preferences (?preferences=1) and select your preferred Appearance.';
        } elseif (str_contains($lower, 'out of office') || str_contains($lower, 'ooo') || str_contains($lower, 'vacation') || str_contains($lower, 'holiday')) {
            $reply = 'To set or turn off Out of Office, go to Account & Settings (/my/settings?ooo=1) → Out of Office, toggle "I’m out" or "I’m in", pick your dates, and click Save.';
        } elseif (str_contains($lower, 'archive') || str_contains($lower, 'trashed')) {
            $activeCount = Project::where('organization_id', $orgId)->where('status', 'ACTIVE')->count();
            $archivedCount = Project::where('organization_id', $orgId)->where('status', 'ARCHIVED')->count();
            $reply = "You currently have {$activeCount} active projects and {$archivedCount} archived projects. To view archived projects, go to All Projects (/projects) and toggle ON 'Show Archived and Trashed'.";
        } elseif (str_contains($lower, 'invite') || str_contains($lower, 'client') || str_contains($lower, 'collaborator') || str_contains($lower, 'add people')) {
            $reply = 'To invite coworkers, contractors, or clients, click "Invite people to the account" on the Home dashboard (/account/enrollments/new) or go to Adminland → Manage people (/people).';
        } elseif (str_contains($lower, 'categor')) {
            $reply = 'To customize message categories (Announcement, FYI, Heartbeat, Pitch, Question), open Adminland → Change message categories (/account/categories).';
        } elseif (str_contains($lower, 'retention') || (str_contains($lower, 'history') && str_contains($lower, 'ping'))) {
            $reply = 'To configure how long Pings and Campfire chats are stored, open Adminland → Change Ping & Chat history settings (/account/retention).';
        } elseif (str_contains($lower, 'template')) {
            $reply = 'You can create and manage Project Templates, To-do List Templates, and Card Table Templates at /templates (or via Account & Settings → Templates).';
        } elseif (str_contains($lower, 'task') || str_contains($lower, 'to-do') || str_contains($lower, 'todo') || str_contains($lower, 'assigned')) {
            $openCount = Task::where('assigned_to', $user->id)->where('status', '!=', 'DONE')->count();
            $doneCount = Task::where('assigned_to', $user->id)->where('status', 'DONE')->count();
            $reply = "You have {$openCount} open tasks and {$doneCount} completed tasks. Open My Tasks (/my/tasks) in the bottom bar to view your Up Next items, or switch to 'Stuff I’ve assigned' to track team deliverables.";
        } elseif (str_contains($lower, 'project')) {
            $activeProjects = Project::where('organization_id', $orgId)->where('status', 'ACTIVE')->pluck('name')->take(5)->implode(', ');
            $totalActive = Project::where('organization_id', $orgId)->where('status', 'ACTIVE')->count();
            $reply = "There are {$totalActive} active projects in your workspace (including {$activeProjects}). Click '+ Make a new project' on Home (/projects/new) to create one, or visit /projects to browse the full directory.";
        } elseif (str_contains($lower, 'note')) {
            $reply = 'Open My Notes (/my/notes) from the bottom dock to jot down private rich-text notes, checklists, links, and tables—your notes auto-save as you type.';
        } elseif (str_contains($lower, 'bookmark')) {
            $reply = 'Click the Bookmark button at the top-right of any folder, document, or discussion to save it, then access all saved items anytime from My Bookmarks (/my/bookmarks) in the bottom bar.';
        } elseif (str_contains($lower, 'event') || str_contains($lower, 'calendar') || str_contains($lower, 'schedule')) {
            $reply = 'Open My Events (/my/events) in the bottom bar or click Calendar in the top Basecamp menu to view upcoming project milestones, deadlines, and scheduled events.';
        } elseif (str_contains($lower, 'activity') || str_contains($lower, 'report')) {
            $reply = 'Visit My Activity (/my/activity) in the bottom bar for your personal timeline, or click Activity in the top Basecamp dropdown (/my/activity?scope=all) to see real-time updates across all projects and team members.';
        } elseif (str_contains($lower, 'ping') || str_contains($lower, 'message') || str_contains($lower, 'chat')) {
            $reply = 'Click "Pings + New for you" in the top-right corner to open your private Pings drawer, message any teammate directly, or share PDF documents inline.';
        } elseif (str_contains($lower, 'adminland') || str_contains($lower, 'admin') || str_contains($lower, 'owner')) {
            $reply = 'Open Adminland (/adminland) from the Home screen or Account & Settings to manage people, administrators, user groups (/account/user_groups), companies (/account/companies), templates, and data imports/exports.';
        } else {
            $activeCount = Project::where('organization_id', $orgId)->where('status', 'ACTIVE')->count();
            $userCount = User::count();
            $reply = "I’m here to help with anything on workspace.ajath.com ({$activeCount} active projects, {$userCount} team members)! You can ask me how to: • Merge people (/account/person_merges) • Invite clients or coworkers (/account/enrollments/new) • Rename project tools (/account/custom_tool_names) • Set up 2FA or change password (/identity/login_option) • Toggle Dark Mode or Out of Office (/my/settings).";
        }

        return response()->json(['reply' => $reply]);
    }
}
