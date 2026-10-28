<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $roleText = $validated['role'] === 'CLIENT' ? 'Client' : ($validated['role'] === 'ADMIN' ? 'Admin' : 'Employee');
        return back()->with('success', "{$roleText} '{$user->name}' added successfully! They can now log in with their email and password.");
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

