@extends('layouts.app')

@section('title', 'New Project')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('dashboard') }}" class="p-2 bg-white rounded-xl border border-slate-200 text-slate-600 hover:text-slate-900 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900">Make a new project</h1>
            <p class="text-xs text-slate-500">Every project comes with its own Message Board, To-dos, Docs, and Campfire chat.</p>
        </div>
    </div>

    @if ($errors->any())
    <div class="bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-2xl p-4 space-y-1">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <form action="{{ route('projects.store') }}" method="POST" class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/90 space-y-6">
        @csrf

        <div>
            <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Project Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all font-semibold"
                placeholder="e.g. Mobile App Redesign, Marketing Q4 Sprint">
        </div>

        <div>
            <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Purpose / Scope Description</label>
            <textarea id="description" name="description" rows="3"
                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all"
                placeholder="What is this project about? What goals are we hitting?">{{ old('description') }}</textarea>
        </div>

        <div>
            <label for="cover_url" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Cover Image URL (Optional)</label>
            <input type="url" id="cover_url" name="cover_url" value="{{ old('cover_url') }}"
                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all"
                placeholder="https://images.unsplash.com/photo-...">
        </div>

        <!-- Assign Employees -->
        <div class="space-y-3 pt-3 border-t border-slate-100">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Assign Team Members</span>
                <span class="text-[10px] text-slate-400">Employees who can view and work on this project</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-48 overflow-y-auto p-1">
                @foreach($employees as $emp)
                <label class="flex items-center gap-3 p-3 rounded-2xl border border-slate-100 hover:border-slate-300 bg-slate-50/60 cursor-pointer transition-all">
                    <input type="checkbox" name="member_ids[]" value="{{ $emp->user->id }}" class="rounded text-amber-500 focus:ring-amber-500 w-4 h-4" {{ $emp->user->id == Auth::id() ? 'checked disabled' : '' }}>
                    <img src="{{ $emp->user->avatar_url ?: 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($emp->user->name) }}" class="w-7 h-7 rounded-full object-cover">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-slate-800 truncate">{{ $emp->user->name }}</p>
                        <p class="text-[10px] text-slate-400 truncate">{{ $emp->department ?: $emp->role }}</p>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <!-- Assign Clients -->
        <div class="space-y-3 pt-3 border-t border-slate-100">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Grant Client Access (Optional)
                </span>
                <span class="text-[10px] text-slate-400">Clients will ONLY see this specific project</span>
            </div>

            @if($clients->isEmpty())
            <p class="text-xs text-slate-400 py-2">No client accounts yet. You can add clients anytime under <a href="{{ route('people.index') }}" class="text-amber-600 font-bold hover:underline">People & Clients</a>.</p>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-48 overflow-y-auto p-1">
                @foreach($clients as $cli)
                <label class="flex items-center gap-3 p-3 rounded-2xl border border-emerald-100 hover:border-emerald-300 bg-emerald-50/40 cursor-pointer transition-all">
                    <input type="checkbox" name="client_ids[]" value="{{ $cli->user->id }}" class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                    <img src="{{ $cli->user->avatar_url ?: 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($cli->user->name) }}" class="w-7 h-7 rounded-full object-cover">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-slate-800 truncate">{{ $cli->user->name }}</p>
                        <p class="text-[10px] text-emerald-600 font-medium truncate">{{ $cli->client_company ?: 'Client Partner' }}</p>
                    </div>
                </label>
                @endforeach
            </div>
            @endif
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('dashboard') }}" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-amber-500 to-rose-600 hover:from-amber-600 hover:to-rose-700 text-white font-bold rounded-2xl text-xs shadow-lg shadow-amber-500/25 transition-all">
                Create Project
            </button>
        </div>
    </form>

</div>
@endsection

