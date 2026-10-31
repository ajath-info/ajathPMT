@extends('layouts.app')

@section('title', 'My Boosts - Basecamp')

@section('content')
@php
    $manjotAvatar = ($user->avatar_url && !str_contains($user->avatar_url, 'dicebear'))
        ? $user->avatar_url
        : '/uploads/avatars/22369668.png';
@endphp

<div x-data="{
        freqModal: false,
        boostFreq: 'Notifying you of new boosts every 3 hours',
        oooActive: localStorage.getItem('bc_ooo_active') !== '0'
    }"
    class="max-w-[920px] mx-auto pb-20 font-sans text-[#1d2d35]">

    <!-- Basecamp Sheet Container (Exact Match to media_1793292796020_5a285afc.jpg) -->
    <div class="bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-84px)] overflow-hidden relative">

        <!-- Top Bar: "✓ Notifying you of new boosts every 3 hours" -->
        <div class="px-6 py-2.5 border-b border-[#ece9e2] flex items-center justify-end text-[12.5px]">
            <div class="relative">
                <button type="button"
                        @click="freqModal = !freqModal"
                        class="inline-flex items-center gap-1.5 text-[#1d2d35] hover:opacity-80 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#2da562] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12.5l2.3 2.3 4.7-4.8"/>
                    </svg>
                    <span x-text="boostFreq">Notifying you of new boosts every 3 hours</span>
                </button>

                <!-- Frequency Popover -->
                <div x-show="freqModal"
                     x-cloak
                     @click.outside="freqModal = false"
                     class="absolute right-0 top-[calc(100%+6px)] w-[250px] rounded-[8px] bg-white border border-[#d8d4cc] shadow-xl py-2 px-3 z-30 text-left space-y-1 text-[13px]">
                    <button type="button"
                            @click="boostFreq = 'Notifying you of new boosts every 3 hours'; freqModal = false"
                            class="w-full py-1.5 px-2 rounded hover:bg-[#f6f4f0] text-left cursor-pointer font-medium">
                        Every 3 hours
                    </button>
                    <button type="button"
                            @click="boostFreq = 'Notifying you of new boosts once a day'; freqModal = false"
                            class="w-full py-1.5 px-2 rounded hover:bg-[#f6f4f0] text-left cursor-pointer font-medium">
                        Once a day
                    </button>
                    <button type="button"
                            @click="boostFreq = 'Boost notifications turned off'; freqModal = false"
                            class="w-full py-1.5 px-2 rounded hover:bg-[#f6f4f0] text-left cursor-pointer font-medium">
                        Don&rsquo;t notify me
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Sheet Content -->
        <div class="px-8 sm:px-14 pt-8 pb-20">

            <!-- Heading: You have 🚀 Boosts! -->
            <h1 class="text-[28px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-[1.15] mb-7">
                You have 🚀 Boosts!
            </h1>

            <!-- =================================================================== -->
            <!-- BOOST GROUP 1: Boosted Wednesday, January 21                        -->
            <!-- =================================================================== -->
            <div class="mb-10">
                <!-- Wavy Orange Divider with Centered Date -->
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-[8px] overflow-hidden">
                        <svg class="w-full h-[8px] text-[#d97706]" preserveAspectRatio="none" viewBox="0 0 400 8" fill="none">
                            <path d="M0 4 Q 5 0, 10 4 T 20 4 T 30 4 T 40 4 T 50 4 T 60 4 T 70 4 T 80 4 T 90 4 T 100 4 T 110 4 T 120 4 T 130 4 T 140 4 T 150 4 T 160 4 T 170 4 T 180 4 T 190 4 T 200 4 T 210 4 T 220 4 T 230 4 T 240 4 T 250 4 T 260 4 T 270 4 T 280 4 T 290 4 T 300 4 T 310 4 T 320 4 T 330 4 T 340 4 T 350 4 T 360 4 T 370 4 T 380 4 T 390 4 T 400 4"
                                  stroke="currentColor" stroke-width="1.35" fill="none"/>
                        </svg>
                    </div>
                    <span class="text-[13px] font-normal text-[#e0823d] whitespace-nowrap select-none">
                        Boosted Wednesday, January 21
                    </span>
                    <div class="flex-1 h-[8px] overflow-hidden">
                        <svg class="w-full h-[8px] text-[#d97706]" preserveAspectRatio="none" viewBox="0 0 400 8" fill="none">
                            <path d="M0 4 Q 5 0, 10 4 T 20 4 T 30 4 T 40 4 T 50 4 T 60 4 T 70 4 T 80 4 T 90 4 T 100 4 T 110 4 T 120 4 T 130 4 T 140 4 T 150 4 T 160 4 T 170 4 T 180 4 T 190 4 T 200 4 T 210 4 T 220 4 T 230 4 T 240 4 T 250 4 T 260 4 T 270 4 T 280 4 T 290 4 T 300 4 T 310 4 T 320 4 T 330 4 T 340 4 T 350 4 T 360 4 T 370 4 T 380 4 T 390 4 T 400 4"
                                  stroke="currentColor" stroke-width="1.35" fill="none"/>
                        </svg>
                    </div>
                </div>

                <!-- Boosted Card + Booster Reaction -->
                <div class="mt-6 flex items-start gap-4">
                    <div class="relative w-[224px] rounded-[10px] border border-[#dcd8d0] bg-[#f9f8f6] p-4 pl-5 ml-3 shadow-2xs">
                        <!-- Overlapping Top-Left User Avatar with OUT Badge & Blue Discussion Icon -->
                        <div class="absolute -left-4 top-3 w-[34px] h-[34px]">
                            <div class="w-[34px] h-[34px] rounded-full overflow-hidden relative shadow-xs">
                                <img src="{{ $manjotAvatar }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                <div x-show="oooActive" class="absolute bottom-0 inset-x-0 h-[38%] bg-[#facc15] flex items-center justify-center">
                                    <span class="text-[7.5px] font-black text-[#1d1c1b] leading-none tracking-tight">OUT</span>
                                </div>
                            </div>
                            <span class="absolute -top-0.5 -right-0.5 w-[13px] h-[13px] rounded-full bg-[#3b82f6] text-white flex items-center justify-center shadow-2xs">
                                <svg class="w-2 h-2 fill-current" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                            </span>
                        </div>

                        <a href="{{ url('/projects/4/discussions/13829') }}"
                           class="block text-[13px] font-bold text-[#1d2d35] hover:underline leading-[1.3]">
                            🏳️ Daily Scrum &ndash; MoM - 21/1/2026
                        </a>
                        <p class="text-[12.5px] text-[#1d2d35] leading-[1.36] mt-1">
                            Daily Scrum &ndash; MoM &bull; Harsh and Jitender are already working on ST1 under the guidance of Shachish Sir. &bull; Nikhil has received a couple of CVs via WhatsApp and LinkedIn. &bull; Nirdesh will schedule interv...
                        </p>
                    </div>

                    <!-- Booster Avatar + Thumbs Up -->
                    <div class="pt-3 flex items-center gap-1.5">
                        <img src="/uploads/avatars/25036340.png"
                             alt="Shachish Sneh"
                             title="Shachish Sneh"
                             class="w-[24px] h-[24px] rounded-full object-cover shadow-2xs">
                        <span class="text-[17px] leading-none select-none">👍</span>
                    </div>
                </div>
            </div>

            <!-- =================================================================== -->
            <!-- BOOST GROUP 2: Boosted Tuesday, September 2 2025                    -->
            <!-- =================================================================== -->
            <div>
                <!-- Wavy Orange Divider with Centered Date -->
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-[8px] overflow-hidden">
                        <svg class="w-full h-[8px] text-[#d97706]" preserveAspectRatio="none" viewBox="0 0 400 8" fill="none">
                            <path d="M0 4 Q 5 0, 10 4 T 20 4 T 30 4 T 40 4 T 50 4 T 60 4 T 70 4 T 80 4 T 90 4 T 100 4 T 110 4 T 120 4 T 130 4 T 140 4 T 150 4 T 160 4 T 170 4 T 180 4 T 190 4 T 200 4 T 210 4 T 220 4 T 230 4 T 240 4 T 250 4 T 260 4 T 270 4 T 280 4 T 290 4 T 300 4 T 310 4 T 320 4 T 330 4 T 340 4 T 350 4 T 360 4 T 370 4 T 380 4 T 390 4 T 400 4"
                                  stroke="currentColor" stroke-width="1.35" fill="none"/>
                        </svg>
                    </div>
                    <span class="text-[13px] font-normal text-[#e0823d] whitespace-nowrap select-none">
                        Boosted Tuesday, September 2 2025
                    </span>
                    <div class="flex-1 h-[8px] overflow-hidden">
                        <svg class="w-full h-[8px] text-[#d97706]" preserveAspectRatio="none" viewBox="0 0 400 8" fill="none">
                            <path d="M0 4 Q 5 0, 10 4 T 20 4 T 30 4 T 40 4 T 50 4 T 60 4 T 70 4 T 80 4 T 90 4 T 100 4 T 110 4 T 120 4 T 130 4 T 140 4 T 150 4 T 160 4 T 170 4 T 180 4 T 190 4 T 200 4 T 210 4 T 220 4 T 230 4 T 240 4 T 250 4 T 260 4 T 270 4 T 280 4 T 290 4 T 300 4 T 310 4 T 320 4 T 330 4 T 340 4 T 350 4 T 360 4 T 370 4 T 380 4 T 390 4 T 400 4"
                                  stroke="currentColor" stroke-width="1.35" fill="none"/>
                        </svg>
                    </div>
                </div>

                <!-- Boosted Card + Booster Reaction -->
                <div class="mt-6 flex items-start gap-4">
                    <div class="relative w-[224px] rounded-[10px] border border-[#dcd8d0] bg-[#f9f8f6] p-4 pl-5 ml-3 shadow-2xs">
                        <!-- Overlapping Top-Left User Avatar with OUT Badge & Blue Comment Icon -->
                        <div class="absolute -left-4 top-3 w-[34px] h-[34px]">
                            <div class="w-[34px] h-[34px] rounded-full overflow-hidden relative shadow-xs">
                                <img src="{{ $manjotAvatar }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                <div x-show="oooActive" class="absolute bottom-0 inset-x-0 h-[38%] bg-[#facc15] flex items-center justify-center">
                                    <span class="text-[7.5px] font-black text-[#1d1c1b] leading-none tracking-tight">OUT</span>
                                </div>
                            </div>
                            <span class="absolute -top-0.5 -right-0.5 w-[13px] h-[13px] rounded-full bg-[#3b82f6] text-white flex items-center justify-center shadow-2xs">
                                <svg class="w-2 h-2 fill-current" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                            </span>
                        </div>

                        <a href="{{ url('/projects/4/discussions/13829') }}"
                           class="block text-[13px] font-bold text-[#1d2d35] hover:underline leading-[1.3]">
                            Re: Final Build | Release Note
                        </a>
                        <p class="text-[12.5px] text-[#1d2d35] leading-[1.36] mt-1">
                            Nirdesh please share your RCA report. Also, Ankit kindky make sure all regression test cases pass before final deployment.
                        </p>
                    </div>

                    <!-- Booster Avatar + Thumbs Up -->
                    <div class="pt-3 flex items-center gap-1.5">
                        <img src="/uploads/avatars/25036340.png"
                             alt="Shachish Sneh"
                             title="Shachish Sneh"
                             class="w-[24px] h-[24px] rounded-full object-cover shadow-2xs">
                        <span class="text-[17px] leading-none select-none">👍</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
