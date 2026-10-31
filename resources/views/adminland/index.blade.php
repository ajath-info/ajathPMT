@extends('layouts.app')

@section('title', 'Adminland - Basecamp')

@section('content')
<div class="max-w-[780px] mx-auto pb-20 font-sans text-[#1d2d35]"
     x-data="{ adminModal: null }">

    <!-- Basecamp Sheet Container (Exact Match to media_1793278236373_0bb3748d.jpg) -->
    <div class="bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-84px)] px-8 sm:px-14 pt-9 pb-16">

        <!-- Top Header Row: Adminland + Hand-Drawn "Upgrades available" Oval -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div class="pt-2">
                <h1 class="text-[30px] sm:text-[34px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-tight">
                    Adminland
                </h1>
                <p class="text-[15.5px] text-[#1d2d35] mt-0.5">
                    Manage your Basecamp account
                </p>
            </div>

            <!-- Hand-Drawn Double Yellow/Olive Oval Badge -->
            <div class="relative w-[300px] h-[112px] flex flex-col items-center justify-center text-center px-6 flex-shrink-0">
                <svg class="absolute inset-0 w-full h-full pointer-events-none" viewBox="0 0 300 112" fill="none">
                    <ellipse cx="150" cy="56" rx="144" ry="49" stroke="#b5ad53" stroke-width="1.6" transform="rotate(-1.5 150 56)"/>
                    <ellipse cx="150" cy="56" rx="142" ry="46" stroke="#b5ad53" stroke-width="1.4" transform="rotate(1.2 150 56)"/>
                </svg>
                <div class="relative z-10 leading-snug">
                    <div class="text-[15.5px] font-extrabold text-[#1d2d35]">Upgrades available</div>
                    <div class="text-[12px] text-[#1d2d35] mt-0.5">Make Basecamp even better with upgrades.</div>
                    <button type="button" @click="adminModal = 'upgrades'" class="text-[12px] text-[#1d2d35] underline hover:opacity-80 cursor-pointer">
                        See your options.
                    </button>
                </div>
            </div>
        </div>

        <!-- Administrators Section -->
        <div class="mt-4">
            <h2 class="text-[18px] font-extrabold text-[#1d2d35] pb-2 border-b border-[#e5e2dc]">
                Administrators
            </h2>

            <div class="mt-3.5 flex flex-wrap items-center gap-4 text-[15px] text-[#1d2d35]">
                <!-- Manjot Singh -->
                <button type="button"
                        @click="$dispatch('open-user-profile', window.BC_USERS_BY_INITIALS['M'])"
                        class="inline-flex items-center gap-2 hover:underline cursor-pointer">
                    <img src="/uploads/avatars/22369668.png" alt="Manjot Singh" class="w-[32px] h-[32px] rounded-full object-cover">
                    <span>Manjot Singh</span>
                </button>

                <!-- Pawandeep Kaur -->
                <button type="button"
                        @click="$dispatch('open-user-profile', window.BC_USERS_BY_INITIALS['PK'])"
                        class="inline-flex items-center gap-2 hover:underline cursor-pointer">
                    <span class="w-[32px] h-[32px] rounded-full bg-[#00897b] text-white font-bold text-[12.5px] flex items-center justify-center select-none">PK</span>
                    <span>Pawandeep Kaur</span>
                </button>

                <!-- Shachish Sneh -->
                <button type="button"
                        @click="$dispatch('open-user-profile', window.BC_USERS_BY_INITIALS['SS'])"
                        class="inline-flex items-center gap-2 hover:underline cursor-pointer">
                    <img src="/uploads/avatars/25036340.png" alt="Shachish Sneh" class="w-[32px] h-[32px] rounded-full object-cover">
                    <span>Shachish Sneh</span>
                </button>
            </div>
        </div>

        <!-- "You're an admin, so you can..." List -->
        <div class="mt-8">
            <h3 class="text-[14px] font-bold text-[#1d2d35] mb-3.5">
                You’re an admin, so you can...
            </h3>

            <div class="space-y-3 text-[15.5px] text-[#1d2d35]">
                <!-- 1. Manage people -->
                <a href="{{ route('people.index') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#e6effa] text-[#4a90e2] flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                        </svg>
                    </span>
                    <span class="group-hover:underline">Manage people</span>
                </a>

                <!-- 2. Add/remove administrators -->
                <a href="{{ route('adminland.administrators') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#e6effa] text-[#4a90e2] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                            <path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/>
                        </svg>
                    </span>
                    <span class="group-hover:underline">Add/remove administrators</span>
                </a>

                <!-- 3. Manage groups -->
                <a href="{{ route('adminland.groups') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#e6effa] text-[#4a90e2] flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M12 12.75c1.63 0 3.07.39 4.24.9 1.08.48 1.76 1.56 1.76 2.73V18H6v-1.61c0-1.18.68-2.26 1.76-2.73 1.17-.52 2.61-.91 4.24-.91zM4 13c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm18 0c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-10-2c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3z"/>
                        </svg>
                    </span>
                    <span class="group-hover:underline">Manage groups</span>
                </a>

                <!-- 4. Manage companies -->
                <a href="{{ route('adminland.companies') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#e6effa] text-[#4a90e2] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                            <path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/>
                        </svg>
                    </span>
                    <span class="group-hover:underline">Manage companies</span>
                </a>

                <!-- 5. Rename project tools -->
                <a href="{{ route('adminland.tools') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#e6effa] text-[#4a90e2] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <rect x="3" y="6" width="18" height="12" rx="2"/><line x1="8" y1="10" x2="8" y2="14"/>
                        </svg>
                    </span>
                    <span class="group-hover:underline">Rename project tools</span>
                </a>

                <!-- 6. Change message categories -->
                <a href="{{ route('adminland.categories') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#e6effa] text-[#4a90e2] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
                            <circle cx="4" cy="6" r="1" fill="currentColor"/><circle cx="4" cy="12" r="1" fill="currentColor"/><circle cx="4" cy="18" r="1" fill="currentColor"/>
                        </svg>
                    </span>
                    <span class="group-hover:underline">Change message categories</span>
                </a>

                <!-- 7. Turn Voice Notes on/off -->
                <a href="{{ route('adminland.voice_notes') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#e6effa] text-[#4a90e2] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                            <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5.91-3c-.49 0-.9.36-.98.85C16.52 14.2 14.47 16 12 16s-4.52-1.8-4.93-4.15c-.08-.49-.49-.85-.98-.85-.61 0-1.09.54-1 1.14.49 3 2.89 5.35 5.91 5.78V20c0 .55.45 1 1 1s1-.45 1-1v-2.08c3.02-.43 5.42-2.78 5.91-5.78.1-.6-.39-1.14-1-1.14z"/>
                        </svg>
                    </span>
                    <span class="group-hover:underline">Turn Voice Notes on/off</span>
                </a>

                <!-- 8. Move projects from Basecamp 2 to Basecamp 5 -->
                <a href="{{ route('adminland.import') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#e6effa] text-[#4a90e2] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </span>
                    <span class="group-hover:underline">Move projects from Basecamp 2 to Basecamp 5</span>
                </a>

                <!-- 9. Merge people -->
                <a href="{{ route('adminland.person_merges') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#e6effa] text-[#4a90e2] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7l4-4 4 4m-4-4v18"/>
                        </svg>
                    </span>
                    <span class="group-hover:underline">Merge people</span>
                </a>

                <!-- 10. Change Ping & Chat history settings -->
                <a href="{{ route('adminland.retention') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors cursor-pointer text-left">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#e6effa] text-[#4a90e2] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                            <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
                        </svg>
                    </span>
                    <span class="group-hover:underline">Change Ping &amp; Chat history settings</span>
                </a>
            </div>
        </div>

        <!-- Account Owners Section -->
        <div class="mt-12">
            <h2 class="text-[18px] font-extrabold text-[#1d2d35] pb-2 border-b border-[#e5e2dc]">
                Account Owners
            </h2>

            <div class="mt-3.5 flex flex-wrap items-center gap-4 text-[15px] text-[#1d2d35]">
                <button type="button"
                        @click="$dispatch('open-user-profile', window.BC_USERS_BY_INITIALS['M'])"
                        class="inline-flex items-center gap-2 hover:underline cursor-pointer">
                    <img src="/uploads/avatars/22369668.png" alt="Manjot Singh" class="w-[32px] h-[32px] rounded-full object-cover">
                    <span>Manjot Singh</span>
                </button>
            </div>
        </div>

        <!-- "You're an account owner, so you can..." List -->
        <div class="mt-8">
            <h3 class="text-[14px] font-bold text-[#1d2d35] mb-3.5">
                You’re an account owner, so you can...
            </h3>

            <div class="space-y-3 text-[15.5px] text-[#1d2d35]">
                <!-- 1. Handle billing, invoices, packages, and upgrades -->
                <a href="{{ route('settings.index') }}" class="flex items-start gap-3 group hover:text-[#1b6ac9] transition-colors py-0.5">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#fef3c7] text-[#d97706] flex items-center justify-center flex-shrink-0 mt-0.5 font-extrabold text-[13px]">$</span>
                    <div>
                        <div class="group-hover:underline leading-snug">Handle billing, invoices, packages, and upgrades</div>
                        <div class="text-[12.5px] text-[#6d6a65]">Your next payment: $1,200 on January 11, 2027.</div>
                    </div>
                </a>

                <!-- 2. Manage storage -->
                <a href="{{ route('settings.index') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#fef3c7] text-[#d97706] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 3C7.58 3 4 4.79 4 7v10c0 2.21 3.58 4 8 4s8-1.79 8-4V7c0-2.21-3.58-4-8-4zm0 2c3.87 0 6 1.5 6 2s-2.13 2-6 2-6-1.5-6-2 2.13-2 6-2zm6 12c0 .5-2.13 2-6 2s-6-1.5-6-2v-2.23c1.61.78 3.72 1.23 6 1.23s4.39-.45 6-1.23V17zm0-5c0 .5-2.13 2-6 2s-6-1.5-6-2V9.77c1.61.78 3.72 1.23 6 1.23s4.39-.45 6-1.23V12z"/></svg>
                    </span>
                    <span class="group-hover:underline">Manage storage</span>
                </a>

                <!-- 3. Add/remove account owners -->
                <a href="{{ route('adminland.administrators') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#fef3c7] text-[#d97706] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/></svg>
                    </span>
                    <span class="group-hover:underline">Add/remove account owners</span>
                </a>

                <!-- 4. Rename this account -->
                <a href="{{ route('settings.index') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#fef3c7] text-[#d97706] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                    </span>
                    <span class="group-hover:underline">Rename this account ({{ $org->name ?? 'Ajath Infotech Pvt Ltd' }})</span>
                </a>

                <!-- 5. View everything in the trash -->
                <a href="{{ route('activity.index') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#fef3c7] text-[#d97706] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    </span>
                    <span class="group-hover:underline">View everything in the trash</span>
                </a>

                <!-- 6. Reassign someone's to-dos -->
                <a href="{{ url('/account/todos/mass_reassignments/new') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#fef3c7] text-[#d97706] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </span>
                    <span class="group-hover:underline">Reassign someone’s to-dos</span>
                </a>

                <!-- 7. Access any project -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#fef3c7] text-[#d97706] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.65 10C11.83 7.67 9.61 6 7 6c-3.31 0-6 2.69-6 6s2.69 6 6 6c2.61 0 4.83-1.67 5.65-4H17v4h4v-4h2v-4H12.65zM7 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
                    </span>
                    <span class="group-hover:underline">Access any project</span>
                </a>

                <!-- 8. Export data from this account -->
                <a href="{{ route('adminland.import') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#fef3c7] text-[#d97706] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/></svg>
                    </span>
                    <span class="group-hover:underline">Export data from this account</span>
                </a>

                <!-- 9. Manage public items -->
                <a href="{{ route('settings.index') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#fef3c7] text-[#d97706] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </span>
                    <span class="group-hover:underline">Manage public items</span>
                </a>

                <!-- 10. Require two-factor authentication -->
                <a href="{{ route('settings.index') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#fef3c7] text-[#d97706] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                    </span>
                    <span class="group-hover:underline">Require two-factor authentication</span>
                </a>

                <!-- 11. Cancel this account -->
                <a href="{{ route('settings.index') }}" class="flex items-center gap-3 group hover:text-[#1b6ac9] transition-colors">
                    <span class="w-[28px] h-[28px] rounded-full bg-[#fef3c7] text-[#d97706] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    </span>
                    <span class="group-hover:underline">Cancel this account</span>
                </a>
            </div>
        </div>

        <!-- Bottom Promotional Upgrade Banners (Timesheet & Admin Pro Pack) -->
        <div class="mt-12 space-y-6">
            <!-- 1. Timesheet Upgrade Card -->
            <div class="relative rounded-[12px] border border-[#22c55e]/60 p-6 sm:p-7 flex flex-col md:flex-row items-center justify-between gap-6">
                <span class="absolute -top-2.5 left-5 px-2 py-0.5 rounded bg-[#4ade80] text-[#052e16] text-[10px] font-black uppercase tracking-wider">UPGRADE</span>
                <div class="max-w-[260px]">
                    <h3 class="text-[22px] font-black text-[#1d2d35] leading-tight mb-2">Timesheet</h3>
                    <p class="text-[14.5px] text-[#1d2d35] leading-relaxed mb-3">
                        Give your team the power to track time spent on projects.
                    </p>
                    <button type="button" @click="adminModal = 'upgrades'" class="text-[14.5px] font-bold text-[#1d2d35] underline hover:text-[#1b6ac9] cursor-pointer">
                        Check out Timesheet
                    </button>
                </div>

                <!-- Mini Website Redesign Timesheet Preview -->
                <div class="w-full md:w-[340px] bg-white rounded-[8px] border border-[#e5e2dc] shadow-md p-3.5 text-[9.5px] text-[#1d2d35] rotate-[1.2deg] shrink-0">
                    <div class="text-[7px] font-bold uppercase tracking-wider text-[#7c7872]">PROJECT</div>
                    <div class="text-[12px] font-black text-[#1d2d35] mb-2">Website Redesign</div>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[#e5e2dc] text-[7.5px] font-bold uppercase text-[#7c7872]">
                                <th class="pb-1">DATE</th>
                                <th class="pb-1">HOURS</th>
                                <th class="pb-1">PERSON</th>
                                <th class="pb-1">NOTES</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e5e2dc]/60">
                            <tr>
                                <td class="py-1">May 19, 2024</td>
                                <td class="py-1">1.5</td>
                                <td class="py-1 font-semibold">Kurt Holloway</td>
                                <td class="py-1 text-[#5c5955]">To-do: <span class="underline text-[#1d64c8]">Hero image</span></td>
                            </tr>
                            <tr>
                                <td class="py-1">May 18, 2024</td>
                                <td class="py-1">3</td>
                                <td class="py-1 font-semibold">Leah Bernstein</td>
                                <td class="py-1 text-[#5c5955] truncate max-w-[110px]">Talked to a few customers...</td>
                            </tr>
                            <tr>
                                <td class="py-1">May 13, 2024</td>
                                <td class="py-1">1</td>
                                <td class="py-1 font-semibold">Tamir Aviv</td>
                                <td class="py-1 text-[#5c5955]">Card: <span class="underline text-[#1d64c8]">Image rendering bug</span></td>
                            </tr>
                            <tr>
                                <td class="py-1">May 12, 2024</td>
                                <td class="py-1">2.5</td>
                                <td class="py-1 font-semibold">Kurt Holloway</td>
                                <td class="py-1 text-[#5c5955]">Card: <span class="underline text-[#1d64c8]">Improve SEO keywords</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 2. Admin Pro Pack Upgrade Card -->
            <div class="relative rounded-[12px] border border-[#d97706]/60 p-6 sm:p-7 flex flex-col md:flex-row items-center justify-between gap-6">
                <span class="absolute -top-2.5 left-5 px-2 py-0.5 rounded bg-[#d97706] text-white text-[10px] font-black uppercase tracking-wider">UPGRADE</span>
                <div class="max-w-[260px]">
                    <h3 class="text-[22px] font-black text-[#1d2d35] leading-tight mb-2">Admin Pro Pack</h3>
                    <p class="text-[14.5px] text-[#1d2d35] leading-relaxed mb-3">
                        The Admin Pro Pack is an upgrade for your account that gives you more control over permissions and access.
                    </p>
                    <button type="button" @click="adminModal = 'upgrades'" class="text-[14.5px] font-bold text-[#1d2d35] underline hover:text-[#1b6ac9] cursor-pointer">
                        Check out the Admin Pro Pack
                    </button>
                </div>

                <!-- Mini 4x2 Feature Grid Preview -->
                <div class="w-full md:w-[340px] bg-white rounded-[8px] border border-[#e5e2dc] shadow-md p-3 grid grid-cols-4 gap-2 text-center text-[8px] leading-tight text-[#1d2d35] rotate-[1.2deg] shrink-0">
                    <div class="p-1.5 rounded bg-[#f7f5f2] flex flex-col items-center justify-center gap-1">
                        <span class="text-[#d97706] font-bold">💬</span>
                        <span>Choose who can send pings</span>
                    </div>
                    <div class="p-1.5 rounded bg-[#f7f5f2] flex flex-col items-center justify-center gap-1">
                        <span class="text-[#d97706] font-bold">🔗</span>
                        <span>Choose who can turn on public links</span>
                    </div>
                    <div class="p-1.5 rounded bg-[#f7f5f2] flex flex-col items-center justify-center gap-1">
                        <span class="text-[#d97706] font-bold">🗂️</span>
                        <span>Choose who can archive, move, and delete projects</span>
                    </div>
                    <div class="p-1.5 rounded bg-[#f7f5f2] flex flex-col items-center justify-center gap-1">
                        <span class="text-[#d97706] font-bold">👥</span>
                        <span>Choose who can change the people on a project</span>
                    </div>
                    <div class="p-1.5 rounded bg-[#f7f5f2] flex flex-col items-center justify-center gap-1">
                        <span class="text-[#d97706] font-bold">📦</span>
                        <span>Archive completed to-dos and cards automatically</span>
                    </div>
                    <div class="p-1.5 rounded bg-[#f7f5f2] flex flex-col items-center justify-center gap-1">
                        <span class="text-[#d97706] font-bold">✏️</span>
                        <span>Limit editing and deleting comments and chats to 15 min</span>
                    </div>
                    <div class="p-1.5 rounded bg-[#f7f5f2] flex flex-col items-center justify-center gap-1">
                        <span class="text-[#d97706] font-bold">🌴</span>
                        <span>Set Out of Office for others</span>
                    </div>
                    <div class="p-1.5 rounded bg-[#f7f5f2] flex flex-col items-center justify-center font-black text-[10px] text-[#d97706]">
                        &amp; more
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Adminland Quick Action Modal -->
    <div x-show="adminModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/40" @click="adminModal = null"></div>
        <div class="relative z-10 w-[440px] max-w-[95vw] bg-white rounded-[10px] border border-[#e5e2dc] shadow-2xl p-6 text-[#1d2d35]">
            <button type="button" @click="adminModal = null" class="absolute top-3.5 right-3.5 p-1 text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer">✕</button>
            <h3 class="text-[20px] font-extrabold text-[#1d2d35] mb-2"
                x-text="adminModal === 'upgrades' ? 'Basecamp Pro Unlimited' : (adminModal === 'groups' ? 'Manage groups' : (adminModal === 'tools' ? 'Rename project tools' : (adminModal === 'categories' ? 'Message categories' : (adminModal === 'voice' ? 'Voice Notes' : 'Ping & Chat history settings'))))"></h3>
            <p class="text-[14px] text-[#6d6a65] leading-relaxed">
                This setting is enabled for <strong>{{ $org->name ?? 'Ajath Infotech Pvt Ltd' }}</strong>.
            </p>
            <div class="mt-4">
                <button type="button" @click="adminModal = null" class="px-4 py-1.5 rounded-[6px] bg-[#1b6ac9] text-white text-[13.5px] font-semibold cursor-pointer">Done</button>
            </div>
        </div>
    </div>

</div>
@endsection
