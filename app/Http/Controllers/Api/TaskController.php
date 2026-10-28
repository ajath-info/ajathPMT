<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Project;

class TaskController extends Controller
{
    public function index(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $tasks = Task::with(['assignee', 'creator'])
            ->where('project_id', $project->id)
            ->orderBy('position')
            ->latest()
            ->get()
            ->map(fn($t) => [
                'id' => (string) $t->id,
                'project_id' => (string) $t->project_id,
                'list_name' => $t->list_name,
                'title' => $t->title,
                'description' => $t->description ?? '',
                'status' => $t->status,
                'priority' => $t->priority,
                'due_date' => $t->due_date,
                'assigned_to' => $t->assigned_to ? (string) $t->assigned_to : null,
                'assignee' => $t->assignee ? [
                    'id' => (string) $t->assignee->id,
                    'name' => $t->assignee->name,
                    'avatar_url' => $t->assignee->avatar_url,
                ] : null,
                'created_by' => (string) $t->created_by,
                'created_at' => $t->created_at->toISOString(),
            ]);

        return response()->json(['status' => 'success', 'data' => $tasks]);
    }

    public function store(Request $request, $projectId)
    {
        $user = $request->user();
        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'list_name' => 'nullable|string',
            'status' => 'nullable|in:TODO,IN_PROGRESS,DONE',
            'priority' => 'nullable|in:LOW,MEDIUM,HIGH,URGENT',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|integer|exists:users,id',
        ]);

        $task = Task::create([
            'project_id' => $project->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'list_name' => $validated['list_name'] ?? 'To-dos',
            'status' => $validated['status'] ?? 'TODO',
            'priority' => $validated['priority'] ?? 'MEDIUM',
            'due_date' => $validated['due_date'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'created_by' => $user->id,
        ]);

        return response()->json(['status' => 'success', 'data' => $task], 201);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:TODO,IN_PROGRESS,DONE',
            'priority' => 'nullable|in:LOW,MEDIUM,HIGH,URGENT',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|integer',
            'position' => 'nullable|integer',
        ]);

        $task->update($validated);

        return response()->json(['status' => 'success', 'data' => $task]);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['status' => 'success', 'message' => 'Task deleted.']);
    }
}

