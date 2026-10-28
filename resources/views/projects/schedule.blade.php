@extends('layouts.app')

@section('title', 'Schedule - ' . $project->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.show', $project->id) }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1 transition-colors">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> {{ $project->name }}
                </a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2.5">
                <i data-lucide="calendar" class="w-6 h-6 text-sky-600"></i>
                <span>Schedule & Milestones</span>
            </h1>
        </div>
    </div>

    <!-- Intro Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-2xs space-y-2">
        <h2 class="text-base font-bold text-slate-900">Upcoming Deadlines & Deliverables</h2>
        <p class="text-xs text-slate-500">Every due date and task milestone across this project is organized here chronologically.</p>
    </div>

    <!-- Timeline of Scheduled Tasks -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs space-y-6">
        @forelse($tasks as $t)
        <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-sky-50/50 transition-colors">
            <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 font-extrabold flex flex-col items-center justify-center text-xs flex-shrink-0">
                <span class="text-[9px] uppercase tracking-wider leading-none">{{ \Carbon\Carbon::parse($t->due_date)->format('M') }}</span>
                <span class="text-sm font-black leading-none mt-0.5">{{ \Carbon\Carbon::parse($t->due_date)->format('d') }}</span>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-900 {{ $t->status === 'DONE' ? 'line-through text-slate-400' : '' }}">{{ $t->title }}</span>
                    @if($t->status === 'DONE')
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Completed</span>
                    @else
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">Pending</span>
                    @endif
                </div>

                @if($t->description)
                <p class="text-xs text-slate-500 mt-1 truncate">{{ $t->description }}</p>
                @endif

                <div class="flex items-center gap-3 mt-2 text-[11px] text-slate-400">
                    <span>Due: {{ \Carbon\Carbon::parse($t->due_date)->format('l, F j, Y') }}</span>
                    @if($t->assignee)
                    <span>•</span>
                    <span class="font-medium text-slate-600">Assigned: {{ $t->assignee->name }}</span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="py-12 text-center text-slate-400 space-y-3">
            <i data-lucide="calendar-check" class="w-12 h-12 mx-auto opacity-40"></i>
            <p class="text-sm font-semibold">No scheduled due dates in this project yet.</p>
            <a href="{{ route('projects.tasks', $project->id) }}" class="inline-block px-4 py-2 bg-emerald-600 text-white font-bold rounded-xl text-xs hover:bg-emerald-700 transition-colors">
                Add a to-do with a due date
            </a>
        </div>
        @endforelse
    </div>

</div>
@endsection
