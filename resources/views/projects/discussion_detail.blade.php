@extends('layouts.app')

@section('title', $discussion->title . ' - Message Board')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="space-y-1">
        <a href="{{ route('projects.discussions', $project->id) }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to Message Board
        </a>
    </div>

    <!-- Main Message Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-sm space-y-6">
        <div class="space-y-3 pb-6 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 bg-amber-50 text-amber-700 text-[10px] font-black rounded uppercase tracking-wider">{{ $discussion->category }}</span>
                <span class="text-xs text-slate-400">&bull; {{ $discussion->created_at->format('M d, Y \a\t g:i A') }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-snug">
                {{ $discussion->title }}
            </h1>
            <div class="flex items-center gap-3 pt-2">
                <img src="{{ $discussion->author->avatar_url ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($discussion->author->name ?? 'User') }}" class="w-10 h-10 rounded-full border border-slate-200">
                <div>
                    <p class="text-sm font-bold text-slate-900">{{ $discussion->author->name ?? 'Unknown' }}</p>
                    <p class="text-xs text-slate-400">{{ $discussion->author->job_title ?? 'Team Member' }}</p>
                </div>
            </div>
        </div>

        <div class="prose max-w-none text-slate-700 text-sm leading-relaxed whitespace-pre-line font-medium">
            {{ $discussion->content }}
        </div>
    </div>

    <!-- Comments Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between px-2">
            <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                <i data-lucide="message-circle" class="w-4 h-4 text-slate-400"></i>
                <span>Discussion & Responses ({{ $discussion->comments->count() }})</span>
            </h2>
        </div>

        @if($discussion->comments->isEmpty())
        <div class="bg-white rounded-2xl p-6 text-center border border-dashed border-slate-200 text-slate-400 text-xs">
            No responses yet. Be the first to share your thoughts below!
        </div>
        @else
        <div class="space-y-3">
            @foreach($discussion->comments as $comment)
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ $comment->author->avatar_url ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($comment->author->name ?? 'User') }}" class="w-7 h-7 rounded-full border border-slate-200">
                        <span class="text-xs font-bold text-slate-900">{{ $comment->author->name ?? 'Unknown' }}</span>
                        <span class="text-[11px] text-slate-400 font-medium">({{ $comment->author->job_title ?? 'Member' }})</span>
                    </div>
                    <span class="text-[11px] text-slate-400 font-medium">{{ $comment->created_at->diffForHumans() }}</span>
                </div>
                <div class="text-xs text-slate-700 leading-relaxed pl-9 whitespace-pre-line font-medium">
                    {{ $comment->content }}
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Add Comment Form -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm space-y-3">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Leave a response</h3>
            <form action="{{ route('projects.discussions.comment', [$project->id, $discussion->id]) }}" method="POST" class="space-y-3">
                @csrf
                <textarea name="content" rows="3" required placeholder="Add your feedback, answer, or thoughts..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 font-medium"></textarea>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition-colors">
                        Post response
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

