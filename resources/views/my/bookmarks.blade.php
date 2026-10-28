@extends('layouts.app')

@section('title', 'My Bookmarks - Ajath Workspace')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i data-lucide="bookmark" class="w-6 h-6 text-amber-500"></i>
                <span>My Bookmarks</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Starred projects and quick access items you have pinned</p>
        </div>
        <a href="{{ route('home') }}" class="px-3.5 py-1.5 bg-white border border-slate-300 rounded-full text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
            Back to Home
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        @forelse($starredProjects as $proj)
        <a href="{{ route('projects.show', $proj->id) }}" class="p-5 bg-white rounded-2xl border border-slate-200 hover:border-emerald-500 transition-all shadow-2xs hover:shadow-xs group flex flex-col justify-between h-40">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 group-hover:text-emerald-600 transition-colors">PROJECT</span>
                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                </div>
                <h3 class="font-black text-slate-900 group-hover:text-emerald-700 text-sm mt-1 line-clamp-2">
                    {{ $proj->name }}
                </h3>
                <p class="text-[11px] text-slate-500 mt-1 line-clamp-2">
                    {{ $proj->client_subtitle ?: ($proj->description ?: 'Active project') }}
                </p>
            </div>
            <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[11px] text-slate-400 font-semibold">
                <span>{{ $proj->members->count() }} members</span>
                <span>{{ $proj->tasks->count() }} tasks</span>
            </div>
        </a>
        @empty
        <div class="col-span-full py-12 text-center text-slate-400 space-y-2 bg-white rounded-2xl border border-slate-200">
            <i data-lucide="star-off" class="w-10 h-10 mx-auto text-amber-300 opacity-60"></i>
            <p class="text-xs font-semibold">No starred projects yet. Click the star on any project card to bookmark it.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
