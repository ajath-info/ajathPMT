@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div x-data="{ 
    createModal: false, 
    inviteModal: false,
    folderModal: false,
    expandedHome: false,
    showAllProjects: false,
    isAnimating: false,
    animTimer: null,
    toggleProjectStar(projectId, event) {
        const btn = event.currentTarget;
        const svg = btn ? btn.querySelector('svg') : null;
        if (svg) {
            const isCurrentlyStarred = svg.classList.contains('fill-[#f5b642]');
            if (isCurrentlyStarred) {
                svg.classList.remove('text-[#f5b642]', 'fill-[#f5b642]');
                svg.classList.add('text-slate-300', 'fill-transparent');
            } else {
                svg.classList.remove('text-slate-300', 'fill-transparent');
                svg.classList.add('text-[#f5b642]', 'fill-[#f5b642]');
            }
        }
        fetch('/projects/' + projectId + '/toggle-pin', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
    },
    toggleExpandedHome() {
        if (this.animTimer) clearTimeout(this.animTimer);
        this.isAnimating = true;
        // Force a style flush so .animating transition rule is active before width changes
        void this.$el.offsetWidth;
        this.expandedHome = !this.expandedHome;
        this.animTimer = setTimeout(() => {
            this.isAnimating = false;
        }, 350);
    },
    editingFolderId: null,
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
    }
}" class="bc-home-shell">

    <!-- 3-Column Basecamp Dashboard Layout (Exact match to app.basecamp.com) -->
    <div :class="[expandedHome ? 'is-expanded' : '', isAnimating ? 'animating' : '', pingsModal ? 'is-pings-open' : '']"
         class="bc-home-row">

        <!-- ================= 1. LEFT COLUMN ================= -->
        <div x-ref="leftCol"
             x-show="!expandedHome"
             class="bc-home-left">
            @php
                $hour = (int)now()->timezone('Asia/Kolkata')->format('H');
                $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
                $firstName = explode(' ', trim($user->name))[0];
            @endphp
            <!-- Greeting (1 single line: 'Good afternoon, Manjot') -->
            <h1 class="text-[17.5px] font-bold text-[#1d2d35] tracking-[-0.02em] leading-tight whitespace-nowrap mb-3">
                {{ $greeting }}, {{ $firstName }}
            </h1>

            <!-- Action Buttons Stack (Right-aligned, auto-width pills hugging content) -->
            <div class="space-y-2 flex flex-col items-center lg:items-end">
                <!-- + Make a new project / + New project -->
                @if(in_array($userRole, ['OWNER', 'ADMIN', 'MEMBER']))
                <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-1.5 py-1.5 px-3.5 bg-white hover:bg-slate-50 text-[#1d2d35] font-bold text-[14.5px] rounded-[8px] border border-[#e8e5df] shadow-[0_1px_2px_rgba(0,0,0,0.04)] transition-all cursor-pointer whitespace-nowrap">
                    <span class="text-[#1b6ac9] font-normal text-[18px] leading-none -mt-0.5">+</span>
                    <span x-text="pingsModal ? 'New project' : 'Make a new project'">Make a new project</span>
                </a>
                @endif

                <!-- Add a folder -->
                <button type="button" @click="addFolder()" class="inline-flex items-center gap-2 py-1.5 px-3.5 bg-white hover:bg-slate-50 focus:ring-2 focus:ring-[#1b6ac9] focus:border-[#1b6ac9] focus:outline-none text-[#1d2d35] font-bold text-[14.5px] rounded-[8px] border border-[#e8e5df] shadow-[0_1px_2px_rgba(0,0,0,0.04)] transition-all cursor-pointer whitespace-nowrap">
                    <i data-lucide="folder" class="w-4 h-4 text-[#1b6ac9] stroke-[2]"></i>
                    <span>Add a folder</span>
                </button>

                <!-- Invite people to the account / Invite people -->
                @if(in_array($userRole, ['OWNER', 'ADMIN']))
                <a href="{{ route('enrollments.create') }}" class="inline-flex items-center gap-2 py-1.5 px-3.5 bg-white hover:bg-slate-50 text-[#1d2d35] font-bold text-[14.5px] rounded-[8px] border border-[#e8e5df] shadow-[0_1px_2px_rgba(0,0,0,0.04)] transition-all cursor-pointer whitespace-nowrap">
                    <i data-lucide="user-plus" class="w-4 h-4 text-[#1b6ac9] stroke-[2]"></i>
                    <span x-text="pingsModal ? 'Invite people' : 'Invite people to the account'">Invite people to the account</span>
                </a>

                <!-- Adminland -->
                <a href="{{ route('adminland.index') }}" class="inline-flex items-center gap-2 py-1.5 px-3.5 bg-white hover:bg-slate-50 text-[#1d2d35] font-bold text-[14.5px] rounded-[8px] border border-[#e8e5df] shadow-[0_1px_2px_rgba(0,0,0,0.04)] transition-all whitespace-nowrap">
                    <i data-lucide="key" class="w-4 h-4 text-[#1b6ac9] stroke-[2]"></i>
                    <span>Adminland</span>
                </a>
                @endif

                <!-- Droplet customize icon (Right-aligned below Adminland) -->
                <div class="pt-1 pr-1">
                    <button type="button" @click="window.toggleBasecampDarkLight && window.toggleBasecampDarkLight()" class="text-[#5a656d] hover:text-[#1d2d35] p-1 rounded-full transition-colors cursor-pointer" title="Toggle Dark / Light Theme">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                            <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- ================= 2. MIDDLE COLUMN (SHADED WELL OR EXPANDED 4-COL VIEW) ================= -->
        <div :class="expandedHome ? 'is-expanded' : ''"
             class="bc-home-middle">
            <div data-flip-well
                 :class="expandedHome ? 'is-expanded' : ''"
                 class="bc-home-well">
                
                <!-- Expand / Collapse icon in top right -->
                <div :class="expandedHome ? 'absolute top-1.5 right-1' : 'absolute top-3.5 right-3.5'" class="absolute top-3.5 right-3.5 z-10">
                    <button type="button" @click="toggleExpandedHome()" class="p-1.5 text-[#283c46] hover:text-black hover:bg-black/5 rounded-lg transition-all duration-200 cursor-pointer" :title="expandedHome ? 'Collapse view' : 'Expand view'">
                        <!-- Maximize icon (shown when collapsed) -->
                        <svg x-show="!expandedHome" class="w-4 h-4 stroke-[2] transition-transform duration-200 hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 3 21 3 21 9"></polyline>
                            <polyline points="9 21 3 21 3 15"></polyline>
                            <line x1="21" y1="3" x2="14" y2="10"></line>
                            <line x1="3" y1="21" x2="10" y2="14"></line>
                        </svg>
                        <!-- Minimize icon (shown when expanded, matching media_1793274322057_554955ce.jpg) -->
                        <svg x-show="expandedHome" x-cloak class="w-4 h-4 stroke-[2] transition-transform duration-200 hover:scale-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="4 14 10 14 10 20"></polyline>
                            <polyline points="20 10 14 10 14 4"></polyline>
                            <line x1="14" y1="10" x2="21" y2="3"></line>
                            <line x1="3" y1="21" x2="10" y2="14"></line>
                        </svg>
                    </button>
                </div>

                <!-- Center: Purple "Ai" Emblem & Search/Jump shortcut hint -->
                <div class="flex flex-col items-center justify-center pt-1 pb-2.5">
                    <div class="relative group inline-flex items-center justify-center">
                        <img data-flip-logo
                             src="{{ asset('images/logo.png') }}" 
                             alt="{{ $currentOrg->name }}" 
                             class="bc-home-logo object-contain select-none cursor-pointer group-hover:scale-105 transition-transform"
                             @click="editingLogo = true"
                             title="Click to customize workspace logo">
                    </div>

                    <!-- Search / Jump keyboard prompt -->
                    <div data-flip-prompt
                         class="bc-home-prompt mt-2.5 text-[#6d6a65] font-normal flex items-center justify-center gap-1 cursor-pointer hover:text-[#1d2d35]"
                         @click="navDropdown = true; navSelectedIndex = 0; $nextTick(() => $refs.navSearchInput && $refs.navSearchInput.focus())">
                        <span>Press</span>
                        <kbd class="px-1.5 py-0.5 text-[10px] font-semibold font-sans bg-white text-[#6d6a65] border border-[#bfbcb4] rounded-[4px] shadow-[0_1px_0_rgba(0,0,0,0.08)] leading-none">SHIFT</kbd>
                        <kbd class="px-1.5 py-0.5 text-[10px] font-semibold font-sans bg-white text-[#6d6a65] border border-[#bfbcb4] rounded-[4px] shadow-[0_1px_0_rgba(0,0,0,0.08)] leading-none">J</kbd>
                        <span>anytime to search or jump</span>
                    </div>

                    <!-- Horizontal Action Buttons Row (Shown in Expanded Home View matching media_1793274322057_554955ce.jpg) -->
                    <div x-ref="expandedActions"
                         x-show="expandedHome"
                         x-cloak
                         class="flex flex-wrap items-center justify-center gap-2.5 mt-4 mb-3">
                        @if(in_array($userRole, ['OWNER', 'ADMIN', 'MEMBER']))
                        <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-1.5 py-1.5 px-3.5 bg-white hover:bg-slate-50 text-[#1d2d35] font-bold text-[14.5px] rounded-[8px] border border-[#e8e5df] shadow-[0_1px_2px_rgba(0,0,0,0.04)] transition-all cursor-pointer whitespace-nowrap">
                            <span class="text-[#1b6ac9] font-normal text-[18px] leading-none -mt-0.5">+</span>
                            <span>Make a new project</span>
                        </a>
                        @endif

                        <button type="button" @click="addFolder()" class="inline-flex items-center gap-2 py-1.5 px-3.5 bg-white hover:bg-slate-50 focus:ring-2 focus:ring-[#1b6ac9] focus:border-[#1b6ac9] focus:outline-none text-[#1d2d35] font-bold text-[14.5px] rounded-[8px] border border-[#e8e5df] shadow-[0_1px_2px_rgba(0,0,0,0.04)] transition-all cursor-pointer whitespace-nowrap">
                            <i data-lucide="folder" class="w-4 h-4 text-[#1b6ac9] stroke-[2]"></i>
                            <span>Add a folder</span>
                        </button>

                        @if(in_array($userRole, ['OWNER', 'ADMIN']))
                        <a href="{{ route('enrollments.create') }}" class="inline-flex items-center gap-2 py-1.5 px-3.5 bg-white hover:bg-slate-50 text-[#1d2d35] font-bold text-[14.5px] rounded-[8px] border border-[#e8e5df] shadow-[0_1px_2px_rgba(0,0,0,0.04)] transition-all cursor-pointer whitespace-nowrap">
                            <i data-lucide="user-plus" class="w-4 h-4 text-[#1b6ac9] stroke-[2]"></i>
                            <span>Invite people to the account</span>
                        </a>

                        <a href="{{ route('adminland.index') }}" class="inline-flex items-center gap-2 py-1.5 px-3.5 bg-white hover:bg-slate-50 text-[#1d2d35] font-bold text-[14.5px] rounded-[8px] border border-[#e8e5df] shadow-[0_1px_2px_rgba(0,0,0,0.04)] transition-all whitespace-nowrap">
                            <i data-lucide="key" class="w-4 h-4 text-[#1b6ac9] stroke-[2]"></i>
                            <span>Adminland</span>
                        </a>
                        @endif

                        <button type="button" @click="window.toggleBasecampDarkLight && window.toggleBasecampDarkLight()" class="text-[#5a656d] hover:text-[#1d2d35] p-1 rounded-full transition-colors ml-0.5 cursor-pointer" title="Toggle Dark / Light Theme">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Project Cards Grid (2-Col in Normal View, 4-Col in Expanded View) -->
                <div :class="expandedHome ? 'is-expanded' : ''"
                     class="bc-home-grid">
                    <!-- Dynamic "New folder" Cards Matching media_1793274250796_8f7e9e01.jpg -->
                    <template x-for="folder in folders" :key="folder.id">
                        <div data-flip-card
                             @click="editingFolderId = folder.id"
                             class="bg-white rounded-[10px] p-3.5 border border-[#ebe8e2] shadow-[0_1px_2px_rgba(0,0,0,0.06)] hover:shadow-md transition-shadow flex flex-col items-center justify-center text-center min-h-[128px] cursor-pointer relative group">
                            <template x-if="editingFolderId !== folder.id">
                                <div>
                                    <div class="text-[16px] font-bold text-[#1d2d35] leading-snug tracking-[-0.02em]" x-text="folder.name"></div>
                                    <div class="text-[12.5px] text-[#6d6a65] mt-0.5" x-text="folder.projectsCount + ' projects'"></div>
                                </div>
                            </template>
                            <template x-if="editingFolderId === folder.id">
                                <div class="w-full px-2" @click.stop>
                                    <input type="text"
                                           x-model="folder.name"
                                           @keydown.enter="editingFolderId = null"
                                           @blur="editingFolderId = null"
                                           x-init="$nextTick(() => { $el.focus(); $el.select(); })"
                                           class="w-full text-center text-[15.5px] font-bold text-[#1d2d35] border border-[#1b6ac9] rounded-[6px] px-2 py-1 focus:outline-none">
                                    <div class="text-[12.5px] text-[#6d6a65] mt-1" x-text="folder.projectsCount + ' projects'"></div>
                                </div>
                            </template>
                        </div>
                    </template>

                    @forelse($projects as $idx => $proj)
                    <div data-flip-card
                         @if($idx >= ($initialProjectCount ?? 22)) x-show="showAllProjects" x-cloak @endif
                         class="bg-white rounded-[10px] p-3.5 border border-[#ebe8e2] shadow-[0_1px_2px_rgba(0,0,0,0.06)] hover:shadow-md transition-shadow flex flex-col justify-between min-h-[128px] group cursor-pointer"
                         onclick="if(!event.target.closest('button') && !event.target.closest('[data-user-badge]')) window.location.href='{{ route('projects.show', $proj->id) }}'">
                        
                        <!-- Top: Title & Star Icon -->
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <a href="{{ route('projects.show', $proj->id) }}" class="text-[16px] font-bold text-[#1d2d35] leading-[1.2] tracking-[-0.02em] line-clamp-2">
                                    {{ $proj->name }}
                                </a>

                                <!-- Star button -->
                                <button type="button" 
                                        @click.prevent.stop="toggleProjectStar({{ $proj->id }}, $event)" 
                                        class="flex-shrink-0 p-0.5 hover:scale-110 transition-transform cursor-pointer focus:outline-none -mt-0.5 -mr-0.5"
                                        title="Bookmark project">
                                    <svg class="w-[18px] h-[18px] transition-colors {{ ($proj->display_starred ?? $proj->is_pinned) ? 'text-[#f5b642] fill-[#f5b642]' : 'text-slate-300 fill-transparent hover:text-[#f5b642]' }}" 
                                         viewBox="0 0 24 24">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></polygon>
                                    </svg>
                                </button>
                            </div>

                            @if(!empty($proj->display_subtitle) || $proj->client_subtitle || $proj->description)
                            <p class="text-[12.5px] text-[#283c46] mt-1 leading-[1.32] line-clamp-2 font-normal">
                                {{ $proj->display_subtitle ?? ($proj->client_subtitle ?: $proj->description) }}
                            </p>
                            @endif
                        </div>

                        <!-- Bottom: Member Avatars Stack or All-access Badge + Optional Progress Gauge -->
                        <div class="flex items-center justify-between mt-3 pt-0.5">
                            <div class="flex items-center -space-x-1">
                                @if($proj->name === 'HARSAC Website Development')
                                    <span class="px-2.5 py-0.5 rounded-full bg-[#d9ebf7] text-[#1d2d35] text-[11px] font-medium leading-tight">All-access</span>
                                @elseif(isset($proj->display_badges) && $proj->display_badges->isNotEmpty())
                                    @foreach($proj->display_badges as $b)
                                        <button type="button" data-user-badge
                                                @click.prevent.stop="openUserProfile({{ json_encode($b) }})"
                                                class="relative w-[22px] h-[22px] rounded-full {{ $b->bg }} border border-white {{ $b->text }} text-[9px] font-bold flex items-center justify-center shadow-2xs select-none hover:scale-110 hover:z-10 transition-transform cursor-pointer focus:outline-none overflow-hidden"
                                                title="{{ $b->name }}">
                                            @if(!empty($b->avatar))
                                                <img src="{{ $b->avatar }}" alt="{{ $b->name }}" class="w-full h-full object-cover">
                                                @if(($b->id ?? 0) == 1 || stripos($b->name ?? '', 'Manjot') !== false)
                                                    <span x-show="localStorage.getItem('bc_ooo_active') !== '0'" class="bc-ooo-badge absolute bottom-0 inset-x-0 h-[36%] bg-[#facc15] flex items-center justify-center pointer-events-none">
                                                        <span class="text-[5.5px] font-black text-[#1d1c1b] leading-none tracking-tight">OUT</span>
                                                    </span>
                                                @endif
                                            @else
                                                {{ $b->initials }}
                                            @endif
                                        </button>
                                    @endforeach
                                @else
                                    @foreach($proj->members->take(6) as $m)
                                        @if($m->avatar_url)
                                            <img src="{{ $m->avatar_url }}" 
                                                 class="w-[22px] h-[22px] rounded-full border border-white object-cover shadow-2xs" 
                                                 title="{{ $m->name }}"
                                                 onerror="this.onerror=null; this.src='https://api.dicebear.com/7.x/initials/svg?seed={{ urlencode($m->name) }}';">
                                        @else
                                            <span class="w-[22px] h-[22px] rounded-full bg-slate-600 border border-white text-[9px] font-bold text-white flex items-center justify-center shadow-2xs" title="{{ $m->name }}">
                                                {{ strtoupper(substr($m->name, 0, 2)) }}
                                            </span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>

                            @if(!empty($proj->has_gauge))
                            <!-- Basecamp Circular Rainbow Progress Gauge (ClubGo) -->
                            <span class="w-[20px] h-[20px] rounded-full flex items-center justify-center shrink-0"
                                  style="background: conic-gradient(#ef4444 0deg 72deg, #f59e0b 72deg 144deg, #10b981 144deg 216deg, #3b82f6 216deg 288deg, #8b5cf6 288deg 360deg); padding: 2.5px;"
                                  title="Progress gauge">
                                <span class="w-full h-full rounded-full bg-white block"></span>
                            </span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="col-span-2 text-center py-10 text-slate-400 text-xs">
                        No projects found.
                    </div>
                    @endforelse
                </div>

                <!-- "See all projects" Centered Pill Button at Bottom of Container (Matches media_1793289676143_ec08b18e.jpg & media_1793289703915_4da1ebfa.jpg) -->
                <div class="pt-5 pb-1 flex justify-center">
                    <a href="{{ route('projects.directory') }}"
                       class="px-3.5 py-1.5 rounded-[7px] border border-[#d6d2c9] bg-white hover:bg-[#fbfaf8] text-[13.5px] font-medium text-[#1d2d35] shadow-[0_1px_2px_rgba(0,0,0,0.04)] transition-colors cursor-pointer">
                        See all projects
                    </a>
                </div>

            </div>
        </div>

        <!-- ================= 3. RIGHT COLUMN (MOST RECENT ACTIVITY) ================= -->
        <div x-ref="rightCol"
             x-show="!expandedHome"
             class="bc-home-right">
            <div class="space-y-2.5">
                <!-- Header ('Most recent activity – View all') -->
                <div class="flex items-baseline gap-1.5">
                    <h2 class="text-[17.5px] font-bold text-[#1d2d35] tracking-[-0.02em]">Most recent activity</h2>
                    <span class="text-[#1d2d35] font-normal text-[15px]">–</span>
                    <a href="{{ route('my.activity', ['scope' => 'all']) }}" class="text-[14px] text-[#1d2d35] underline hover:text-black font-normal">View all</a>
                </div>

                <!-- Timeline Items -->
                <div class="space-y-2.5">
                    @forelse($recentActivities as $act)
                    <div class="space-y-0.5">
                        <!-- Timestamp & Icon Indicator -->
                        <div class="flex items-center gap-1.5 text-[12px] text-[#6d6a65]">
                            @if($act->type === 'project_created')
                            <span class="w-3.5 h-3.5 rounded-full bg-[#4a90e2] text-white inline-flex items-center justify-center flex-shrink-0" title="Project created">
                                <svg class="w-2 h-2 fill-current" viewBox="0 0 12 12">
                                    <rect x="1" y="1" width="4" height="4" rx="0.8"/>
                                    <rect x="7" y="1" width="4" height="4" rx="0.8"/>
                                    <rect x="1" y="7" width="4" height="4" rx="0.8"/>
                                    <rect x="7" y="7" width="4" height="4" rx="0.8"/>
                                </svg>
                            </span>
                            @elseif($act->type === 'file')
                            <span class="w-3.5 h-3.5 rounded-full bg-[#f5b642] text-white inline-flex items-center justify-center flex-shrink-0 text-[8px]">📄</span>
                            @elseif($act->type === 'task')
                            <span class="w-3.5 h-3.5 rounded-full bg-[#2da562] text-white inline-flex items-center justify-center flex-shrink-0">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            @else
                            <span class="w-3.5 h-3.5 rounded-full bg-[#4a90e2] text-white inline-flex items-center justify-center flex-shrink-0 text-[8px]">💬</span>
                            @endif
                            <span class="font-normal">{{ $act->time_str }}</span>
                        </div>

                        <!-- Content Row -->
                        <div class="pl-5 text-[14px] leading-[1.35] text-[#1d2d35]">
                            @if(isset($act->badge))
                            <button type="button"
                                    @click="openUserProfile({{ json_encode($act->badge) }})"
                                    class="relative w-[18px] h-[18px] rounded-full {{ $act->badge->bg }} text-white text-[8.5px] font-bold inline-flex items-center justify-center align-text-bottom mr-0.5 select-none hover:scale-110 transition-transform cursor-pointer focus:outline-none overflow-hidden"
                                    title="{{ $act->badge->name }}">
                                @if(!empty($act->badge->avatar))
                                    <img src="{{ $act->badge->avatar }}" alt="{{ $act->badge->name }}" class="w-full h-full object-cover">
                                    @if(($act->badge->id ?? 0) == 1 || stripos($act->badge->name ?? '', 'Manjot') !== false)
                                        <span x-show="localStorage.getItem('bc_ooo_active') !== '0'" class="bc-ooo-badge absolute bottom-0 inset-x-0 h-[36%] bg-[#facc15] flex items-center justify-center pointer-events-none">
                                            <span class="text-[4.8px] font-black text-[#1d1c1b] leading-none tracking-tight">OUT</span>
                                        </span>
                                    @endif
                                @else
                                    {{ $act->badge->initials }}
                                @endif
                            </button>
                            @elseif($act->author_avatar)
                            <img src="{{ $act->author_avatar }}" class="w-[18px] h-[18px] rounded-full object-cover inline-block align-text-bottom mr-0.5 cursor-pointer" title="{{ $act->author_name }}">
                            @else
                            <span class="w-[18px] h-[18px] rounded-full bg-teal-600 text-white font-bold text-[8.5px] inline-flex items-center justify-center align-text-bottom mr-0.5 cursor-pointer">
                                {{ $act->author_initials }}
                            </span>
                            @endif

                            @if(isset($act->badge))
                            <button type="button" @click="openUserProfile({{ json_encode($act->badge) }})" class="font-bold text-[#1d2d35] hover:underline cursor-pointer">{{ $act->author_name }}</button>
                            @else
                            <span class="font-bold text-[#1d2d35]">{{ $act->author_name }}</span>
                            @endif
                            <span class="text-[#1d2d35]">{{ $act->action }}</span>
                            @if($act->type === 'project_created')
                            <a href="{{ $act->target_url }}" class="text-[#1d2d35] hover:underline font-normal">{{ $act->target_title }}</a>
                            @else
                            <a href="{{ $act->target_url }}" class="text-[#1b6ac9] underline hover:text-[#124b8f] font-normal">{{ $act->target_title }}</a>
                            @endif
                            @if(!empty($act->project_name) && !str_contains($act->target_title, $act->project_name))
                            <span class="text-[#1d2d35]">—</span>
                            <a href="{{ $act->project_url }}" class="text-[#1d2d35] hover:underline">{{ $act->project_name }}</a>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-slate-400 text-xs">No recent activity recorded.</p>
                    @endforelse
                </div>

                <!-- Active people in the last 24 hours -->
                <div class="pt-2 space-y-1.5">
                    <p class="text-[14px] font-bold text-[#1d2d35]">
                        {{ count($activePeople) }} people active in the last 24 hours
                    </p>
                    <div class="flex items-center gap-1">
                        @foreach($activePeople as $p)
                            <button type="button"
                                    @click="openUserProfile({{ json_encode($p) }})"
                                    class="relative w-[28px] h-[28px] rounded-full {{ $p->bg }} text-white font-bold text-[11px] flex items-center justify-center shadow-2xs select-none hover:scale-105 transition-transform cursor-pointer focus:outline-none overflow-hidden"
                                    title="{{ $p->name }}">
                                @if(!empty($p->avatar))
                                    <img src="{{ $p->avatar }}" alt="{{ $p->name }}" class="w-full h-full object-cover">
                                    @if(($p->id ?? 0) == 1 || stripos($p->name ?? '', 'Manjot') !== false)
                                        <span x-show="localStorage.getItem('bc_ooo_active') !== '0'" class="bc-ooo-badge absolute bottom-0 inset-x-0 h-[36%] bg-[#facc15] flex items-center justify-center pointer-events-none">
                                            <span class="text-[6px] font-black text-[#1d1c1b] leading-none tracking-tight">OUT</span>
                                        </span>
                                    @endif
                                @else
                                    {{ $p->initials }}
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
                <script>
                    window.addEventListener('pageshow', function (ev) {
                        if (ev.persisted) {
                            window.location.reload();
                        }
                    });
                </script>

            </div>
        </div>

    </div>

    <!-- Edit Logo Modal -->
    <div x-show="editingLogo" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div @click="editingLogo = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 border border-slate-200">
                <form action="{{ route('organization.logo') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <h3 class="text-sm font-black text-slate-900">Customize Workspace Logo</h3>
                    <div @click="$refs.logoFileInput.click()" 
                         class="w-full h-32 border-2 border-dashed border-slate-300 rounded-xl flex flex-col items-center justify-center cursor-pointer hover:border-slate-400 transition-colors">
                        <template x-if="!tempPreview">
                            <div class="text-center">
                                <i data-lucide="image" class="w-6 h-6 text-slate-400 mx-auto mb-1"></i>
                                <span class="text-xs font-semibold text-slate-600">Upload new logo PNG / SVG</span>
                            </div>
                        </template>
                        <template x-if="tempPreview">
                            <img :src="tempPreview" class="h-20 max-w-full object-contain">
                        </template>
                        <input x-ref="logoFileInput" type="file" name="logo" accept="image/*" class="hidden" @change="previewLogo($event)">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="editingLogo = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Project Modal -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div @click="createModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6 border border-slate-200">
                <form action="{{ route('projects.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-base font-bold text-slate-900">Make a new project</h3>
                        <button type="button" @click="createModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-4 h-4"></i></button>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Project Name</label>
                        <input type="text" name="name" required placeholder="e.g., Marketing Campaign Q4" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description (optional)</label>
                        <textarea name="description" rows="2" placeholder="Brief summary of the project goals..." class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="createModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs">Create Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Invite People Modal -->
    <div x-show="inviteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div @click="inviteModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 border border-slate-200">
                <form action="{{ route('people.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-base font-bold text-slate-900">Invite people to the account</h3>
                        <button type="button" @click="inviteModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-4 h-4"></i></button>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Address</label>
                        <input type="email" name="email" required placeholder="colleague@company.com" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Role</label>
                        <select name="role" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="MEMBER">Team Member (Engineering / Marketing)</option>
                            <option value="ADMIN">Administrator</option>
                            <option value="CLIENT">Client</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="inviteModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs">Send Invitation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Folder Modal -->
    <div x-show="folderModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div @click="folderModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 border border-slate-200">
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-base font-bold text-slate-900">Add a folder</h3>
                        <button type="button" @click="folderModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-4 h-4"></i></button>
                    </div>
                    <p class="text-xs text-slate-600">Organize your projects into folders for clients, departments, or archive categories.</p>
                    <input type="text" placeholder="Folder Name (e.g. Client Projects, Internal, 2026 Archive)" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="folderModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl">Cancel</button>
                        <button type="button" @click="folderModal = false" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs">Create Folder</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
