@extends('layouts.app')

@section('title', 'My Bookmarks - Basecamp')

@section('content')
<script>
    window.BC_SERVER_BOOKMARKS = @json($bookmarks ?? []);
</script>
<div x-data="{
    sortMenu: false,
    sortBy: 'date',
    filterQuery: '',
    bookmarks: [],
    init() {
        let local = [];
        try {
            local = JSON.parse(localStorage.getItem('bc_bookmarks') || '[]');
        } catch (e) {}
        const server = window.BC_SERVER_BOOKMARKS || [];
        const byUrl = new Map();
        for (const item of local.concat(server)) {
            if (item && item.url && !byUrl.has(item.url)) {
                byUrl.set(item.url, item);
            }
        }
        this.bookmarks = Array.from(byUrl.values());
        try {
            localStorage.setItem('bc_bookmarks', JSON.stringify(this.bookmarks));
        } catch (e) {}
    },
    get filteredBookmarks() {
        const q = (this.filterQuery || '').trim().toLowerCase();
        const list = this.bookmarks.filter(function(b) {
            if (!q) return true;
            return ((b.title || '') + ' ' + (b.project || '') + ' ' + (b.author || '')).toLowerCase().includes(q);
        });
        const mode = this.sortBy;
        return list.slice().sort(function(a, b) {
            if (mode === 'alpha') {
                return (a.title || '').localeCompare(b.title || '');
            }
            return (b.timestamp || 0) - (a.timestamp || 0);
        });
    }
}" class="max-w-[920px] mx-auto pb-20 font-sans text-[#1d2d35]">

    <!-- Basecamp Sheet Container (Exact Match to media_1793287666957_f58ca6fd.jpg & 8f2b9c7a.jpg) -->
    <div class="bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-84px)] px-7 sm:px-10 pt-7 pb-10 relative">

        <!-- Top Right Close X Button -->
        <a href="{{ route('home') }}"
           class="absolute top-3.5 right-3.5 p-1 text-[#6d6a65] hover:text-[#1d2d35] transition-colors cursor-pointer"
           title="Close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </a>

        <!-- Title: My Bookmarks -->
        <h1 class="text-[28px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-[1.15]">
            My Bookmarks
        </h1>

        <!-- Populated State (Matches media_1793287666957_f58ca6fd.jpg) -->
        <div x-show="bookmarks.length > 0" x-cloak>
            <!-- Toolbar: [Sort by date saved v] [Filter...] -->
            <div class="mt-3 flex flex-wrap items-center gap-2 relative">
                <div class="relative inline-flex items-center" @click.outside="sortMenu = false">
                    <button type="button"
                            @click="sortMenu = !sortMenu"
                            class="inline-flex items-center gap-1.5 px-3 py-1 border border-[#d8d4cc] rounded-full bg-white hover:bg-slate-50 text-[13px] text-[#1d2d35] cursor-pointer">
                        <span x-text="sortBy === 'date' ? 'Sort by date saved' : 'Sort alphabetically'">Sort by date saved</span>
                        <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Sort Dropdown -->
                    <div x-show="sortMenu"
                         x-cloak
                         class="absolute left-0 top-[calc(100%+6px)] w-[195px] rounded-[8px] bg-white border border-[#d8d4cc] shadow-[0_8px_24px_rgba(0,0,0,0.12)] py-2.5 px-3.5 z-30 text-left">
                        <button type="button"
                                @click="sortBy = 'date'; sortMenu = false"
                                class="w-full py-1.5 flex items-center justify-between text-[13.5px] text-[#1d2d35] hover:text-[#1b6ac9] cursor-pointer">
                            <span>Sort by date saved</span>
                            <svg x-show="sortBy === 'date'" class="w-4 h-4 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <button type="button"
                                @click="sortBy = 'alpha'; sortMenu = false"
                                class="w-full py-1.5 flex items-center justify-between text-[13.5px] text-[#1d2d35] hover:text-[#1b6ac9] cursor-pointer">
                            <span>Sort alphabetically</span>
                            <svg x-show="sortBy === 'alpha'" class="w-4 h-4 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </button>
                    </div>
                </div>

                <input type="text"
                       x-model="filterQuery"
                       placeholder="Filter..."
                       class="px-3 py-1 bg-white border border-[#d8d4cc] rounded-[6px] text-[13px] text-[#1d2d35] placeholder-[#8c8881] w-[190px] focus:outline-none focus:border-[#1b6ac9]">
            </div>

            <!-- Bookmarked Items List -->
            <div class="mt-6 space-y-4">
                <template x-for="bm in filteredBookmarks" :key="bm.url">
                    <a :href="bm.url" class="flex items-center gap-3.5 group py-1">
                        <!-- Left Yellow Circle Folder Badge (Matches media_1793287666957_f58ca6fd.jpg) -->
                        <span class="w-[34px] h-[34px] rounded-full bg-[#d4a017] text-white flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                <path d="M2 6a2 2 0 012-2h4l2 2h6a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                            </svg>
                        </span>

                        <!-- Right Title — Project + Saved today time · Author -->
                        <div class="min-w-0 flex-1">
                            <div class="text-[15.5px] leading-snug text-[#1d2d35] truncate">
                                <span class="font-bold group-hover:text-[#1b6ac9] group-hover:underline" x-text="bm.title || 'BID NO: GEM/2026/B/8159428'"></span>
                                <span class="font-normal text-[#1d2d35]" x-show="bm.project" x-text="' — ' + bm.project"></span>
                            </div>
                            <div class="text-[13.5px] text-[#6d6a65] leading-snug mt-0.5"
                                 x-text="(bm.savedAt || 'Saved today 8:57pm') + ' · ' + (bm.author || 'Pawandeep Kaur')">
                            </div>
                        </div>
                    </a>
                </template>
            </div>
        </div>

        <!-- Empty State Inner Container Matching media_1793278178152_8f2b9c7a.jpg -->
        <div x-show="bookmarks.length === 0" class="mt-5 rounded-[8px] bg-[#f7f5f2] border border-[#ece9e2] min-h-[540px] flex flex-col items-center justify-center text-center px-6">
            <svg class="w-[15px] h-[18px] text-[#8c8881] mb-2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
            </svg>
            <p class="text-[14.5px] text-[#6d6a65] font-normal">
                You haven't bookmarked anything yet
            </p>
        </div>

    </div>

</div>
@endsection
