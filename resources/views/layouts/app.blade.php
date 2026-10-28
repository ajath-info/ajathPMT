<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Workspace') - Ajath PMT</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fdf8f6',
                            100: '#f2e8e5',
                            200: '#eaddd7',
                            500: '#e05a47',
                            600: '#c84432',
                            700: '#a63424',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Alpine.js CDN for interactive dropdowns & tabs -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full flex flex-col font-sans text-slate-800 antialiased selection:bg-amber-500 selection:text-white">

    <!-- Top Basecamp Navbar -->
    <header class="bg-slate-900 text-slate-100 border-b border-slate-800 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Left: Logo & Company -->
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 font-bold text-lg text-white hover:text-amber-400 transition-colors">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-amber-500 to-rose-500 flex items-center justify-center text-white shadow-md">
                            <i data-lucide="layers" class="w-5 h-5"></i>
                        </div>
                        <span class="tracking-tight">Ajath <span class="text-amber-400 font-extrabold">PMT</span></span>
                    </a>

                    @if(isset($currentOrg) || isset($org) || (isset($project) && isset($project->organization)))
                    @php $displayOrg = $currentOrg ?? ($org ?? ($project->organization ?? null)); @endphp
                    @if($displayOrg)
                    <div class="hidden sm:flex items-center gap-1.5 px-3 py-1 bg-slate-800/80 rounded-full text-xs font-medium text-slate-300 border border-slate-700">
                        <i data-lucide="building" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span class="max-w-[160px] truncate">{{ $displayOrg->name }}</span>
                    </div>
                    @endif
                    @endif
                </div>

                <!-- Center: Navigation Links -->
                <nav class="hidden md:flex items-center gap-1 text-sm font-semibold">
                    <a href="{{ route('dashboard') }}" class="px-3.5 py-2 rounded-lg {{ request()->routeIs('dashboard') || request()->routeIs('home') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }} transition-colors flex items-center gap-2">
                        <i data-lucide="layout-grid" class="w-4 h-4"></i>
                        <span>Home</span>
                    </a>
                    <a href="{{ route('people.index') }}" class="px-3.5 py-2 rounded-lg {{ request()->routeIs('people.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }} transition-colors flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4"></i>
                        <span>People & Clients</span>
                    </a>
                </nav>

                <!-- Right: User & Role Badge & Logout -->
                <div class="flex items-center gap-3">
                    @auth
                    <div class="flex items-center gap-2.5">
                        <img src="{{ Auth::user()->avatar_url ?: 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode(Auth::user()->name) }}" class="w-8 h-8 rounded-full border border-slate-700 bg-slate-800 object-cover" alt="User">
                        <div class="hidden sm:block text-left">
                            <p class="text-xs font-bold text-white leading-tight">{{ Auth::user()->name }}</p>
                            @php
                                $role = isset($userRole) ? $userRole : (session('user_role') ?? 'MEMBER');
                            @endphp
                            <span class="inline-block text-[10px] font-bold px-1.5 py-0.5 rounded leading-none mt-0.5
                                {{ $role === 'OWNER' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : '' }}
                                {{ $role === 'ADMIN' ? 'bg-purple-500/20 text-purple-400 border border-purple-500/30' : '' }}
                                {{ $role === 'CLIENT' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : '' }}
                                {{ $role === 'MEMBER' ? 'bg-sky-500/20 text-sky-400 border border-sky-500/30' : '' }}
                            ">
                                {{ $role === 'CLIENT' ? 'CLIENT PARTNER' : ($role === 'OWNER' ? 'COMPANY OWNER' : ($role === 'ADMIN' ? 'ADMINISTRATOR' : 'EMPLOYEE')) }}
                            </span>
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition-colors" title="Log out">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Global Alerts -->
    @if(session('success'))
    <div class="bg-emerald-600 text-white text-xs sm:text-sm font-medium px-4 py-2.5 shadow-sm text-center flex items-center justify-center gap-2 animate-in fade-in">
        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-rose-600 text-white text-xs sm:text-sm font-medium px-4 py-2.5 shadow-sm text-center flex items-center justify-center gap-2 animate-in fade-in">
        <i data-lucide="alert-circle" class="w-4 h-4"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Main Page Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200/80 py-6 text-center text-xs text-slate-400 mt-auto">
        <div class="max-w-7xl mx-auto px-4">
            <p>Ajath PMT • 100% PHP/Laravel & MySQL Basecamp Platform • <a href="https://workspace.ajath.com" class="text-slate-600 hover:underline">workspace.ajath.com</a></p>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
