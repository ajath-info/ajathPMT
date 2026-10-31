@extends('layouts.app')

@section('title', "Basecamp's everywhere - Workspace")

@section('content')
<div class="max-w-[980px] mx-auto px-4 pt-4 pb-20 font-sans text-[#1d2d35]"
     x-data="{ notice: null }">

    <!-- Top Centered Basecamp Mountain Emblem (Matches media_1793292556979_1c7c3ef1.jpg) -->
    <div class="flex justify-center mb-5">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center hover:opacity-90 transition-opacity" title="Back to Home">
            <svg class="w-12 h-10 drop-shadow-sm" viewBox="0 0 64 54" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M32 3C16.5 3 4 26.5 4 41.5C4 47.5 8.8 51 15.5 51H48.5C55.2 51 60 47.5 60 41.5C60 26.5 47.5 3 32 3Z" fill="#2ea44f" stroke="#ffffff" stroke-width="3.5" stroke-linejoin="round"/>
                <path d="M10 35.5C15.5 35.5 19.5 26 24.5 26C28.5 26 31.5 33 36.5 33C41.5 33 45.5 22.5 53.5 34.5" stroke="#ffffff" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    </div>

    <!-- Toast banner when user clicks an app download button on workspace.ajath.com -->
    <div x-show="notice" x-cloak x-transition
         class="mb-4 max-w-[640px] mx-auto px-4 py-2.5 rounded-[8px] border border-[#5cc073] bg-[#e8f5e9] text-[#1d2d35] text-[13.5px] font-semibold flex items-center justify-between">
        <span x-text="notice"></span>
        <button type="button" @click="notice = null" class="text-xs font-bold ml-3 cursor-pointer">✕</button>
    </div>

    <!-- Main Sheet Card (Matches media_1793292556979_1c7c3ef1.jpg) -->
    <div class="bg-white rounded-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.05)] px-6 sm:px-16 py-12 text-center">

        <!-- Hero Title -->
        <h1 class="text-[34px] sm:text-[48px] font-black text-[#1d2d35] tracking-[-0.03em] leading-[1.1] mb-4">
            Basecamp&rsquo;s everywhere
        </h1>

        <!-- Hero Subtitle -->
        <p class="max-w-[680px] mx-auto text-[17px] sm:text-[20px] text-[#1d2d35] leading-[1.45]">
            <strong class="font-extrabold">Basecamp is ready whenever and wherever you are</strong> &mdash; iOS, Android, Mac, Windows,
            <a href="{{ route('dashboard') }}" class="text-[#3b82f6] underline font-bold hover:opacity-85">on the web</a>,
            and on the command line.
        </p>

        <!-- Wavy Divider with Small Mountain Icon -->
        <div class="my-10 flex items-center justify-center gap-3 text-[#6d6a65]/60 select-none">
            <svg class="w-40 h-3" viewBox="0 0 160 12" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M0 6 Q 5 1, 10 6 T 20 6 T 30 6 T 40 6 T 50 6 T 60 6 T 70 6 T 80 6 T 90 6 T 100 6 T 110 6 T 120 6 T 130 6 T 140 6 T 150 6 T 160 6"/>
            </svg>
            <svg class="w-5 h-4 text-[#6d6a65]" viewBox="0 0 24 20" fill="none" stroke="currentColor" stroke-width="1.6">
                <path d="M12 2C6 2 2 11 2 16C2 18 4 19 7 19H17C20 19 22 18 22 16C22 11 18 2 12 2Z"/>
                <path d="M4 13C6.5 13 8 9.5 10 9.5C11.5 9.5 13 12 15 12C17 12 18.5 8.5 20.5 12.5"/>
            </svg>
            <svg class="w-40 h-3" viewBox="0 0 160 12" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M0 6 Q 5 1, 10 6 T 20 6 T 30 6 T 40 6 T 50 6 T 60 6 T 70 6 T 80 6 T 90 6 T 100 6 T 110 6 T 120 6 T 130 6 T 140 6 T 150 6 T 160 6"/>
            </svg>
        </div>

        <!-- Section 1: iOS and Android mobile apps -->
        <div id="mobile" class="max-w-[660px] mx-auto">
            <h2 class="text-[24px] sm:text-[28px] font-black text-[#1d2d35] tracking-[-0.02em] mb-3">
                iOS and Android mobile apps
            </h2>
            <p class="text-[16px] sm:text-[18px] text-[#1d2d35] leading-[1.5] mb-6">
                Comment, catch-up, chat, or even put those notifications on snooze &mdash; you can do it all with Basecamp apps for
                <a href="#ios" @click.prevent="notice = 'Basecamp for iPhone is ready for your workspace.ajath.com account.'" class="text-[#3b82f6] underline font-bold hover:opacity-85">iPhone</a>,
                <a href="#ipad" @click.prevent="notice = 'Basecamp for iPad is ready for your workspace.ajath.com account.'" class="text-[#3b82f6] underline font-bold hover:opacity-85">iPad</a>, or
                <a href="#android" @click.prevent="notice = 'Basecamp for Android is ready for your workspace.ajath.com account.'" class="text-[#3b82f6] underline font-bold hover:opacity-85">Android</a>.
            </p>

            <!-- App Store & Google Play Store Buttons -->
            <div class="flex flex-wrap items-center justify-center gap-3.5">
                <button type="button"
                        @click="notice = 'Basecamp for iOS (iPhone & iPad) — sign in with your workspace.ajath.com account.'"
                        class="inline-flex items-center gap-2.5 px-4 py-2 rounded-[7px] bg-[#222f38] hover:bg-[#2c3c47] text-white border border-[#374151] transition-colors cursor-pointer">
                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.96 6.33c.67-.81 1.12-1.94 1-3.08-.96.04-2.13.64-2.82 1.45-.61.7-.11 1.86-.99 2.97 1.08.08 2.14-.53 2.81-1.34z"/>
                    </svg>
                    <div class="text-left leading-tight">
                        <div class="text-[9px] font-medium opacity-85">Download on the</div>
                        <div class="text-[15px] font-bold tracking-tight">App Store</div>
                    </div>
                </button>

                <button type="button"
                        @click="notice = 'Basecamp for Android — sign in with your workspace.ajath.com account.'"
                        class="inline-flex items-center gap-2.5 px-4 py-2 rounded-[7px] bg-[#222f38] hover:bg-[#2c3c47] text-white border border-[#374151] transition-colors cursor-pointer">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none">
                        <path d="M3 20.5V3.5C3 2.91 3.34 2.39 3.84 2.15L13.69 12L3.84 21.85C3.34 21.6 3 21.09 3 20.5Z" fill="#38BDF8"/>
                        <path d="M16.81 15.12L6.05 21.34L14.54 12.85L16.81 15.12Z" fill="#F43F5E"/>
                        <path d="M20.16 10.81C20.5 11.08 20.75 11.5 20.75 12C20.75 12.5 20.53 12.9 20.18 13.18L17.89 14.5L15.39 12L17.89 9.5L20.16 10.81Z" fill="#FACC15"/>
                        <path d="M6.05 2.66L16.81 8.88L14.54 11.15L6.05 2.66Z" fill="#4ADE80"/>
                    </svg>
                    <div class="text-left leading-tight">
                        <div class="text-[9px] font-medium uppercase opacity-85">GET IT ON</div>
                        <div class="text-[15px] font-bold tracking-tight">Google Play</div>
                    </div>
                </button>
            </div>
        </div>

        <!-- Section 2: Mac and Windows desktop apps -->
        <div id="desktop" class="max-w-[660px] mx-auto mt-14 pt-8 border-t border-[#ece9e2]">
            <h2 class="text-[24px] sm:text-[28px] font-black text-[#1d2d35] tracking-[-0.02em] mb-3">
                Mac and Windows desktop apps
            </h2>
            <p class="text-[16px] sm:text-[18px] text-[#1d2d35] leading-[1.5] mb-6">
                All the power of Basecamp in a dedicated desktop app for your
                <a href="#mac" @click.prevent="notice = 'Basecamp for macOS (Apple Silicon & Intel) is ready for workspace.ajath.com.'" class="text-[#3b82f6] underline font-bold hover:opacity-85">Mac</a>
                (Intel or Apple Silicon) or
                <a href="#windows" @click.prevent="notice = 'Basecamp for Windows (64-bit) is ready for workspace.ajath.com.'" class="text-[#3b82f6] underline font-bold hover:opacity-85">Windows PC</a>.
            </p>

            <div class="flex flex-wrap items-center justify-center gap-3.5">
                <button type="button"
                        @click="notice = 'Basecamp Desktop for macOS — connected to workspace.ajath.com.'"
                        class="px-5 py-2.5 rounded-full bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-bold text-[14px] transition-colors cursor-pointer">
                    Download for Mac
                </button>
                <button type="button"
                        @click="notice = 'Basecamp Desktop for Windows — connected to workspace.ajath.com.'"
                        class="px-5 py-2.5 rounded-full bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-bold text-[14px] transition-colors cursor-pointer">
                    Download for Windows
                </button>
            </div>
        </div>

        <div class="mt-10 pt-6 border-t border-[#ece9e2]">
            <a href="{{ route('my.settings') }}" class="text-[14px] font-bold text-[#3b82f6] underline hover:opacity-85">
                &larr; Back to Account &amp; Settings
            </a>
        </div>
    </div>
</div>
@endsection
