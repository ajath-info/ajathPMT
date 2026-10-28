<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Project;

class DocumentController extends Controller
{
    public function index($projectId)
    {
        $project = Project::findOrFail($projectId);
        $docs = Document::with('author')
            ->where('project_id', $project->id)
            ->latest()
            ->get()
            ->map(fn($d) => [
                'id' => (string) $d->id,
                'project_id' => (string) $d->project_id,
                'title' => $d->title,
                'content' => $d->content ?? '',
                'author' => [
                    'id' => (string) $d->author->id,
                    'name' => $d->author->name,
                    'avatar_url' => $d->author->avatar_url,
                ],
                'created_at' => $d->created_at->toISOString(),
            ]);

        return response()->json(['status' => 'success', 'data' => $docs]);
    }

    public function store(Request $request, $projectId)
    {
        $user = $request->user();
        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $doc = Document::create([
            'project_id' => $project->id,
            'title' => $validated['title'],
            'content' => $validated['content'] ?? '',
            'author_id' => $user->id,
        ]);

        return response()->json(['status' => 'success', 'data' => $doc], 201);
    }

    public function show($id)
    {
        $doc = Document::with('author')->findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $doc]);
    }
}

