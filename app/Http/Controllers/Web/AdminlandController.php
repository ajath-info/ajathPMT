<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Project;
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
        $orgId = session('active_org_id');
        $org = Organization::findOrFail($orgId);

        $currentMembership = OrganizationMember::where('organization_id', $org->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $userRole = $currentMembership->role;
        $totalProjects = Project::where('organization_id', $org->id)->count();
        $activeProjects = Project::where('organization_id', $org->id)->where('status', 'ACTIVE')->count();
        $archivedProjects = Project::where('organization_id', $org->id)->where('status', 'ARCHIVED')->count();
        $totalMembers = OrganizationMember::where('organization_id', $org->id)->count();

        return view('adminland.index', compact('org', 'user', 'userRole', 'totalProjects', 'activeProjects', 'archivedProjects', 'totalMembers'));
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
