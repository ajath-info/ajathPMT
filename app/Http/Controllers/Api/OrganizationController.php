<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $memberships = OrganizationMember::with(['organization.owner'])
            ->where('user_id', $user->id)
            ->get();

        $orgs = $memberships->map(function ($m) {
            $org = $m->organization;
            return [
                'id' => (string) $org->id,
                'name' => $org->name,
                'slug' => $org->slug,
                'description' => $org->description,
                'logo_url' => $org->logo_url,
                'role' => $m->role,
                'department' => $m->department,
                'client_company' => $m->client_company,
                'is_owner' => $org->owner_id === $m->user_id,
                'created_at' => $org->created_at->toISOString(),
            ];
        });

        return response()->json(['status' => 'success', 'data' => $orgs]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $slug = Str::slug($validated['name']) . '-' . Str::random(4);

        $org = Organization::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'owner_id' => $user->id,
        ]);

        OrganizationMember::create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'role' => 'OWNER',
            'department' => 'Executive',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => (string) $org->id,
                'name' => $org->name,
                'slug' => $org->slug,
                'role' => 'OWNER',
            ],
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $org = Organization::withCount(['members', 'projects'])->findOrFail($id);

        $membership = OrganizationMember::where('organization_id', $org->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => (string) $org->id,
                'name' => $org->name,
                'slug' => $org->slug,
                'description' => $org->description,
                'role' => $membership->role,
                'members_count' => $org->members_count,
                'projects_count' => $org->projects_count,
            ],
        ]);
    }

    public function members(Request $request, $id)
    {
        $org = Organization::findOrFail($id);
        $members = OrganizationMember::with(['user.projects'])
            ->where('organization_id', $org->id)
            ->get();

        $data = $members->map(function ($m) use ($org) {
            $user = $m->user;
            $assignedProjects = $user->projects()
                ->where('organization_id', $org->id)
                ->get()
                ->map(fn($p) => [
                    'id' => (string) $p->id,
                    'name' => $p->name,
                    'role' => $p->pivot->role,
                ]);

            return [
                'id' => (string) $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'job_title' => $user->job_title,
                'role' => $m->role, // OWNER, ADMIN, MEMBER, CLIENT
                'department' => $m->department,
                'client_company' => $m->client_company,
                'joined_at' => $m->joined_at ? $m->joined_at->toISOString() : $m->created_at->toISOString(),
                'assigned_projects' => $assignedProjects,
            ];
        });

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function addMember(Request $request, $id)
    {
        $currentUser = $request->user();
        $org = Organization::findOrFail($id);

        // Check permission: Only OWNER or ADMIN can add members
        $currentMembership = OrganizationMember::where('organization_id', $org->id)
            ->where('user_id', $currentUser->id)
            ->first();

        if (!$currentMembership || !in_array($currentMembership->role, ['OWNER', 'ADMIN'])) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized. Only Owners or Admins can enroll members.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'role' => 'required|in:ADMIN,MEMBER,CLIENT',
            'password' => 'nullable|string|min:6',
            'job_title' => 'nullable|string',
            'department' => 'nullable|string',
            'client_company' => 'nullable|string',
            'project_ids' => 'nullable|array',
            'project_ids.*' => 'integer',
        ]);

        $defaultPassword = !empty($validated['password']) ? $validated['password'] : 'password123';

        // Find or create user
        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($defaultPassword),
                'job_title' => $validated['job_title'] ?? ($validated['role'] === 'CLIENT' ? 'Client Representative' : 'Team Member'),
                'avatar_url' => 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($validated['name']),
                'timezone' => 'Asia/Kolkata',
            ]);
        }

        // Add to organization members
        $orgMember = OrganizationMember::updateOrCreate(
            ['organization_id' => $org->id, 'user_id' => $user->id],
            [
                'role' => $validated['role'],
                'department' => $validated['department'] ?? null,
                'client_company' => $validated['client_company'] ?? null,
                'joined_at' => now(),
            ]
        );

        // Assign to specified projects
        if (!empty($validated['project_ids'])) {
            $projectRole = $validated['role'] === 'CLIENT' ? 'PROJECT_CLIENT' : 'PROJECT_MEMBER';
            foreach ($validated['project_ids'] as $projId) {
                ProjectMember::firstOrCreate(
                    ['project_id' => $projId, 'user_id' => $user->id],
                    ['role' => $projectRole]
                );
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Member added successfully.',
            'data' => [
                'id' => (string) $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $orgMember->role,
                'department' => $orgMember->department,
                'client_company' => $orgMember->client_company,
            ],
        ], 201);
    }

    public function removeMember(Request $request, $id, $userId)
    {
        $currentUser = $request->user();
        $org = Organization::findOrFail($id);

        $currentMembership = OrganizationMember::where('organization_id', $org->id)
            ->where('user_id', $currentUser->id)
            ->first();

        if (!$currentMembership || !in_array($currentMembership->role, ['OWNER', 'ADMIN'])) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        if ($org->owner_id == $userId) {
            return response()->json(['status' => 'error', 'message' => 'Cannot remove organization owner.'], 400);
        }

        OrganizationMember::where('organization_id', $org->id)->where('user_id', $userId)->delete();
        ProjectMember::whereHas('project', fn($q) => $q->where('organization_id', $org->id))
            ->where('user_id', $userId)
            ->delete();

        return response()->json(['status' => 'success', 'message' => 'Member removed successfully.']);
    }
}

