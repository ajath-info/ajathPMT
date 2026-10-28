@extends('layouts.app')

@section('title', 'Ajath Workspace')

@section('content')
<div x-data="{ 
    createModal: false, 
    inviteModal: false,
    folderModal: false,
    projectFilter: 'active',
    selectedColor: 'white',
    inviteRole: 'MEMBER',
    editingLogo: false,
    displayMode: '{{ $currentOrg->logo_url === 'text_only' ? 'text' : 'logo' }}',
    logoUrl: '{{ ($currentOrg->logo_url && $currentOrg->logo_url !== 'text_only') ? $currentOrg->logo_url : asset('images/logo.png') }}',
    tempPreview: null,
    isDragging: false,
    previewLogo(e) {
        const file = e.target.files[0];
        if (file) {
            this.tempPreview = URL.createObjectURL(file);
        }
    },
    handleLogoDrop(e) {
        this.isDragging = false;
        const file = e.dataTransfer.files[0];
        if (file) {
            this.$refs.logoFileInput.files = e.dataTransfer.files;
            this.tempPreview = URL.createObjectURL(file);
        }
    },
    saveTextOnly() {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('mode', 'text');
        fetch('{{ route('organization.logo') }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData
        }).then(r => r.json()).then(data => {
            this.displayMode = 'text';
            this.editingLogo = false;
            this.tempPreview = null;
        }).catch(() => {
            window.location.reload();
        });
    },
    toggleStar(projId, el) {
        const icon = el.querySelector('svg') || el.querySelector('i');
        fetch('/projects/' + projId + '/toggle-pin', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(r => r.json()).then(data => {
            if (icon) {
                if (data.is_pinned) {
                    icon.classList.add('text-amber-400', 'fill-amber-400');
                    icon.classList.remove('text-slate-300');
                } else {
                    icon.classList.remove('text-amber-400', 'fill-amber-400');
                    icon.classList.add('text-slate-300');
                }
            }
        }).catch(() => {
            window.location.reload();
        });
    }
}" class="space-y-8">

    <!-- Top Hero Section: "Ai" Gradient Emblem & Action Bar -->
    <div class="flex flex-col items-center justify-center pt-2 pb-4 space-y-4">
        
        <!-- Logo Area / Upload Box (Matching Basecamp screenshots) -->
        <div class="w-full flex justify-center">
            
            <!-- 1. Normal View (when not editing) -->
            <div x-show="!editingLogo" class="relative group inline-flex items-center justify-center">
                <!-- Image Logo View -->
                <div x-show="displayMode === 'logo'" class="flex items-center justify-center">
                    @php
                        $activeLogo = ($currentOrg->logo_url && $currentOrg->logo_url !== 'text_only') ? $currentOrg->logo_url : asset('images/logo.png');
                    @endphp
                    <img src="{{ $activeLogo }}" 
                         :src="logoUrl" 
                         alt="{{ $currentOrg->name }}" 
                         class="h-20 sm:h-24 w-auto max-w-[260px] object-contain select-none cursor-pointer transition-transform group-hover:scale-[1.01]" 
                         @click="editingLogo = true"
                         onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                </div>

                <!-- Company Name Text View (when 'Just display our company name' selected) -->
                <div x-show="displayMode === 'text'" class="py-2">
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight cursor-pointer hover:text-slate-900 transition-colors"
                        @click="editingLogo = true">
                        {{ $currentOrg->name }}
                    </h1>
                </div>

                <!-- Settings Gear Icon on Hover (Matches media_1793203009707_94d5c896.png) -->
                @if(in_array($userRole, ['OWNER', 'ADMIN']))
                <button type="button" 
                        @click="editingLogo = true" 
                        class="absolute -top-1 -right-8 p-1 text-slate-400 hover:text-slate-700 opacity-0 group-hover:opacity-100 transition-all rounded-lg hover:bg-slate-200/50" 
                        title="Change logo or company name">
                    <i data-lucide="settings" class="w-4 h-4 text-slate-500 hover:text-slate-800"></i>
                </button>
                @endif
            </div>

            <!-- 2. Edit Mode View (Matches media_1793203037855_b2659fc2.png) -->
            <div x-show="editingLogo" x-cloak class="w-full max-w-sm mx-auto flex flex-col items-center space-y-2.5">
                <form action="{{ route('organization.logo') }}" 
                      method="POST" 
                      enctype="multipart/form-data" 
                      class="w-full flex flex-col items-center space-y-2.5">
                    @csrf
                    
                    <!-- Dashed Border Box -->
                    <div @click="$refs.logoFileInput.click()" 
                         @dragover.prevent="isDragging = true" 
                         @dragleave.prevent="isDragging = false" 
                         @drop.prevent="handleLogoDrop($event)"
                         :class="isDragging ? 'border-emerald-500 bg-emerald-50/50' : 'border-slate-300/90 bg-transparent hover:bg-white/60'"
                         class="w-[280px] h-[95px] border-2 border-dashed rounded-xl flex flex-col items-center justify-center cursor-pointer transition-all">
                        
                        <template x-if="!tempPreview">
                            <span class="text-sm font-semibold text-slate-600">Add your logo...</span>
                        </template>

                        <template x-if="tempPreview">
                            <img :src="tempPreview" class="h-16 max-w-[240px] object-contain">
                        </template>

                        <input x-ref="logoFileInput" 
                               type="file" 
                               name="logo" 
                               accept="image/*" 
                               class="hidden" 
                               @change="previewLogo($event)">
                    </div>

                    <!-- Save and Cancel Buttons -->
                    <div class="flex items-center justify-center gap-2">
                        <button type="submit" 
                                class="px-4 py-1.5 bg-[#2870c9] hover:bg-[#1f5ca8] text-white font-semibold text-xs rounded-lg shadow-2xs transition-all">
                            Save
                        </button>
                        <button type="button" 
                                @click="editingLogo = false; tempPreview = null;" 
                                class="px-4 py-1.5 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-lg border border-slate-300 shadow-2xs transition-all">
                            Cancel
                        </button>
                    </div>

                    <!-- Just display our company name link -->
                    <div>
                        <button type="button" 
                                @click="saveTextOnly()" 
                                class="text-xs text-slate-600 hover:text-slate-900 underline underline-offset-2 transition-colors">
                            Just display our company name
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Search / Jump keyboard prompt -->
        <div class="text-xs text-slate-500 font-medium flex items-center gap-1.5 cursor-pointer hover:text-slate-800 transition-colors" @click="searchModal = true">
            <span>Press</span>
            <kbd class="px-1.5 py-0.5 text-[10px] font-bold font-mono bg-white text-slate-700 border border-slate-300 rounded shadow-2xs">SHIFT</kbd>
            <kbd class="px-1.5 py-0.5 text-[10px] font-bold font-mono bg-white text-slate-700 border border-slate-300 rounded shadow-2xs">J</kbd>
            <span>anytime to search or jump</span>
        </div>

        <!-- Basecamp Action Pill Buttons Bar -->
        <div class="flex items-center gap-2 flex-wrap justify-center pt-1">
            @if(in_array($userRole, ['OWNER', 'ADMIN', 'MEMBER']))
            <button @click="createModal = true" class="flex items-center gap-1.5 px-3.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-full border border-slate-300 shadow-2xs transition-all">
                <i data-lucide="plus" class="w-3.5 h-3.5 text-blue-600"></i>
                <span>New project</span>
            </button>
            @endif

            <button @click="folderModal = true" class="flex items-center gap-1.5 px-3.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-full border border-slate-300 shadow-2xs transition-all">
                <i data-lucide="folder" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Add a folder</span>
            </button>

            @if(in_array($userRole, ['OWNER', 'ADMIN']))
            <button @click="inviteModal = true" class="flex items-center gap-1.5 px-3.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-full border border-slate-300 shadow-2xs transition-all">
                <i data-lucide="user-plus" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Invite people</span>
            </button>

            <a href="{{ route('adminland.index') }}" class="flex items-center gap-1.5 px-3.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-full border border-slate-300 shadow-2xs transition-all">
                <i data-lucide="key" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Adminland</span>
            </a>

            <a href="{{ route('adminland.import') }}" class="flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs rounded-full border border-amber-300 shadow-2xs transition-all" title="Import from Basecamp">
                <i data-lucide="arrow-down-to-dot" class="w-3.5 h-3.5 text-amber-600"></i>
                <span>Import Basecamp</span>
            </a>
            @endif

            <!-- Theme / Droplet Icon -->
            <button @click="createModal = true" class="p-1.5 bg-white hover:bg-slate-50 text-slate-500 hover:text-slate-800 rounded-full border border-slate-300 shadow-2xs transition-all" title="Customize">
                <i data-lucide="droplet" class="w-3.5 h-3.5"></i>
            </button>
        </div>

    </div>

    <!-- The 4-Column Basecamp Project Cards Grid -->
    <div class="max-w-[1360px] mx-auto space-y-3">
        <!-- Live vs Archived Switcher -->
        <div class="flex items-center justify-between px-1">
            <div class="flex items-center gap-3 text-xs font-bold">
                <button @click="projectFilter = 'active'" :class="projectFilter === 'active' ? 'text-slate-900 border-b-2 border-emerald-600 pb-0.5' : 'text-slate-400 hover:text-slate-700'" class="transition-colors flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Live Projects ({{ $projects->where('status', 'ACTIVE')->count() }})</span>
                </button>
                <span class="text-slate-300">|</span>
                <button @click="projectFilter = 'archived'" :class="projectFilter === 'archived' ? 'text-slate-900 border-b-2 border-amber-600 pb-0.5' : 'text-slate-400 hover:text-slate-700'" class="transition-colors flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <span>Archived Projects ({{ $projects->where('status', 'ARCHIVED')->count() }})</span>
                </button>
            </div>

            <span class="text-[11px] text-slate-400 font-medium">Sorted by Starred</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            
            @forelse($projects as $proj)
            @php
                $color = $proj->color_theme ?? 'white';
                $cardStyles = [
                    'yellow' => 'bg-[#fefce8] border-[#fde047] hover:border-[#eab308] border-t-4 border-t-[#eab308]',
                    'orange' => 'bg-[#fff7ed] border-[#fed7aa] hover:border-[#f97316] border-t-4 border-t-[#f97316]',
                    'coral'  => 'bg-[#fff1f2] border-[#fecdd3] hover:border-[#f43f5e] border-t-4 border-t-[#f43f5e]',
                    'pink'   => 'bg-[#fdf2f8] border-[#fbcfe8] hover:border-[#ec4899] border-t-4 border-t-[#ec4899]',
                    'purple' => 'bg-[#f5f3ff] border-[#ddd6fe] hover:border-[#8b5cf6] border-t-4 border-t-[#8b5cf6]',
                    'blue'   => 'bg-[#eff6ff] border-[#bfdbfe] hover:border-[#3b82f6] border-t-4 border-t-[#3b82f6]',
                    'green'  => 'bg-[#f0fdf4] border-[#bbf7d0] hover:border-[#22c55e] border-t-4 border-t-[#22c55e]',
                    'sand'   => 'bg-[#fbf7ee] border-[#e7dcce] hover:border-[#b49770] border-t-4 border-t-[#b49770]',
                    'gray'   => 'bg-[#f8fafc] border-[#e2e8f0] hover:border-[#94a3b8] border-t-4 border-t-[#94a3b8]',
                    'white'  => 'bg-white border-slate-200/90 hover:border-slate-300',
                ][$color] ?? 'bg-white border-slate-200/90 hover:border-slate-300';

                $avatarColors = [
                    'SN' => 'bg-purple-600',
                    'PK' => 'bg-teal-500',
                    'DT' => 'bg-emerald-600',
                    'RO' => 'bg-blue-600',
                    'GK' => 'bg-fuchsia-600',
                    'E'  => 'bg-orange-500',
                    'PI' => 'bg-pink-500',
                    'AI' => 'bg-emerald-700',
                    'RS' => 'bg-rose-500',
                    'RK' => 'bg-indigo-600',
                    'AY' => 'bg-violet-600',
                    'IY' => 'bg-cyan-600',
                ];
            @endphp

            <div x-data="{
                    cardColor: '{{ $color }}',
                    popoverOpen: false,
                    isStarred: {{ $proj->is_pinned ? 'true' : 'false' }},
                    colorMap: {
                        white:  'bg-white border-slate-200/90 hover:border-slate-300',
                        yellow: 'bg-[#fefce8] border-[#fde047] hover:border-[#eab308] border-t-4 border-t-[#eab308]',
                        orange: 'bg-[#fff7ed] border-[#fed7aa] hover:border-[#f97316] border-t-4 border-t-[#f97316]',
                        coral:  'bg-[#fff1f2] border-[#fecdd3] hover:border-[#f43f5e] border-t-4 border-t-[#f43f5e]',
                        pink:   'bg-[#fdf2f8] border-[#fbcfe8] hover:border-[#ec4899] border-t-4 border-t-[#ec4899]',
                        purple: 'bg-[#f5f3ff] border-[#ddd6fe] hover:border-[#8b5cf6] border-t-4 border-t-[#8b5cf6]',
                        blue:   'bg-[#eff6ff] border-[#bfdbfe] hover:border-[#3b82f6] border-t-4 border-t-[#3b82f6]',
                        green:  'bg-[#f0fdf4] border-[#bbf7d0] hover:border-[#22c55e] border-t-4 border-t-[#22c55e]',
                        sand:   'bg-[#fbf7ee] border-[#e7dcce] hover:border-[#b49770] border-t-4 border-t-[#b49770]',
                        gray:   'bg-[#f8fafc] border-[#e2e8f0] hover:border-[#94a3b8] border-t-4 border-t-[#94a3b8]'
                    },
                    updateColor(c) {
                        this.cardColor = c;
                        this.popoverOpen = false;
                        fetch('{{ route('projects.color', $proj->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ color_theme: c })
                        });
                    },
                    toggleStar() {
                        this.isStarred = !this.isStarred; // Single click: grey -> yellow instantly!
                        fetch('{{ route('projects.toggle-pin', $proj->id) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        }).then(r => r.json()).then(data => {
                            this.isStarred = !!data.is_pinned;
                            setTimeout(() => {
                                window.location.reload();
                            }, 280);
                        }).catch(err => {
                            console.error(err);
                        });
                    }
                 }"
                 x-show="projectFilter === 'all' || (projectFilter === 'active' && '{{ $proj->status }}' === 'ACTIVE') || (projectFilter === 'archived' && '{{ $proj->status }}' === 'ARCHIVED')"
                 :class="colorMap[cardColor] || colorMap['white']"
                 class="relative rounded-2xl p-4 h-[165px] flex flex-col justify-between transition-all duration-200 basecamp-card-shadow group border">
                
                <!-- Card Header: Title, Subtitle, Star -->
                <div>
                    <div class="flex items-start justify-between gap-2">
                        <a href="{{ route('projects.show', $proj->id) }}" class="font-bold text-sm text-slate-900 hover:text-emerald-700 transition-colors line-clamp-2 leading-snug flex-1">
                            {{ $proj->name }}
                        </a>

                        <!-- Star / Pin Toggle (Single click instant color change & auto-sort) -->
                        <button type="button" 
                                @click.stop="toggleStar()" 
                                class="flex-shrink-0 transition-transform hover:scale-125 pt-0.5 cursor-pointer focus:outline-none" 
                                :title="isStarred ? 'Unstar this project' : 'Star this project'">
                            <svg class="w-4 h-4 transition-colors duration-150" 
                                 :class="isStarred ? 'text-amber-400 fill-amber-400' : 'text-slate-300 fill-transparent hover:text-amber-300'" 
                                 xmlns="http://www.w3.org/2000/svg" 
                                 viewBox="0 0 24 24" 
                                 stroke="currentColor" 
                                 stroke-width="2" 
                                 stroke-linecap="round" 
                                 stroke-linejoin="round">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            </svg>
                        </button>
                    </div>

                    <!-- Client Subtitle -->
                    <p class="text-[11px] text-slate-500 mt-1 line-clamp-2 leading-tight">
                        {{ $proj->client_subtitle ?: ($proj->description ?: 'Active project') }}
                    </p>

                    <!-- Badge (e.g. All-access) -->
                    @if($proj->badge_text)
                    <span class="inline-block mt-1.5 px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-sky-100 text-sky-700">
                        {{ $proj->badge_text }}
                    </span>
                    @endif
                </div>

                <!-- Bottom Row: Overlapping Member Avatars, Donut, and Rainbow Color Picker -->
                <div class="flex items-center justify-between pt-2">
                    <div class="flex items-center -space-x-1.5">
                        @foreach($proj->members->take(6) as $m)
                        @php
                            $words = explode(' ', trim($m->name));
                            $inits = count($words) >= 2 
                                ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                                : strtoupper(substr($m->name, 0, 2));
                            if (preg_match('/\(([A-Z]{1,2})\)/', $m->name, $matches)) {
                                $inits = $matches[1];
                            }
                            $bgCol = $avatarColors[$inits] ?? 'bg-slate-600';
                        @endphp
                        <span class="w-6 h-6 rounded-full {{ $bgCol }} border-2 border-white text-[9px] font-extrabold flex items-center justify-center text-white shadow-2xs" title="{{ $m->name }}">
                            {{ $inits }}
                        </span>
                        @endforeach

                        @if($proj->members->count() > 6)
                        <span class="w-6 h-6 rounded-full bg-slate-200 border-2 border-white text-[9px] font-bold flex items-center justify-center text-slate-600 shadow-2xs">
                            +{{ $proj->members->count() - 6 }}
                        </span>
                        @endif
                    </div>

                    <!-- Right of Bottom Row: Donut + Palette Ring Button with Popover -->
                    <div class="flex items-center gap-2">
                        <!-- Progress Donut (if present) -->
                        @if(isset($proj->progress) && $proj->progress > 0)
                        <div class="w-4 h-4 rounded-full border-2 border-orange-300 border-t-orange-500 animate-spin-none" title="{{ $proj->progress }}% completed"></div>
                        @endif

                        <!-- Rainbow Palette Ring Button & Swatches Popover (Matches Basecamp screenshot) -->
                        <div class="relative">
                            <button type="button" 
                                    @click.stop="popoverOpen = !popoverOpen" 
                                    class="w-5 h-5 rounded-full p-0.5 border border-slate-300/80 hover:scale-110 transition-transform bg-white shadow-2xs flex items-center justify-center cursor-pointer" 
                                    title="Choose card color">
                                <div class="w-3.5 h-3.5 rounded-full" style="background: conic-gradient(#ef4444, #f97316, #eab308, #22c55e, #3b82f6, #a855f7, #ef4444);"></div>
                            </button>

                            <!-- 10-Swatch Popover (Exact match to Basecamp color picker) -->
                            <div x-show="popoverOpen" 
                                 x-cloak 
                                 @click.outside="popoverOpen = false" 
                                 class="absolute bottom-7 right-0 z-50 p-2.5 bg-white rounded-2xl shadow-xl border border-slate-200 w-44 animate-in fade-in zoom-in-95">
                                <div class="grid grid-cols-5 gap-2">
                                    <!-- Row 1 -->
                                    <button type="button" @click="updateColor('white')" class="w-6 h-6 rounded-full border border-slate-300 relative flex items-center justify-center hover:scale-110 transition-transform bg-white shadow-2xs" title="Default (White)">
                                        <span class="w-[1.5px] h-5 bg-rose-500 rotate-45 transform"></span>
                                    </button>
                                    <button type="button" @click="updateColor('yellow')" class="w-6 h-6 rounded-full bg-[#eab308] hover:scale-110 transition-transform shadow-2xs" title="Yellow"></button>
                                    <button type="button" @click="updateColor('orange')" class="w-6 h-6 rounded-full bg-[#f97316] hover:scale-110 transition-transform shadow-2xs" title="Orange"></button>
                                    <button type="button" @click="updateColor('coral')" class="w-6 h-6 rounded-full bg-[#f43f5e] hover:scale-110 transition-transform shadow-2xs" title="Coral"></button>
                                    <button type="button" @click="updateColor('pink')" class="w-6 h-6 rounded-full bg-[#ec4899] hover:scale-110 transition-transform shadow-2xs" title="Pink"></button>

                                    <!-- Row 2 -->
                                    <button type="button" @click="updateColor('purple')" class="w-6 h-6 rounded-full bg-[#8b5cf6] hover:scale-110 transition-transform shadow-2xs" title="Purple"></button>
                                    <button type="button" @click="updateColor('blue')" class="w-6 h-6 rounded-full bg-[#38bdf8] hover:scale-110 transition-transform shadow-2xs" title="Blue"></button>
                                    <button type="button" @click="updateColor('green')" class="w-6 h-6 rounded-full bg-[#22c55e] hover:scale-110 transition-transform shadow-2xs" title="Green"></button>
                                    <button type="button" @click="updateColor('sand')" class="w-6 h-6 rounded-full bg-[#b49770] hover:scale-110 transition-transform shadow-2xs" title="Sand"></button>
                                    <button type="button" @click="updateColor('gray')" class="w-6 h-6 rounded-full bg-[#64748b] hover:scale-110 transition-transform shadow-2xs" title="Gray"></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            @empty
            <div class="col-span-full py-16 text-center text-slate-400 space-y-3">
                <i data-lucide="layers" class="w-12 h-12 mx-auto opacity-40"></i>
                <p class="text-sm font-semibold">No projects found in this workspace.</p>
                @if(in_array($userRole, ['OWNER', 'ADMIN']))
                <button @click="createModal = true" class="px-4 py-2 bg-emerald-600 text-white font-bold rounded-xl text-xs">
                    Create your first project
                </button>
                @endif
            </div>
            @endforelse

        </div>

        <!-- See all projects bottom pill -->
        <div class="text-center pt-8">
            <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-2 px-5 py-2 bg-slate-200/90 hover:bg-slate-300 text-slate-700 font-bold rounded-xl text-xs transition-all shadow-2xs hover:shadow-xs">
                <span>See all projects</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>

    <!-- Modal: + New Project -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <div @click="createModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
            
            <div class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all max-w-lg w-full border border-slate-200 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-black text-slate-900">Create a New Project</h3>
                    <button @click="createModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('projects.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Project Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Mobile Apps & Admin Console" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Client / Subtitle Label</label>
                        <input type="text" name="client_subtitle" placeholder="e.g. Client: Seller and Driver Apps" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Description (Optional)</label>
                        <textarea name="description" rows="2" placeholder="Brief scope of work..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <!-- Basecamp 10 Professional Color Themes -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Card Color Accent</label>
                        <div class="grid grid-cols-5 gap-2">
                            <label class="flex items-center justify-center p-2 rounded-xl bg-white border border-slate-300 cursor-pointer">
                                <input type="radio" name="color_theme" value="white" checked class="text-slate-800">
                                <span class="text-[10px] font-bold text-slate-600 ml-1">White</span>
                            </label>
                            <label class="flex items-center justify-center p-2 rounded-xl bg-[#fefce8] border border-amber-300 cursor-pointer">
                                <input type="radio" name="color_theme" value="yellow" class="text-amber-500">
                                <span class="text-[10px] font-bold text-amber-700 ml-1">Yellow</span>
                            </label>
                            <label class="flex items-center justify-center p-2 rounded-xl bg-[#fff7ed] border border-orange-300 cursor-pointer">
                                <input type="radio" name="color_theme" value="orange" class="text-orange-500">
                                <span class="text-[10px] font-bold text-orange-700 ml-1">Orange</span>
                            </label>
                            <label class="flex items-center justify-center p-2 rounded-xl bg-[#fff1f2] border border-rose-300 cursor-pointer">
                                <input type="radio" name="color_theme" value="coral" class="text-rose-500">
                                <span class="text-[10px] font-bold text-rose-700 ml-1">Coral</span>
                            </label>
                            <label class="flex items-center justify-center p-2 rounded-xl bg-[#fdf2f8] border border-pink-300 cursor-pointer">
                                <input type="radio" name="color_theme" value="pink" class="text-pink-500">
                                <span class="text-[10px] font-bold text-pink-700 ml-1">Pink</span>
                            </label>
                            <label class="flex items-center justify-center p-2 rounded-xl bg-[#f5f3ff] border border-purple-300 cursor-pointer">
                                <input type="radio" name="color_theme" value="purple" class="text-purple-500">
                                <span class="text-[10px] font-bold text-purple-700 ml-1">Purple</span>
                            </label>
                            <label class="flex items-center justify-center p-2 rounded-xl bg-[#eff6ff] border border-sky-300 cursor-pointer">
                                <input type="radio" name="color_theme" value="blue" class="text-sky-500">
                                <span class="text-[10px] font-bold text-sky-700 ml-1">Blue</span>
                            </label>
                            <label class="flex items-center justify-center p-2 rounded-xl bg-[#f0fdf4] border border-emerald-300 cursor-pointer">
                                <input type="radio" name="color_theme" value="green" class="text-emerald-500">
                                <span class="text-[10px] font-bold text-emerald-700 ml-1">Green</span>
                            </label>
                            <label class="flex items-center justify-center p-2 rounded-xl bg-[#fbf7ee] border border-amber-200 cursor-pointer">
                                <input type="radio" name="color_theme" value="sand" class="text-amber-700">
                                <span class="text-[10px] font-bold text-amber-800 ml-1">Sand</span>
                            </label>
                            <label class="flex items-center justify-center p-2 rounded-xl bg-[#f8fafc] border border-slate-300 cursor-pointer">
                                <input type="radio" name="color_theme" value="gray" class="text-slate-600">
                                <span class="text-[10px] font-bold text-slate-700 ml-1">Gray</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="createModal = false" class="px-4 py-2 text-xs font-bold text-slate-600">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-colors">
                            Create project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Invite People / Client -->
    <div x-show="inviteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <div @click="inviteModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
            
            <div class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all max-w-lg w-full border border-slate-200 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-black text-slate-900">Add Team Member or Client</h3>
                    <button @click="inviteModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('people.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl">
                        <button type="button" @click="inviteRole = 'MEMBER'" :class="inviteRole !== 'CLIENT' ? 'bg-white shadow text-slate-900 font-bold' : 'text-slate-600 font-semibold'" class="py-2 rounded-lg text-xs transition-all">
                            Team Member
                        </button>
                        <button type="button" @click="inviteRole = 'CLIENT'" :class="inviteRole === 'CLIENT' ? 'bg-white shadow text-slate-900 font-bold' : 'text-slate-600 font-semibold'" class="py-2 rounded-lg text-xs transition-all">
                            Client Partner
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Full Name *</label>
                            <input type="text" name="name" required placeholder="e.g. Deepak Tiwari" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email (Login) *</label>
                            <input type="email" name="email" required placeholder="deepak@company.com" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Password *</label>
                            <input type="password" name="password" required placeholder="Initial password" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div x-show="inviteRole !== 'CLIENT'">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Role</label>
                            <select name="role" :disabled="inviteRole === 'CLIENT'" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                                <option value="MEMBER">Member (Assigned Projects)</option>
                                <option value="ADMIN">Admin (All Projects)</option>
                            </select>
                        </div>

                        <template x-if="inviteRole === 'CLIENT'">
                            <input type="hidden" name="role" value="CLIENT">
                        </template>

                        <div x-show="inviteRole === 'CLIENT'" class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Client Company *</label>
                            <input type="text" name="client_company" placeholder="e.g. NDC Homes" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                        </div>
                    </div>

                    <div class="space-y-1.5 pt-2 border-t border-slate-100">
                        <label class="block text-xs font-bold text-slate-700">Assign to Projects</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 max-h-36 overflow-y-auto p-2 bg-slate-50 rounded-xl border border-slate-200">
                            @foreach($projects as $p)
                            <label class="flex items-center gap-2 p-1.5 rounded-lg bg-white border border-slate-100 text-xs font-semibold cursor-pointer">
                                <input type="checkbox" name="project_ids[]" value="{{ $p->id }}" class="rounded text-emerald-600">
                                <span class="truncate">{{ $p->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="inviteModal = false" class="px-4 py-2 text-xs font-bold text-slate-600">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-colors">
                            Send Login Access
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Add a Folder -->
    <div x-show="folderModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <div @click="folderModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
            
            <div class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all max-w-sm w-full border border-slate-200 p-6 space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h3 class="text-sm font-black text-slate-900">Add a Project Folder</h3>
                    <button @click="folderModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <p class="text-xs text-slate-500">Group related client and internal projects into categorized folders.</p>
                <div>
                    <input type="text" placeholder="e.g. 2026 Client Deliverables" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button @click="folderModal = false" class="px-3 py-1.5 text-xs font-bold text-slate-600">Cancel</button>
                    <button @click="folderModal = false" class="px-4 py-1.5 bg-emerald-600 text-white font-bold rounded-xl text-xs">Save Folder</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
