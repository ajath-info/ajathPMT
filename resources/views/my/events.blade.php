@extends('layouts.app')

@section('title', 'My Events - Ajath Workspace')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Breadcrumb & Title Bar -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1 transition-colors">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">My Events</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1 flex items-center gap-2.5">
                <i data-lucide="calendar" class="w-7 h-7 text-amber-500"></i>
                <span>My Events & Schedule</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Upcoming milestones, deliverables, and event deadlines across all projects</p>
        </div>

        <a href="{{ route('home') }}" class="px-3.5 py-1.5 bg-white border border-slate-300 rounded-full text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
            Back to Home
        </a>
    </div>

    <!-- Events List Grouped with Basecamp Calendar Day Badges -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h2 class="text-sm font-black text-slate-800 flex items-center gap-2">
                <span>Upcoming Milestones & Deadlines</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">{{ $events->count() }} items</span>
            </h2>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($events as $ev)
            @php
                $carbonDate = \Carbon\Carbon::parse($ev->due_date);
                $isPast = $carbonDate->isPast();
            @endphp
            <div class="py-4 flex items-center justify-between gap-4 group">
                <div class="flex items-center gap-4">
                    <!-- Basecamp Day Badge -->
                    <div class="w-14 h-14 rounded-2xl {{ $isPast ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-amber-50 border-amber-200 text-amber-800' }} border flex flex-col items-center justify-center flex-shrink-0 shadow-2xs">
                        <span class="text-[10px] uppercase font-black tracking-tight">{{ $carbonDate->format('M') }}</span>
                        <span class="text-lg font-black leading-none">{{ $carbonDate->format('d') }}</span>
                    </div>

                    <div class="space-y-0.5">
                        <a href="{{ route('projects.show', $ev->project_id) }}" class="text-sm font-bold text-slate-900 group-hover:text-emerald-700 transition-colors">
                            {{ $ev->title }}
                        </a>
                        <div class="flex items-center gap-2 text-xs text-slate-400">
                            <span class="font-semibold text-slate-600">{{ $ev->project->name ?? 'Project' }}</span>
                            <span>•</span>
                            <span>{{ $carbonDate->format('l, g:i A') }}</span>
                        </div>
                    </div>
                </div>

                <span class="px-3 py-1 rounded-full text-[11px] font-bold {{ $isPast ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700' }} flex-shrink-0">
                    {{ $isPast ? 'Overdue (' . $carbonDate->diffForHumans() . ')' : $carbonDate->diffForHumans() }}
                </span>
            </div>
            @empty
            <div class="py-12 text-center text-slate-400 space-y-2">
                <i data-lucide="calendar-check" class="w-10 h-10 mx-auto text-amber-400 opacity-60"></i>
                <p class="text-xs font-semibold">No scheduled events or deadlines right now.</p>
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
