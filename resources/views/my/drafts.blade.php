@extends('layouts.app')

@section('title', 'Your Drafts')

@section('content')
<div class="max-w-[780px] mx-auto px-4 sm:px-6 pb-20 pt-2" x-data="{
    selectedDraft: null,
    postedNotice: false,
    drafts: [
        { id: 5245770226, filename: 'EFB10105-13F6-4F33-9D93-F969774B0EDB.jpeg', project: 'Ajath Infotech Pvt Ltd HQ', updated: 'Aug 19, 2022', size: '4.91 MB', image: 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1080&q=80' },
        { id: 5245769812, filename: '7B63E802-AC1C-4992-BE39-1A06A82BBA8D.jpeg', project: 'Ajath Infotech Pvt Ltd HQ', updated: 'Aug 19, 2022', size: '3.84 MB', image: 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1080&q=80' },
        { id: 5245768338, filename: '4B607FA8-0222-4D54-A7C4-F78D487BA593.jpeg', project: 'Ajath Infotech Pvt Ltd HQ', updated: 'Aug 19, 2022', size: '4.12 MB', image: 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1080&q=80' },
        { id: 5245767904, filename: '0FDEFE76-4515-49C9-87B3-4F98F5423301.jpeg', project: 'Ajath Infotech Pvt Ltd HQ', updated: 'Aug 19, 2022', size: '3.65 MB', image: 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1080&q=80' },
        { id: 5245767119, filename: 'A7000580-6B8B-4E68-8E04-ACA3F8A1ECDD.jpeg', project: 'Ajath Infotech Pvt Ltd HQ', updated: 'Aug 19, 2022', size: '4.48 MB', image: 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1080&q=80' },
        { id: 5245766401, filename: '63CC1F81-C630-41EC-B65A-31106DED9C1C.jpeg', project: 'Ajath Infotech Pvt Ltd HQ', updated: 'Aug 19, 2022', size: '3.92 MB', image: 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1080&q=80' }
    ],
    removeDraft(index) {
        this.drafts.splice(index, 1);
    }
}">
    <div class="bc-sheet overflow-hidden min-h-[640px]">

        {{-- =============================================================== --}}
        {{-- VIEW 1: YOUR DRAFTS LIST (/my/drafts)                           --}}
        {{-- =============================================================== --}}
        <div x-show="!selectedDraft" class="px-8 sm:px-14 pt-9 pb-16">
            <h1 class="text-[28px] sm:text-[32px] font-black text-[#1d1c1b] tracking-tight leading-tight mb-6">Your Drafts</h1>

            <div class="divide-y divide-[#e5e2dc]">
                <template x-for="(draft, index) in drafts" :key="draft.id">
                    <div class="py-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5 min-w-0">
                            {{-- Basecamp DRAFT Document Page Icon --}}
                            <button type="button" @click="selectedDraft = draft; postedNotice = false"
                                class="w-9 h-11 rounded-[4px] bg-white border border-[#d4d0c8] shadow-2xs flex flex-col items-center justify-center shrink-0 relative overflow-hidden cursor-pointer hover:border-[#7c7872] transition-colors">
                                <span class="w-4 h-1.5 rounded-t-full border border-[#9ca3af] mb-0.5"></span>
                                <span class="px-1 py-0.5 bg-[#e5e7eb] text-[#4b5563] text-[6.5px] font-black tracking-wider uppercase leading-none rounded-2xs">DRAFT</span>
                                <span class="w-5 h-px bg-[#d1d5db] mt-1"></span>
                                <span class="w-4 h-px bg-[#d1d5db] mt-0.5"></span>
                            </button>

                            <div class="min-w-0">
                                <button type="button" @click="selectedDraft = draft; postedNotice = false"
                                    class="text-[15px] font-bold text-[#1d1c1b] hover:underline truncate block text-left cursor-pointer"
                                    x-text="draft.filename"></button>
                                <div class="text-[13px] text-[#5c5955] mt-0.5">
                                    File in <span x-text="draft.project"></span> &bull; Updated on <span x-text="draft.updated"></span> &bull;
                                </div>
                            </div>
                        </div>

                        {{-- Right Trash Icon --}}
                        <button type="button" @click="removeDraft(index)" title="Delete draft"
                            class="p-1.5 text-[#7c7872] hover:text-[#dc2626] transition-colors shrink-0 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </template>

                <div x-show="drafts.length === 0" x-cloak class="py-12 text-center text-[14px] text-[#7c7872]">
                    You don&rsquo;t have any saved drafts right now.
                </div>
            </div>
        </div>

        {{-- =============================================================== --}}
        {{-- VIEW 2: UNPUBLISHED DRAFT DETAIL VIEW (media_1793278574312)     --}}
        {{-- =============================================================== --}}
        <div x-show="selectedDraft" x-cloak>
            {{-- Top Breadcrumb Header Bar --}}
            <div class="px-6 py-3 border-b border-[#e5e2dc] bg-[#f7f5f2]/60 flex items-center justify-between gap-2 text-[13px]">
                <div class="flex items-center flex-wrap gap-1.5 text-[#5c5955]">
                    <a href="{{ route('projects.show', 4) }}" class="font-bold text-[#1d1c1b] hover:underline">Ajath Infotech Pvt Ltd HQ</a>
                    <span>&lsaquo;</span>
                    <a href="{{ route('projects.docs', 4) }}" class="hover:text-[#1d1c1b] hover:underline">Docs &amp; Files</a>
                    <span>&lsaquo;</span>
                    <button type="button" @click="selectedDraft = null" class="hover:text-[#1d1c1b] hover:underline cursor-pointer">
                        Janmaashtami 2022 team outing
                    </button>
                </div>

                <button type="button" @click="selectedDraft = null"
                    class="w-7 h-7 rounded-full border border-[#d4d0c8] flex items-center justify-center text-[#5c5955] hover:text-[#1d1c1b] hover:bg-[#e5e2dc]/40 transition-colors cursor-pointer"
                    title="Back to Your Drafts">
                    &bull;&bull;&bull;
                </button>
            </div>

            {{-- Sheet Body --}}
            <div class="px-8 sm:px-14 pt-7 pb-16">
                {{-- Top Unpublished Draft Banner Box --}}
                <div class="rounded-[8px] border border-[#e5e2dc] bg-[#f7f5f2]/90 px-6 py-5 text-center mb-8">
                    <p class="text-[13.5px] text-[#1d1c1b]">
                        <strong class="font-bold">This unpublished draft</strong> was edited on Aug 19, 2022.
                        <a href="javascript:void(0)" @click="navigator.clipboard && navigator.clipboard.writeText(window.location.href)" class="underline text-[#1d64c8] hover:opacity-80">Share a link</a> or...
                    </p>

                    <div class="mt-3.5 flex items-center justify-center gap-2.5">
                        <a href="{{ route('projects.docs', 4) }}"
                            class="px-4 py-1.5 rounded-[6px] bg-[#60a5fa] hover:bg-[#3b82f6] text-[#0f172a] text-[13px] font-semibold transition-colors shadow-xs">
                            Continue editing
                        </a>
                        <button type="button" @click="postedNotice = true"
                            class="px-4 py-1.5 rounded-[6px] border border-[#d4d0c8] bg-white hover:bg-[#f2efe9] text-[#1d64c8] text-[13px] font-semibold transition-colors cursor-pointer">
                            Post this file now...
                        </button>
                    </div>

                    <div x-show="postedNotice" x-cloak class="mt-3 text-[12.5px] font-semibold text-[#1f883d]">
                        File published to Ajath Infotech Pvt Ltd HQ &rsaquo; Docs &amp; Files!
                    </div>
                </div>

                {{-- File Title & Metadata --}}
                <h1 class="text-[26px] sm:text-[30px] font-black text-[#1d1c1b] tracking-tight leading-tight break-all"
                    x-text="selectedDraft ? selectedDraft.filename : ''"></h1>
                <p class="text-[14px] text-[#5c5955] mt-1">
                    August 19, 2022 &bull; <span x-text="selectedDraft ? selectedDraft.size : '4.91 MB'"></span>
                </p>

                {{-- Large Image Preview --}}
                <div class="mt-5 max-w-[540px] rounded-[4px] overflow-hidden border border-[#e5e2dc] shadow-xs bg-[#1d1c1b]">
                    <img :src="selectedDraft ? selectedDraft.image : ''"
                         :alt="selectedDraft ? selectedDraft.filename : ''"
                         class="w-full h-auto object-cover">
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
