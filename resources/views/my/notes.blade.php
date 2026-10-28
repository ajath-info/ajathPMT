@extends('layouts.app')

@section('title', 'My Notes - Ajath Workspace')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Top Breadcrumb & Title Bar -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1 transition-colors">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">My Notes</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1 flex items-center gap-2.5">
                <i data-lucide="notebook-pen" class="w-7 h-7 text-indigo-600"></i>
                <span>My Notes & Scratchpad</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Your private scratchpad. Only visible to you.</p>
        </div>

        <a href="{{ route('home') }}" class="px-3.5 py-1.5 bg-white border border-slate-300 rounded-full text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
            Back to Home
        </a>
    </div>

    <!-- Notes Editor Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs space-y-4">
        <form action="{{ route('my.notes.save') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <textarea name="notes" rows="16" class="w-full p-4 text-xs font-mono bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white text-slate-800 leading-relaxed placeholder-slate-400 transition-all" placeholder="Jot down quick thoughts, private reminders, meeting snippets, or todo lists...">{{ $notes }}</textarea>
            </div>
            <div class="flex items-center justify-between pt-2">
                <span class="text-[11px] text-slate-400 font-medium">Notes are private and encrypted to your user session.</span>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition-all shadow-2xs">
                    Save Notes
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
