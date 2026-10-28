<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\Project;
use App\Models\Discussion;
use App\Models\DiscussionComment;
use App\Models\ChatMessage;
use App\Models\Checkin;
use App\Models\CheckinAnswer;
use App\Models\Organization;

class MyStuffController extends Controller
{
    public function tasks(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id');

        $tasks = Task::with('project')
            ->whereHas('project', function ($q) use ($orgId) {
                if ($orgId) {
                    $q->where('organization_id', $orgId);
                }
            })
            ->where('assigned_to', $user->id)
            ->orderBy('due_date', 'asc')
            ->get();

        $openTasks = $tasks->where('status', '!=', 'DONE');
        $completedTasks = $tasks->where('status', 'DONE');

        return view('my.tasks', compact('user', 'tasks', 'openTasks', 'completedTasks'));
    }

    public function events(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id');

        // Upcoming task deadlines and check-ins
        $events = Task::with('project')
            ->whereHas('project', function ($q) use ($orgId) {
                if ($orgId) {
                    $q->where('organization_id', $orgId);
                }
            })
            ->whereNotNull('due_date')
            ->orderBy('due_date', 'asc')
            ->take(20)
            ->get();

        return view('my.events', compact('user', 'events'));
    }

    public function bookmarks(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id');

        $starredProjects = Project::where('organization_id', $orgId)
            ->where('is_pinned', true)
            ->with(['members', 'tasks'])
            ->get();

        return view('my.bookmarks', compact('user', 'starredProjects'));
    }

    public function activity(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id');

        $recentTasks = Task::with(['project', 'assignee'])
            ->whereHas('project', fn($q) => $q->where('organization_id', $orgId))
            ->latest()
            ->take(15)
            ->get();

        $recentDiscussions = Discussion::with(['project', 'author'])
            ->whereHas('project', fn($q) => $q->where('organization_id', $orgId))
            ->latest()
            ->take(10)
            ->get();

        $recentChats = ChatMessage::with(['project', 'user'])
            ->whereHas('project', fn($q) => $q->where('organization_id', $orgId))
            ->latest()
            ->take(15)
            ->get();

        return view('my.activity', compact('user', 'recentTasks', 'recentDiscussions', 'recentChats'));
    }

    public function notes(Request $request)
    {
        $user = Auth::user();
        $notes = session('user_notes_' . $user->id, "### My Quick Scratchpad\n- Follow up on client deliverables\n- Prepare sprint demo for Edward\n- Review QA reports for Dubbly & Budfit");

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
}
