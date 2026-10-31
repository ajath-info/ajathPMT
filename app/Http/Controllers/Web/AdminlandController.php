<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Project;
use App\Models\User;
use App\Services\BasecampImporterService;

class AdminlandController extends Controller
{
    protected BasecampImporterService $importer;

    public function __construct(BasecampImporterService $importer)
    {
        $this->importer = $importer;
    }

    public function index()
    {
        $user = Auth::user();
        $orgId = session('active_org_id') ?: ($user->organizations()->first()?->id ?? 1);
        $org = Organization::find($orgId) ?? Organization::first();

        $currentMembership = OrganizationMember::where('organization_id', $org->id)
            ->where('user_id', $user->id)
            ->first();

        $userRole = $currentMembership->role ?? 'ADMIN';
        $totalProjects = Project::where('organization_id', $org->id)->count();
        $activeProjects = Project::where('organization_id', $org->id)->where('status', 'ACTIVE')->count();
        $archivedProjects = Project::where('organization_id', $org->id)->where('status', 'ARCHIVED')->count();
        $totalMembers = OrganizationMember::where('organization_id', $org->id)->count();

        return view('adminland.index', compact('org', 'user', 'userRole', 'totalProjects', 'activeProjects', 'archivedProjects', 'totalMembers'));
    }

    public function administrators()
    {
        $user = Auth::user();
        $orgId = session('active_org_id') ?: ($user->organizations()->first()?->id ?? 1);
        $org = Organization::findOrFail($orgId);

        $members = OrganizationMember::with('user')
            ->where('organization_id', $org->id)
            ->get();

        return view('adminland.administrators', compact('org', 'user', 'members'));
    }

