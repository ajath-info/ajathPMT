@extends('layouts.app')

@section('title', 'Automatic Check-ins - ' . $project->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.show', $project->id) }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1 transition-colors">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> {{ $project->name }}
                </a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2.5">
                <i data-lucide="repeat" class="w-6 h-6 text-teal-600"></i>
                <span>Automatic Check-ins</span>
            </h1>
        </div>
    </div>

    <!-- Question Prompt Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs space-y-4">
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-teal-100 text-teal-800">Recurring Daily Question</span>
            <span class="text-xs text-slate-400">&bull; 5:00 PM Every Workday</span>
        </div>
        <h2 class="text-xl sm:text-2xl font-black text-slate-900">{{ $checkin->question }}</h2>
        <p class="text-xs text-slate-500">Share your daily accomplishments, notes, or blockers with the team.</p>

        <!-- Post Your Answer Form -->
        <form action="{{ route('projects.checkins.store', $project->id) }}" method="POST" class="pt-4 border-t border-slate-100 space-y-3">
            @csrf
            <input type="hidden" name="checkin_id" value="{{ $checkin->id }}">
            <textarea name="answer" required rows="3" placeholder="Write what you worked on today..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all"></textarea>
            
            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl text-xs shadow-2xs transition-colors">
                    Post your check-in
                </button>
            </div>
        </form>
    </div>

    <!-- Team Check-in Answers Timeline -->
    <div class="space-y-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 px-2">Recent Responses</h3>

        @forelse($answers as $ans)
        <div class="bg-white rounded-3xl p-5 border border-slate-200/90 shadow-2xs space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ $ans->user->avatar_url ?: 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($ans->user->name) }}" class="w-8 h-8 rounded-full border border-slate-200 object-cover" alt="{{ $ans->user->name }}">
                    <div>
                        <p class="text-xs font-bold text-slate-900">{{ $ans->user->name }}</p>
                        <p class="text-[10px] text-slate-400">{{ $ans->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>

            <div class="text-xs text-slate-700 leading-relaxed bg-slate-50 p-3.5 rounded-2xl border border-slate-100 whitespace-pre-line">
                {{ $ans->answer }}
            </div>
        </div>
        @empty
        <div class="bg-white rounded-3xl p-10 text-center text-slate-400 border border-slate-200/90 shadow-2xs space-y-2">
            <i data-lucide="message-circle" class="w-10 h-10 mx-auto opacity-40"></i>
            <p class="text-xs font-semibold">No responses recorded yet today. Be the first to check in!</p>
        </div>
        @endforelse
    </div>

</div>
@endsection

