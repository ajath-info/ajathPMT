@extends('layouts.app')

@section('title', 'Message Board - ' . $project->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.show', $project->id) }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> {{ $project->name }}
                </a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2.5">
                <i data-lucide="message-square" class="w-6 h-6 text-amber-600"></i>
                <span>Message Board</span>
            </h1>
        </div>
    </div>

    <!-- Post a Message Card -->
    <div x-data="{ open: false }" class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Pitch an idea, make an announcement, or ask a question</h2>
                <p class="text-xs text-slate-500 mt-0.5">Post an organized topic so everyone can weigh in asynchronously.</p>
            </div>
            <button @click="open = !open" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span x-text="open ? 'Close' : 'Post a message'"></span>
            </button>
        </div>

        <form x-show="open" x-cloak action="{{ route('projects.discussions.store', $project->id) }}" method="POST" class="pt-4 border-t border-slate-100 space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Subject / Title *</label>
                    <input type="text" name="title" required placeholder="What's this discussion about?" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Category *</label>
                    <select name="category" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="Announcement">📢 Announcement</option>
                        <option value="Idea & Pitch">💡 Idea & Pitch</option>
                        <option value="Question">❓ Question</option>
                        <option value="FYI / Update">📝 FYI / Update</option>
                        <option value="General">💬 General</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Message Body *</label>
                <textarea name="content" rows="5" required placeholder="Write your full message or pitch here..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 leading-relaxed"></textarea>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition-colors shadow-sm">
                    Post this message
                </button>
            </div>
        </form>
    </div>

    <!-- Message List -->
    <div class="space-y-3">
        @if($discussions->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/90 shadow-sm space-y-3">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mx-auto">
                <i data-lucide="message-square" class="w-6 h-6"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900">No messages yet</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">Use the Message Board for big discussions, decisions, and announcements so nothing gets lost in chat.</p>
        </div>
        @else
            @foreach($discussions as $disc)
            <a href="{{ route('projects.discussions.show', [$project->id, $disc->id]) }}" class="block bg-white rounded-2xl p-5 border border-slate-200/90 shadow-sm hover:shadow-md hover:border-amber-300 transition-all group">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3.5 flex-1 min-w-0">
                        <img src="{{ $disc->author->avatar_url ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($disc->author->name ?? 'User') }}" class="w-10 h-10 rounded-full border border-slate-200 flex-shrink-0 mt-0.5">
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2 py-0.5 bg-amber-50 text-amber-700 text-[10px] font-black rounded uppercase tracking-wider">{{ $disc->category }}</span>
                                <span class="text-xs text-slate-400">by <strong class="text-slate-700">{{ $disc->author->name ?? 'Unknown' }}</strong> &bull; {{ $disc->created_at->diffForHumans() }}</span>
                            </div>
                            <h3 class="text-base font-bold text-slate-900 group-hover:text-amber-600 transition-colors truncate">
                                {{ $disc->title }}
                            </h3>
                            <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                                {{ Str::limit(strip_tags($disc->content), 160) }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 px-3 py-1.5 bg-slate-50 rounded-xl text-slate-500 group-hover:bg-amber-50 group-hover:text-amber-700 transition-colors flex-shrink-0">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span class="text-xs font-black">{{ $disc->comments->count() }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        @endif
    </div>

</div>
@endsection

