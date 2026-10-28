<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Task;
use App\Models\Discussion;
use App\Models\DiscussionComment;
use App\Models\Document;
use App\Models\ChatMessage;

class ProjectController extends Controller
{
    private function checkProjectAccess($project, $user)
    {
        $orgMembership = OrganizationMember::where('organization_id', $project->organization_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$orgMembership) {
            abort(403, 'Unauthorized. Not a member of this organization.');
        }

        if (in_array($orgMembership->role, ['OWNER', 'ADMIN'])) {
            return $orgMembership->role;
        }

        $isMember = ProjectMember::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isMember) {
            abort(403, 'Unauthorized. You do not have access to this project.');
        }

        return $orgMembership->role;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id');
        $org = Organization::findOrFail($orgId);

        $membership = OrganizationMember::where('organization_id', $org->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $query = Project::where('organization_id', $org->id)
            ->with(['members', 'tasks']);

        if (!in_array($membership->role, ['OWNER', 'ADMIN'])) {
            $query->whereHas('members', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $allProjects = $query->orderBy('name', 'asc')->get();

        $filter = $request->query('filter', 'active'); // 'active' or 'archived'

        $activeProjects = $allProjects->where('status', 'ACTIVE')->values();
        $archivedProjects = $allProjects->where('status', 'ARCHIVED')->values();

        $displayProjects = ($filter === 'archived') ? $archivedProjects : $activeProjects;

        // Group alphabetically by first character
        $groupedProjects = $displayProjects->groupBy(function ($project) {
            $first = strtoupper(substr(trim($project->name), 0, 1));
            return ctype_alpha($first) ? $first : '#';
        })->sortKeys();

        // Distinct available letters for quick jump bar
        $availableLetters = $groupedProjects->keys()->toArray();

        return view('projects.index', compact(
            'org',
            'user',
            'membership',
            'groupedProjects',
            'activeProjects',
            'archivedProjects',
            'availableLetters',
            'filter'
        ));
    }

    public function create()
    {
        $user = Auth::user();
        $orgId = session('active_org_id');
        $org = Organization::findOrFail($orgId);

        $membership = OrganizationMember::where('organization_id', $org->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($membership->role === 'CLIENT') {
            abort(403, 'Clients cannot create projects.');
        }

        $employees = OrganizationMember::with('user')
            ->where('organization_id', $org->id)
            ->whereIn('role', ['OWNER', 'ADMIN', 'MEMBER'])
            ->get();

        $clients = OrganizationMember::with('user')
            ->where('organization_id', $org->id)
            ->where('role', 'CLIENT')
            ->get();

        return view('projects.create', compact('org', 'employees', 'clients'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id');
        $org = Organization::findOrFail($orgId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_subtitle' => 'nullable|string|max:255',
            'color_theme' => 'nullable|string|max:50',
            'cover_url' => 'nullable|string',
            'visibility' => 'nullable|in:ORGANIZATION,PRIVATE',
            'member_ids' => 'nullable|array',
            'client_ids' => 'nullable|array',
        ]);

        $slug = Str::slug($validated['name']) . '-' . Str::random(4);

        $project = Project::create([
            'organization_id' => $org->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'client_subtitle' => $validated['client_subtitle'] ?? null,
            'color_theme' => $validated['color_theme'] ?? 'white',
            'is_pinned' => false,
            'cover_url' => $validated['cover_url'] ?? 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?w=800',
            'status' => 'ACTIVE',
            'visibility' => $validated['visibility'] ?? 'ORGANIZATION',
            'created_by' => $user->id,
        ]);

        // Add creator as lead
        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'role' => 'PROJECT_LEAD',
        ]);

        // Add employees
        if (!empty($validated['member_ids'])) {
            foreach ($validated['member_ids'] as $mId) {
                if ($mId != $user->id) {
                    ProjectMember::firstOrCreate([
                        'project_id' => $project->id,
                        'user_id' => $mId,
                    ], ['role' => 'PROJECT_MEMBER']);
                }
            }
        }

        // Add clients
        if (!empty($validated['client_ids'])) {
            foreach ($validated['client_ids'] as $cId) {
                ProjectMember::firstOrCreate([
                    'project_id' => $project->id,
                    'user_id' => $cId,
                ], ['role' => 'PROJECT_CLIENT']);
            }
        }

        return redirect()->route('projects.show', $project->id)->with('success', 'Project created successfully!');
    }

    public function togglePin($id)
    {
        $user = Auth::user();
        $project = Project::findOrFail($id);
        $this->checkProjectAccess($project, $user);

        $project->is_pinned = !$project->is_pinned;
        $project->save();

        if (request()->wantsJson()) {
            return response()->json(['is_pinned' => $project->is_pinned]);
        }

        return back();
    }

    public function updateColor(Request $request, $id)
    {
        $user = Auth::user();
        $project = Project::findOrFail($id);
        $this->checkProjectAccess($project, $user);

        $validated = $request->validate([
            'color_theme' => 'required|string|in:white,yellow,orange,coral,pink,purple,blue,green,sand,gray'
        ]);

        $project->color_theme = $validated['color_theme'];
        $project->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'color_theme' => $project->color_theme
            ]);
        }

