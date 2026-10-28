@extends('layouts.app')

@section('title', 'Adminland - ' . $org->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="space-y-1">
        <a href="{{ route('home') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to Dashboard
        </a>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 flex items-center gap-2.5">
            <i data-lucide="key" class="w-7 h-7 text-amber-500"></i>
            <span>Adminland</span>
        </h1>
        <p class="text-xs text-slate-500">The central control hub for {{ $org->name }}'s workspace settings, data, and permissions.</p>
    </div>

    <!-- Company Stats Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Organization Workspace</span>
            <h2 class="text-xl font-black text-slate-900">{{ $org->name }}</h2>
            <p class="text-xs text-slate-500 mt-0.5">Managed by {{ $user->name }} &bull; {{ $totalMembers }} team members</p>
        </div>

        <div class="flex items-center gap-4 text-center">
            <div class="px-4 py-2 bg-slate-50 rounded-2xl border border-slate-100">
                <span class="text-lg font-black text-slate-900">{{ $activeProjects }}</span>
                <p class="text-[10px] font-bold text-slate-400 uppercase">Active</p>
            </div>
            <div class="px-4 py-2 bg-slate-50 rounded-2xl border border-slate-100">
                <span class="text-lg font-black text-slate-900">{{ $archivedProjects }}</span>
                <p class="text-[10px] font-bold text-slate-400 uppercase">Archived</p>
            </div>
        </div>
    </div>

    <!-- Adminland Action Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        
        <!-- Import / Export Card -->
        <a href="{{ route('adminland.import') }}" class="block p-6 bg-white rounded-3xl border border-slate-200/90 shadow-xs hover:border-amber-400 hover:shadow-md transition-all group">
            <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                <i data-lucide="arrow-down-to-dot" class="w-5 h-5"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900 group-hover:text-amber-600 transition-colors">Import & Export Basecamp Data</h3>
            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                Import all live and archived projects, to-dos, messages, and docs from a Basecamp ZIP file or direct API sync, and export complete workspace backups.
            </p>
        </a>

        <!-- People & Permissions -->
        <a href="{{ route('people.index') }}" class="block p-6 bg-white rounded-3xl border border-slate-200/90 shadow-xs hover:border-indigo-400 hover:shadow-md transition-all group">
            <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">People & Client Roster</h3>
            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                Enroll team members and external clients, assign roles, configure project access, and manage credentials.
            </p>
        </a>

        <!-- New Project Setup -->
        <a href="{{ route('projects.create') }}" class="block p-6 bg-white rounded-3xl border border-slate-200/90 shadow-xs hover:border-emerald-400 hover:shadow-md transition-all group">
            <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900 group-hover:text-emerald-600 transition-colors">Create a Project</h3>
            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                Start a new workspace with Basecamp's iconic 6 tools: To-dos, Message Board, Docs, Campfire, Schedule, and Check-ins.
            </p>
        </a>

        <!-- Direct Data Export -->
        <form action="{{ route('adminland.export') }}" method="POST" class="block">
            @csrf
            <button type="submit" class="w-full text-left p-6 bg-white rounded-3xl border border-slate-200/90 shadow-xs hover:border-blue-400 hover:shadow-md transition-all group h-full">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="download" class="w-5 h-5"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Download Workspace Backup</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Instantly package all live and archived data into a single downloadable .zip archive.
                </p>
            </button>
        </form>

    </div>

</div>
@endsection

