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

        $projects = $projectQuery->latest()->get();

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
}
