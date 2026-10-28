@extends('layouts.app')

@section('title', 'My Notes - Ajath Workspace')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i data-lucide="file-edit" class="w-6 h-6 text-indigo-600"></i>
                <span>My Private Notes</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Personal notepad and scratchpad only visible to you</p>
        </div>
        <a href="{{ route('home') }}" class="px-3.5 py-1.5 bg-white border border-slate-300 rounded-full text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
            Back to Home
        </a>
    </div>

    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-2xs space-y-4">
        <form action="{{ route('my.notes.save') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <textarea name="notes" rows="14" class="w-full p-4 text-xs font-mono bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 text-slate-800 leading-relaxed placeholder-slate-400" placeholder="Jot down quick thoughts, private reminders, meeting snippets, or todo lists...">{{ $notes }}</textarea>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-400">Notes are private to your user account.</span>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition-all shadow-2xs">
                    Save Notes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

