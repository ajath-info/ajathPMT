@extends('layouts.app')

@section('title', 'Account & Settings - Basecamp')

@section('content')
    <div class="w-full px-4 pt-6 pb-20 font-sans text-[#1d2d35]"
        x-data="{ activeSubModal: '{{ request()->query('ooo') ? 'ooo' : (request()->query('devices') ? 'devices' : '') }}' }">
        x-data="{ activeSubModal:
        '{{ request()->query('preferences') ? 'preferences' : (request()->query('ooo') ? 'ooo' : (request()->query('devices') ? 'devices' : '')) }}'
        }">

        <!-- Centered Heading Matching media_1793278223186_960423f9.jpg -->
        <h1 class="text-[26px] sm:text-[28px] font-extrabold text-[#1d2d35] tracking-[-0.025em] text-center mb-5">
            Account &amp; Settings
        </h1>

        <!-- ========================================================================= -->
        <!-- CARD 1: SETTINGS MENU CARD (Exact Match to media_1793278223186_960423f9.jpg) -->
        <!-- ========================================================================= -->
        <div
            class="w-[432px] max-w-[95vw] mx-auto bg-white rounded-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] overflow-hidden">
            <!-- Card Subheader -->
            <div
                class="py-2.5 px-4 text-center text-[13.5px] font-bold text-[#1d2d35] border-b border-[#ece9e2] bg-[#fbfaf8]">
                Settings
            </div>

            <div class="divide-y divide-[#ece9e2] text-[15px] text-[#1d2d35]">
                <!-- 1. Adminland -->
                <a href="{{ route('adminland.index') }}"
                    class="flex items-center gap-3 px-4 py-3 hover:bg-[#f6f4f0] transition-colors">
                    <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <span>Adminland</span>
                </a>

                <!-- 2. Templates -->
                <a href="{{ route('templates.index') }}"
                    class="w-full flex items-center gap-3 px-4 py-3 hover:bg-[#f6f4f0] transition-colors text-left cursor-pointer">
                    <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="16" rx="2" />
                        <line x1="3" y1="9" x2="21" y2="9" />
                    </svg>
                    <span>Templates</span>
                </a>

                <!-- 3. Profile, password, 2FA -->
                <a href="{{ route('my.profile', ['edit' => 1]) }}"
                    class="flex items-center gap-3 px-4 py-3 hover:bg-[#f6f4f0] transition-colors">
                    <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z" />
                    </svg>
                    <span>Profile, password, 2FA</span>
                </a>

                <!-- 4. Notification settings -->
                <a href="{{ route('my.notifications.settings') }}"
                    class="w-full flex items-center gap-3 px-4 py-3 hover:bg-[#f6f4f0] transition-colors text-left cursor-pointer">
                    <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z" />
                    </svg>
                    <span>Notification settings</span>
                </a>

                <!-- 5. Preferences -->
                <button type="button" @click="activeSubModal = 'preferences'"
                    class="w-full flex items-center gap-3 px-4 py-3 hover:bg-[#f6f4f0] transition-colors text-left cursor-pointer">
                    <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="3" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z" />
                    </svg>
                    <span>Preferences</span>
                </button>

                <!-- 6. Out of Office -->
                <button type="button" @click="activeSubModal = 'ooo'"
                    class="w-full flex items-center gap-3 px-4 py-3 hover:bg-[#f6f4f0] transition-colors text-left cursor-pointer">
                    <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9" />
                        <polyline points="12 7 12 12 15 15" />
                    </svg>
                    <span>Out of Office</span>
                </button>

                <!-- 7. My devices (6 devices set up) -->
                <button type="button" @click="activeSubModal = 'devices'"
                    class="w-full flex items-center gap-3 px-4 py-3 hover:bg-[#f6f4f0] transition-colors text-left cursor-pointer">
                    <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <rect x="2" y="4" width="14" height="12" rx="2" />
                        <rect x="16" y="8" width="6" height="12" rx="1" />
                    </svg>
                    <span>My devices (6 devices set up)</span>
                </button>

                <!-- 8. Connected apps -->
                <button type="button" @click="activeSubModal = 'apps'"
                    class="w-full flex items-center gap-3 px-4 py-3 hover:bg-[#f6f4f0] transition-colors text-left cursor-pointer">
                    <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                    <span>Connected apps</span>
                </button>

                <!-- 9. Help -->
                <button type="button" @click="$dispatch('open-support-widget', { tab: 'contact' })"
                    class="w-full flex items-center justify-between px-4 py-3 hover:bg-[#f6f4f0] transition-colors text-left cursor-pointer">
                    <span class="flex items-center gap-3">
                        <span
                            class="w-4 h-4 inline-flex items-center justify-center font-extrabold text-[14px] text-[#1d2d35]">?</span>
                        <span>Help</span>
                    </span>
                    <kbd
                        class="px-1.5 py-0.5 text-[10.5px] font-mono border border-[#d6d2c9] rounded-[4px] text-[#6d6a65] bg-[#fbfaf8]">?</kbd>
                </button>

                <!-- 10. Log out -->
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-between px-4 py-3 hover:bg-[#f6f4f0] transition-colors text-left cursor-pointer">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-[#1d2d35] flex-shrink-0" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Log out</span>
                        </span>
                        <kbd
                            class="px-1.5 py-0.5 text-[10.5px] font-mono border border-[#d6d2c9] rounded-[4px] text-[#6d6a65] bg-[#fbfaf8]">:MQ</kbd>
                    </button>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- CARD 2: GET THE BASECAMP APPS (Exact Match to media_1793278223186_960423f9.jpg) -->
        <!-- ========================================================================= -->
        <div
            class="mt-5 w-[432px] max-w-[95vw] mx-auto bg-white rounded-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] overflow-hidden">
            <div
                class="py-2.5 px-4 text-center text-[13.5px] font-bold text-[#1d2d35] border-b border-[#ece9e2] bg-[#fbfaf8]">
                Get the Basecamp apps
            </div>

            <div class="divide-y divide-[#ece9e2] text-[15px] text-[#1d2d35] text-center">
                <div class="px-4 py-3.5">
                    <div class="mb-2.5">Mobile apps for iPhone, iPad, &amp; Android</div>
                    <div class="flex items-center justify-center gap-2.5">
                        {{-- App Store Badge --}}
                        <a href="https://basecamp.com/mobile" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] bg-black text-white border border-[#374151] hover:opacity-90 transition-opacity">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path
                                    d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.96 6.33c.67-.81 1.12-1.94 1-3.08-.96.04-2.13.64-2.82 1.45-.61.7-.11 1.86-.99 2.97 1.08.08 2.14-.53 2.81-1.34z" />
                            </svg>
                            <div class="text-left leading-none">
                                <div class="text-[7px] font-medium opacity-85">Download on the</div>
                                <div class="text-[11px] font-bold tracking-tight">App Store</div>
                            </div>
                        </a>

                        {{-- Google Play Badge --}}
                        <a href="https://basecamp.com/mobile" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] bg-black text-white border border-[#374151] hover:opacity-90 transition-opacity">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M3 20.5V3.5C3 2.91 3.34 2.39 3.84 2.15L13.69 12L3.84 21.85C3.34 21.6 3 21.09 3 20.5Z"
                                    fill="#38BDF8" />
                                <path d="M16.81 15.12L6.05 21.34L14.54 12.85L16.81 15.12Z" fill="#F43F5E" />
                                <path
                                    d="M20.16 10.81C20.5 11.08 20.75 11.5 20.75 12C20.75 12.5 20.53 12.9 20.18 13.18L17.89 14.5L15.39 12L17.89 9.5L20.16 10.81Z"
                                    fill="#FACC15" />
                                <path d="M6.05 2.66L16.81 8.88L14.54 11.15L6.05 2.66Z" fill="#4ADE80" />
                            </svg>
                            <div class="text-left leading-none">
                                <div class="text-[7px] font-medium uppercase opacity-85">GET IT ON</div>
                                <div class="text-[11px] font-bold tracking-tight">Google Play</div>
                            </div>
                        </a>
                    </div>
                </div>
                <a href="https://basecamp.com/desktop" target="_blank" rel="noopener"
                    class="block px-4 py-3 hover:bg-[#f6f4f0] transition-colors">
                    Desktop apps for Mac &amp; Windows
                </a>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- SUB-MODALS FOR NOTIFICATION SETTINGS, PREFERENCES, OUT OF OFFICE, ETC.    -->
        <!-- ========================================================================= -->
        <div x-show="activeSubModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog"
            aria-modal="true">
            <div class="fixed inset-0 bg-black/40" @click="activeSubModal = null"></div>

            <div
                class="relative z-10 w-[460px] max-w-[95vw] bg-white rounded-[10px] border border-[#e5e2dc] shadow-2xl p-6 text-[#1d2d35]">
                <button type="button" @click="activeSubModal = null"
                    class="absolute top-3.5 right-3.5 p-1 text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer">✕</button>

                <!-- Notifications -->
                <div x-show="activeSubModal === 'notifications'">
                    <h3 class="text-[20px] font-extrabold text-[#1d2d35] mb-3">Notification settings</h3>
                    <form action="{{ route('my.settings.update') }}" method="POST" class="space-y-3 text-[14px]">
                        @csrf
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="radio" name="notifications" value="always" {{ ($notificationPref ?? 'always') === 'always' ? 'checked' : '' }} class="mt-1">
                            <span><strong>Always notify me</strong> (24/7 for Pings, mentions, and assignments)</span>
                        </label>
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="radio" name="notifications" value="work_hours" {{ ($notificationPref ?? '') === 'work_hours' ? 'checked' : '' }} class="mt-1">
                            <span><strong>Only during work hours</strong> (Work Can Wait: Mon–Fri, 9am–6pm IST)</span>
                        </label>
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="radio" name="notifications" value="paused" {{ ($notificationPref ?? '') === 'paused' ? 'checked' : '' }} class="mt-1">
                            <span><strong>Focus Mode (Shhh...)</strong> — temporarily pause notifications</span>
                        </label>
                        <div class="pt-3">
                            <button type="submit"
                                class="px-4 py-2 rounded-[6px] bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-semibold text-[13.5px] cursor-pointer">Save
                                changes</button>
                        </div>
                    </form>
                </div>

                <!-- Preferences (Exact Match to media_1793282226159_9c71de60.jpg) -->
                <!-- Preferences (Exact Match to media_1793282226159_9c71de60.jpg & media_1793289805630_53755f22.jpg) -->
                <div x-show="activeSubModal === 'preferences'" x-data="{
                        appearance: localStorage.getItem('bc_appearance') || 'os',
                        timezone: '(GMT+05:30) Chennai',
                        firstDay: 'Monday',
                        timeFormat: '12-hour clock',
                        setAppearance(mode) {
                            this.appearance = mode;
                            localStorage.setItem('bc_appearance', mode);
                            if (window.applyBasecampTheme) {
                                window.applyBasecampTheme(mode);
                            }
                        }
                    }" class="px-1 py-1">
                    }" @bc-theme-changed.window="appearance = $event.detail.mode" class="px-1 py-1">
                    <h3 class="text-[26px] font-black text-[#1d2d35] text-center tracking-tight mb-5">Preferences</h3>

                    {{-- Centered Divider: Appearance --}}
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-px bg-[#e5e2dc] flex-1"></div>
                        <span class="text-[13.5px] font-bold text-[#1d2d35]">Appearance</span>
                        <div class="h-px bg-[#e5e2dc] flex-1"></div>
                    </div>

                    {{-- 3-Column Appearance Selector: Always light | Always dark | Same as OS --}}
                    <div class="grid grid-cols-3 gap-2.5 mb-6">
                        <button type="button" @click="setAppearance('light')"
                            class="py-3 px-2 rounded-[8px] border flex flex-col items-center justify-center gap-1.5 text-[12.5px] transition-all cursor-pointer"
                            :class="appearance === 'light'
                                        ? 'bg-[#74d38a] border-[#5cc073] text-[#052e16] font-semibold shadow-xs'
                                        : 'bg-white border-[#d4d0c8] text-[#1d2d35] hover:border-[#9ca3af]'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="4" />
                                <path stroke-linecap="round"
                                    d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41m11.32-11.32l1.41-1.41" />
                            </svg>
                            <span>Always light</span>
                        </button>

                        <button type="button" @click="setAppearance('dark')"
                            class="py-3 px-2 rounded-[8px] border flex flex-col items-center justify-center gap-1.5 text-[12.5px] transition-all cursor-pointer"
                            :class="appearance === 'dark'
                                        ? 'bg-[#74d38a] border-[#5cc073] text-[#052e16] font-semibold shadow-xs'
                                        : 'bg-white border-[#d4d0c8] text-[#1d2d35] hover:border-[#9ca3af]'">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                            </svg>
                            <span>Always dark</span>
                        </button>

                        <button type="button" @click="setAppearance('os')"
                            class="py-3 px-2 rounded-[8px] border flex flex-col items-center justify-center gap-1.5 text-[12.5px] transition-all cursor-pointer"
                            :class="appearance === 'os'
                                        ? 'bg-[#74d38a] border-[#5cc073] text-[#052e16] font-semibold shadow-xs'
                                        : 'bg-white border-[#d4d0c8] text-[#1d2d35] hover:border-[#9ca3af]'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="12" rx="1.5" />
                                <path stroke-linecap="round" d="M8 20h8M12 16v4" />
                            </svg>
                            <span>Same as OS</span>
                        </button>
                    </div>

                    {{-- Centered Divider: Date/Time --}}
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-px bg-[#e5e2dc] flex-1"></div>
                        <span class="text-[13.5px] font-bold text-[#1d2d35]">Date/Time</span>
                        <div class="h-px bg-[#e5e2dc] flex-1"></div>
                    </div>

                    <form action="{{ route('my.settings.update') }}" method="POST" @submit.prevent="activeSubModal = null"
                        class="space-y-4 text-left">
                        @csrf
                        {{-- 1. Time zone --}}
                        <div>
                            <label class="block text-[13.5px] font-bold text-[#1d2d35]">Time zone</label>
                            <p class="text-[11.5px] text-[#5c5955] leading-snug mb-1.5">
                                Basecamp uses your time zone setting for email notifications, event reminders,
                                <a href="{{ route('my.profile') }}" class="underline text-[#2563eb]">your profile</a>, and
                                your
                                <a href="{{ route('my.notifications.settings') }}"
                                    class="underline text-[#2563eb]">notification settings</a>.
                            </p>
                            <select x-model="timezone"
                                class="w-full h-[38px] px-3 rounded-[6px] border border-[#d6d2c9] bg-white text-[13.5px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                                <option>(GMT+03:00) Moscow</option>
                                <option>(GMT+03:00) Nairobi</option>
                                <option>(GMT+03:00) Riyadh</option>
                                <option>(GMT+03:00) St. Petersburg</option>
                                <option>(GMT+03:00) Volgograd</option>
                                <option>(GMT+03:30) Tehran</option>
                                <option>(GMT+04:00) Abu Dhabi</option>
                                <option>(GMT+04:00) Baku</option>
                                <option>(GMT+04:00) Muscat</option>
                                <option>(GMT+04:00) Samara</option>
                                <option>(GMT+04:00) Tbilisi</option>
                                <option>(GMT+04:00) Yerevan</option>
                                <option>(GMT+04:30) Kabul</option>
                                <option>(GMT+05:00) Almaty</option>
                                <option>(GMT+05:00) Astana</option>
                                <option>(GMT+05:00) Ekaterinburg</option>
                                <option>(GMT+05:00) Islamabad</option>
                                <option>(GMT+05:00) Karachi</option>
                                <option>(GMT+05:00) Tashkent</option>
                                <option selected>(GMT+05:30) Chennai</option>
                                <option>(GMT+05:30) Kolkata</option>
                                <option>(GMT+05:30) Mumbai</option>
                                <option>(GMT+05:30) New Delhi</option>
                                <option>(GMT+05:30) Sri Jayawardenepura</option>
                                <option>(GMT+05:45) Kathmandu</option>
                                <option>(GMT+06:00) Dhaka</option>
                                <option>(GMT+06:00) Urumqi</option>
                                <option>(GMT+06:30) Rangoon</option>
                                <option>(GMT+07:00) Bangkok</option>
                                <option>(GMT+07:00) Hanoi</option>
                                <option>(GMT+07:00) Jakarta</option>
                                <option>(GMT+07:00) Krasnoyarsk</option>
                            </select>
                        </div>

                        {{-- 2. First day of the week --}}
                        <div>
                            <label class="block text-[13.5px] font-bold text-[#1d2d35]">First day of the week</label>
                            <p class="text-[11.5px] text-[#5c5955] leading-snug mb-1.5">
                                Basecamp uses this when presenting calendars.
                            </p>
                            <select x-model="firstDay"
                                class="w-full h-[38px] px-3 rounded-[6px] border border-[#d6d2c9] bg-white text-[13.5px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                                <option>Sunday</option>
                                <option selected>Monday</option>
                                <option>Tuesday</option>
                                <option>Wednesday</option>
                                <option>Thursday</option>
                                <option>Friday</option>
                                <option>Saturday</option>
                            </select>
                        </div>

                        {{-- 3. Time format --}}
                        <div>
                            <label class="block text-[13.5px] font-bold text-[#1d2d35]">Time format</label>
                            <p class="text-[11.5px] text-[#5c5955] leading-snug mb-1.5">
                                Basecamp uses this format when displaying the time.
                            </p>
                            <select x-model="timeFormat"
                                class="w-full h-[38px] px-3 rounded-[6px] border border-[#d6d2c9] bg-white text-[13.5px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                                <option selected>12-hour clock</option>
                                <option>24-hour clock</option>
                            </select>
                        </div>

                        <div class="pt-1">
                            <button type="submit"
                                class="w-full py-2.5 rounded-[6px] bg-[#60a5fa] hover:bg-[#3b82f6] text-[#0f172a] font-semibold text-[13.5px] transition-colors cursor-pointer">
                                Save my Date/Time changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Out of Office (Exact Match to media_1793282247678_4f5961a3.jpg & media_1793282263326_a178370f.jpg) -->
                <div x-show="activeSubModal === 'ooo'" x-data="{
                        isOut: false,
                        startDate: 'Thu, Oct 29, 2026',
                        endDate: 'Fri, Nov 6, 2026',
                        activeCalendar: null,
                        calMonth: 9,
                        calYear: 2026,
                        selectedStartDay: 29,
                        selectedStartMonth: 9,
                        selectedEndDay: 6,
                        selectedEndMonth: 10,
                        savedOoo: false,
                        monthNames: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        dayNames: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                        openCal(which) {
                            this.activeCalendar = (this.activeCalendar === which) ? null : which;
                            if (which === 'start') {
                                this.calMonth = this.selectedStartMonth;
                            } else {
                                this.calMonth = this.selectedEndMonth;
                            }
                        },
                        prevMonth() {
                            if (this.calMonth === 0) { this.calMonth = 11; this.calYear--; }
                            else { this.calMonth--; }
                        },
                        nextMonth() {
                            if (this.calMonth === 11) { this.calMonth = 0; this.calYear++; }
                            else { this.calMonth++; }
                        },
                        getCalendarCells() {
                            const firstDay = new Date(this.calYear, this.calMonth, 1);
                            const startDow = (firstDay.getDay() + 6) % 7; // Monday = 0
                            const daysInMonth = new Date(this.calYear, this.calMonth + 1, 0).getDate();
                            const daysInPrev = new Date(this.calYear, this.calMonth, 0).getDate();
                            const cells = [];
                            for (let i = startDow - 1; i >= 0; i--) {
                                cells.push({ day: daysInPrev - i, monthOffset: -1, inMonth: false });
                            }
                            for (let d = 1; d <= daysInMonth; d++) {
                                cells.push({ day: d, monthOffset: 0, inMonth: true });
                            }
                            let nextDay = 1;
                            while (cells.length % 7 !== 0) {
                                cells.push({ day: nextDay++, monthOffset: 1, inMonth: false });
                            }
                            return cells;
                        },
                        pickDate(cell) {
                            const m = (this.calMonth + cell.monthOffset + 12) % 12;
                            const dt = new Date(this.calYear, this.calMonth + cell.monthOffset, cell.day);
                            const formatted = this.dayNames[dt.getDay()] + ', ' + this.monthNames[dt.getMonth()] + ' ' + dt.getDate() + ', ' + dt.getFullYear();
                            if (this.activeCalendar === 'start') {
                                this.startDate = formatted;
                                this.selectedStartDay = dt.getDate();
                                this.selectedStartMonth = dt.getMonth();
                            } else {
                                this.endDate = formatted;
                                this.selectedEndDay = dt.getDate();
                                this.selectedEndMonth = dt.getMonth();
                            }
                            this.activeCalendar = null;
                        }
                    }" class="max-h-[78vh] overflow-y-auto px-1 py-1 text-left">
                    <h3 class="text-[26px] font-black text-[#1d2d35] text-center tracking-tight mb-1">Out of Office?</h3>
                    <p class="text-[13.5px] text-[#5c5955] text-center leading-snug mb-5">
                        Heading out on holiday? Taking a few days off?<br>
                        Let your colleagues know you&rsquo;re out.
                    </p>

                    {{-- Lavender Profile Card --}}
                    <div class="rounded-[12px] bg-[#c095fa] px-5 py-6 text-center mb-5 shadow-xs">
                        {{-- Avatar with optional yellow OUT overlay --}}
                        <div class="relative w-[108px] h-[108px] rounded-full overflow-hidden mx-auto shadow-md">
                            <img src="{{ (Auth::user()->avatar_url && !str_contains(Auth::user()->avatar_url, 'dicebear')) ? Auth::user()->avatar_url : '/uploads/avatars/22369668.png' }}"
                                alt="{{ Auth::user()->name ?? 'Manjot Singh' }}" class="w-full h-full object-cover">
                            <div x-show="isOut" x-cloak
                                class="absolute bottom-0 inset-x-0 h-[40%] bg-[#facc15] flex items-center justify-center">
                                <span class="text-[20px] font-black text-[#1d1c1b] tracking-tight leading-none">OUT</span>
                            </div>
                        </div>

                        <div class="text-[18px] font-black text-[#1d1c1b] mt-3 leading-tight">
                            {{ Auth::user()->name ?? 'Manjot Singh' }}
                        </div>
                        <div class="text-[11.5px] font-bold text-[#1d1c1b]/75 mt-0.5">
                            {{ Auth::user()->job_title ?? 'Global VP & Partner at Ajath Infotech Pvt Ltd' }}
                        </div>

                        {{-- I'm in / I'm out Switch --}}
                        <div class="mt-4 flex items-center justify-center gap-2.5 text-[13.5px] font-medium text-[#1d1c1b]">
                            <span>I&rsquo;m in</span>
                            <button type="button" @click="isOut = !isOut; savedOoo = false"
                                class="w-11 h-6 rounded-[6px] p-1 flex items-center transition-colors cursor-pointer border"
                                :class="isOut ? 'bg-[#74d38a] border-[#5cc073]' : 'bg-[#eef2ff] border-[#60a5fa]'">
                                <span class="w-3.5 h-4 rounded-[3px] bg-[#1e262b] transition-transform"
                                    :class="isOut ? ' ml-auto' : 'mr-auto'"></span>
                            </button>
                            <span>I&rsquo;m out</span>
                        </div>

                        {{-- Expanded Date Range Inputs when "I'm out" is active --}}
                        <div x-show="isOut" x-cloak class="mt-4 pt-4 border-t border-[#1d1c1b]/15 relative">
                            <div class="grid grid-cols-2 gap-2.5 mb-3.5">
                                <div class="relative">
                                    <label class="block text-[11.5px] font-bold text-[#1d1c1b] text-center mb-1">I&rsquo;m
                                        out starting on...</label>
                                    <div class="relative" @click="openCal('start')">
                                        <input type="text" x-model="startDate" readonly
                                            class="w-full px-2.5 py-1.5 pr-7 rounded-[6px] bg-[#1e262b] text-white text-[12px] font-medium focus:outline-none cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-white/60 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none"
                                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <rect x="3" y="4" width="18" height="18" rx="2" />
                                            <line x1="16" y1="2" x2="16" y2="6" />
                                            <line x1="8" y1="2" x2="8" y2="6" />
                                            <line x1="3" y1="10" x2="21" y2="10" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="relative">
                                    <label class="block text-[11.5px] font-bold text-[#1d1c1b] text-center mb-1">Back in the
                                        office on...</label>
                                    <div class="relative" @click="openCal('end')">
                                        <input type="text" x-model="endDate" readonly
                                            class="w-full px-2.5 py-1.5 pr-7 rounded-[6px] bg-[#1e262b] text-white text-[12px] font-medium focus:outline-none cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-white/60 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none"
                                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <rect x="3" y="4" width="18" height="18" rx="2" />
                                            <line x1="16" y1="2" x2="16" y2="6" />
                                            <line x1="8" y1="2" x2="8" y2="6" />
                                            <line x1="3" y1="10" x2="21" y2="10" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            {{-- Floating Dark Calendar Popover (Matches media_1793282270966 & media_1793282274950) --}}
                            <div x-show="activeCalendar" x-cloak @click.outside="activeCalendar = null"
                                class="absolute top-[74px] z-40 w-[224px] rounded-[10px] bg-[#1f2930] border border-[#374151] p-3.5 shadow-2xl text-white select-none"
                                :class="activeCalendar === 'start' ? 'left-0' : 'right-0'">
                                {{-- Top Month/Year Selectors & Prev/Next Arrows --}}
                                <div class="flex items-center justify-between mb-2.5 text-[12px] font-bold">
                                    <div class="flex items-center gap-1.5">
                                        <select x-model.number="calMonth"
                                            class="bg-transparent text-white font-bold focus:outline-none cursor-pointer">
                                            <template x-for="(mName, mIdx) in monthNames" :key="mIdx">
                                                <option :value="mIdx" x-text="mName" class="bg-[#1f2930] text-white">
                                                </option>
                                            </template>
                                        </select>
                                        <select x-model.number="calYear"
                                            class="bg-transparent text-white font-bold focus:outline-none cursor-pointer">
                                            <option :value="2025" class="bg-[#1f2930] text-white">2025</option>
                                            <option :value="2026" class="bg-[#1f2930] text-white">2026</option>
                                            <option :value="2027" class="bg-[#1f2930] text-white">2027</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-2 text-white/80">
                                        <button type="button" @click="prevMonth()"
                                            class="hover:text-white cursor-pointer px-1">&lsaquo;</button>
                                        <button type="button" @click="nextMonth()"
                                            class="hover:text-white cursor-pointer px-1">&rsaquo;</button>
                                    </div>
                                </div>

                                {{-- Weekday Headers --}}
                                <div
                                    class="grid grid-cols-7 text-center text-[9.5px] font-bold text-[#9ca3af] uppercase mb-1.5">
                                    <span>MO</span><span>TU</span><span>WE</span><span>TH</span><span>FR</span><span>SA</span><span>SU</span>
                                </div>

                                {{-- Day Cells --}}
                                <div class="grid grid-cols-7 gap-y-1 text-center text-[11.5px]">
                                    <template x-for="(cell, cIdx) in getCalendarCells()" :key="cIdx">
                                        <button type="button" @click="pickDate(cell)"
                                            class="w-6 h-6 mx-auto rounded-full flex items-center justify-center transition-colors cursor-pointer"
                                            :class="((activeCalendar === 'start' && cell.inMonth && cell.day === selectedStartDay && calMonth === selectedStartMonth) ||
                                                             (activeCalendar === 'end' && cell.inMonth && cell.day === selectedEndDay && calMonth === selectedEndMonth))
                                                        ? 'bg-white text-[#1d1c1b] font-bold'
                                                        : ((activeCalendar === 'end' && !cell.inMonth && cell.day === selectedStartDay)
                                                            ? 'bg-white/20 text-white/80 font-semibold'
                                                            : (cell.inMonth ? 'text-white hover:bg-white/15 font-medium' : 'text-[#6b7280] hover:bg-white/10'))">
                                            <span x-text="cell.day"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <button type="button" @click="savedOoo = true"
                                class="w-full py-2 rounded-[6px] bg-[#74d38a] hover:bg-[#5cc073] text-[#052e16] font-bold text-[13.5px] transition-colors cursor-pointer">
                                <span x-text="savedOoo ? 'Saved!' : 'Save'"></span>
                            </button>

                            <p class="text-[11.5px] text-[#1d1c1b]/70 mt-2.5 leading-snug">
                                Note: It may take up to 30min for your avatar to update everywhere.
                            </p>
                        </div>
                    </div>

                    {{-- Explanatory FAQ Copy Below Lavender Card --}}
                    <div class="text-[13px] text-[#1d2d35] leading-relaxed space-y-3.5">
                        <div>
                            <div class="font-bold">How will my colleagues know that I&rsquo;m out?</div>
                            <p class="text-[#5c5955]">There are several ways in which we flag that you are out, and when you
                                are coming back:</p>
                            <ul class="list-disc pl-5 mt-2 space-y-2 text-[#5c5955]">
                                <li>
                                    <strong class="text-[#1d2d35]">Person Card + Avatar:</strong> We place an
                                    &ldquo;OUT&rdquo; yellow overlay on your avatar across the entire Basecamp. Check it out
                                    by switching &ldquo;I&rsquo;m out&rdquo; using the toggle above.
                                </li>
                                <li>
                                    <strong class="text-[#1d2d35]">Autocomplete:</strong> If someone types @yourname, the
                                    autocomplete will show that you are out and when you are coming back.
                                </li>
                                <li>
                                    <strong class="text-[#1d2d35]">Your activity:</strong> If someone visits your activity
                                    page, they will see when you are out and when you are coming back.
                                </li>
                            </ul>
                        </div>

                        <div>
                            <div class="font-bold">Do I get notifications while I&rsquo;m Out?</div>
                            <p class="text-[#5c5955]">
                                Out of Office doesn&rsquo;t affect your notifications. To limit your own notifications, you
                                can change your notifications from the Notifications Settings screen
                            </p>
                        </div>

                        <div>
                            <div class="font-bold">Is there an auto-responder while out?</div>
                            <p class="text-[#5c5955]">
                                Out of Office is NOT an auto-responder. OOO is a visual way to communicate to other people
                                that you&rsquo;re out, and when you&rsquo;ll be back.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- My Devices (Exact Match to media_1793282283158, media_1793282286891, media_1793282292567) -->
                <div x-show="activeSubModal === 'devices'" x-data="{
                        troubleOpen: false,
                        testedId: null,
                        devices: [
                            { id: '1586460', name: 'iPad Pro (12.9-inch)', type: 'ios', registered: 'November 29, 2018 2:08pm', app: 'Official Basecamp app for iOS' },
                            { id: '2233764', name: 'RMX1921', type: 'android', registered: 'October 21, 2019 6:36pm', app: 'Official Basecamp app for Android' },
                            { id: '2815603', name: 'iPhone X', type: 'ios', registered: 'July 27, 2020 8:03pm', app: 'Official Basecamp app for iOS' },
                            { id: '3006294', name: 'iPhone', type: 'ios', registered: 'November 3, 2020 11:14am', app: 'Official Basecamp app for iOS' },
                            { id: '3754639', name: 'iPhone', type: 'ios', registered: 'December 13, 2021 5:06pm', app: 'Official Basecamp app for iOS' },
                            { id: '4153934', name: 'iPad', type: 'ios', registered: 'July 20, 2022 10:57am', app: 'Official Basecamp app for iOS' }
                        ]
                    }" class="max-h-[78vh] overflow-y-auto px-1 py-1 text-left">
                    <h3 class="text-[26px] font-black text-[#1d2d35] tracking-tight leading-tight mb-2">My Devices</h3>
                    <p class="text-[13.5px] text-[#5c5955] leading-relaxed mb-4">
                        Notifications were enabled on these devices when you installed the Basecamp app. If you&rsquo;re no
                        longer using Basecamp on a device, you can remove them.
                    </p>

                    {{-- Devices List --}}
                    <div class="divide-y divide-[#e5e2dc]">
                        <template x-for="(dev, dIdx) in devices" :key="dev.id">
                            <div class="py-4 flex items-start gap-3.5">
                                {{-- Apple vs Android Icon --}}
                                <div class="w-7 flex-shrink-0 pt-0.5 flex justify-center">
                                    <template x-if="dev.type === 'ios'">
                                        <svg class="w-6 h-6 text-[#1d2d35] fill-current" viewBox="0 0 24 24">
                                            <path
                                                d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.96 6.33c.67-.81 1.12-1.94 1-3.08-.96.04-2.13.64-2.82 1.45-.61.7-.11 1.86-.99 2.97 1.08.08 2.14-.53 2.81-1.34z" />
                                        </svg>
                                    </template>
                                    <template x-if="dev.type === 'android'">
                                        <svg class="w-6 h-6 text-[#84cc16] fill-current" viewBox="0 0 24 24">
                                            <path
                                                d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.5511 0 .9993.4482.9993.9993.0001.5511-.4482.9997-.9993.9997m-11.046 0c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.5511 0 .9993.4482.9993.9993 0 .5511-.4482.9997-.9993.9997m11.4045-6.02l1.9973-3.4592a.416.416 0 00-.1521-.5676.416.416 0 00-.5676.1521l-2.0223 3.503C15.5902 8.2439 13.8533 7.8508 12 7.8508s-3.5902.3931-5.1367 1.0989L4.841 5.4467a.4161.4161 0 00-.5677-.1521.4157.4157 0 00-.1521.5676l1.9973 3.4592C2.6889 11.1867.3432 14.6589 0 18.761h24c-.3435-4.1021-2.6892-7.5743-6.1185-9.4396" />
                                        </svg>
                                    </template>
                                </div>

                                {{-- Device Info & Buttons --}}
                                <div class="flex-1 min-w-0">
                                    <div class="text-[16px] font-bold text-[#1d2d35] leading-snug" x-text="dev.name"></div>
                                    <div class="text-[11.5px] text-[#5c5955] leading-snug mt-0.5">
                                        Registered <span x-text="dev.registered"></span> via
                                        <a href="https://basecamp.com/mobile" target="_blank" rel="noopener"
                                            class="underline text-[#1d2d35] hover:text-[#1b6ac9]" x-text="dev.app"></a>
                                    </div>
                                    <div class="text-[11.5px] text-[#5c5955] leading-snug">
                                        Device ID: <span x-text="dev.id"></span>
                                    </div>

                                    <div class="mt-2.5 flex items-center gap-2">
                                        <button type="button" @click="testedId = dev.id"
                                            class="px-3 py-1 rounded-[5px] bg-[#60a5fa] hover:bg-[#3b82f6] text-[#0f172a] text-[12px] font-semibold transition-colors cursor-pointer">
                                            <span x-text="testedId === dev.id ? 'Test sent!' : 'Test this device'"></span>
                                        </button>
                                        <button type="button" @click="devices.splice(dIdx, 1)"
                                            class="px-3 py-1 rounded-[5px] border border-[#d4d0c8] bg-white hover:bg-[#f7f5f2] text-[#5c5955] hover:text-[#1d2d35] text-[12px] font-medium transition-colors cursor-pointer">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Collapsible "Having trouble?" Accordion --}}
                    <div class="mt-4 pt-2 border-t border-[#e5e2dc]">
                        <button type="button" @click="troubleOpen = !troubleOpen"
                            class="w-full px-4 py-2.5 rounded-[7px] bg-[#f2f0ec] hover:bg-[#e8e5df] flex items-center justify-between text-[13.5px] font-bold text-[#1d2d35] transition-colors cursor-pointer">
                            <span>Having trouble?</span>
                            <svg class="w-4 h-4 text-[#5c5955] transition-transform"
                                :class="troubleOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="troubleOpen" x-cloak
                            class="pt-3.5 px-1 text-[12.5px] text-[#5c5955] leading-relaxed space-y-3">
                            <p>If you aren&rsquo;t getting notifications on your device, try sending a test notification
                                with the button above.</p>

                            <div>
                                <div class="font-bold text-[#1d2d35] mb-1">If you don&rsquo;t receive the test
                                    notification...</div>
                                <ul class="list-disc pl-5 space-y-1.5">
                                    <li>
                                        Verify that you&rsquo;ve enabled notifications for Basecamp (here are instructions
                                        for
                                        <a href="https://basecamp.com/help" target="_blank" rel="noopener"
                                            class="underline text-[#1d2d35]">iOS</a> and
                                        <a href="https://basecamp.com/help" target="_blank" rel="noopener"
                                            class="underline text-[#1d2d35]">Android</a>).
                                    </li>
                                    <li>Already enabled notifications? Try disabling then re-enabling them in the Basecamp
                                        app, then sending another test.</li>
                                    <li>Still not receiving test notifications? Remove the device from this screen, then
                                        re-enable notifications in the Basecamp app and try again.</li>
                                    <li>
                                        If all else fails, <a href="{{ route('my.support') }}"
                                            class="underline text-[#1d2d35]">contact our support team</a> and we&rsquo;ll
                                        get things straightened out.
                                    </li>
                                </ul>
                            </div>

                            <div>
                                <div class="font-bold text-[#1d2d35]">Device not listed here?</div>
                                <p>
                                    First install the <a href="https://basecamp.com/mobile" target="_blank" rel="noopener"
                                        class="underline text-[#1d2d35]">Android</a> or
                                    <a href="https://basecamp.com/mobile" target="_blank" rel="noopener"
                                        class="underline text-[#1d2d35]">iOS</a> app, then
                                    <a href="{{ route('my.notifications.settings') }}"
                                        class="underline text-[#1d2d35]">enable notifications</a>.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Connected apps -->
                <div x-show="activeSubModal === 'apps'">
                    <h3 class="text-[20px] font-extrabold text-[#1d2d35] mb-2">Connected apps</h3>
                    <p class="text-[14px] text-[#6d6a65] leading-relaxed">
                        Manage third-party integrations and OAuth applications connected to your Basecamp account.
                    </p>
                    <div class="mt-4">
                        <button type="button" @click="activeSubModal = null"
                            class="px-4 py-1.5 rounded-[6px] bg-[#1b6ac9] text-white text-[13.5px] font-semibold cursor-pointer">Done</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection