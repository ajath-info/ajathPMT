<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sign In') - Ajath Workspace</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full flex items-center justify-center bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 p-4 font-sans text-slate-800 antialiased">
    <div class="w-full max-w-md space-y-6">
        <!-- Logo -->
        <div class="text-center space-y-2">
            <img src="{{ asset('images/logo.png') }}" alt="Ajath Workspace" class="w-16 h-16 object-contain mx-auto drop-shadow-lg">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                Ajath <span class="text-emerald-400">Workspace</span>
            </h1>
            <p class="text-sm text-slate-400">
                Modern Team Collaboration & Project Management
            </p>
        </div>

        @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold px-4 py-3 rounded-2xl text-center">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-semibold px-4 py-3 rounded-2xl text-center">
            {{ session('error') }}
        </div>
        @endif

        @yield('content')
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>

