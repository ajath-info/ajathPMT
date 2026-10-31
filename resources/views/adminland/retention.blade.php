@extends('layouts.app')

@section('title', 'Change Pings & Chat History Settings')

@section('content')
<div class="max-w-[780px] mx-auto px-4 sm:px-6 pb-20 pt-2" x-data="{
    pingsRetention: 'Forever',
    chatsRetention: 'Forever',
    options: ['Forever', '30 days', '90 days', '1 year', '2 years'],
    savedBanner: false
}">
    <div class="bc-sheet bg-white overflow-hidden min-h-[640px] text-[#1d2d35]">

        {{-- Top Breadcrumb Header Bar --}}
        <div class="px-6 py-3 border-b border-[#e5e2dc] bg-[#f7f5f2] flex items-center gap-1.5 text-[13px] font-medium text-[#5c5955]">
            <a href="{{ route('adminland.index') }}" class="hover:text-[#1d2d35] hover:underline">Adminland</a>
        </div>

        {{-- Sheet Body --}}
        <div class="px-8 sm:px-14 pt-8 pb-16">
            <h1 class="text-[28px] sm:text-[32px] font-black text-[#1d2d35] tracking-tight leading-tight mb-2">Change Pings &amp; Chat History Settings</h1>
            <p class="text-[15px] text-[#1d2d35] leading-relaxed mb-8">
                By default, Basecamp keeps browseable, searchable transcripts of all Pings and Chats for as long as you keep the account. Here you can change that setting so that Basecamp automatically and permanently deletes chats (including any attachments) after a certain amount of time.
            </p>

            {{-- Centered Section Divider Heading --}}
            <div class="flex items-center gap-4 mb-6">
                <div class="h-px bg-[#e5e2dc] flex-1"></div>
                <h2 class="text-[15px] font-bold text-[#1d2d35] shrink-0">How long should Basecamp keep chat history?</h2>
                <div class="h-px bg-[#e5e2dc] flex-1"></div>
            </div>

            {{-- Two Columns: ...in Pings? / ...in Chats? --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-7">
                {{-- ...in Pings? --}}
                <div class="rounded-[8px] border border-[#e5e2dc] bg-[#f7f5f2] px-6 py-6 text-center flex flex-col">
                    <h3 class="text-[16px] font-bold text-[#1d2d35] mb-2">...in Pings?</h3>
                    <p class="text-[13px] text-[#5c5955] leading-relaxed mb-5">
                        Pings are private conversations between individuals. Only the participants can see the history.
                    </p>

                    <div class="space-y-2.5 mt-auto">
                        <template x-for="opt in options" :key="'pings-' + opt">
                            <button type="button" @click="pingsRetention = opt"
                                class="w-full py-2 px-4 rounded-[6px] text-[13px] transition-colors border cursor-pointer"
                                :class="pingsRetention === opt
                                    ? 'bg-[#1d1c1b] text-white font-bold border-[#1d1c1b] shadow-xs'
                                    : 'bg-white text-[#1d2d35] font-medium border-[#d4d0c8] hover:bg-[#f2efe9]'">
                                <span x-text="opt"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- ...in Chats? --}}
                <div class="rounded-[8px] border border-[#e5e2dc] bg-[#f7f5f2] px-6 py-6 text-center flex flex-col">
                    <h3 class="text-[16px] font-bold text-[#1d2d35] mb-2">...in Chats?</h3>
                    <p class="text-[13px] text-[#5c5955] leading-relaxed mb-5">
                        Chats are real-time group conversations inside projects. Everyone in the project can see the history.
                    </p>

                    <div class="space-y-2.5 mt-auto">
                        <template x-for="opt in options" :key="'chats-' + opt">
                            <button type="button" @click="chatsRetention = opt"
                                class="w-full py-2 px-4 rounded-[6px] text-[13px] transition-colors border cursor-pointer"
                                :class="chatsRetention === opt
                                    ? 'bg-[#1d1c1b] text-white font-bold border-[#1d1c1b] shadow-xs'
                                    : 'bg-white text-[#1d2d35] font-medium border-[#d4d0c8] hover:bg-[#f2efe9]'">
                                <span x-text="opt"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Caution Text --}}
            <div class="mb-6 text-[13.5px] leading-relaxed text-[#1d2d35]">
                <span class="bg-[#fef08a]/85 px-1 py-0.5 rounded-xs">
                    <strong class="font-bold">Use caution.</strong> If you change these settings, any existing chats older than what you select above will be permanently and immediately deleted across the account. That can&rsquo;t be undone.
                </span>
            </div>

            {{-- Confirmation Notice when clicked --}}
            <div x-show="savedBanner" x-cloak class="mb-5 px-4 py-3 rounded-[6px] border border-[#bfe3c8] bg-[#eef8f1] text-[13px] font-medium text-[#1f883d]">
                Retention settings updated: Pings (<span class="font-bold" x-text="pingsRetention"></span>), Chats (<span class="font-bold" x-text="chatsRetention"></span>).
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2.5">
                <button type="button" @click="savedBanner = true"
                    class="px-4 py-2 rounded-[6px] bg-[#60a5fa] hover:bg-[#3b82f6] text-[#0f172a] text-[13px] font-semibold transition-colors shadow-xs cursor-pointer">
                    Review changes...
                </button>
                <a href="{{ route('adminland.index') }}"
                    class="px-4 py-2 rounded-[6px] border border-[#d4d0c8] bg-white hover:bg-[#f7f5f2] text-[13px] font-medium text-[#1d2d35] transition-colors">
                    Never mind
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