        return back()->with('success', 'Project color updated.');
    }

    public function archive($id)
    {
        $user = Auth::user();
        $project = Project::findOrFail($id);
        $this->checkProjectAccess($project, $user);

        $project->update(['status' => 'ARCHIVED']);

        return back()->with('success', 'Project "' . $project->name . '" archived.');
    }

    public function unarchive($id)
    {
        $user = Auth::user();
        $project = Project::findOrFail($id);
        $this->checkProjectAccess($project, $user);

        $project->update(['status' => 'ACTIVE']);

        return back()->with('success', 'Project "' . $project->name . '" restored to active.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $project = Project::findOrFail($id);
        $role = $this->checkProjectAccess($project, $user);

        if (!in_array($role, ['OWNER', 'ADMIN'])) {
            abort(403, 'Only admins or owners can delete a project.');
        }

        $projectName = $project->name;
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project "' . $projectName . '" moved to trash.');
    }

    public function show($id)
    {
        $user = Auth::user();
        $project = Project::with(['members', 'creator'])->findOrFail($id);
        $userRole = $this->checkProjectAccess($project, $user);

        // Fetch tool data previews
        $tasks = Task::with('assignee')
            ->where('project_id', $project->id)
            ->latest()
            ->take(6)
            ->get();

        $discussions = Discussion::with('author')
            ->where('project_id', $project->id)
            ->latest()
            ->take(4)
            ->get();

        $docs = Document::with('author')
            ->where('project_id', $project->id)
            ->latest()
            ->take(4)
            ->get();

        $messages = ChatMessage::with('sender')
            ->where('project_id', $project->id)
            ->where('type', 'project')
            ->latest()
            ->take(15)
            ->get()
            ->reverse();

        $doneTasks = Task::where('project_id', $project->id)->where('status', 'DONE')->count();
        $totalTasks = Task::where('project_id', $project->id)->count();
        $progress = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;

        return view('projects.show', compact(
            'project',
            'user',
            'userRole',
            'tasks',
            'discussions',
            'docs',
            'messages',
            'progress',
            'totalTasks',
            'doneTasks'
        ));
    }

    // TASKS
    public function tasks($id)
    {
        $user = Auth::user();
        $project = Project::with('members')->findOrFail($id);
        $userRole = $this->checkProjectAccess($project, $user);

        $tasks = Task::with(['assignee', 'creator'])
            ->where('project_id', $project->id)
            ->orderByRaw("FIELD(status, 'TODO', 'IN_PROGRESS', 'DONE')")
            ->latest()
            ->get();

        return view('projects.tasks', compact('project', 'user', 'userRole', 'tasks'));
    }

    public function storeTask(Request $request, $id)
    {
        $user = Auth::user();
        $project = Project::findOrFail($id);
        $this->checkProjectAccess($project, $user);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:LOW,MEDIUM,HIGH,URGENT',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        Task::create([
            'project_id' => $project->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'status' => 'TODO',
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'Task created successfully!');
    }

    public function toggleTask(Request $request, $id, $taskId)
    {
        $user = Auth::user();
        $project = Project::findOrFail($id);
        $this->checkProjectAccess($project, $user);

        $task = Task::where('project_id', $project->id)->findOrFail($taskId);
        $task->status = $task->status === 'DONE' ? 'TODO' : 'DONE';
        $task->save();

        return back()->with('success', 'Task status updated!');
    }

    public function deleteTask(Request $request, $id, $taskId)
    {
        $user = Auth::user();
        $project = Project::findOrFail($id);
        $this->checkProjectAccess($project, $user);

        Task::where('project_id', $project->id)->findOrFail($taskId)->delete();
        return back()->with('success', 'Task deleted.');
    }

    // DISCUSSIONS
    public function discussions($id)
    {
        $user = Auth::user();
        $project = Project::findOrFail($id);
        $userRole = $this->checkProjectAccess($project, $user);

        $discussions = Discussion::with(['author', 'comments'])
            ->where('project_id', $project->id)
            ->latest()
            ->get();

        return view('projects.discussions', compact('project', 'user', 'userRole', 'discussions'));
    }

    public function storeDiscussion(Request $request, $id)
    {
        $user = Auth::user();
        $project = Project::findOrFail($id);
        $this->checkProjectAccess($project, $user);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'content' => 'required|string',
        ]);

        Discussion::create([
            'project_id' => $project->id,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'content' => $validated['content'],
            'author_id' => $user->id,
        ]);

        return back()->with('success', 'Discussion message posted!');
    }

    public function showDiscussion($id, $discId)
    {
        $user = Auth::user();
        $project = Project::findOrFail($id);
        $userRole = $this->checkProjectAccess($project, $user);

        $discussion = Discussion::with(['author', 'comments.author'])
            ->where('project_id', $project->id)
            ->findOrFail($discId);

        return view('projects.discussion_detail', compact('project', 'user', 'userRole', 'discussion'));
    }

    public function storeComment(Request $request, $id, $discId)
    {
        $user = Auth::user();
        $project = Project::findOrFail($id);
        $this->checkProjectAccess($project, $user);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        DiscussionComment::create([
            'discussion_id' => $discId,
            'author_id' => $user->id,
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Comment added.');
    }

    // DOCS
    public function docs($id)
    {
        $user = Auth::user();
        $project = Project::findOrFail($id);
        $userRole = $this->checkProjectAccess($project, $user);

        $docs = Document::with('author')
            ->where('project_id', $project->id)
            ->latest()
            ->get();

        return view('projects.docs', compact('project', 'user', 'userRole', 'docs'));
    }

    public function storeDoc(Request $request, $id)
    {
        $user = Auth::user();
        $project = Project::findOrFail($id);
        $this->checkProjectAccess($project, $user);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        Document::create([
            'project_id' => $project->id,
            'title' => $validated['title'],
            'content' => $validated['content'] ?? '',
            'author_id' => $user->id,
        ]);

        return back()->with('success', 'Document created!');
    }

    // CHAT
    public function chat($id)
    {
        $user = Auth::user();
        $project = Project::with('members')->findOrFail($id);
        $userRole = $this->checkProjectAccess($project, $user);

        $messages = ChatMessage::with('sender')
            ->where('project_id', $project->id)
            ->where('type', 'project')
            ->latest()
            ->take(50)
            ->get()
            ->reverse();

        return view('projects.chat', compact('project', 'user', 'userRole', 'messages'));
    }

    public function storeChat(Request $request, $id)
    {
        $user = Auth::user();
        $project = Project::findOrFail($id);
        $this->checkProjectAccess($project, $user);

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        ChatMessage::create([
            'project_id' => $project->id,
            'organization_id' => $project->organization_id,
            'sender_id' => $user->id,
            'content' => $validated['content'],
            'type' => 'project',
        ]);

        return back()->with('success', 'Message sent.');
    }

    // SCHEDULE
    public function schedule($id)
    {
        $user = Auth::user();
        $project = Project::with('members')->findOrFail($id);
        $userRole = $this->checkProjectAccess($project, $user);

        $tasks = Task::with('assignee')
            ->where('project_id', $project->id)
            ->whereNotNull('due_date')
            ->orderBy('due_date', 'asc')
            ->get();

        return view('projects.schedule', compact('project', 'user', 'userRole', 'tasks'));
    }

    // AUTOMATIC CHECK-INS
    public function checkins($id)
    {
        $user = Auth::user();
        $project = Project::with('members')->findOrFail($id);
        $userRole = $this->checkProjectAccess($project, $user);

        $checkin = \App\Models\Checkin::firstOrCreate(
            ['project_id' => $project->id, 'question' => 'What did you work on today?'],
            ['frequency' => 'DAILY', 'time' => '17:00:00', 'is_active' => true]
        );

        $answers = \App\Models\CheckinAnswer::with('user')
            ->where('checkin_id', $checkin->id)
            ->latest()
            ->take(30)
            ->get();

        return view('projects.checkins', compact('project', 'user', 'userRole', 'checkin', 'answers'));
    }

    public function storeCheckinAnswer(Request $request, $id)
    {
        $user = Auth::user();
        $project = Project::findOrFail($id);
        $this->checkProjectAccess($project, $user);

        $validated = $request->validate([
            'checkin_id' => 'required|integer',
            'answer' => 'required|string|max:5000',
        ]);

        \App\Models\CheckinAnswer::create([
            'checkin_id' => $validated['checkin_id'],
            'user_id' => $user->id,
            'answer' => $validated['answer'],
        ]);

        return back()->with('success', 'Check-in response posted!');
    }
}

