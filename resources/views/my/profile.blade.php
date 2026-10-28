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
    <div x-data="{
            avatarPreview: '{{ $user->avatar_url ?: "https://api.dicebear.com/7.x/initials/svg?seed=" . urlencode($user->name) }}',
            previewFile(e) {
                const file = e.target.files[0];
                if (file) {
                    this.avatarPreview = URL.createObjectURL(file);
                }
            }
         }"
         class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs space-y-6">
        
        <form action="{{ route('my.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Basecamp Profile Avatar Upload Area -->
            <div class="flex flex-col sm:flex-row items-center gap-6 p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80">
                <div class="relative group cursor-pointer" @click="$refs.avatarInput.click()" title="Click to choose a photo">
                    <img :src="avatarPreview" 
                         class="w-20 h-20 rounded-full border-2 border-emerald-500 object-cover shadow-sm group-hover:opacity-85 transition-all">
                    <div class="absolute inset-0 bg-black/40 rounded-full flex flex-col items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity">
                        <i data-lucide="camera" class="w-5 h-5 mb-0.5"></i>
                        <span class="text-[9px] font-bold">Change</span>
                    </div>
                </div>

                <div class="space-y-1.5 text-center sm:text-left flex-1">
                    <h3 class="text-sm font-black text-slate-900">Profile Photo</h3>
                    <p class="text-xs text-slate-500">Upload a picture from your device (PNG, JPG, JPEG, GIF, or WEBP up to 5MB).</p>
                    
                    <div class="flex items-center gap-2 justify-center sm:justify-start pt-1">
                        <button type="button" 
                                @click="$refs.avatarInput.click()" 
                                class="px-3.5 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl shadow-2xs transition-colors flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="upload" class="w-3.5 h-3.5 text-slate-500"></i>
                            <span>Upload photo...</span>
                        </button>
                        <input x-ref="avatarInput" 
                               type="file" 
                               name="avatar" 
                               accept="image/png,image/jpeg,image/jpg,image/webp,image/gif" 
                               class="hidden" 
                               @change="previewFile($event)">
                    </div>
                </div>
            </div>

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
                <label class="block text-xs font-bold text-slate-700 mb-1">Bio / What I Work On</label>
                <textarea name="bio" rows="3" placeholder="Tell your teammates what you specialize in..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">{{ old('bio', $user->bio) }}</textarea>
            </div>

            <div class="flex items-center justify-end pt-3 border-t border-slate-100">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-2xs transition-all cursor-pointer">
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

