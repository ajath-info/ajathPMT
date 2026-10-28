<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatMessage;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $projectId = $request->query('project_id');
        $recipientId = $request->query('recipient_id');

        $query = ChatMessage::with(['sender', 'recipient'])->latest();

        if ($projectId) {
            $query->where('project_id', $projectId)->where('type', 'project');
        } elseif ($recipientId) {
            $query->where(function ($q) use ($user, $recipientId) {
                $q->where('sender_id', $user->id)->where('recipient_id', $recipientId);
            })->orWhere(function ($q) use ($user, $recipientId) {
                $q->where('sender_id', $recipientId)->where('recipient_id', $user->id);
            });
        }

        $messages = $query->take(50)->get()->reverse()->values()->map(fn($m) => [
            'id' => (string) $m->id,
            'project_id' => $m->project_id ? (string) $m->project_id : null,
            'content' => $m->content,
            'type' => $m->type,
            'sender' => [
                'id' => (string) $m->sender->id,
                'name' => $m->sender->name,
                'avatar_url' => $m->sender->avatar_url,
            ],
            'created_at' => $m->created_at->toISOString(),
        ]);

        return response()->json(['status' => 'success', 'data' => $messages]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'content' => 'required|string',
            'project_id' => 'nullable|integer',
            'recipient_id' => 'nullable|integer',
            'organization_id' => 'required|integer',
            'type' => 'nullable|in:project,direct',
        ]);

        $msg = ChatMessage::create([
            'content' => $validated['content'],
            'project_id' => $validated['project_id'] ?? null,
            'recipient_id' => $validated['recipient_id'] ?? null,
            'organization_id' => $validated['organization_id'],
            'sender_id' => $user->id,
            'type' => $validated['type'] ?? 'project',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => (string) $msg->id,
                'content' => $msg->content,
                'sender' => [
                    'id' => (string) $user->id,
                    'name' => $user->name,
                    'avatar_url' => $user->avatar_url,
                ],
                'created_at' => $msg->created_at->toISOString(),
            ],
        ], 201);
    }
}