    public function grantAdministrator(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id') ?: ($user->organizations()->first()?->id ?? 1);
        $org = Organization::findOrFail($orgId);

        $targetUserId = (int) $request->input('user_id');
        if ($targetUserId > 0) {
            OrganizationMember::updateOrCreate(
                ['organization_id' => $org->id, 'user_id' => $targetUserId],
                ['role' => 'ADMIN', 'joined_at' => now()]
            );
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('adminland.administrators');
    }

    public function revokeAdministrator(Request $request, $userId)
    {
        $user = Auth::user();
        $orgId = session('active_org_id') ?: ($user->organizations()->first()?->id ?? 1);
        $org = Organization::findOrFail($orgId);

        OrganizationMember::where('organization_id', $org->id)
            ->where('user_id', (int) $userId)
            ->where('role', '!=', 'OWNER')
            ->update(['role' => 'MEMBER']);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('adminland.administrators');
    }

    public function userGroups(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id') ?: ($user->organizations()->first()?->id ?? 1);
        $org = Organization::findOrFail($orgId);
        $initialView = 'list';

        return view('adminland.groups', compact('org', 'user', 'initialView'));
    }

    public function createUserGroup(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id') ?: ($user->organizations()->first()?->id ?? 1);
        $org = Organization::findOrFail($orgId);
        $initialView = 'new';

        return view('adminland.groups', compact('org', 'user', 'initialView'));
    }

    public function companies(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id') ?: ($user->organizations()->first()?->id ?? 1);
        $org = Organization::findOrFail($orgId);

        return view('adminland.companies', compact('org', 'user'));
    }

    public function customToolNames(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id') ?: ($user->organizations()->first()?->id ?? 1);
        $org = Organization::findOrFail($orgId);

        return view('adminland.tools', compact('org', 'user'));
    }

    public function categories(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id') ?: ($user->organizations()->first()?->id ?? 1);
        $org = Organization::findOrFail($orgId);

        return view('adminland.categories', compact('org', 'user'));
    }

    public function voiceNotes(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id') ?: ($user->organizations()->first()?->id ?? 1);
        $org = Organization::findOrFail($orgId);

        return view('adminland.voice_notes', compact('org', 'user'));
    }

    public function personMerges(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id') ?: ($user->organizations()->first()?->id ?? 1);
        $org = Organization::findOrFail($orgId);
        $people = User::orderBy('name')->get();

        return view('adminland.person_merges', compact('org', 'user', 'people'));
    }

    public function retention(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id') ?: ($user->organizations()->first()?->id ?? 1);
        $org = Organization::findOrFail($orgId);

        return view('adminland.retention', compact('org', 'user'));
    }

    public function templates(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id') ?: ($user->organizations()->first()?->id ?? 1);
        $org = Organization::findOrFail($orgId);
        $initialTab = $request->query('tab', 'project');
        $initialView = 'list';
        if ($request->is('template_library/todolists/new')) {
            $initialTab = 'todolists';
            $initialView = 'new_todolist';
        } elseif ($request->is('template_library/card_tables/new')) {
            $initialTab = 'card_tables';
            $initialView = 'new_card_table';
        } elseif ($request->is('templates/new')) {
            $initialTab = 'project';
            $initialView = 'new_project';
        } elseif ($request->is('template_library/todolists')) {
            $initialTab = 'todolists';
        } elseif ($request->is('template_library/card_tables')) {
            $initialTab = 'card_tables';
        }

        return view('adminland.templates', compact('org', 'user', 'initialTab', 'initialView'));
    }

    public function showTemplate(Request $request, $id)
    {
        $user = Auth::user();
        $orgId = session('active_org_id') ?: ($user->organizations()->first()?->id ?? 1);
        $org = Organization::findOrFail($orgId);
        $initialTab = 'project';
        $initialView = 'detail';

        return view('adminland.templates', compact('org', 'user', 'initialTab', 'initialView'));
    }

    public function importView()
    {
        $user = Auth::user();
        $orgId = session('active_org_id');
        $org = Organization::findOrFail($orgId);

        $currentMembership = OrganizationMember::where('organization_id', $org->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (!in_array($currentMembership->role, ['OWNER', 'ADMIN'])) {
            abort(403, 'Only Owners and Administrators can import or export data.');
        }

        $userRole = $currentMembership->role;
        return view('adminland.import', compact('org', 'user', 'userRole'));
    }

    public function importZip(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id');
        $org = Organization::findOrFail($orgId);

        $request->validate([
            'zip_file' => 'required|file|mimes:zip|max:102400', // up to 100MB
        ]);

        try {
            $file = $request->file('zip_file');
            $stats = $this->importer->importFromZip($file->getRealPath(), $org->id, $user->id);

            return redirect()->route('adminland.import')->with('success', "Import complete! Imported {$stats['projects_imported']} projects, {$stats['tasks_imported']} to-dos, {$stats['discussions_imported']} discussions, {$stats['docs_imported']} docs, and {$stats['people_imported']} people.");
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function importApi(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id');
        $org = Organization::findOrFail($orgId);

        $validated = $request->validate([
            'account_id' => 'required|string',
            'api_token' => 'required|string',
        ]);

        try {
            $stats = $this->importer->importFromApi($validated['api_token'], $validated['account_id'], $org->id, $user->id);

            return redirect()->route('adminland.import')->with('success', "Basecamp API sync complete! Imported {$stats['projects_imported']} projects, {$stats['tasks_imported']} to-dos, {$stats['discussions_imported']} discussions, {$stats['docs_imported']} docs, and {$stats['people_imported']} people.");
        } catch (\Exception $e) {
            return back()->with('error', 'API Sync failed: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $user = Auth::user();
        $orgId = session('active_org_id');
        $org = Organization::findOrFail($orgId);

        $currentMembership = OrganizationMember::where('organization_id', $org->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (!in_array($currentMembership->role, ['OWNER', 'ADMIN'])) {
            abort(403, 'Unauthorized.');
        }

        try {
            $zipPath = $this->importer->exportWorkspaceZip($org->id);
            return response()->download($zipPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }
}

