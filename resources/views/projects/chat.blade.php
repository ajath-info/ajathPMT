@extends('layouts.app')

@section('title', 'Campfire Chat - ' . $project->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-4">

    <!-- Header & Breadcrumbs -->
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.show', $project->id) }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> {{ $project->name }}
                </a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2.5">
                <i data-lucide="flame" class="w-6 h-6 text-rose-500"></i>
                <span>Campfire</span>
            </h1>
        </div>

        <!-- Campfire Members summary -->
        <div class="hidden sm:flex items-center -space-x-2">
            @foreach($project->members->take(5) as $m)
            <img src="{{ $m->avatar_url ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($m->name) }}" title="{{ $m->name }}" class="w-8 h-8 rounded-full border-2 border-white">
            @endforeach
            @if($project->members->count() > 5)
            <span class="w-8 h-8 rounded-full bg-slate-100 border-2 border-white text-[11px] font-bold text-slate-600 flex items-center justify-center">
                +{{ $project->members->count() - 5 }}
            </span>
            @endif
        </div>
    </div>

    <!-- Chat Box Card -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm flex flex-col h-[650px] overflow-hidden">
        
        <!-- Info Banner -->
        <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <span class="flex items-center gap-1.5 font-medium">
                <i data-lucide="info" class="w-3.5 h-3.5 text-slate-400"></i>
                Casual group chat for this project. Messages are saved and searchable by the team.
            </span>
            <span class="font-bold text-slate-700">{{ $project->members->count() }} participants</span>
        </div>

        <!-- Messages Stream -->
        <div id="chat-messages" class="flex-1 p-6 overflow-y-auto space-y-4">
            @if($messages->isEmpty())
            <div class="h-full flex flex-col items-center justify-center text-center text-slate-400 space-y-2">
                <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center">
                    <i data-lucide="flame" class="w-6 h-6"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">The campfire is ready!</p>
                <p class="text-xs max-w-xs">Drop a greeting or quick question to get the conversation started.</p>
            </div>
            @else
                @foreach($messages as $msg)
                @php
                    $isMe = $msg->sender_id === $user->id;
                @endphp
                <div class="flex items-start gap-3 {{ $isMe ? 'flex-row-reverse' : '' }}">
                    <img src="{{ $msg->sender->avatar_url ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($msg->sender->name ?? 'User') }}" class="w-8 h-8 rounded-full border border-slate-200 flex-shrink-0 mt-0.5">
                    <div class="max-w-[75%] space-y-1">
                        <div class="flex items-center gap-2 {{ $isMe ? 'justify-end' : '' }}">
                            <span class="text-[11px] font-bold text-slate-900">{{ $isMe ? 'You' : ($msg->sender->name ?? 'User') }}</span>
                            <span class="text-[10px] text-slate-400">{{ $msg->created_at->format('g:i A') }}</span>
                        </div>
                        <div class="px-4 py-2.5 rounded-2xl text-xs font-medium leading-relaxed {{ $isMe ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-slate-100 text-slate-800 rounded-tl-none' }}">
                            {{ $msg->content }}
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>

        <!-- Message Input Form -->
        <div class="p-4 bg-white border-t border-slate-100">
            <form action="{{ route('projects.chat.store', $project->id) }}" method="POST" class="flex items-center gap-2">
                @csrf
                <input type="text" name="content" required autofocus placeholder="Say something around the campfire..." autocomplete="off" class="flex-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 font-medium">
                <button type="submit" class="px-5 py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-2xl text-xs flex items-center gap-1.5 transition-colors shadow-sm">
                    <span>Send</span>
                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                </button>
            </form>
        </div>

    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('chat-messages');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
</script>
@endsection

