@extends('layouts.app')

@section('title', 'My Boosts - Ajath Workspace')

@section('content')
<div x-data="{ boostModal: false }" class="max-w-4xl mx-auto space-y-6">

    <!-- Top Breadcrumb & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1 transition-colors">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">My Boosts</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1 flex items-center gap-2.5">
                <span class="text-2xl">🚀</span>
                <span>My Boosts</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Applause, cheers, and high-fives sent by your teammates across projects.</p>
        </div>

        <button @click="boostModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold rounded-xl text-xs shadow-2xs transition-all self-start sm:self-auto">
            <span>🚀</span>
            <span>Send a Boost</span>
        </button>
    </div>

    <!-- Cheer Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 bg-white rounded-3xl border border-slate-200/90 shadow-2xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-orange-50 text-2xl flex items-center justify-center flex-shrink-0">
                🚀
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Boosts Received</span>
                <p class="text-2xl font-black text-slate-900">{{ count($receivedBoosts) }} cheers</p>
            </div>
        </div>

        <div class="p-5 bg-white rounded-3xl border border-slate-200/90 shadow-2xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-2xl flex items-center justify-center flex-shrink-0">
                👏
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Boosts Given</span>
                <p class="text-2xl font-black text-slate-900">{{ count($givenBoosts) }} sent</p>
            </div>
        </div>

        <div class="p-5 bg-white rounded-3xl border border-slate-200/90 shadow-2xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-pink-50 text-2xl flex items-center justify-center flex-shrink-0">
                ❤️
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Team Spirit</span>
                <p class="text-2xl font-black text-rose-600">100% High</p>
            </div>
        </div>
    </div>

    <!-- Boosts Stream -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs space-y-6">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                <span>Recent Applause & Cheers</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Live</span>
            </h3>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($receivedBoosts as $b)
            <div class="py-4 flex items-start gap-4 group">
                <div class="relative flex-shrink-0">
                    <img src="{{ $b['from_avatar'] }}" class="w-10 h-10 rounded-full border border-slate-200 object-cover">
                    <span class="absolute -bottom-1 -right-1 text-base bg-white rounded-full p-0.5 shadow-2xs leading-none">
                        {{ $b['emoji'] }}
                    </span>
                </div>

                <div class="space-y-1 flex-1">
                    <p class="text-xs text-slate-800 leading-snug">
                        <strong class="font-black text-slate-900">{{ $b['from_name'] }}</strong>
                        <span class="font-medium text-slate-600">boosted you</span>
                        <span class="text-slate-500">{{ $b['context'] }}</span>
                    </p>
                    <div class="flex items-center gap-2 text-[10px] text-slate-400 font-medium">
                        <span class="text-emerald-700 font-semibold">{{ $b['project'] }}</span>
                        <span>•</span>
                        <span>{{ $b['time'] }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="py-8 text-center text-slate-400">
                <p class="text-xs font-semibold">No boosts received yet. Do great work and cheer on your team!</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Modal: Send a Boost -->
    <div x-show="boostModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <div @click="boostModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
            
            <div class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all max-w-sm w-full border border-slate-200 p-6 space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <span>🚀 Send a Boost</span>
                    </h3>
                    <button @click="boostModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('my.boosts.give') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pick a Teammate</label>
                        <select name="user_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                            @foreach($teamMembers as $tm)
                            <option value="{{ $tm->id }}">{{ $tm->name }} ({{ $tm->job_title ?: 'Team' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Choose Reaction</label>
                        <div class="grid grid-cols-5 gap-2 text-center">
                            <label class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer flex flex-col items-center">
                                <input type="radio" name="emoji" value="🚀" checked class="hidden">
                                <span class="text-xl">🚀</span>
                                <span class="text-[9px] font-bold text-slate-600 mt-1">Rocket</span>
                            </label>
                            <label class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer flex flex-col items-center">
                                <input type="radio" name="emoji" value="👏" class="hidden">
                                <span class="text-xl">👏</span>
                                <span class="text-[9px] font-bold text-slate-600 mt-1">Clap</span>
                            </label>
                            <label class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer flex flex-col items-center">
                                <input type="radio" name="emoji" value="🎉" class="hidden">
                                <span class="text-xl">🎉</span>
                                <span class="text-[9px] font-bold text-slate-600 mt-1">Party</span>
                            </label>
                            <label class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer flex flex-col items-center">
                                <input type="radio" name="emoji" value="❤️" class="hidden">
                                <span class="text-xl">❤️</span>
                                <span class="text-[9px] font-bold text-slate-600 mt-1">Heart</span>
                            </label>
                            <label class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer flex flex-col items-center">
                                <input type="radio" name="emoji" value="⚡" class="hidden">
                                <span class="text-xl">⚡</span>
                                <span class="text-[9px] font-bold text-slate-600 mt-1">Energy</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Personal Note (Optional)</label>
                        <input type="text" name="message" placeholder="Great job on the deliverable!" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="boostModal = false" class="px-4 py-2 text-xs font-bold text-slate-600">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold rounded-xl text-xs shadow-2xs transition-colors">
                            Send Boost
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

