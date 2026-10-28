@extends('layouts.auth')

@section('title', 'Register Company')

@section('content')
<div class="bg-slate-900/90 backdrop-blur-md rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-800 space-y-6">
    <div class="text-left">
        <h2 class="text-xl font-bold text-white">Create a New Organization</h2>
        <p class="text-xs text-slate-400 mt-1">Set up your multi-tenant workspace and invite your employees & clients.</p>
    </div>

    @if ($errors->any())
    <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs rounded-2xl p-3.5 space-y-1">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <form action="{{ route('register') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label for="company_name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Company / Workspace Name</label>
            <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" required autofocus
                class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                placeholder="e.g. Acme Studio LLC">
        </div>

        <div>
            <label for="name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Your Full Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                placeholder="e.g. Sarah Jenkins">
        </div>

        <div>
            <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Work Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                placeholder="sarah@acmestudio.com">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Password</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                    placeholder="••••••••">
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Confirm</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                    class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                    placeholder="••••••••">
            </div>
        </div>

        <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-amber-500 to-rose-600 hover:from-amber-600 hover:to-rose-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-amber-500/25 transition-all">
            Create Workspace & Start
        </button>
    </form>

    <div class="text-center pt-2 text-xs text-slate-400">
        Already have an account? 
        <a href="{{ route('login') }}" class="font-bold text-amber-400 hover:underline">
            Sign In
        </a>
    </div>
</div>
@endsection

