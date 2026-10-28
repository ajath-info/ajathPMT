<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Discussion;
use App\Models\DiscussionComment;
use App\Models\Project;

class DiscussionController extends Controller
{
    public function index($projectId)
    {
        $project = Project::findOrFail($projectId);
        $discussions = Discussion::with(['author', 'comments.author'])
            ->where('project_id', $project->id)
            ->latest()
            ->get()
            ->map(fn($d) => [
                'id' => (string) $d->id,
                'project_id' => (string) $d->project_id,
                'category' => $d->category,
                'title' => $d->title,
                'content' => $d->content,
                'is_pinned' => (bool) $d->is_pinned,
                'author' => [
                    'id' => (string) $d->author->id,
                    'name' => $d->author->name,
                    'avatar_url' => $d->author->avatar_url,
                ],
                'comments_count' => $d->comments->count(),
                'created_at' => $d->created_at->toISOString(),
            ]);

        return response()->json(['status' => 'success', 'data' => $discussions]);
    }

    public function store(Request $request, $projectId)
    {
        $user = $request->user();
        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string',
        ]);

        $disc = Discussion::create([
            'project_id' => $project->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category' => $validated['category'] ?? 'General',
            'author_id' => $user->id,
        ]);

        return response()->json(['status' => 'success', 'data' => $disc], 201);
    }

    public function show($id)
    {
        $d = Discussion::with(['author', 'comments.author'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => (string) $d->id,
                'project_id' => (string) $d->project_id,
                'category' => $d->category,
                'title' => $d->title,
                'content' => $d->content,
                'is_pinned' => (bool) $d->is_pinned,
                'author' => [
                    'id' => (string) $d->author->id,
                    'name' => $d->author->name,
                    'avatar_url' => $d->author->avatar_url,
                ],
                'comments' => $d->comments->map(fn($c) => [
                    'id' => (string) $c->id,
                    'content' => $c->content,
                    'created_at' => $c->created_at->toISOString(),
                    'author' => [
                        'id' => (string) $c->author->id,
                        'name' => $c->author->name,
                        'avatar_url' => $c->author->avatar_url,
                    ],
                ]),
                'created_at' => $d->created_at->toISOString(),
            ],
        ]);
    }

    public function addComment(Request $request, $id)
    {
        $user = $request->user();
        $disc = Discussion::findOrFail($id);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $comment = DiscussionComment::create([
            'discussion_id' => $disc->id,
            'author_id' => $user->id,
            'content' => $validated['content'],
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => (string) $comment->id,
                'content' => $comment->content,
                'author' => [
                    'id' => (string) $user->id,
                    'name' => $user->name,
                    'avatar_url' => $user->avatar_url,
                ],
                'created_at' => $comment->created_at->toISOString(),
            ],
        ], 201);
    }
}

