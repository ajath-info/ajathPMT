@extends('layouts.app')

@section('title', 'My Events - Ajath Workspace')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i data-lucide="calendar" class="w-6 h-6 text-amber-500"></i>
                <span>My Events & Deadlines</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Upcoming milestones, schedules, and deliverables across all projects</p>
        </div>
        <a href="{{ route('home') }}" class="px-3.5 py-1.5 bg-white border border-slate-300 rounded-full text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
            Back to Home
        </a>
    </div>

    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-2xs space-y-4">
        <div class="divide-y divide-slate-100">
            @forelse($events as $ev)
            <div class="py-3.5 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 flex flex-col items-center justify-center text-amber-800">
                        <span class="text-[10px] uppercase font-bold tracking-tight">{{ \Carbon\Carbon::parse($ev->due_date)->format('M') }}</span>
                        <span class="text-base font-black leading-none">{{ \Carbon\Carbon::parse($ev->due_date)->format('d') }}</span>
                    </div>
                    <div>
                        <a href="{{ route('projects.show', $ev->project_id) }}" class="text-xs font-bold text-slate-800 hover:text-emerald-700 transition-colors">
                            {{ $ev->title }}
                        </a>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $ev->project->name ?? 'Project Event' }}</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                    {{ \Carbon\Carbon::parse($ev->due_date)->diffForHumans() }}
                </span>
            </div>
            @empty
            <div class="py-8 text-center text-slate-400 space-y-2">
                <i data-lucide="calendar-check" class="w-10 h-10 mx-auto text-amber-400 opacity-60"></i>
                <p class="text-xs font-semibold">No scheduled events or deadlines right now.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
