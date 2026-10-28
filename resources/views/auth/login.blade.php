@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
<div class="bg-slate-900/90 backdrop-blur-md rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-800 space-y-6">
    <div class="text-left">
        <h2 class="text-xl font-bold text-white">Sign in to your account</h2>
        <p class="text-xs text-slate-400 mt-1">Enter your email and password to access your workspace.</p>
    </div>

    @if ($errors->any())
    <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs rounded-2xl p-3.5 space-y-1">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <form action="{{ route('login') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                placeholder="name@company.com">
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Password</label>
            </div>
            <input type="password" id="password" name="password" required
                class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                placeholder="••••••••">
        </div>

        <div class="flex items-center justify-between text-xs text-slate-400 pt-1">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="remember" class="rounded bg-slate-950 border-slate-700 text-amber-500 focus:ring-amber-500">
                <span>Remember me</span>
            </label>
        </div>

        <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-amber-500 to-rose-600 hover:from-amber-600 hover:to-rose-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-amber-500/25 transition-all">
            Sign In to Workspace
        </button>
    </form>

    <!-- 1-Click Persona Access -->
    <div class="pt-5 border-t border-slate-800 space-y-3">
        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-slate-400">
            <span class="text-amber-400 flex items-center gap-1">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> 1-Click Test Personas
            </span>
            <span class="text-[10px] text-slate-500">MySQL Seeded</span>
        </div>

        <div class="grid grid-cols-3 gap-2">
            <a href="{{ route('quick-login', 'owner') }}" class="p-2.5 rounded-xl border border-slate-800 bg-slate-950/60 hover:border-amber-500 hover:bg-amber-500/10 transition-all text-center group">
                <p class="text-xs font-bold text-white group-hover:text-amber-400">Owner</p>
                <p class="text-[10px] text-slate-400 truncate">Claire</p>
            </a>
            <a href="{{ route('quick-login', 'member') }}" class="p-2.5 rounded-xl border border-slate-800 bg-slate-950/60 hover:border-sky-500 hover:bg-sky-500/10 transition-all text-center group">
                <p class="text-xs font-bold text-white group-hover:text-sky-400">Employee</p>
                <p class="text-[10px] text-slate-400 truncate">Marcus</p>
            </a>
            <a href="{{ route('quick-login', 'client') }}" class="p-2.5 rounded-xl border border-slate-800 bg-slate-950/60 hover:border-emerald-500 hover:bg-emerald-500/10 transition-all text-center group">
                <p class="text-xs font-bold text-white group-hover:text-emerald-400">Client</p>
                <p class="text-[10px] text-slate-400 truncate">Edward</p>
            </a>
        </div>
    </div>

    <div class="text-center pt-2 text-xs text-slate-400">
        New company? 
        <a href="{{ route('register') }}" class="font-bold text-amber-400 hover:underline">
            Register an Organization
        </a>
    </div>
</div>
@endsection

