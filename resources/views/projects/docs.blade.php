@extends('layouts.app')

@section('title', 'Docs & Files - ' . $project->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.show', $project->id) }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> {{ $project->name }}
                </a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2.5">
                <i data-lucide="file-text" class="w-6 h-6 text-sky-600"></i>
                <span>Docs & Files</span>
            </h1>
        </div>
    </div>

    <!-- Create Document Card -->
    <div x-data="{ open: false }" class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Share knowledge, specs, and documentation</h2>
                <p class="text-xs text-slate-500 mt-0.5">Keep project briefs, meeting notes, guidelines, and assets in one place.</p>
            </div>
            <button @click="open = !open" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span x-text="open ? 'Close' : 'New document'"></span>
            </button>
        </div>

        <form x-show="open" x-cloak action="{{ route('projects.docs.store', $project->id) }}" method="POST" class="pt-4 border-t border-slate-100 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Document Title *</label>
                <input type="text" name="title" required placeholder="e.g. Project Scope & Architecture, Brand Guidelines, API Specs..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 font-semibold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Document Content</label>
                <textarea name="content" rows="6" placeholder="Write or paste your doc content here..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 leading-relaxed font-mono text-xs"></textarea>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl text-xs transition-colors shadow-sm">
                    Save document
                </button>
            </div>
        </form>
    </div>

    <!-- Documents Grid -->
    @if($docs->isEmpty())
    <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/90 shadow-sm space-y-3">
        <div class="w-12 h-12 bg-sky-50 text-sky-600 rounded-2xl flex items-center justify-center mx-auto">
            <i data-lucide="file-text" class="w-6 h-6"></i>
        </div>
        <h3 class="text-base font-bold text-slate-900">No documents yet</h3>
        <p class="text-xs text-slate-500 max-w-sm mx-auto">Create your first document to store project requirements, release notes, or client deliverables.</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($docs as $doc)
        <div x-data="{ expanded: false }" class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-4">
            <div class="space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                    </div>
                    <span class="text-[10px] text-slate-400 font-medium">{{ $doc->created_at->format('M d, Y') }}</span>
                </div>
                <h3 class="text-sm font-bold text-slate-900">{{ $doc->title }}</h3>
                <div class="text-xs text-slate-600 leading-relaxed font-medium">
                    <div x-show="!expanded" class="line-clamp-3">
                        {{ $doc->content ?: 'Empty document.' }}
                    </div>
                    <div x-show="expanded" x-cloak class="whitespace-pre-line bg-slate-50 p-3 rounded-xl border border-slate-100 text-[11px] font-mono">
                        {{ $doc->content }}
                    </div>
                </div>
                @if(strlen($doc->content) > 120)
                <button @click="expanded = !expanded" class="text-[11px] font-bold text-sky-600 hover:text-sky-700">
                    <span x-text="expanded ? 'Show less' : 'Read full document'"></span>
                </button>
                @endif
            </div>

            <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                <img src="{{ $doc->author->avatar_url ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($doc->author->name ?? 'User') }}" class="w-5 h-5 rounded-full border border-slate-200">
                <span class="text-[11px] font-medium text-slate-500">Created by <strong class="text-slate-700">{{ $doc->author->name ?? 'Member' }}</strong></span>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection

