@extends('layouts.app')

@section('title', 'My Activity - Ajath Workspace')

@section('content')
<div x-data="{ filter: 'all' }" class="max-w-4xl mx-auto space-y-6">

    <!-- Top Breadcrumb & Title Bar -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1 transition-colors">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">My Activity</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1 flex items-center gap-2.5">
                <i data-lucide="activity" class="w-7 h-7 text-emerald-600"></i>
                <span>My Activity & Company Timeline</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Live updates, campfire chats, and to-do milestones across all your projects</p>
        </div>

        <a href="{{ route('home') }}" class="px-3.5 py-1.5 bg-white border border-slate-300 rounded-full text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
            Back to Home
        </a>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 text-xs font-bold bg-white p-1.5 rounded-2xl border border-slate-200/90 shadow-2xs max-w-fit">
        <button @click="filter = 'all'" :class="filter === 'all' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100'" class="px-3.5 py-1.5 rounded-xl transition-all">
            All Activity
        </button>
        <button @click="filter = 'tasks'" :class="filter === 'tasks' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100'" class="px-3.5 py-1.5 rounded-xl transition-all">
            To-dos
        </button>
        <button @click="filter = 'discussions'" :class="filter === 'discussions' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100'" class="px-3.5 py-1.5 rounded-xl transition-all">
            Discussions
        </button>
        <button @click="filter = 'chat'" :class="filter === 'chat' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100'" class="px-3.5 py-1.5 rounded-xl transition-all">
            Campfire Chats
        </button>
    </div>

    <div class="space-y-4">
        <!-- Recent Tasks Activity -->
        <div x-show="filter === 'all' || filter === 'tasks'" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs space-y-4">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-2">
                <i data-lucide="check-square" class="w-4 h-4 text-emerald-600"></i>
                <span>Recent To-Dos</span>
            </h2>
            <div class="divide-y divide-slate-100">
                @forelse($recentTasks as $rt)
                <div class="py-3 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full {{ $rt->status === 'DONE' ? 'bg-emerald-500' : 'bg-amber-400' }} flex-shrink-0"></span>
                        <div>
                            <a href="{{ route('projects.tasks', $rt->project_id) }}" class="font-bold text-slate-800 hover:text-emerald-700">
                                {{ $rt->title }}
                            </a>
                            <span class="text-slate-400 text-[11px] ml-1">in {{ $rt->project->name ?? 'Project' }}</span>
                        </div>
                    </div>
                    <span class="text-[10px] text-slate-400 font-medium">{{ $rt->created_at->diffForHumans() }}</span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-2">No recent tasks</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Discussions -->
        <div x-show="filter === 'all' || filter === 'discussions'" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs space-y-4">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-2">
                <i data-lucide="message-square" class="w-4 h-4 text-amber-500"></i>
                <span>Recent Message Board Posts</span>
            </h2>
            <div class="divide-y divide-slate-100">
                @forelse($recentDiscussions as $disc)
                <div class="py-3 flex items-start justify-between text-xs">
                    <div>
                        <a href="{{ route('projects.discussions.show', [$disc->project_id, $disc->id]) }}" class="font-bold text-slate-800 hover:text-amber-700">
                            {{ $disc->title }}
                        </a>
                        <p class="text-[11px] text-slate-400 mt-0.5">by {{ $disc->author->name ?? 'Author' }} in {{ $disc->project->name ?? 'Project' }}</p>
                    </div>
                    <span class="text-[10px] text-slate-400 font-medium">{{ $disc->created_at->diffForHumans() }}</span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-2">No discussions yet</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Campfire Chats -->
        <div x-show="filter === 'all' || filter === 'chat'" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs space-y-4">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-2">
                <i data-lucide="flame" class="w-4 h-4 text-orange-500"></i>
                <span>Campfire Chatter</span>
            </h2>
            <div class="divide-y divide-slate-100">
                @forelse($recentChats as $chat)
                <div class="py-3 flex items-start justify-between text-xs">
                    <div class="flex items-start gap-3">
                        <span class="w-7 h-7 rounded-full bg-slate-700 text-white font-extrabold flex items-center justify-center text-[10px] flex-shrink-0">
                            {{ strtoupper(substr($chat->sender->name ?? 'U', 0, 2)) }}
                        </span>
                        <div>
                            <span class="font-bold text-slate-800">{{ $chat->sender->name ?? 'User' }}</span>
                            <span class="text-slate-400 text-[10px] ml-1">in {{ $chat->project->name ?? 'Campfire' }}</span>
                            <p class="text-slate-600 mt-0.5 leading-relaxed">{{ $chat->content }}</p>
                        </div>
                    </div>
                    <span class="text-[10px] text-slate-400 flex-shrink-0 font-medium">{{ $chat->created_at->diffForHumans() }}</span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-2">No recent chat messages</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
