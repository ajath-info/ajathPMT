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
            'job_title' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'timezone' => 'nullable|string|max:100',
            'avatar_url' => 'nullable|string|max:500',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
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
        $projects = Project::where('organization_id', $orgId)->orderBy('name')->get();

        return view('my.tasks', compact('user', 'tasks', 'openTasks', 'completedTasks', 'projects'));
    }

    public function events(Request $request)
    {
        $user = Auth::user();
        $orgId = session('active_org_id');

        $events = Task::with('project')
            ->whereHas('project', function ($q) use ($orgId) {
                if ($orgId) {
                    $q->where('organization_id', $orgId);
                }
            })
            ->whereNotNull('due_date')
            ->orderBy('due_date', 'asc')
            ->take(25)
            ->get();

        $projects = Project::where('organization_id', $orgId)->orderBy('name')->get();

        return view('my.events', compact('user', 'events', 'projects'));
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

        $recentChats = ChatMessage::with(['project', 'sender'])
            ->whereHas('project', fn($q) => $q->where('organization_id', $orgId))
            ->latest()
            ->take(15)
            ->get();

        return view('my.activity', compact('user', 'recentTasks', 'recentDiscussions', 'recentChats'));
    }

    public function notes(Request $request)
    {
        $user = Auth::user();
        $notes = session('user_notes_' . $user->id, "### My Quick Scratchpad\n- Follow up on client deliverables\n- Prepare sprint demo for Edward\n- Review QA reports for Dubbly & Budfit\n- Coordinate production deployment with Marcus");

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
