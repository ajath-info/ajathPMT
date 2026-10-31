@extends('layouts.app')

@section('title', 'Campfire - ' . $project->name)

@section('content')
@php
    $badgePalette = [
        'GK' => '#d946ef',
        'PK' => '#0d9488',
        'AI' => '#10b981',
        'RK' => '#db2777',
        'SN' => '#9333ea',
        'MS' => '#0284c7',
    ];
    $fallbackColors = ['#d946ef', '#0d9488', '#10b981', '#db2777', '#9333ea', '#0284c7', '#ea580c'];
    $prevSenderId = null;
@endphp

<!-- Exact Basecamp Campfire Layout Matching media_1793273396851_5706aab7.jpg .. media_1793273801100_edc74e48.jpg -->
<div class="w-full sm:w-[72vw] max-w-[1130px] mx-auto font-sans text-[#1d2d35] relative"
     x-data="{
         activeView: new URLSearchParams(window.location.search).get('view') || 'chat',
         lightboxSrc: null,
         lightboxDownloadUrl: null,
         lightboxOpen: false,
         lightboxClosing: false,
         lightboxName: '',
         lightboxSize: '352 KB',
         openLightbox(src, name, size, downloadUrl = null) {
             this.lightboxSrc = src;
             this.lightboxDownloadUrl = downloadUrl || src;
             this.lightboxName = name;
             this.lightboxSize = size || '352 KB';
             this.lightboxClosing = false;
             this.lightboxOpen = true;
         },
         closeLightbox() {
             if ((!this.lightboxOpen && !this.lightboxSrc) || this.lightboxClosing) return;
             this.lightboxClosing = true;
             setTimeout(() => {
                 this.lightboxOpen = false;
                 this.lightboxClosing = false;
                 this.lightboxSrc = null;
             }, 200);
         },
         notifying: {{ ($project->status === 'ARCHIVED' || $project->id == 5) ? 'true' : 'false' }},
         chatVisibility: 'team',
         draftVisibility: 'team',
         moreMenuOpen: false,
         bubbleUpOpen: false,
         copiedToast: false,
         notifiedModalOpen: false,
         moveModalOpen: false,
         addChatbotOpen: false,
         chatbotName: '',
         chatbotUrl: '',
         chatbots: [],
         copyCampfireLink() {
             if (navigator.clipboard) {
                 navigator.clipboard.writeText(window.location.href);
             }
             this.moreMenuOpen = false;
             this.bubbleUpOpen = false;
             this.copiedToast = true;
             setTimeout(() => { this.copiedToast = false; }, 2600);
         },
         switchView(view) {
             this.activeView = view;
             this.moreMenuOpen = false;
             this.bubbleUpOpen = false;
             if (view === 'chat') {
                 this.$nextTick(() => {
                     const c = document.getElementById('chat-messages');
                     if (c) c.scrollTop = c.scrollHeight;
                 });
             }
         }
     }">

    <!-- "✓ Copied" Toast Pill Matching media_1793273771430_b2f2e700.jpg -->
    <div x-show="copiedToast"
         x-cloak
         x-transition.opacity.duration.200ms
         class="absolute -top-3.5 left-1/2 -translate-x-1/2 z-50 bg-[#323b43] text-white rounded-full px-4 py-1.5 text-[14px] font-medium shadow-[0_4px_12px_rgba(0,0,0,0.22)] inline-flex items-center gap-2 select-none pointer-events-none">
        <svg class="w-4 h-4 text-[#2ea44f]" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        <span>Copied</span>
    </div>

    @if($project->status === 'ARCHIVED')
        <!-- Centered Yellow Archived Callout Box with Downward Pointer (Matches media_1793339157521_028ad416.png) -->
        <div class="w-[490px] max-w-full mx-auto mb-3.5 bg-[#fffdf7] border border-[#e7c767] rounded-[6px] px-5 py-3 text-center text-[13.5px] text-[#1d2d35] relative">
            <span>This project is <strong class="font-bold">archived</strong>. You can </span>
            <form action="{{ route('projects.unarchive', $project->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-[#1b6ac9] underline hover:text-[#124b8f] cursor-pointer">restore it</button>
            </form>
            <span> or </span>
            <form action="{{ route('projects.destroy', $project->id) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-[#1b6ac9] underline hover:text-[#124b8f] cursor-pointer">put it in the trash</button>
            </form><span>.</span>
            <span class="w-3 h-3 bg-[#fffdf7] border-r border-b border-[#e7c767] rotate-45 absolute -bottom-[7px] left-1/2 -translate-x-1/2"></span>
        </div>
    @endif

    <!-- Basecamp White Paper Sheet + Right Vertical Privacy Tab -->
    <div class="relative">
        @if($project->id != 4 && $project->id != 5)
        <!-- Right Vertical Blue Privacy Tab (Matches media_1793339157521_028ad416.png & media_1793346486764_dd35dd2f.jpg) -->
        <button type="button"
                @click="draftVisibility = chatVisibility; switchView('privacy')"
                class="hidden lg:inline-flex items-center gap-1.5 bg-[#cfe6f7] hover:bg-[#bddcf4] text-[#1d2d35] text-[11.5px] font-bold py-3 px-1 rounded-r-[6px] absolute top-1.5 -right-[23px] z-10 select-none transition-colors cursor-pointer"
                style="writing-mode: vertical-rl;">
            <svg x-show="chatVisibility === 'team'" class="w-3 h-3 fill-current rotate-90 flex-shrink-0" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
            </svg>
            <svg x-show="chatVisibility === 'client'" x-cloak class="w-3 h-3 fill-current rotate-90 flex-shrink-0" viewBox="0 0 20 20">
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
            </svg>
            <span x-text="chatVisibility === 'team' ? 'This chat is private to our team.' : 'The client can see this chat.'">This chat is private to our team.</span>
            <span class="underline font-bold">Change</span>
        </button>
        @endif

        <div class="bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] flex flex-col {{ $project->status === 'ARCHIVED' ? 'h-[calc(100vh-148px)]' : 'h-[calc(100vh-92px)]' }} overflow-hidden relative">

        <!-- Top Sub-Bar: Breadcrumb on Left, Bookmark + Notify me when people chat + ••• on Right -->
        <div class="px-5 py-2 border-b border-[#ece9e2] flex items-center justify-between text-[13px] flex-shrink-0 bg-white relative z-20">
            <div class="flex items-center gap-1.5">
                <a href="{{ route('projects.show', $project->id) }}" class="font-bold text-[#1d2d35] hover:underline transition-colors">
                    {{ $project->name }}
                </a>
                <span class="text-[#8c8881] text-[12px]">‹</span>
                <button type="button"
                        @click="switchView('chat')"
                        :class="activeView !== 'chat' ? 'hover:underline cursor-pointer' : ''"
                        class="text-[#1d2d35]">
                    Campfire
                </button>
            </div>

            <div class="flex items-center gap-3.5 text-[#1d2d35] relative" x-show="activeView === 'chat'">
                <button type="button" onclick="window.toggleBookmark && window.toggleBookmark(this)" class="inline-flex items-center gap-1 text-[12.5px] hover:underline transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#4a4640]" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                    <span data-bookmark-label>Bookmark</span>
                </button>

                <button type="button" @click="notifying = !notifying" class="inline-flex items-center gap-1 text-[12.5px] hover:underline transition-colors cursor-pointer">
                    <svg x-show="notifying" x-cloak class="w-3.5 h-3.5 text-[#1d2d35] fill-current" viewBox="0 0 20 20">
                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                    </svg>
                    <span x-text="notifying ? 'Notifying' : 'Notify me when people chat'"></span>
                </button>

                <!-- ••• Dropdown Menu Matching media_1793272345370_2fbc4e58.jpg & media_1793273747513_d4c708ed.jpg -->
                <div class="relative" @click.outside="moreMenuOpen = false; bubbleUpOpen = false">
                    <button type="button"
                            @click="moreMenuOpen = !moreMenuOpen; if (!moreMenuOpen) bubbleUpOpen = false"
                            :class="moreMenuOpen ? 'border-[#1d2d35] bg-black/[0.03]' : 'border-transparent hover:bg-black/[0.05]'"
                            class="w-7 h-7 rounded-full border flex items-center justify-center text-[#1d2d35] transition-colors cursor-pointer"
                            title="More options">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                        </svg>
                    </button>

                    <div x-show="moreMenuOpen"
                         x-cloak
                         class="absolute right-0 mt-1.5 w-[216px] bg-white rounded-[8px] border border-[#e5e2dc] shadow-[0_4px_18px_rgba(0,0,0,0.10)] py-2 text-[13.5px] text-[#1d2d35] z-30">

                        <!-- Bubble up + Sub-Menu Matching media_1793273747513_d4c708ed.jpg -->
                        <div class="relative px-1.5">
                            <button type="button"
                                    @click="bubbleUpOpen = !bubbleUpOpen"
                                    :class="bubbleUpOpen ? 'bg-[#1b6ac9] text-white rounded-[6px]' : 'text-[#1d2d35] hover:bg-[#f6f4f0] rounded-[6px]'"
                                    class="w-full px-2.5 py-1.5 text-left flex items-center gap-2.5 cursor-pointer transition-colors">
                                <svg class="w-4 h-4 flex-shrink-0" :class="bubbleUpOpen ? 'text-white' : 'text-[#8c8881]'" viewBox="0 0 20 20" fill="currentColor">
                                    <circle cx="7" cy="9" r="4.2"/>
                                    <circle cx="13.5" cy="13" r="2.3"/>
                                    <circle cx="14" cy="6" r="1.4"/>
                                </svg>
                                <span>Bubble up</span>
                            </button>

                            <!-- Bubble up Schedule Sub-Menu -->
                            <div x-show="bubbleUpOpen"
                                 x-cloak
                                 class="absolute -left-5 top-[38px] w-[232px] bg-white rounded-[8px] border border-[#e5e2dc] shadow-[0_6px_20px_rgba(0,0,0,0.12)] py-2 px-3 text-[13.5px] text-[#1d2d35] z-40 space-y-1">
                                <button type="button" @click="bubbleUpOpen = false; moreMenuOpen = false" class="w-full py-1 px-1 rounded hover:bg-[#f6f4f0] flex items-center justify-between cursor-pointer">
                                    <span class="flex items-center gap-2.5">
                                        <svg class="w-3.5 h-3.5 text-[#6d6a65]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg>
                                        <span>Now</span>
                                    </span>
                                </button>
                                <button type="button" @click="bubbleUpOpen = false; moreMenuOpen = false" class="w-full py-1 px-1 rounded hover:bg-[#f6f4f0] flex items-center justify-between cursor-pointer">
                                    <span class="flex items-center gap-2.5">
                                        <svg class="w-3.5 h-3.5 text-[#6d6a65]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg>
                                        <span>Tomorrow</span>
                                    </span>
                                    <span class="text-[12px] text-[#6d6a65]">Fri, 8:00am</span>
                                </button>
                                <button type="button" @click="bubbleUpOpen = false; moreMenuOpen = false" class="w-full py-1 px-1 rounded hover:bg-[#f6f4f0] flex items-center justify-between cursor-pointer">
                                    <span class="flex items-center gap-2.5">
                                        <svg class="w-3.5 h-3.5 text-[#6d6a65]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg>
                                        <span>This weekend</span>
                                    </span>
                                    <span class="text-[12px] text-[#6d6a65]">Sat, 8:00am</span>
                                </button>
                                <button type="button" @click="bubbleUpOpen = false; moreMenuOpen = false" class="w-full py-1 px-1 rounded hover:bg-[#f6f4f0] flex items-center justify-between cursor-pointer">
                                    <span class="flex items-center gap-2.5">
                                        <svg class="w-3.5 h-3.5 text-[#6d6a65]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg>
                                        <span>Next week</span>
                                    </span>
                                    <span class="text-[12px] text-[#6d6a65]">Mon, 8:00am</span>
                                </button>
                                <button type="button" @click="bubbleUpOpen = false; moreMenuOpen = false" class="w-full py-1 px-1 rounded hover:bg-[#f6f4f0] flex items-center justify-between cursor-pointer">
                                    <span class="flex items-center gap-2.5">
                                        <svg class="w-3.5 h-3.5 text-[#6d6a65]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg>
                                        <span>Pick a date</span>
                                    </span>
                                    <span class="text-[12px] text-[#6d6a65]">8:00am</span>
                                </button>
                            </div>
                        </div>

                        <!-- Copy link -->
                        <button type="button"
                                @click="copyCampfireLink()"
                                class="w-full px-4 py-1.5 text-left hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <svg class="w-4 h-4 text-[#8c8881] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            <span>Copy link</span>
                        </button>

                        <div class="my-1.5 border-t border-[#ece9e2]"></div>

                        <!-- View in Bonfire -->
                        <button type="button"
                                @click="moreMenuOpen = false; bubbleUpOpen = false"
                                class="w-full pl-10 pr-3.5 py-1.5 text-left hover:bg-[#f6f4f0] cursor-pointer whitespace-nowrap">
                            View in Bonfire
                        </button>

                        <!-- See all files posted here -->
                        <button type="button"
                                @click="switchView('files')"
                                class="w-full pl-10 pr-3.5 py-1.5 text-left hover:bg-[#f6f4f0] cursor-pointer whitespace-nowrap">
                            See all files posted here
                        </button>

                        <!-- Notifying people on project -->
                        <button type="button"
                                @click="moreMenuOpen = false; bubbleUpOpen = false; notifiedModalOpen = true"
                                class="w-full pl-10 pr-3.5 py-1.5 text-left hover:bg-[#f6f4f0] cursor-pointer whitespace-nowrap">
                            @if($project->id == 4)
                                Notifying 7 people
                            @else
                                @php $notifyCount = max(1, $project->members->count()); @endphp
                                Notifying {{ $notifyCount }} {{ $notifyCount === 1 ? 'person' : 'people' }}
                            @endif
                        </button>

                        <!-- Configure chatbots -->
                        <button type="button"
                                @click="switchView('chatbots')"
                                class="w-full pl-10 pr-3.5 py-1.5 text-left hover:bg-[#f6f4f0] cursor-pointer whitespace-nowrap">
                            Configure chatbots
                        </button>

                        <div class="my-1.5 border-t border-[#ece9e2]"></div>

                        <!-- Move... -->
                        <button type="button"
                                @click="moreMenuOpen = false; bubbleUpOpen = false"
                                class="w-full pl-10 pr-3.5 py-1.5 text-left hover:bg-[#f6f4f0] cursor-pointer whitespace-nowrap">
                            Move...
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- VIEW 1: CAMPFIRE CHAT STREAM (activeView === 'chat')          -->
        <!-- ============================================================= -->
        <div x-show="activeView === 'chat'" class="flex-1 min-h-0 flex flex-col overflow-hidden">
            <!-- Messages Stream -->
            <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain px-10 py-6 space-y-2.5 bg-white" id="chat-messages">
                @if($messages->isEmpty())
                    <!-- Basecamp Empty Campfire Callout Box Matching media_1793339157521_028ad416.png -->
                    <div class="pt-9 pb-8 flex justify-center">
                        <div class="w-[362px] max-w-full rounded-[6px] border border-dashed border-[#f3d467] bg-white px-6 py-5 relative">
                            <div class="flex items-start gap-3.5 text-left">
                                <!-- Left Double Speech Bubble Icon -->
                                <svg class="w-6 h-6 text-[#b8b4ac] flex-shrink-0 mt-0.5 fill-current" viewBox="0 0 24 24">
                                    <path d="M15 4H5a2 2 0 00-2 2v7a2 2 0 002 2h2v3l3.5-3H15a2 2 0 002-2V6a2 2 0 00-2-2z"/>
                                    <path d="M19 9h-1v5a3 3 0 01-3 3h-3.2l-1.3 1.1A1 1 0 0011 19h4.5L19 22v-3h1a2 2 0 002-2v-6a2 2 0 00-2-2z" opacity="0.75"/>
                                </svg>

                                <div class="text-[13.5px] leading-[1.45] text-[#6d6a65]">
                                    <p>
                                        <strong class="font-bold text-[#6d6a65]">Chat casually</strong> in Basecamp. Ask questions, connect with your team, and share news without ceremony.
                                    </p>
                                    <p class="mt-3.5 font-bold text-[#6d6a65]">
                                        Start by saying “Hi!” to your team.
                                    </p>
                                </div>
                            </div>

                            <!-- Curved Yellow Arrow Pointing Down Out of Box -->
                            <svg class="w-8 h-9 text-[#f3c623] absolute -bottom-3.5 left-4 pointer-events-none" viewBox="0 0 36 42" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M32 6 C 16 6, 12 18, 12 37"/>
                                <polyline points="7 31 12 38 17 31"/>
                            </svg>
                        </div>
                    </div>
                @endif
                @php
                    $prevDateKey = null;
                    $prevSenderId = null;
                    $prevMsgTime = null;
                @endphp
                @foreach($messages as $msg)
                    @php
                        $msgDateKey = $msg->created_at->format('Y-m-d');
                        $showDateDivider = ($msgDateKey !== $prevDateKey);
                        if ($showDateDivider) {
                            $prevDateKey = $msgDateKey;
                            $prevSenderId = null;
                            $prevMsgTime = null;
                        }
                        $senderName = $msg->sender->name ?? 'Gaurav Khulbey';
                        $isMe = (($msg->sender_id === auth()->id() || ($project->id == 5 && stripos($senderName, 'Manjot') !== false)) && ($project->id != 4 || $msg->created_at->gt(\Carbon\Carbon::parse('2026-10-29 20:00:00'))));
                        if (strtoupper($senderName) === 'SHACHISH SNEH') {
                            $senderName = 'Shachish Sneh';
                        }
                        $parts = preg_split('/\s+/', trim($senderName));
                        $initials = strtoupper(substr($parts[0] ?? 'U', 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                        $badgeColor = $badgePalette[$initials] ?? $fallbackColors[$msg->sender_id % count($fallbackColors)];

                        $senderAvatarUrl = ($msg->sender && $msg->sender->avatar_url && str_starts_with($msg->sender->avatar_url, '/uploads/avatars/'))
                            ? $msg->sender->avatar_url
                            : null;
                        $senderHasOutBadge = false;
                        if (stripos($senderName, 'Shachish') !== false) {
                            $senderAvatarUrl = '/uploads/avatars/25036340.png';
                        } elseif (stripos($senderName, 'Manjot') !== false) {
                            $senderAvatarUrl = '/uploads/avatars/22369668.png';
                            $senderHasOutBadge = true;
                        } elseif (stripos($senderName, 'Prashant') !== false) {
                            $senderAvatarUrl = '/uploads/avatars/22369670.png';
                        } elseif (stripos($senderName, 'Amit Rai') !== false) {
                            $senderAvatarUrl = '/uploads/avatars/36088205.png';
                        }

                        $rawContent = trim($msg->content);
                        if (str_starts_with($rawContent, "There was 2 mistakes in the report")) {
                            $rawContent = "There was 2 mistakes in the report because it's AI testing , one was the report mentions that there was tls 1.1 and 1.2 used but actually there was only 1.3 used which is more secure and reliable , and secondly it shows postgres version16.4 used but the actually version which is used is 16.10";
                        }

                        // Format Basecamp inline @Mention chips (Matching media_1793346732747_1a06b573.png)
                        $mentionsManjot = (stripos($rawContent, '@Manjot') !== false);
                        $formattedHtml = e($rawContent);
                        $formattedHtml = preg_replace(
                            '/@Shachish(?:\s+Sneh)?\b/i',
                            '<span class="inline-flex items-center gap-1.5 align-middle"><img src="/uploads/avatars/25036340.png" alt="Shachish" class="w-[20px] h-[20px] rounded-full object-cover"><span>Shachish</span></span>',
                            $formattedHtml
                        );
                        $formattedHtml = preg_replace(
                            '/@Manjot(?:\s+Singh)?\b/i',
                            '<span class="inline-flex items-center gap-1.5 align-middle"><span class="relative inline-flex w-[20px] h-[20px]"><img src="/uploads/avatars/22369668.png" alt="Manjot" class="w-[20px] h-[20px] rounded-full object-cover"><span class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 bg-[#ffd500] text-[#1d2d35] text-[5.5px] font-black uppercase px-0.5 rounded-[1.5px] leading-none">OUT</span></span><span>Manjot</span></span>',
                            $formattedHtml
                        );
                        $formattedHtml = preg_replace(
                            '/@Shiv(?:\s+Narayan)?\b/i',
                            '<span class="inline-flex items-center gap-1.5 align-middle"><span class="w-[20px] h-[20px] rounded-full bg-[#9333ea] text-white text-[9px] font-bold inline-flex items-center justify-center">SN</span><span>Shiv</span></span>',
                            $formattedHtml
                        );
                        $formattedHtml = preg_replace(
                            '/@Gaurav(?:\s+Khulbey)?\b/i',
                            '<span class="inline-flex items-center gap-1.5 align-middle"><span class="w-[20px] h-[20px] rounded-full bg-[#d946ef] text-white text-[9px] font-bold inline-flex items-center justify-center">GK</span><span>Gaurav</span></span>',
                            $formattedHtml
                        );
                        $formattedHtml = preg_replace(
                            '/@Prashant\b/i',
                            '<span class="inline-flex items-center gap-1.5 align-middle"><img src="/uploads/avatars/22369670.png" alt="Prashant" class="w-[20px] h-[20px] rounded-full object-cover"><span>Prashant</span></span>',
                            $formattedHtml
                        );
                        $formattedHtml = preg_replace(
                            '/@Amit(?:\s+Rai)?\b/i',
                            '<span class="inline-flex items-center gap-1.5 align-middle"><img src="/uploads/avatars/36088205.png" alt="Amit" class="w-[20px] h-[20px] rounded-full object-cover"><span>Amit</span></span>',
                            $formattedHtml
                        );
                        $formattedHtml = preg_replace(
                            '/(https?:\/\/[^\s<]+)/i',
                            '<a href="$1" target="_blank" rel="noopener" class="text-[#1b6ac9] underline hover:text-[#124b8f] break-all">$1</a>',
                            $formattedHtml
                        );

                        $imageFile = null;
                        if (preg_match('/\[Attachment:\s*([^\]]+\.(?:png|jpg|jpeg|gif|webp|pdf))\]/i', $rawContent, $m)) {
                            $imageFile = trim($m[1]);
                        } elseif (preg_match('/^([^\s•]+\.(?:png|jpg|jpeg|gif|webp|pdf))/i', $rawContent, $m)) {
                            $imageFile = trim($m[1]);
                        }
                        if ($imageFile === 'testing_evidence_1.png') {
                            $imageFile = '1000099828.jpg';
                        } elseif ($imageFile === 'testing_evidence_2.png') {
                            $imageFile = '1000099830.jpg';
                        }
                        $mgmtDisplayMap = [
                            'mgmt_1.png' => '1.png',
                            'mgmt_2.png' => '2.png',
                            'mgmt_3.png' => '3.png',
                            'mgmt_4.png' => '4.png',
                            'mgmt_5.png' => '5.png',
                            'Brown_Badge_Security_Logo.png' => 'Brown Badge with Cross Icon Security Logo.png',
                            'Green_Badge_Football_Logo.png' => 'Green Badge American Football Logo.png',
                            'Green_Beige_Light_Bulb_Sticker.png' => 'Green and Beige Light Bulb Vintage Badges Curved Text Circle Sticker.png',
                            'Black_White_Weightlifting_Logo.png' => 'Black and White Illustrative Hand Weightlifting Badge Health and Fitness Gym Logo (1).png',
                            'Pink_Blue_Badge_Logo.png' => 'Pink and Blue Badge Logo.png',
                            'White_Orange_Cupcake_Logo.png' => 'White and Orange Cupcake Hipster Logo.png',
                        ];
                        $mgmtBannerMap = [
                            'Group_51.png' => 'Group 51.png',
                            'Group_50.png' => 'Group 50.png',
                            'banner_6.png' => 'banner 6.png',
                            'banner_7.png' => 'banner 7.png',
                            'banner_9.png' => 'banner 9.png',
                            'banner10.png' => 'banner10.png',
                            'banner11.png' => 'banner11.png',
                            'purple_variant_2_banner.png' => 'purple variant 2 banner.png',
                            'light_banner.png' => 'light banner.png',
                            'purple_banner.png' => 'purple banner.png',
                            'dark_banner.png' => 'dark banner.png',
                            'light_banner_2.png' => 'light banner 2.png',
                            'Group 51.png' => 'Group 51.png',
                            'Group 50.png' => 'Group 50.png',
                            'banner 6.png' => 'banner 6.png',
                            'banner 7.png' => 'banner 7.png',
                            'banner 9.png' => 'banner 9.png',
                            'purple variant 2 banner.png' => 'purple variant 2 banner.png',
                            'light banner.png' => 'light banner.png',
                            'purple banner.png' => 'purple banner.png',
                            'dark banner.png' => 'dark banner.png',
                            'light banner 2.png' => 'light banner 2.png',
                        ];
                        $isMgmtShield = isset($mgmtDisplayMap[$imageFile]);
                        $isMgmtBanner = isset($mgmtBannerMap[$imageFile]);
                        if ($isMgmtBanner && str_contains($imageFile, ' ')) {
                            $imageFile = str_replace(' ', '_', $imageFile);
                        }
                        if ($isMgmtShield) {
                            $displayFileName = $mgmtDisplayMap[$imageFile];
                        } elseif ($isMgmtBanner) {
                            $displayFileName = $mgmtBannerMap[$imageFile];
                        } elseif ($imageFile === 'report_screenshot_1.png' || $imageFile === 'DOC-20260918-WA0000.pdf') {
                            $displayFileName = 'DOC-20260918-WA0000.pdf';
                        } else {
                            $displayFileName = $imageFile;
                        }
                        $imageSizeLabel = ($imageFile === '1000099828.jpg') ? '348 KB' : (($imageFile === '1000099830.jpg') ? '352 KB' : ($isMgmtShield ? '28.4 KB' : ($isMgmtBanner ? '142 KB' : '40.3 KB')));
                        $imageUrl = ($displayFileName === 'DOC-20260918-WA0000.pdf')
                            ? '/uploads/campfire/report_screenshot_1.png?v=20261031_3'
                            : ($imageFile ? '/uploads/campfire/' . $imageFile . '?v=20261031_3' : null);
                        $downloadUrl = ($displayFileName === 'DOC-20260918-WA0000.pdf')
                            ? '/uploads/campfire/DOC-20260918-WA0000.pdf?v=20261031_3'
                            : $imageUrl;
                        $sameSenderAsPrev = ($prevSenderId === $msg->sender_id) && ($prevMsgTime && abs($msg->created_at->diffInSeconds($prevMsgTime)) < 300);
                        $prevSenderId = $msg->sender_id;
                        $prevMsgTime = $msg->created_at;
                    @endphp

                    @if($showDateDivider)
                        <div class="flex items-center gap-3 py-2">
                            <div class="flex-1 border-t border-[#e5e2dc]"></div>
                            <span class="text-[11.5px] font-bold uppercase tracking-wider text-[#6d6a65]">{{ strtoupper($msg->created_at->year < 2026 ? $msg->created_at->format('l, F j Y') : $msg->created_at->format('l, F j')) }}</span>
                            <div class="flex-1 border-t border-[#e5e2dc]"></div>
                        </div>
                    @endif

                    @if($isMe)
                        <!-- Outgoing Message (Right-Aligned Light Blue Basecamp Bubble) -->
                        <div class="flex items-start justify-end gap-2.5 group/row">
                            <span class="opacity-0 group-hover/row:opacity-100 transition-opacity text-[#1d2d35] font-bold tracking-widest text-[12px] self-center mr-1 select-none cursor-pointer">•••</span>
                            <div class="max-w-[75%] bg-[#e6f2fa] rounded-[10px] px-4 py-2 text-[#1d2d35]">
                                @if(!$sameSenderAsPrev)
                                    <div class="flex items-center justify-end gap-1.5 mb-0.5 text-[12px]">
                                        <span class="text-[#6d6a65]">{{ strtolower($msg->created_at->format('g:ia')) }}</span>
                                        <span class="font-bold text-[#1d2d35]">Me</span>
                                    </div>
                                @endif
                                @if($imageUrl)
                                    <div class="inline-block text-center relative group/img">
                                        <img src="{{ $imageUrl }}"
                                             alt="{{ $displayFileName }}"
                                             onload="const c = document.getElementById('chat-messages'); if(c && !window._userScrolledChat) c.scrollTop = c.scrollHeight;"
                                             @click="openLightbox('{{ $imageUrl }}', '{{ addslashes($displayFileName) }}', '{{ $imageSizeLabel }}', '{{ $downloadUrl }}')"
                                             class="w-[237px] h-auto block rounded-[2px] cursor-pointer">
                                        <div class="mt-2 text-[13px] text-[#1d2d35] text-center">
                                            <button type="button" @click="openLightbox('{{ $imageUrl }}', '{{ addslashes($displayFileName) }}', '{{ $imageSizeLabel }}', '{{ $downloadUrl }}')" class="hover:underline cursor-pointer">{{ $displayFileName }}</button>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-[14.5px] leading-[1.45] whitespace-pre-line break-words">{!! $formattedHtml !!}</div>
                                @endif
                            </div>

                            <div class="w-[34px] h-[34px] flex-shrink-0 mt-0.5 relative">
                                @if(!$sameSenderAsPrev)
                                    @php
                                        $meUser = auth()->user();
                                        $isManjotMe = ($project->id == 5) || ($meUser && (stripos($meUser->name ?? '', 'Manjot') !== false || $meUser->id == 1));
                                        $meAvatar = $isManjotMe
                                            ? '/uploads/avatars/22369668.png'
                                            : ($meUser && $meUser->avatar_url && !str_contains($meUser->avatar_url, 'dicebear') ? $meUser->avatar_url : '/uploads/avatars/22369668.png');
                                    @endphp
                                    <img src="{{ $meAvatar }}"
                                         alt="Me"
                                         class="w-[34px] h-[34px] rounded-full object-cover">
                                    @if($isManjotMe)
                                        <span class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 bg-[#ffd500] text-[#1d2d35] text-[7px] font-black uppercase px-1 rounded-[2px] leading-tight">OUT</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @else
                        <!-- Incoming Message (Left-Aligned Warm Cream Basecamp Card/Bubble) -->
                        <div class="flex items-start gap-2.5">
                            <!-- Left 34px Avatar Column -->
                            <div class="w-[34px] flex-shrink-0 mt-0.5">
                                @if(!$sameSenderAsPrev)
                                    @if($senderAvatarUrl)
                                        <button type="button"
                                                onclick="window.dispatchEvent(new CustomEvent('open-user-profile', { detail: window.BC_USERS_BY_INITIALS['{{ $initials }}'] || { id: {{ $msg->sender_id }}, name: '{{ addslashes($senderName) }}', firstName: '{{ addslashes($parts[0] ?? 'Member') }}', initials: '{{ $initials }}', avatar: '{{ $senderAvatarUrl }}' } }))"
                                                class="w-[34px] h-[34px] rounded-full relative flex items-center justify-center select-none cursor-pointer hover:opacity-90 transition-opacity">
                                            <img src="{{ $senderAvatarUrl }}" alt="{{ $senderName }}" class="w-[34px] h-[34px] rounded-full object-cover">
                                            @if($senderHasOutBadge)
                                                <span class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 bg-[#ffd500] text-[#1d2d35] text-[7px] font-black uppercase px-1 rounded-[2px] leading-tight">OUT</span>
                                            @endif
                                        </button>
                                    @else
                                        <button type="button"
                                                onclick="window.dispatchEvent(new CustomEvent('open-user-profile', { detail: window.BC_USERS_BY_INITIALS['{{ $initials }}'] || { id: {{ $msg->sender_id }}, name: '{{ addslashes($senderName) }}', firstName: '{{ addslashes($parts[0] ?? 'Member') }}', initials: '{{ $initials }}', bg: 'bg-[#d946ef]' } }))"
                                                class="w-[34px] h-[34px] rounded-full text-white font-bold text-[12px] flex items-center justify-center select-none cursor-pointer hover:opacity-90 transition-opacity"
                                                style="background-color: {{ $badgeColor }}">
                                            {{ $initials }}
                                        </button>
                                    @endif
                                @endif
                            </div>

                            @if($imageUrl)
                                <!-- Standalone Warm Beige Image Card + Top-Right Hover Action Pill -->
                                <div class="flex items-center gap-3 group/row">
                                    <div class="{{ ($isMgmtShield || $isMgmtBanner) ? 'bg-[#fcf8f4] p-3' : 'bg-[#faf6f0] p-2.5' }} rounded-[6px] inline-block text-center relative group/img">
                                        @if(!$sameSenderAsPrev)
                                            <div class="flex items-baseline gap-1.5 mb-1.5 text-[12.5px] text-left">
                                                <span class="font-bold text-[#1d2d35]">{{ $senderName }}</span>
                                                <span class="text-[#6d6a65] text-[11.5px]">{{ strtolower($msg->created_at->format('g:ia')) }}</span>
                                            </div>
                                        @endif

                                        <div class="relative inline-block">
                                            <img src="{{ $imageUrl }}"
                                                 alt="{{ $displayFileName }}"
                                                 onload="const c = document.getElementById('chat-messages'); if(c && !window._userScrolledChat) c.scrollTop = c.scrollHeight;"
                                                 @click="openLightbox('{{ $imageUrl }}', '{{ addslashes($displayFileName) }}', '{{ $imageSizeLabel }}', '{{ $downloadUrl }}')"
                                                 class="{{ $isMgmtShield ? 'w-[270px] h-[270px] bg-white object-contain' : ($isMgmtBanner ? 'w-[680px] max-w-[66vw] h-auto object-cover' : 'w-[237px] h-auto') }} block rounded-t-[2px] cursor-pointer">
                                            @if($displayFileName === 'DOC-20260918-WA0000.pdf')
                                                <div class="h-[2.5px] w-full bg-[#1b6ac9]"></div>
                                            @endif

                                            <!-- Top-Right Action Buttons (Open & Download Icons Matching media_1793336267097_5cea19df.jpg) -->
                                            <div class="opacity-0 group-hover/img:opacity-100 transition-opacity absolute top-2 right-2 flex items-center gap-1.5 text-[#1d2d35]">
                                                <a href="{{ $downloadUrl }}"
                                                   target="_blank"
                                                   @click.stop
                                                   class="w-[26px] h-[26px] rounded-[4px] bg-white/95 hover:bg-white border border-[#e5e2dc] shadow-[0_1px_3px_rgba(0,0,0,0.12)] flex items-center justify-center cursor-pointer"
                                                   title="Open in new tab">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 3h7v7m0-7L10 14m-4-7H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-1"/>
                                                    </svg>
                                                </a>
                                                <a href="{{ $downloadUrl }}"
                                                   download="{{ $displayFileName }}"
                                                   @click.stop
                                                   class="w-[26px] h-[26px] rounded-[4px] bg-white/95 hover:bg-white border border-[#e5e2dc] shadow-[0_1px_3px_rgba(0,0,0,0.12)] flex items-center justify-center cursor-pointer"
                                                   title="Download">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>

                                        <div class="{{ ($isMgmtShield || $isMgmtBanner) ? 'mt-2 bg-[#f6f2ee] rounded-[3px] py-1.5 px-3 text-[12.5px]' : 'mt-2 text-[13px]' }} text-[#1d2d35] text-center">
                                            <button type="button" @click="openLightbox('{{ $imageUrl }}', '{{ addslashes($displayFileName) }}', '{{ $imageSizeLabel }}', '{{ $downloadUrl }}')" class="hover:underline cursor-pointer">{{ $displayFileName }}</button>
                                        </div>
                                    </div>
                                    <span class="{{ ($isMgmtShield || $isMgmtBanner) ? 'opacity-80 group-hover/row:opacity-100' : 'opacity-0 group-hover/row:opacity-100' }} transition-opacity text-[#1d2d35] font-bold tracking-widest text-[13px] select-none cursor-pointer">•••</span>
                                </div>
                            @else
                                <!-- Text Bubble + Right ••• -->
                                <div class="flex items-center gap-3 max-w-[78%] group/row">
                                    <div class="{{ $mentionsManjot ? 'bg-[#f8ead5]' : 'bg-[#faf6f0]' }} rounded-[10px] px-4 py-2.5 text-[#1d2d35]">
                                        @if(!$sameSenderAsPrev)
                                            <div class="flex items-baseline gap-1.5 mb-1 text-[12.5px]">
                                                <span class="font-bold text-[#1d2d35]">{{ $senderName }}</span>
                                                <span class="text-[#6d6a65] text-[11.5px]">{{ strtolower($msg->created_at->format('g:ia')) }}</span>
                                            </div>
                                        @endif
                                        <div class="text-[14.5px] leading-[1.45] whitespace-pre-line break-words">{!! $formattedHtml !!}</div>
                                    </div>
                                    <span class="opacity-0 group-hover/row:opacity-100 transition-opacity text-[#1d2d35] font-bold tracking-widest text-[13px] select-none cursor-pointer">•••</span>
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>

            @if($project->status !== 'ARCHIVED')
            <!-- Bottom Input Bar Matching Basecamp Campfire (media_1793346468111_0c839a4a.jpg & media_1793346474536_675f974f.jpg) -->
            <div class="px-6 py-3.5 bg-white flex-shrink-0"
                 x-data="{
                    emojiOpen: false,
                    formatBarOpen: false,
                    recordingAudio: false,
                    recordSeconds: 0,
                    recordTimer: null,
                    attachedFileName: '',
                    emojis: ['👍','❤️','🎉','🚀','😊','😂','🙌','👏','🔥','💯','✅','👀','🙏','💡','✨','🎯'],
                    insertEmoji(em) {
                        const inp = this.$refs.chatTextInput;
                        if (inp) {
                            inp.value = (inp.value ? inp.value + ' ' : '') + em;
                            inp.focus();
                            inp.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        this.emojiOpen = false;
                    },
                    applyFormat(prefix, suffix) {
                        const inp = this.$refs.chatTextInput;
                        if (!inp) return;
                        const s = inp.selectionStart || 0;
                        const e = inp.selectionEnd || 0;
                        const val = inp.value || '';
                        const sel = val.slice(s, e) || 'text';
                        inp.value = val.slice(0, s) + prefix + sel + (suffix !== undefined ? suffix : prefix) + val.slice(e);
                        inp.focus();
                    },
                    startAudioRecord() {
                        this.recordingAudio = true;
                        this.recordSeconds = 0;
                        clearInterval(this.recordTimer);
                        this.recordTimer = setInterval(() => { this.recordSeconds++; }, 1000);
                    },
                    stopAudioRecord(attach) {
                        clearInterval(this.recordTimer);
                        this.recordingAudio = false;
                        if (attach) {
                            const inp = this.$refs.chatTextInput;
                            if (inp) {
                                const mins = Math.floor(this.recordSeconds / 60);
                                const secs = String(this.recordSeconds % 60).padStart(2, '0');
                                inp.value = (inp.value ? inp.value + ' ' : '') + '🎙️ Voice Note (' + mins + ':' + secs + ')';
                                inp.focus();
                            }
                        }
                    }
                 }">
                <form action="{{ route('projects.chat.store', $project->id) }}" method="POST" enctype="multipart/form-data" class="relative">
                    @csrf
                    <input type="file"
                           name="attachment"
                           x-ref="campfireFileInput"
                           @change="attachedFileName = $event.target.files[0] ? $event.target.files[0].name : ''"
                           class="hidden">

                    <!-- Attached File Preview Pill -->
                    <div x-show="attachedFileName !== ''" x-cloak class="mb-2 px-3 py-1.5 rounded-[6px] bg-[#f5f3ef] border border-[#e5e2dc] flex items-center justify-between text-[13px] text-[#1d2d35]">
                        <div class="flex items-center gap-2 truncate">
                            <svg class="w-4 h-4 text-[#1b6ac9] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <span class="font-semibold truncate" x-text="attachedFileName"></span>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button type="submit" class="px-3 py-1 rounded-full bg-[#199849] hover:bg-[#147a3a] text-white font-bold text-[12px] cursor-pointer">Send file</button>
                            <button type="button" @click="$refs.campfireFileInput.value = ''; attachedFileName = ''" class="text-[#6d6a65] hover:text-[#1d2d35] font-bold px-1 cursor-pointer">✕</button>
                        </div>
                    </div>

                    <!-- Inline Voice Note Recorder Bar -->
                    <div x-show="recordingAudio" x-cloak class="mb-2 px-3.5 py-2 rounded-[6px] bg-[#fff8f6] border border-[#f5c2c0] flex items-center justify-between text-[13px] text-[#1d2d35]">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#d93025] animate-pulse"></span>
                            <span class="font-bold text-[#a62c21]">Recording audio...</span>
                            <span class="font-mono text-[#6d6a65]" x-text="Math.floor(recordSeconds / 60) + ':' + String(recordSeconds % 60).padStart(2, '0')"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="stopAudioRecord(true)" class="px-3 py-1 rounded-full bg-[#199849] hover:bg-[#147a3a] text-white font-bold text-[12px] cursor-pointer">Attach clip</button>
                            <button type="button" @click="stopAudioRecord(false)" class="px-2.5 py-1 rounded-full border border-[#d6d2c9] bg-white text-[#6d6a65] text-[12px] cursor-pointer">Cancel</button>
                        </div>
                    </div>

                    <!-- Expandable Basecamp Rich Formatting Toolbar (Toggled by 'A' Button) -->
                    <div x-show="formatBarOpen" x-cloak class="mb-1.5 px-2.5 py-1.5 rounded-t-[7px] border border-[#d6d2c9] bg-[#fbfaf8] flex items-center gap-1 text-[#1d2d35]">
                        <button type="button" @click="applyFormat('**', '**')" class="px-2 py-0.5 rounded hover:bg-[#ece9e2] font-extrabold text-[13px] cursor-pointer" title="Bold">B</button>
                        <button type="button" @click="applyFormat('*', '*')" class="px-2 py-0.5 rounded hover:bg-[#ece9e2] italic font-serif text-[13.5px] cursor-pointer" title="Italic">I</button>
                        <button type="button" @click="applyFormat('~~', '~~')" class="px-2 py-0.5 rounded hover:bg-[#ece9e2] line-through text-[13px] cursor-pointer" title="Strikethrough">S</button>
                        <span class="w-[1px] h-4 bg-[#d6d2c9] mx-1"></span>
                        <button type="button" @click="applyFormat('[', '](https://)')" class="p-1 rounded hover:bg-[#ece9e2] cursor-pointer" title="Link">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                        </button>
                        <button type="button" @click="applyFormat('> ', '')" class="px-2 py-0.5 rounded hover:bg-[#ece9e2] font-serif font-bold text-[14px] cursor-pointer" title="Quote">”</button>
                        <button type="button" @click="applyFormat('`', '`')" class="px-1.5 py-0.5 rounded hover:bg-[#ece9e2] font-mono text-[12px] font-bold cursor-pointer" title="Code">&lt;&gt;</button>
                        <button type="button" @click="applyFormat('• ', '')" class="p-1 rounded hover:bg-[#ece9e2] cursor-pointer" title="Bulleted list">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        </button>
                    </div>

                    <div class="flex items-center border border-[#d6d2c9] focus-within:border-[1.5px] focus-within:border-[#1b6ac9] rounded-[7px] bg-white px-4 py-2.5">
                        <input type="text"
                               name="content"
                               x-ref="chatTextInput"
                               autocomplete="off"
                               class="flex-1 text-[14.5px] text-[#1d2d35] bg-transparent focus:outline-none">

                        <!-- Hidden submit button so pressing Enter inside input submits form (unless @mention menu is open) -->
                        <button type="submit" class="hidden" aria-hidden="true"></button>

                        <!-- Right Action Icons Matching Basecamp Input Box (media_1793346474536_675f974f.jpg) -->
                        <div class="flex items-center gap-3.5 text-[#1d2d35] pl-3 relative">
                            <!-- 1. Emoji Picker Button & Popover -->
                            <div class="relative inline-flex items-center" @click.outside="emojiOpen = false">
                                <button type="button" @click="emojiOpen = !emojiOpen" class="hover:opacity-75 transition-opacity cursor-pointer" title="Add emoji">
                                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"/>
                                        <path stroke-linecap="round" d="M8 14s1.5 2 4 2 4-2 4-2"/>
                                        <line x1="9" y1="9" x2="9.01" y2="9" stroke-width="2.5" stroke-linecap="round"/>
                                        <line x1="15" y1="9" x2="15.01" y2="9" stroke-width="2.5" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <div x-show="emojiOpen"
                                     x-cloak
                                     class="absolute right-0 bottom-[calc(100%+10px)] w-[210px] p-2 rounded-[8px] bg-white border border-[#d8d4cc] shadow-[0_8px_24px_rgba(0,0,0,0.14)] grid grid-cols-4 gap-1 z-40">
                                    <template x-for="em in emojis" :key="em">
                                        <button type="button"
                                                @click="insertEmoji(em)"
                                                class="py-1.5 rounded hover:bg-[#f2f0ec] text-[18px] flex items-center justify-center cursor-pointer"
                                                x-text="em"></button>
                                    </template>
                                </div>
                            </div>

                            <!-- 2. Paperclip (Attach File) -->
                            <button type="button"
                                    @click="$refs.campfireFileInput.click()"
                                    class="hover:opacity-75 transition-opacity cursor-pointer"
                                    title="Attach files or images">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                            </button>

                            <!-- 3. Microphone (Record Audio Clip) -->
                            <button type="button"
                                    @click="recordingAudio ? stopAudioRecord(true) : startAudioRecord()"
                                    :class="recordingAudio ? 'text-[#d93025]' : 'text-[#1d2d35]'"
                                    class="hover:opacity-75 transition-opacity cursor-pointer"
                                    title="Record an audio clip">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 10v2a7 7 0 01-14 0v-2M12 19v4M8 23h8"/>
                                </svg>
                            </button>

                            <!-- 4. 'A' Rich Text Formatting Toggle -->
                            <button type="button"
                                    @click="formatBarOpen = !formatBarOpen"
                                    :class="formatBarOpen ? 'text-[#1b6ac9]' : 'text-[#1d2d35]'"
                                    class="hover:opacity-75 transition-opacity font-serif font-bold text-[15px] underline decoration-2 cursor-pointer"
                                    title="Formatting options">
                                A
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @endif
        </div>

        <!-- ============================================================= -->
        <!-- VIEW 2: ALL THE FILES POSTED TO THIS CHAT (63659a21.jpg)      -->
        <!-- ============================================================= -->
        <div x-show="activeView === 'files'" x-cloak class="flex-1 min-h-0 overflow-y-auto px-10 sm:px-14 pt-8 pb-16">
            <h1 class="text-[28px] sm:text-[31px] font-black text-[#1d2d35] tracking-[-0.02em] leading-tight">
                All the files posted to this chat
            </h1>

            @if($project->id == 4)
            <div class="mt-6 flex flex-wrap gap-4">
                <!-- Card 1: 1000099830.jpg -->
                <div class="w-[198px] rounded-[8px] border border-[#e5e2dc] bg-white p-2.5 text-center shadow-[0_1px_2px_rgba(0,0,0,0.03)] hover:border-[#c7c3bc] transition">
                    <div class="w-full h-[222px] overflow-hidden rounded-[2px] cursor-pointer"
                         @click="openLightbox('/uploads/campfire/1000099830.jpg?v=20261030_5', '1000099830.jpg', '352 KB')">
                        <img src="/uploads/campfire/1000099830.jpg?v=20261030_5"
                             alt="1000099830.jpg"
                             class="w-full h-full object-cover object-center">
                    </div>
                    <div class="mt-2.5 text-[14px] text-[#1d2d35] truncate">
                        <button type="button"
                                @click="openLightbox('/uploads/campfire/1000099830.jpg?v=20261030_5', '1000099830.jpg', '352 KB')"
                                class="hover:underline cursor-pointer">
                            1000099830.jpg
                        </button>
                    </div>
                    <div class="mt-0.5 text-[12.5px] text-[#6d6a65]">
                        <span>Oct 6</span>
                        <span>•</span>
                        <a href="/uploads/campfire/1000099830.jpg?v=20261030_5" download="1000099830.jpg" class="underline hover:text-[#1d2d35]">Download</a>
                    </div>
                </div>

                <!-- Card 2: 1000099828.jpg -->
                <div class="w-[198px] rounded-[8px] border border-[#e5e2dc] bg-white p-2.5 text-center shadow-[0_1px_2px_rgba(0,0,0,0.03)] hover:border-[#c7c3bc] transition">
                    <div class="w-full h-[222px] overflow-hidden rounded-[2px] cursor-pointer"
                         @click="openLightbox('/uploads/campfire/1000099828.jpg?v=20261030_5', '1000099828.jpg', '348 KB')">
                        <img src="/uploads/campfire/1000099828.jpg?v=20261030_5"
                             alt="1000099828.jpg"
                             class="w-full h-full object-cover object-center">
                    </div>
                    <div class="mt-2.5 text-[14px] text-[#1d2d35] truncate">
                        <button type="button"
                                @click="openLightbox('/uploads/campfire/1000099828.jpg?v=20261030_5', '1000099828.jpg', '348 KB')"
                                class="hover:underline cursor-pointer">
                            1000099828.jpg
                        </button>
                    </div>
                    <div class="mt-0.5 text-[12.5px] text-[#6d6a65]">
                        <span>Oct 6</span>
                        <span>•</span>
                        <a href="/uploads/campfire/1000099828.jpg?v=20261030_5" download="1000099828.jpg" class="underline hover:text-[#1d2d35]">Download</a>
                    </div>
                </div>

                <!-- Card 3: DOC-20260918-WA0000. -->
                <div class="w-[198px] rounded-[8px] border border-[#e5e2dc] bg-white p-2.5 text-center shadow-[0_1px_2px_rgba(0,0,0,0.03)] hover:border-[#c7c3bc] transition">
                    <div class="w-full h-[222px] overflow-hidden rounded-[2px] cursor-pointer"
                         @click="openLightbox('/uploads/campfire/report_screenshot_1.png?v=20261030_5', 'DOC-20260918-WA0000.pdf', '40.3 KB', '/uploads/campfire/DOC-20260918-WA0000.pdf?v=20261030_5')">
                        <img src="/uploads/campfire/report_screenshot_1.png?v=20261030_5"
                             alt="DOC-20260918-WA0000."
                             class="w-full h-full object-cover object-top">
                    </div>
                    <div class="mt-2.5 text-[14px] text-[#1d2d35] truncate px-1">
                        <button type="button"
                                @click="openLightbox('/uploads/campfire/report_screenshot_1.png?v=20261030_5', 'DOC-20260918-WA0000.pdf', '40.3 KB', '/uploads/campfire/DOC-20260918-WA0000.pdf?v=20261030_5')"
                                class="hover:underline cursor-pointer">
                            DOC-20260918-WA0000.
                        </button>
                    </div>
                    <div class="mt-0.5 text-[12.5px] text-[#6d6a65]">
                        <span>Sep 22</span>
                        <span>•</span>
                        <a href="/uploads/campfire/DOC-20260918-WA0000.pdf?v=20261030_5" download="DOC-20260918-WA0000.pdf" class="underline hover:text-[#1d2d35]">Download</a>
                    </div>
                </div>
            </div>
            @elseif($project->id == 5)
            <div class="mt-6 flex flex-wrap gap-4">
                @php
                    $p5Files = [
                        ['file' => 'mgmt_2.png', 'label' => '2.png', 'size' => '28.4 KB'],
                        ['file' => 'mgmt_1.png', 'label' => '1.png', 'size' => '28.4 KB'],
                        ['file' => 'mgmt_5.png', 'label' => '5.png', 'size' => '28.4 KB'],
                        ['file' => 'mgmt_4.png', 'label' => '4.png', 'size' => '28.4 KB'],
                        ['file' => 'mgmt_3.png', 'label' => '3.png', 'size' => '28.4 KB'],
                        ['file' => 'White_Orange_Cupcake_Logo.png', 'label' => 'White and Orange Cupcake Hipster Logo.png', 'size' => '42.1 KB'],
                        ['file' => 'Pink_Blue_Badge_Logo.png', 'label' => 'Pink and Blue Badge Logo.png', 'size' => '54.2 KB'],
                        ['file' => 'Black_White_Weightlifting_Logo.png', 'label' => 'Black and White Illustrative Hand Weightlifting Badge Health and Fitness Gym Logo (1).png', 'size' => '38.6 KB'],
                        ['file' => 'Green_Beige_Light_Bulb_Sticker.png', 'label' => 'Green and Beige Light Bulb Vintage Badges Curved Text Circle Sticker.png', 'size' => '31.4 KB'],
                        ['file' => 'Green_Badge_Football_Logo.png', 'label' => 'Green Badge American Football Logo.png', 'size' => '34.8 KB'],
                        ['file' => 'Brown_Badge_Security_Logo.png', 'label' => 'Brown Badge with Cross Icon Security Logo.png', 'size' => '29.5 KB'],
                        ['file' => 'light_banner_2.png', 'label' => 'light banner 2.png', 'size' => '184 KB'],
                        ['file' => 'dark_banner.png', 'label' => 'dark banner.png', 'size' => '142 KB'],
                        ['file' => 'purple_banner.png', 'label' => 'purple banner.png', 'size' => '164 KB'],
                        ['file' => 'light_banner.png', 'label' => 'light banner.png', 'size' => '178 KB'],
                        ['file' => 'purple_variant_2_banner.png', 'label' => 'purple variant 2 banner.png', 'size' => '156 KB'],
                        ['file' => 'banner11.png', 'label' => 'banner11.png', 'size' => '128 KB'],
                        ['file' => 'banner10.png', 'label' => 'banner10.png', 'size' => '162 KB'],
                        ['file' => 'banner_9.png', 'label' => 'banner 9.png', 'size' => '134 KB'],
                        ['file' => 'banner_7.png', 'label' => 'banner 7.png', 'size' => '148 KB'],
                        ['file' => 'banner_6.png', 'label' => 'banner 6.png', 'size' => '118 KB'],
                        ['file' => 'Group_50.png', 'label' => 'Group 50.png', 'size' => '152 KB'],
                        ['file' => 'Group_51.png', 'label' => 'Group 51.png', 'size' => '158 KB'],
                    ];
                @endphp
                @foreach($p5Files as $item)
                <div class="w-[198px] rounded-[8px] border border-[#e5e2dc] bg-white p-2.5 text-center shadow-[0_1px_2px_rgba(0,0,0,0.03)] hover:border-[#c7c3bc] transition">
                    <div class="w-full h-[222px] overflow-hidden rounded-[2px] cursor-pointer flex items-center justify-center bg-white"
                         @click="openLightbox('/uploads/campfire/{{ $item['file'] }}?v=20261031_3', '{{ $item['label'] }}', '{{ $item['size'] }}')">
                        <img src="/uploads/campfire/{{ $item['file'] }}?v=20261031_3"
                             alt="{{ $item['label'] }}"
                             class="w-full h-full object-contain">
                    </div>
                    <div class="mt-2.5 text-[14px] text-[#1d2d35] truncate">
                        <button type="button"
                                @click="openLightbox('/uploads/campfire/{{ $item['file'] }}?v=20261031_3', '{{ $item['label'] }}', '{{ $item['size'] }}')"
                                class="hover:underline cursor-pointer">
                            {{ $item['label'] }}
                        </button>
                    </div>
                    <div class="mt-0.5 text-[12.5px] text-[#6d6a65]">
                        <span>Sep 8</span>
                        <span>•</span>
                        <a href="/uploads/campfire/{{ $item['file'] }}?v=20261031_3" download="{{ $item['label'] }}" class="underline hover:text-[#1d2d35]">Download</a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
                <div class="mt-8 pt-6 border-t border-[#ece9e2] text-[14.5px] text-[#6d6a65]">
                    No files have been posted to <strong class="text-[#1d2d35]">{{ $project->name }}</strong>’s Campfire yet.
                </div>
            @endif
        </div>

        <!-- ============================================================= -->
        <!-- VIEW 3: CONFIGURE CHATBOTS                                    -->
        <!-- ============================================================= -->
        <div x-show="activeView === 'chatbots'" x-cloak class="flex-1 min-h-0 overflow-y-auto px-10 sm:px-14 pt-8 pb-16">
            <div class="max-w-[680px]">
                <h1 class="text-[28px] sm:text-[31px] font-black text-[#1d2d35] tracking-[-0.02em] leading-tight">
                    Chatbots
                </h1>
                <p class="mt-2.5 text-[15px] text-[#1d2d35] leading-relaxed">
                    Chatbots are a way for other apps, services, or scripts to post messages to this Campfire room, or respond to interactive commands.
                </p>

                <div class="mt-5">
                    <button type="button"
                            @click="addChatbotOpen = !addChatbotOpen"
                            class="bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-bold text-[13.5px] px-4 py-2 rounded-full inline-flex items-center gap-1.5 transition cursor-pointer">
                        <span class="text-[16px] leading-none font-normal">+</span>
                        <span>Add a new chatbot</span>
                    </button>
                </div>

                <!-- Inline Add a Chatbot Form -->
                <div x-show="addChatbotOpen" x-transition class="mt-5 rounded-[8px] border border-[#d6d2c9] bg-[#fbf9f5] p-5 space-y-4">
                    <div>
                        <label class="block text-[13.5px] font-bold text-[#1d2d35] mb-1">Name</label>
                        <input type="text"
                               x-model="chatbotName"
                               placeholder="e.g. Deploy Bot, GitHub Notifier..."
                               class="w-full px-3 py-1.5 bg-white border border-[#d6d2c9] rounded-[6px] text-[14px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                    </div>
                    <div>
                        <label class="block text-[13.5px] font-bold text-[#1d2d35] mb-1">Command URL <span class="font-normal text-[#6d6a65]">(optional)</span></label>
                        <input type="url"
                               x-model="chatbotUrl"
                               placeholder="https://example.com/webhook"
                               class="w-full px-3 py-1.5 bg-white border border-[#d6d2c9] rounded-[6px] text-[14px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                    </div>
                    <div class="flex items-center gap-2 pt-1">
                        <button type="button"
                                @click="if (chatbotName.trim()) { chatbots.push({ name: chatbotName.trim(), url: chatbotUrl.trim() }); chatbotName = ''; chatbotUrl = ''; addChatbotOpen = false; }"
                                class="px-4 py-1.5 rounded-full bg-[#198744] hover:bg-[#146c36] text-white font-bold text-[13px] cursor-pointer">
                            Add this chatbot
                        </button>
                        <button type="button"
                                @click="addChatbotOpen = false"
                                class="px-3.5 py-1.5 rounded-full border border-[#d6d2c9] bg-white text-[#6d6a65] text-[13px] hover:text-[#1d2d35] cursor-pointer">
                            Cancel
                        </button>
                    </div>
                </div>

                <!-- Configured Chatbots List -->
                <div class="mt-7 space-y-3" x-show="chatbots.length > 0">
                    <template x-for="(bot, idx) in chatbots" :key="idx">
                        <div class="rounded-[8px] border border-[#e5e2dc] bg-white px-4 py-3 flex items-center justify-between">
                            <div>
                                <div class="text-[15px] font-bold text-[#1d2d35]" x-text="bot.name"></div>
                                <div class="text-[12.5px] text-[#6d6a65]" x-text="bot.url || 'Incoming webhook enabled'"></div>
                            </div>
                            <button type="button" @click="chatbots.splice(idx, 1)" class="text-[12.5px] text-[#a62c21] hover:underline cursor-pointer">Remove</button>
                        </div>
                    </template>
                </div>

                <div x-show="chatbots.length === 0 && !addChatbotOpen" class="mt-8 pt-6 border-t border-[#ece9e2] text-[14px] text-[#6d6a65]">
                    No chatbots have been configured for <strong class="text-[#1d2d35]">{{ $project->name }}</strong> yet.
                </div>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- VIEW 4: WHO'S ALLOWED TO SEE THIS CHAT? (dd35dd2f.jpg)        -->
        <!-- ============================================================= -->
        <div x-show="activeView === 'privacy'" x-cloak class="flex-1 min-h-0 overflow-y-auto px-10 sm:px-20 pt-10 pb-16">
            <div class="flex flex-col sm:flex-row items-start gap-7 max-w-[720px]">
                <!-- Left Preview Card -->
                <div class="w-[196px] h-[116px] rounded-[8px] border border-[#e5e2dc] bg-white shadow-2xs p-5 flex items-center flex-shrink-0">
                    <span class="text-[17px] font-bold text-[#1d2d35]">Campfire</span>
                </div>

                <!-- Right Options Column -->
                <div class="flex-1 pt-1">
                    <h1 class="text-[26px] font-extrabold text-[#1d2d35] tracking-[-0.02em] leading-tight mb-3.5">
                        Who’s allowed to see this chat?
                    </h1>

                    <div class="space-y-2">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="radio"
                                   value="team"
                                   x-model="draftVisibility"
                                   class="w-4 h-4 accent-[#1d2d35]">
                            <span :class="draftVisibility === 'team' ? 'bg-[#cfe6f7]' : 'bg-transparent'"
                                  class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[13.5px] font-medium text-[#1d2d35] transition-colors">
                                <svg class="w-3.5 h-3.5 fill-current text-[#1d2d35]" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                <span>Our team only—private to us</span>
                            </span>
                        </label>

                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="radio"
                                   value="client"
                                   x-model="draftVisibility"
                                   class="w-4 h-4 accent-[#1d2d35]">
                            <span :class="draftVisibility === 'client' ? 'bg-[#cfe6f7]' : 'bg-transparent'"
                                  class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[13.5px] font-medium text-[#1d2d35] transition-colors">
                                <svg class="w-3.5 h-3.5 fill-current text-[#1d2d35]" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
                                <span>Allow the client to see this too</span>
                            </span>
                        </label>
                    </div>

                    <div class="mt-5 flex items-center gap-2">
                        <button type="button"
                                @click="chatVisibility = draftVisibility; switchView('chat')"
                                class="px-4 py-2 rounded-[6px] bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-semibold text-[14px] transition-colors cursor-pointer">
                            Save
                        </button>
                        <button type="button"
                                @click="switchView('chat')"
                                class="px-4 py-2 rounded-[6px] border border-[#e5e2dc] bg-white text-[#1b6ac9] hover:bg-[#fbfaf8] font-medium text-[14px] transition-colors cursor-pointer">
                            Never mind
                        </button>
                    </div>
                </div>
            </div>
        </div>

        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL: "Who’s being notified?" Matching media_1793273801100_edc74e48.jpg -->
    <!-- ================================================================= -->
    <div x-show="notifiedModalOpen"
         x-cloak
         @click.self="notifiedModalOpen = false"
         @keydown.escape.window="notifiedModalOpen = false"
         class="fixed inset-0 z-50 bg-black/45 flex items-center justify-center p-4">

        <div class="w-[500px] max-w-[94vw] bg-white rounded-[10px] shadow-[0_8px_32px_rgba(0,0,0,0.24)] px-7 py-6 text-[#1d2d35]">
            <h2 class="text-[21px] font-black text-[#1d2d35] tracking-[-0.015em] mb-3.5">
                Who’s being notified?
            </h2>

            <div class="divide-y divide-[#ece9e2] border-b border-[#ece9e2] text-[15px]">
                @if($project->id == 4)
                    <!-- 1. Ajath Infotech -->
                    <div class="py-2.5 flex items-center gap-3">
                        <span class="w-[32px] h-[32px] rounded-full bg-[#2bb673] text-white font-bold text-[12px] inline-flex items-center justify-center flex-shrink-0 select-none">AI</span>
                        <div class="truncate">
                            <span class="font-bold text-[#1d2d35]">Ajath Infotech</span>
                        </div>
                    </div>

                    <!-- 2. Gaurav Khulbey -->
                    <div class="py-2.5 flex items-center gap-3">
                        <span class="w-[32px] h-[32px] rounded-full bg-[#d946ef] text-white font-bold text-[12px] inline-flex items-center justify-center flex-shrink-0 select-none">GK</span>
                        <div class="truncate">
                            <span class="font-bold text-[#1d2d35]">Gaurav Khulbey</span><span class="text-[#1d2d35]">, Cybersecurity Expert</span>
                        </div>
                    </div>

                    <!-- 3. Pawandeep Kaur -->
                    <div class="py-2.5 flex items-center gap-3">
                        <span class="w-[32px] h-[32px] rounded-full bg-[#0d9488] text-white font-bold text-[12px] inline-flex items-center justify-center flex-shrink-0 select-none">PK</span>
                        <div class="truncate">
                            <span class="font-bold text-[#1d2d35]">Pawandeep Kaur</span><span class="text-[#1d2d35]">, Manager</span>
                        </div>
                    </div>

                    <!-- 4. Prashant -->
                    <div class="py-2.5 flex items-center gap-3">
                        <img src="/uploads/avatars/22369668.png" alt="Prashant" class="w-[32px] h-[32px] rounded-full object-cover flex-shrink-0">
                        <div class="truncate">
                            <span class="font-bold text-[#1d2d35]">Prashant</span><span class="text-[#1d2d35]">, Analyst</span>
                        </div>
                    </div>

                    <!-- 5. Rajesh Kumar -->
                    <div class="py-2.5 flex items-center gap-3">
                        <span class="w-[32px] h-[32px] rounded-full bg-[#ec4899] text-white font-bold text-[12px] inline-flex items-center justify-center flex-shrink-0 select-none">RK</span>
                        <div class="truncate">
                            <span class="font-bold text-[#1d2d35]">Rajesh Kumar</span><span class="text-[#1d2d35]">, Project Lead</span>
                        </div>
                    </div>

                    <!-- 6. Shachish Sneh -->
                    <div class="py-2.5 flex items-center gap-3">
                        <img src="/uploads/avatars/25036340.png" alt="Shachish Sneh" class="w-[32px] h-[32px] rounded-full object-cover flex-shrink-0">
                        <div class="truncate">
                            <span class="font-bold text-[#1d2d35]">Shachish Sneh</span><span class="text-[#1d2d35]">, Solution Architect &amp; Technology M...</span>
                        </div>
                    </div>

                    <!-- 7. Shiv Narayan -->
                    <div class="py-2.5 flex items-center gap-3">
                        <span class="w-[32px] h-[32px] rounded-full bg-[#9333ea] text-white font-bold text-[12px] inline-flex items-center justify-center flex-shrink-0 select-none">SN</span>
                        <div class="truncate">
                            <span class="font-bold text-[#1d2d35]">Shiv Narayan</span>
                        </div>
                    </div>
                @else
                    @forelse($project->members as $member)
                        @php
                            $mParts = preg_split('/\s+/', trim($member->name));
                            $mInitials = strtoupper(substr($mParts[0] ?? 'U', 0, 1) . (isset($mParts[1]) ? substr($mParts[1], 0, 1) : ''));
                            $mColor = $badgePalette[$mInitials] ?? $fallbackColors[$member->id % count($fallbackColors)];
                        @endphp
                        <div class="py-2.5 flex items-center gap-3">
                            @if($member->avatar_url && !str_contains($member->avatar_url, 'dicebear'))
                                <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" class="w-[32px] h-[32px] rounded-full object-cover flex-shrink-0">
                            @else
                                <span class="w-[32px] h-[32px] rounded-full text-white font-bold text-[12px] inline-flex items-center justify-center flex-shrink-0 select-none" style="background-color: {{ $mColor }}">{{ $mInitials }}</span>
                            @endif
                            <div class="truncate">
                                <span class="font-bold text-[#1d2d35]">{{ $member->name }}</span>@if($member->role_title)<span class="text-[#1d2d35]">, {{ $member->role_title }}</span>@endif
                            </div>
                        </div>
                    @empty
                        <div class="py-2.5 flex items-center gap-3">
                            <span class="w-[32px] h-[32px] rounded-full bg-[#1b6ac9] text-white font-bold text-[12px] inline-flex items-center justify-center flex-shrink-0 select-none">M</span>
                            <div class="truncate">
                                <span class="font-bold text-[#1d2d35]">{{ auth()->user()->name ?? 'Manjot Singh' }}</span>
                            </div>
                        </div>
                    @endforelse
                @endif
            </div>

            <div class="mt-5">
                <button type="button"
                        @click="notifiedModalOpen = false"
                        class="bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-semibold text-[14px] px-4 py-2 rounded-[6px] transition cursor-pointer">
                    Done
                </button>
            </div>
        </div>
    </div>

    <!-- Exact Basecamp Full-Screen White Image Lightbox Matching media_1793334184550_f79ff56d.jpg -->
    <style>
        @keyframes bcLightboxFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes bcLightboxFadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        @keyframes bcLightboxZoomIn {
            from { opacity: 0; transform: scale(0.84); }
            to { opacity: 1; transform: scale(1); }
        }
        @keyframes bcLightboxZoomOut {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(0.84); }
        }
        .bc-lightbox-backdrop-enter {
            animation: bcLightboxFadeIn 220ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .bc-lightbox-backdrop-leave {
            animation: bcLightboxFadeOut 200ms cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        .bc-lightbox-content-enter {
            animation: bcLightboxZoomIn 220ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .bc-lightbox-content-leave {
            animation: bcLightboxZoomOut 200ms cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
    </style>
    <div x-show="lightboxOpen || lightboxSrc"
         x-cloak
         @click="closeLightbox()"
         @keydown.escape.window="closeLightbox()"
         :class="lightboxClosing ? 'bc-lightbox-backdrop-leave' : 'bc-lightbox-backdrop-enter'"
         class="fixed inset-0 z-50 bg-white flex flex-col items-center justify-center p-6 cursor-zoom-out">

        <!-- Top-Right Close X -->
        <button type="button"
                @click.stop="closeLightbox()"
                class="absolute top-5 right-6 text-[#1d2d35] hover:opacity-70 text-[26px] leading-none font-normal cursor-pointer"
                aria-label="Close">
            ✕
        </button>

        <!-- Center High-Res Image or Embedded PDF Viewer (Matching media_1793336294446_ba623fa8.jpg) -->
        <div :class="lightboxClosing ? 'bc-lightbox-content-leave' : 'bc-lightbox-content-enter'"
             class="flex flex-col items-center justify-center w-full">
            <template x-if="lightboxName && lightboxName.toLowerCase().endsWith('.pdf')">
                <iframe :src="lightboxDownloadUrl || lightboxSrc"
                        @click.stop
                        style="width: min(92vw, 1220px); height: 78vh;"
                        class="border-0 bg-[#282828] shadow-[0_2px_12px_rgba(0,0,0,0.16)] cursor-default"></iframe>
            </template>
            <template x-if="!lightboxName || !lightboxName.toLowerCase().endsWith('.pdf')">
                <img :src="lightboxSrc"
                     :alt="lightboxName"
                     style="height: 76vh; max-height: 76vh; width: auto; max-width: 90vw; object-fit: contain;"
                     class="select-none cursor-zoom-out">
            </template>

            <!-- Bottom Caption Matching media_1793336294446_ba623fa8.jpg & media_1793334184550_f79ff56d.jpg -->
            <div class="mt-3.5 text-center cursor-default" @click.stop>
                <div class="text-[13.5px] font-bold text-[#1d2d35]" x-text="'&quot;' + lightboxName + '&quot;'"></div>
                <div class="text-[13px] text-[#6d6a65] mt-0.5 flex items-center justify-center gap-1.5">
                    <span x-text="lightboxName"></span>
                    <span>·</span>
                    <span x-text="lightboxSize"></span>
                    <template x-if="!lightboxName || !lightboxName.toLowerCase().endsWith('.pdf')">
                        <span class="inline-flex items-center gap-1.5">
                            <span>·</span>
                            <a :href="lightboxDownloadUrl || lightboxSrc" target="_blank" class="underline hover:text-[#1d2d35]">View full-size</a>
                        </span>
                    </template>
                    <span>·</span>
                    <a :href="lightboxDownloadUrl || lightboxSrc" :download="lightboxName" class="underline hover:text-[#1d2d35] px-1 py-0.5 rounded-[3px] focus:outline-2 focus:outline-[#1b6ac9]">Download</a>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    window._userScrolledChat = false;
    function scrollCampfireToBottom() {
        const container = document.getElementById('chat-messages');
        if (container && !window._userScrolledChat) {
            container.scrollTop = container.scrollHeight;
        }
    }
    document.addEventListener('DOMContentLoaded', () => {
        scrollCampfireToBottom();
        const container = document.getElementById('chat-messages');
        if (container) {
            container.addEventListener('wheel', (e) => {
                if (e.deltaY < 0) window._userScrolledChat = true;
            }, { passive: true });
            container.addEventListener('touchmove', () => {
                window._userScrolledChat = true;
            }, { passive: true });
        }
    });
    window.addEventListener('load', scrollCampfireToBottom);
</script>
@endsection
