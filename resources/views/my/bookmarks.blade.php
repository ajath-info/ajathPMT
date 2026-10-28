@extends('layouts.app')

@section('title', 'My Bookmarks - Ajath Workspace')

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
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">My Bookmarks</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1 flex items-center gap-2.5">
                <i data-lucide="bookmark" class="w-7 h-7 text-amber-500"></i>
                <span>My Bookmarks</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Starred projects and quick-access workspaces you have pinned</p>
        </div>

        <a href="{{ route('home') }}" class="px-3.5 py-1.5 bg-white border border-slate-300 rounded-full text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
            Back to Home
        </a>
    </div>

    <!-- Starred Projects Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        @forelse($starredProjects as $proj)
        <a href="{{ route('projects.show', $proj->id) }}" class="p-5 bg-white rounded-3xl border border-slate-200/90 hover:border-amber-400 transition-all shadow-2xs hover:shadow-md group flex flex-col justify-between h-44">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase text-amber-600 tracking-wider">STARRED PROJECT</span>
                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                </div>
                <h3 class="font-black text-slate-900 group-hover:text-amber-700 text-sm mt-2 line-clamp-2 leading-snug">
                    {{ $proj->name }}
                </h3>
                <p class="text-[11px] text-slate-500 mt-1 line-clamp-2">
                    {{ $proj->client_subtitle ?: ($proj->description ?: 'Active project') }}
                </p>
            </div>
            <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-[11px] text-slate-400 font-semibold">
                <span>{{ $proj->members->count() }} members</span>
                <span>{{ $proj->tasks->count() }} to-dos</span>
            </div>
        </a>
        @empty
        <div class="col-span-full py-16 text-center text-slate-400 space-y-3 bg-white rounded-3xl border border-slate-200/90 shadow-2xs">
            <i data-lucide="star-off" class="w-12 h-12 mx-auto text-amber-300 opacity-60"></i>
            <h3 class="text-sm font-black text-slate-800">No bookmarked projects yet</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">Click the star icon in the top right of any project card on your home dashboard to pin it here.</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
