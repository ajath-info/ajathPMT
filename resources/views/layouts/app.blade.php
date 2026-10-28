<!DOCTYPE html>
<html lang="en" class="h-full bg-[#f4f6f5]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Basecamp') - Ajath PMT</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='16' cy='16' r='16' fill='%23199849'/%3E%3Cpath d='M8 22l8-12 8 12H8z' fill='%23fff'/%3E%3C/svg%3E">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        basecamp: {
                            green: '#199849',
                            darkGreen: '#127337',
                            canvas: '#f4f6f5',
                            cream: '#fbfaf8',
                        }
                    },
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', 'Helvetica', 'Arial', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { background-color: #f4f6f5; color: #283038; }
        .basecamp-card-shadow { box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04); }
        .basecamp-card-shadow:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04); }
    </style>
</head>
<body x-data="{ 
    searchModal: false, 
    pingsModal: false, 
    navDropdown: false,
    searchQuery: '',
    toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            if (document.exitFullscreen) { document.exitFullscreen(); }
        }
    }
}" 
@keydown.window="if (($event.shiftKey && ($event.key === 'J' || $event.key === 'j')) || ($event.key === '/' && !$event.target.matches('input, textarea'))) { $event.preventDefault(); searchModal = true; $nextTick(() => $refs.searchInput.focus()); } if ($event.key === 'Escape') { searchModal = false; pingsModal = false; navDropdown = false; }"
class="min-h-full flex flex-col font-sans antialiased selection:bg-amber-400 selection:text-slate-900">

    <!-- Top Basecamp Header -->
    <header class="bg-[#f4f6f5] border-b border-slate-200/80 sticky top-0 z-40 backdrop-blur-sm bg-[#f4f6f5]/95">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-14">
                
                <!-- Left: Home link / Breadcrumb -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="p-1.5 text-slate-500 hover:text-slate-900 rounded-lg hover:bg-slate-200/50 transition-colors" title="Go to Home">
                        <i data-lucide="home" class="w-4 h-4"></i>
                    </a>

                    @if(isset($project))
                    <span class="text-slate-300">/</span>
                    <a href="{{ route('projects.show', $project->id) }}" class="text-xs font-bold text-slate-800 hover:text-emerald-700 max-w-[200px] truncate">
                        {{ $project->name }}
                    </a>
                    @endif
                </div>

                <!-- Center: The Basecamp Mountain Logo & Dropdown -->
                <div class="relative flex items-center justify-center">
                    <button @click="navDropdown = !navDropdown" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full hover:bg-slate-200/60 transition-colors group">
                        <!-- Basecamp Mountain Icon (Green Circle with white peak) -->
                        <div class="w-5 h-5 rounded-full bg-[#199849] flex items-center justify-center text-white shadow-sm flex-shrink-0">
                            <svg viewBox="0 0 24 24" class="w-3 h-3 fill-white" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 4L3 18h18L12 4zm0 4.5l5.2 8H6.8L12 8.5z"/>
                            </svg>
                        </div>
                        <span class="font-black text-sm tracking-tight text-slate-800 group-hover:text-black">Basecamp</span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-800 transition-transform" :class="navDropdown ? 'rotate-180' : ''"></i>
                    </button>

                    <!-- Basecamp Switcher Dropdown -->
                    <div x-show="navDropdown" x-cloak @click.outside="navDropdown = false" class="absolute top-11 bg-white rounded-2xl shadow-xl border border-slate-200 py-2 w-72 z-50 text-xs">
                        <div class="px-4 py-2 border-b border-slate-100">
                            <p class="text-[10px] font-bold uppercase text-slate-400">Current Workspace</p>
                            <p class="font-bold text-slate-900 truncate text-sm">
                                {{ session('active_org_name', 'Ajath Infotech Pvt Ltd') }}
                            </p>
                        </div>
                        <div class="py-1">
                            <a href="{{ route('home') }}" class="flex items-center gap-2 px-4 py-2 text-slate-700 hover:bg-slate-50 font-semibold">
                                <i data-lucide="layout-grid" class="w-4 h-4 text-emerald-600"></i>
                                <span>Home Dashboard</span>
                            </a>
                            <a href="{{ route('projects.index') }}" class="flex items-center gap-2 px-4 py-2 text-slate-700 hover:bg-slate-50 font-semibold">
                                <i data-lucide="folder-kanban" class="w-4 h-4 text-[#199849]"></i>
                                <span>All Projects</span>
                            </a>
                            <a href="{{ route('people.index') }}" class="flex items-center gap-2 px-4 py-2 text-slate-700 hover:bg-slate-50 font-semibold">
                                <i data-lucide="users" class="w-4 h-4 text-indigo-600"></i>
                                <span>People & Clients</span>
                            </a>
                            <button @click="navDropdown = false; searchModal = true;" class="w-full text-left flex items-center gap-2 px-4 py-2 text-slate-700 hover:bg-slate-50 font-semibold">
                                <i data-lucide="search" class="w-4 h-4 text-amber-600"></i>
                                <span>Search or Jump (Shift+J)</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right: Pings/Notifications, Fullscreen, User & Logout -->
                <div class="flex items-center gap-2">
                    
                    <!-- Pings ~ Notifications Pill Button -->
                    <button @click="pingsModal = true" class="flex items-center gap-1.5 px-3 py-1 bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs rounded-full border border-slate-300/80 shadow-2xs transition-all">
                        <i data-lucide="bell" class="w-3.5 h-3.5 text-slate-600"></i>
                        <span>Pings ~ Notifications</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    </button>

                    <!-- Fullscreen Expand Button -->
                    <button @click="toggleFullscreen()" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-200/50 transition-colors" title="Toggle Fullscreen">
                        <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                    </button>

                    <!-- User Avatar & Logout Menu -->
                    @auth
                    <div class="flex items-center gap-1.5 pl-2 border-l border-slate-200">
                        <img src="{{ Auth::user()->avatar_url ?: 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode(Auth::user()->name) }}" class="w-7 h-7 rounded-full border border-slate-300 object-cover" title="{{ Auth::user()->name }} ({{ session('user_role', 'MEMBER') }})">
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-200/50 transition-colors" title="Log out">
                                <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                            </button>
                        </form>
                    </div>
                    @endauth
                </div>

            </div>
        </div>
    </header>

    <!-- Global Flash Alerts -->
    @if(session('success'))
    <div class="bg-emerald-600 text-white text-xs font-semibold px-4 py-2 shadow-sm text-center flex items-center justify-center gap-2 animate-in fade-in">
        <i data-lucide="check-circle" class="w-4 h-4"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-rose-600 text-white text-xs font-semibold px-4 py-2 shadow-sm text-center flex items-center justify-center gap-2 animate-in fade-in">
        <i data-lucide="alert-circle" class="w-4 h-4"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Main Content Container -->
    <main class="flex-1 max-w-[1400px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="py-6 text-center text-xs text-slate-400 mt-auto">
        <div class="max-w-7xl mx-auto px-4">
            <p>Ajath Basecamp • Connected to <a href="https://workspace.ajath.com" class="text-slate-600 hover:underline font-semibold">workspace.ajath.com</a></p>
        </div>
    </footer>

    <!-- Search / Jump Modal (Shift + J) -->
    <div x-show="searchModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-start justify-center min-h-screen pt-20 px-4 pb-20 text-center">
            <div @click="searchModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
            
            <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all max-w-xl w-full border border-slate-200">
                <div class="p-4 border-b border-slate-100 flex items-center gap-3">
                    <i data-lucide="search" class="w-5 h-5 text-slate-400"></i>
                    <input x-ref="searchInput" x-model="searchQuery" type="text" placeholder="Type a project, person, or tool to jump..." class="w-full text-base font-semibold text-slate-800 placeholder-slate-400 focus:outline-none">
                    <kbd class="text-[10px] font-mono px-2 py-0.5 bg-slate-100 text-slate-500 rounded border border-slate-200">ESC</kbd>
                </div>

                <div class="p-3 max-h-80 overflow-y-auto space-y-1">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-3 py-1">Quick Jump</p>
                    
                    @if(isset($projects))
                        @foreach($projects as $p)
                        <a href="{{ route('projects.show', $p->id) }}" 
                           x-show="searchQuery === '' || '{{ strtolower(addslashes($p->name)) }}'.includes(searchQuery.toLowerCase())"
                           class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100 transition-colors text-xs font-bold text-slate-800 group">
                            <span class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span>{{ $p->name }}</span>
                            </span>
                            <span class="text-[10px] text-slate-400 group-hover:text-slate-600 font-medium">{{ $p->client_subtitle ?: 'Project' }}</span>
                        </a>
                        @endforeach
                    @endif

                    <a href="{{ route('projects.index') }}" 
                       x-show="searchQuery === '' || 'all projects list directory'.includes(searchQuery.toLowerCase())"
                       class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100 transition-colors text-xs font-bold text-slate-800">
                        <span class="flex items-center gap-2">
                            <i data-lucide="folder-kanban" class="w-4 h-4 text-[#199849]"></i>
                            <span>All Projects (Alphabetical Directory)</span>
                        </span>
                        <span class="text-[10px] text-slate-400 font-medium">Projects</span>
                    </a>

                    <a href="{{ route('people.index') }}" 
                       x-show="searchQuery === '' || 'people everyone clients'.includes(searchQuery.toLowerCase())"
                       class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100 transition-colors text-xs font-bold text-slate-800">
                        <span class="flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4 text-indigo-600"></i>
                            <span>Everyone & Clients Directory</span>
                        </span>
                        <span class="text-[10px] text-slate-400 font-medium">People</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Pings & Notifications Drawer / Modal -->
    <div x-show="pingsModal" x-cloak class="fixed inset-0 z-50 overflow-hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-slate-900/30 backdrop-blur-xs transition-opacity" @click="pingsModal = false"></div>
        <div class="fixed inset-y-0 right-0 max-w-sm w-full bg-white shadow-2xl border-l border-slate-200 flex flex-col z-50">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="bell" class="w-4 h-4 text-emerald-600"></i>
                    <h3 class="text-sm font-black text-slate-900">Pings & Notifications</h3>
                </div>
                <button @click="pingsModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="flex-1 p-4 overflow-y-auto space-y-3">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1">
                    <div class="flex items-center justify-between text-[10px] text-slate-400">
                        <span class="font-bold text-slate-700">Project Activity</span>
                        <span>Just now</span>
                    </div>
                    <p class="text-xs text-slate-700 font-medium">All 17 Basecamp projects are synchronized and ready.</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1">
                    <div class="flex items-center justify-between text-[10px] text-slate-400">
                        <span class="font-bold text-slate-700">Sprint Task</span>
                        <span>2h ago</span>
                    </div>
                    <p class="text-xs text-slate-700 font-medium">New deliverable milestone assigned to team.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
