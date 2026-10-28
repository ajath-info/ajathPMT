@extends('layouts.app')

@section('title', 'My Activity - Ajath Workspace')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i data-lucide="activity" class="w-6 h-6 text-emerald-600"></i>
                <span>My Activity & Company Timeline</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Live updates, chats, check-ins, and to-do milestones across your projects</p>
        </div>
        <a href="{{ route('home') }}" class="px-3.5 py-1.5 bg-white border border-slate-300 rounded-full text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
            Back to Home
        </a>
    </div>

    <div class="space-y-4">
        <!-- Recent Tasks Activity -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-2xs space-y-3">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-2">
                <i data-lucide="check-square" class="w-4 h-4 text-emerald-600"></i>
                <span>Recent To-Dos</span>
            </h2>
            <div class="divide-y divide-slate-100">
                @forelse($recentTasks as $rt)
                <div class="py-2.5 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $rt->status === 'DONE' ? 'bg-emerald-500' : 'bg-amber-400' }}"></span>
                        <a href="{{ route('projects.tasks', $rt->project_id) }}" class="font-bold text-slate-800 hover:text-emerald-700">
                            {{ $rt->title }}
                        </a>
                        <span class="text-slate-400">in {{ $rt->project->name ?? 'Project' }}</span>
                    </div>
                    <span class="text-[10px] text-slate-400">{{ $rt->created_at->diffForHumans() }}</span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-2">No recent tasks</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Campfire Chats -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-2xs space-y-3">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-2">
                <i data-lucide="flame" class="w-4 h-4 text-orange-500"></i>
                <span>Campfire Chatter</span>
            </h2>
            <div class="divide-y divide-slate-100">
                @forelse($recentChats as $chat)
                <div class="py-2.5 flex items-start justify-between text-xs">
                    <div class="flex items-start gap-2.5">
                        <span class="w-6 h-6 rounded-full bg-slate-700 text-white font-extrabold flex items-center justify-center text-[10px] flex-shrink-0">
                            {{ strtoupper(substr($chat->user->name ?? 'U', 0, 2)) }}
                        </span>
                        <div>
                            <span class="font-bold text-slate-800">{{ $chat->user->name ?? 'User' }}</span>
                            <span class="text-slate-400 text-[10px] ml-1">in {{ $chat->project->name ?? 'Campfire' }}</span>
                            <p class="text-slate-600 mt-0.5">{{ $chat->message }}</p>
                        </div>
                    </div>
                    <span class="text-[10px] text-slate-400 flex-shrink-0">{{ $chat->created_at->diffForHumans() }}</span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-2">No recent chat messages</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
