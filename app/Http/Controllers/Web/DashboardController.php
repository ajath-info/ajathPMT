<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Project;
use App\Models\Task;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get user's organizations
        $memberships = OrganizationMember::with('organization')
            ->where('user_id', $user->id)
            ->get();

        if ($memberships->isEmpty()) {
            return view('dashboard_empty');
        }

        // Active organization from session or first membership
        $activeOrgId = session('active_org_id', $memberships->first()->organization_id);
        $currentMembership = $memberships->firstWhere('organization_id', $activeOrgId) ?? $memberships->first();
        $currentOrg = $currentMembership->organization;
        $userRole = $currentMembership->role; // OWNER, ADMIN, MEMBER, CLIENT

        session([
            'active_org_id' => $currentOrg->id,
            'active_org_name' => $currentOrg->name,
            'user_role' => $userRole,
        ]);

        // Strict Project Access Control
        $projectQuery = Project::with(['members', 'creator'])
            ->withCount(['tasks', 'discussions', 'documents'])
            ->where('organization_id', $currentOrg->id);

        if (!in_array($userRole, ['OWNER', 'ADMIN'])) {
            $projectQuery->whereHas('members', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $allProjects = $projectQuery->get();

        // 1. Starred (yellow) projects: shown on top, sorted by name ascending (A-Z)
        $pinnedProjects = $allProjects->where('is_pinned', true)
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        // 2. Other (unstarred) projects: shown below, sorted by date descending (latest first)
        $otherProjects = $allProjects->where('is_pinned', false)
            ->sortByDesc('created_at')
            ->values();

        // Merged in order: Starred on top (A-Z), Other below (date DESC)
        $projects = $pinnedProjects->concat($otherProjects);

        // Calculate project progress
        foreach ($projects as $proj) {
            $done = $proj->tasks()->where('status', 'DONE')->count();
            $total = $proj->tasks_count;
            $proj->progress = $total > 0 ? round(($done / $total) * 100) : 0;
            $proj->is_client_project = $proj->members->contains(function ($m) {
                return $m->pivot->role === 'PROJECT_CLIENT';
            });
        }

        // People counts
        $totalMembers = OrganizationMember::where('organization_id', $currentOrg->id)
            ->whereIn('role', ['OWNER', 'ADMIN', 'MEMBER'])
            ->count();

        $totalClients = OrganizationMember::where('organization_id', $currentOrg->id)
            ->where('role', 'CLIENT')
            ->count();

        // Recent user tasks
        $myTasks = Task::with('project')
            ->whereHas('project', fn($q) => $q->where('organization_id', $currentOrg->id))
            ->where('assigned_to', $user->id)
            ->where('status', '!=', 'DONE')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'user',
            'currentOrg',
            'userRole',
            'memberships',
            'projects',
            'totalMembers',
            'totalClients',
            'myTasks'
        ));
    }

    public function switchOrg($id)
    {
        $user = Auth::user();
        $mem = OrganizationMember::where('organization_id', $id)
            ->where('user_id', $user->id)
            ->first();

        if ($mem) {
            session([
                'active_org_id' => $id,
                'user_role' => $mem->role,
            ]);
        }

        return redirect()->route('dashboard');
    }

    public function updateLogo(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id');
        $org = Organization::findOrFail($orgId);

        $membership = OrganizationMember::where('organization_id', $org->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$membership || !in_array($membership->role, ['OWNER', 'ADMIN'])) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return back()->with('error', 'Only owners and admins can update the workspace logo.');
        }

        // Mode: Just display company name
        if ($request->input('mode') === 'text') {
            $org->logo_url = 'text_only';
            $org->save();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'mode' => 'text',
                    'company_name' => $org->name
                ]);
            }
            return back()->with('success', 'Workspace set to display company name.');
        }

        // Mode: Upload image
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg,webp,gif|max:5120',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $uploadDir = public_path('uploads/logos');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $filename = 'logo_' . $org->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);

            $rootUploadDir = base_path('uploads/logos');
            if (!is_dir($rootUploadDir)) {
                mkdir($rootUploadDir, 0777, true);
            }
            @copy($uploadDir . '/' . $filename, $rootUploadDir . '/' . $filename);

            $org->logo_url = '/uploads/logos/' . $filename;
            $org->save();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'mode' => 'logo',
                    'logo_url' => asset('uploads/logos/' . $filename)
                ]);
            }

            return back()->with('success', 'Workspace logo updated successfully!');
        }

        return back();
    }
}
