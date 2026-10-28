@extends('layouts.app')

@section('title', 'My Profile - Ajath Workspace')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Breadcrumb & Title -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1 transition-colors">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">My Profile</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1 flex items-center gap-3">
                <span>{{ $user->name }}</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider {{ $membership && in_array($membership->role, ['OWNER', 'ADMIN']) ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-slate-100 text-slate-700' }}">
                    {{ $membership->role ?? 'MEMBER' }}
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Part of {{ $org->name ?? 'Ajath Workspace' }}</p>
        </div>

        <a href="{{ route('my.settings') }}" class="flex items-center gap-1.5 px-3.5 py-1.5 bg-white border border-slate-300 rounded-full text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
            <i data-lucide="settings" class="w-3.5 h-3.5 text-slate-500"></i>
            <span>Account & Settings</span>
        </a>
    </div>

    <!-- Quick Stats Cards (Basecamp Style) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="p-4 bg-white rounded-2xl border border-slate-200/90 shadow-2xs">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Projects</span>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total_projects'] }}</p>
        </div>
        <div class="p-4 bg-white rounded-2xl border border-slate-200/90 shadow-2xs">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">To-dos Assigned</span>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ $stats['assigned_tasks'] }}</p>
        </div>
        <div class="p-4 bg-white rounded-2xl border border-slate-200/90 shadow-2xs">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Completed</span>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['completed_tasks'] }}</p>
        </div>
        <div class="p-4 bg-white rounded-2xl border border-slate-200/90 shadow-2xs">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Campfire Chats</span>
            <p class="text-2xl font-black text-orange-500 mt-1">{{ $stats['messages_sent'] }}</p>
        </div>
    </div>

    <!-- Profile Edit Form Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs space-y-6">
        <div class="flex items-center gap-4 pb-4 border-b border-slate-100">
            <img src="{{ $user->avatar_url ?: 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($user->name) }}" 
                 class="w-16 h-16 rounded-full border-2 border-emerald-500 object-cover shadow-sm">
            <div>
                <h3 class="text-sm font-black text-slate-900">Personal Information</h3>
                <p class="text-xs text-slate-500">Your profile details are visible to your team and clients on projects.</p>
            </div>
        </div>

        <form action="{{ route('my.profile.update') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                    <input type="email" value="{{ $user->email }}" disabled class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-xs font-semibold text-slate-500 cursor-not-allowed">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Managed in Account Settings.</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Job Title</label>
                    <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}" placeholder="e.g. Lead Mobile Engineer" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Timezone</label>
                    <select name="timezone" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                        <option value="Asia/Kolkata" {{ $user->timezone === 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (IST +5:30)</option>
                        <option value="UTC" {{ $user->timezone === 'UTC' ? 'selected' : '' }}>UTC (GMT)</option>
                        <option value="America/New_York" {{ $user->timezone === 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                        <option value="Europe/London" {{ $user->timezone === 'Europe/London' ? 'selected' : '' }}>Europe/London (BST)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Avatar Image URL</label>
                <input type="url" name="avatar_url" value="{{ old('avatar_url', $user->avatar_url) }}" placeholder="https://..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                <span class="text-[10px] text-slate-400 mt-0.5 block">Paste a public image URL or leave empty for automatic initials emblem.</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Bio / What I Work On</label>
                <textarea name="bio" rows="3" placeholder="Tell your teammates what you specialize in..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">{{ old('bio', $user->bio) }}</textarea>
            </div>

            <div class="flex items-center justify-end pt-3 border-t border-slate-100">
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-2xs transition-all">
                    Save Profile Changes
                </button>
            </div>
        </form>
    </div>

    <!-- My Projects Grid -->
    <div class="space-y-3">
        <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
            <i data-lucide="folder-kanban" class="w-4 h-4 text-[#199849]"></i>
            <span>My Projects ({{ $myProjects->count() }})</span>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            @forelse($myProjects as $p)
            <a href="{{ route('projects.show', $p->id) }}" class="p-4 bg-white rounded-2xl border border-slate-200/90 hover:border-emerald-500 transition-all shadow-2xs hover:shadow-xs group flex flex-col justify-between h-32">
                <div>
                    <div class="flex items-start justify-between gap-1">
                        <span class="font-bold text-xs text-slate-900 group-hover:text-emerald-700 transition-colors line-clamp-2">
                            {{ $p->name }}
                        </span>
                        @if($p->is_pinned)
                        <i data-lucide="star" class="w-3.5 h-3.5 text-amber-400 fill-amber-400 flex-shrink-0"></i>
                        @endif
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1 line-clamp-1">
                        {{ $p->client_subtitle ?: ($p->description ?: 'Active project') }}
                    </p>
                </div>
                <div class="flex items-center justify-between text-[10px] text-slate-400 font-semibold pt-2 border-t border-slate-100">
                    <span>{{ $p->members->count() }} members</span>
                    <span>{{ $p->tasks->count() }} to-dos</span>
                </div>
            </a>
            @empty
            <div class="col-span-full p-6 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
                <p class="text-xs font-semibold">No assigned projects yet.</p>
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection

