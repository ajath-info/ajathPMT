<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $orgId = $request->query('organization_id');

        if (!$orgId) {
            $membership = OrganizationMember::where('user_id', $user->id)->first();
            $orgId = $membership ? $membership->organization_id : null;
        }

        if (!$orgId) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $membership = OrganizationMember::where('organization_id', $orgId)
            ->where('user_id', $user->id)
            ->first();

        if (!$membership) {
            return response()->json(['status' => 'error', 'message' => 'Not a member of this organization.'], 403);
        }

        $query = Project::with(['creator', 'members'])
            ->withCount(['tasks', 'discussions', 'documents'])
            ->where('organization_id', $orgId);

        // Strict Access Control:
        // If OWNER or ADMIN, view all projects in the company.
        // If MEMBER or CLIENT, view ONLY projects they are explicitly assigned to!
        if (!in_array($membership->role, ['OWNER', 'ADMIN'])) {
            $query->whereHas('members', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $projects = $query->latest()->get()->map(function ($p) use ($user) {
            $userRoleInProject = $p->members->firstWhere('id', $user->id)?->pivot->role ?? 'PROJECT_MEMBER';
            $doneTasks = $p->tasks()->where('status', 'DONE')->count();
            $totalTasks = $p->tasks_count;
            $progress = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;

            return [
                'id' => (string) $p->id,
                'organization_id' => (string) $p->organization_id,
                'name' => $p->name,
                'slug' => $p->slug,
                'description' => $p->description ?? '',
                'cover_url' => $p->cover_url,
                'status' => $p->status,
                'visibility' => $p->visibility,
                'start_date' => $p->start_date,
                'created_by' => (string) $p->created_by,
                'created_at' => $p->created_at->toISOString(),
                'members_count' => $p->members->count(),
                'progress' => $progress,
                'user_project_role' => $userRoleInProject,
                'members' => $p->members->map(fn($m) => [
                    'id' => (string) $m->id,
                    'name' => $m->name,
                    'avatar_url' => $m->avatar_url,
                    'email' => $m->email,
                    'role' => $m->pivot->role,
                ]),
            ];
        });

        return response()->json(['status' => 'success', 'data' => $projects]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'organization_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_url' => 'nullable|string',
            'visibility' => 'nullable|in:ORGANIZATION,PRIVATE',
            'member_ids' => 'nullable|array',
            'client_ids' => 'nullable|array',
        ]);

        $membership = OrganizationMember::where('organization_id', $validated['organization_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$membership || $membership->role === 'CLIENT') {
            return response()->json(['status' => 'error', 'message' => 'Clients cannot create projects.'], 403);
        }

        $slug = Str::slug($validated['name']) . '-' . Str::random(4);

        $project = Project::create([
            'organization_id' => $validated['organization_id'],
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'cover_url' => $validated['cover_url'] ?? 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?w=800',
            'status' => 'ACTIVE',
            'visibility' => $validated['visibility'] ?? 'ORGANIZATION',
            'created_by' => $user->id,
        ]);

        // Add creator as PROJECT_LEAD
        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'role' => 'PROJECT_LEAD',
        ]);

        // Add team members
        if (!empty($validated['member_ids'])) {
            foreach ($validated['member_ids'] as $mId) {
                if ($mId != $user->id) {
                    ProjectMember::firstOrCreate([
                        'project_id' => $project->id,
                        'user_id' => $mId,
                    ], [
                        'role' => 'PROJECT_MEMBER',
                    ]);
                }
            }
        }

        // Add clients
        if (!empty($validated['client_ids'])) {
            foreach ($validated['client_ids'] as $cId) {
                ProjectMember::firstOrCreate([
                    'project_id' => $project->id,
                    'user_id' => $cId,
                ], [
                    'role' => 'PROJECT_CLIENT',
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => (string) $project->id,
                'name' => $project->name,
                'slug' => $project->slug,
            ],
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $project = Project::with(['members', 'creator'])->findOrFail($id);

        $orgMembership = OrganizationMember::where('organization_id', $project->organization_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // If not OWNER/ADMIN, user must be a member of this project
        if (!in_array($orgMembership->role, ['OWNER', 'ADMIN'])) {
            $isMember = $project->members->contains('id', $user->id);
            if (!$isMember) {
                return response()->json(['status' => 'error', 'message' => 'Access denied to this project.'], 403);
            }
        }

        $doneTasks = $project->tasks()->where('status', 'DONE')->count();
        $totalTasks = $project->tasks()->count();
        $progress = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => (string) $project->id,
                'organization_id' => (string) $project->organization_id,
                'name' => $project->name,
                'slug' => $project->slug,
                'description' => $project->description ?? '',
                'cover_url' => $project->cover_url,
                'status' => $project->status,
                'visibility' => $project->visibility,
                'progress' => $progress,
                'members_count' => $project->members->count(),
                'members' => $project->members->map(fn($m) => [
                    'id' => (string) $m->id,
                    'name' => $m->name,
                    'email' => $m->email,
                    'avatar_url' => $m->avatar_url,
                    'job_title' => $m->job_title,
                    'role' => $m->pivot->role,
                ]),
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:ACTIVE,ON_HOLD,COMPLETED,ARCHIVED',
            'cover_url' => 'nullable|string',
        ]);

        $project->update($validated);

        return response()->json(['status' => 'success', 'data' => $project]);
    }

    public function destroy(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return response()->json(['status' => 'success', 'message' => 'Project deleted.']);
    }

    public function addMember(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'role' => 'nullable|in:PROJECT_LEAD,PROJECT_MEMBER,PROJECT_CLIENT',
        ]);

        ProjectMember::updateOrCreate(
            ['project_id' => $project->id, 'user_id' => $validated['user_id']],
            ['role' => $validated['role'] ?? 'PROJECT_MEMBER']
        );

        return response()->json(['status' => 'success', 'message' => 'Member added to project.']);
    }

    public function removeMember(Request $request, $id, $userId)
    {
        $project = Project::findOrFail($id);
        ProjectMember::where('project_id', $project->id)->where('user_id', $userId)->delete();

        return response()->json(['status' => 'success', 'message' => 'Member removed from project.']);
    }
}

