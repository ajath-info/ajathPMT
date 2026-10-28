@extends('layouts.app')

@section('title', 'My Tasks - Ajath Workspace')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-full bg-emerald-600 text-white font-extrabold flex items-center justify-center text-xs">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </span>
                <h1 class="text-2xl font-black text-slate-900">My Tasks</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">All to-dos assigned to you across projects in {{ session('active_org_name', 'Ajath Workspace') }}</p>
        </div>
        <a href="{{ route('home') }}" class="px-3.5 py-1.5 bg-white border border-slate-300 rounded-full text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
            Back to Home
        </a>
    </div>

    <!-- Open Tasks -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-2xs space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-black text-slate-800 flex items-center gap-2">
                <i data-lucide="check-square" class="w-4 h-4 text-emerald-600"></i>
                <span>Open Tasks ({{ $openTasks->count() }})</span>
            </h2>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($openTasks as $t)
            <div class="py-3 flex items-start justify-between gap-3 group">
                <div class="flex items-start gap-3">
                    <form action="{{ route('projects.tasks.toggle', [$t->project_id, $t->id]) }}" method="POST" class="pt-0.5">
                        @csrf
                        <input type="checkbox" onchange="this.form.submit()" class="w-4 h-4 rounded text-emerald-600 border-slate-300 cursor-pointer">
                    </form>
                    <div>
                        <a href="{{ route('projects.tasks', $t->project_id) }}" class="text-xs font-bold text-slate-800 hover:text-emerald-700 transition-colors">
                            {{ $t->title }}
                        </a>
                        <div class="flex items-center gap-2 text-[11px] text-slate-400 mt-0.5">
                            <span class="font-medium text-slate-600">{{ $t->project->name ?? 'Project' }}</span>
                            @if($t->due_date)
                            <span>•</span>
                            <span class="text-amber-600 font-semibold flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                Due {{ \Carbon\Carbon::parse($t->due_date)->format('M d, Y') }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $t->priority === 'URGENT' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600' }}">
                    {{ $t->priority }}
                </span>
            </div>
            @empty
            <div class="py-8 text-center text-slate-400 space-y-2">
                <i data-lucide="check-circle-2" class="w-10 h-10 mx-auto text-emerald-500 opacity-60"></i>
                <p class="text-xs font-semibold">You're all caught up! No pending tasks assigned to you.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Completed Tasks -->
    @if($completedTasks->isNotEmpty())
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-2xs space-y-4">
        <h2 class="text-sm font-black text-slate-400 flex items-center gap-2">
            <i data-lucide="check-check" class="w-4 h-4"></i>
            <span>Completed ({{ $completedTasks->count() }})</span>
        </h2>
        <div class="divide-y divide-slate-100 opacity-75">
            @foreach($completedTasks as $t)
            <div class="py-2.5 flex items-center justify-between text-xs">
                <span class="line-through text-slate-400 font-medium">{{ $t->title }}</span>
                <span class="text-[10px] text-slate-400">{{ $t->project->name ?? '' }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

