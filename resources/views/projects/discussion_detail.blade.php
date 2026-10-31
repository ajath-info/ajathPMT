@extends('layouts.app')

@section('title', $discussion->title . ' - ' . $project->name)

@section('content')
@php
    $isShubhamOct2021 = ($discussion->id == 6013 || stripos($discussion->title, 'Shubham Work log - October - 2021') !== false);

    // Determine clean author display for general discussions
    $rawContent = trim($discussion->content ?? '');
    $parsedAuthorName = $discussion->author->name ?? 'Team Member';
    $parsedJobTitle = $discussion->author->job_title ?? '';
    $parsedDateStr = $discussion->created_at ? $discussion->created_at->format('M j, Y') : 'Oct 29, 2026';
    $cleanedBody = $rawContent;

    // Many imported discussions start with "Author Name • Oct 01, 2021 at 1:24 PM • ..."
    if (preg_match('/^([^•\n]+)\s*•\s*([A-Za-z]{3}\s+\d{1,2},\s+\d{4})(?:\s+at\s+\d{1,2}:\d{2}\s*[AP]M)?\s*•?\s*(.*)$/is', $rawContent, $m)) {
        if ($parsedAuthorName === 'Ajath Infotech' || empty($discussion->author)) {
            $parsedAuthorName = trim($m[1]);
        }
        $parsedDateStr = preg_replace('/\s+0(\d),/', ' $1,', trim($m[2]));
        $cleanedBody = trim($m[3]);
    }

    if (stripos($parsedAuthorName, 'Shubham') !== false && empty($parsedJobTitle)) {
        $parsedJobTitle = 'Sn. Php developer';
    } elseif (stripos($parsedAuthorName, 'Gaurav') !== false && (empty($parsedJobTitle) || $parsedJobTitle === 'Team Member')) {
        $parsedJobTitle = 'Cybersecurity Expert at Ajath Infotech Pvt Ltd';
    } elseif (empty($parsedJobTitle) || $parsedJobTitle === 'Ajath Infotech Pvt Ltd') {
        $parsedJobTitle = 'Member at Ajath Infotech Pvt Ltd';
    }

    // Determine avatar badge color & initials
    $nameWords = preg_split('/\s+/', trim($parsedAuthorName));
    $authorInitials = count($nameWords) >= 2
        ? strtoupper(substr($nameWords[0], 0, 1) . substr(end($nameWords), 0, 1))
        : strtoupper(substr($parsedAuthorName, 0, 1));

    $badgeColors = [
        'S'  => '#15b79e',
        'GK' => '#d946ef',
        'PK' => '#00897b',
        'RK' => '#d81b60',
        'AI' => '#10b981',
        'SN' => '#9333ea',
    ];
    $authorBadgeBg = $badgeColors[$authorInitials] ?? '#15b79e';

    // Split bullet points if body contains "•"
    $bodyLines = [];
    $bodyHeading = null;
    if (str_contains($cleanedBody, '•')) {
        $segments = array_values(array_filter(array_map('trim', explode('•', $cleanedBody))));
        if (!empty($segments)) {
            if (str_ends_with($segments[0], ':')) {
                $bodyHeading = array_shift($segments);
            }
            $bodyLines = $segments;
        }
    }

    $fullEmojiGrid = [
        '🤩','🥳','😀','😃','😅','😂','😎','😉','😋','😬',
        '😮','😳','🤔','😌','😢','😭','😱','👍','👏','👋',
        '🙌','🙏','💪','🤘','✊','👎','✌️','👉','👆','✋',
        '🎉','✨','❤️','💯','👀','💩','☀️','🌙','💥','🔥',
        '🎂','🍴','💰','🏅','🚨','💡','🔧','📈','✅','📢',
    ];

    $parsedShortDateStr = preg_replace('/,\s*\d{4}$/', '', $parsedDateStr);
    $currentUserAvatar = ($user->avatar_url && !str_contains($user->avatar_url, 'dicebear') && !str_contains($user->avatar_url, 'avatar_1_1793286396'))
        ? $user->avatar_url
        : '/uploads/avatars/22369668.png';

    $formatMentions = function (string $text): string {
        $escaped = e($text);
        return preg_replace_callback(
            '/@([A-Za-z][A-Za-z0-9_]*(?:\s+(?:Sneh|Singh|Khulbey|Kaur|Kumar|Narayan|Verma|Aneja|Shree|Garg|Banger))?)/',
            function ($matches) {
                $name = trim($matches[1]);
                $lower = strtolower($name);
                $avatarHtml = '';
                if (str_contains($lower, 'shachish')) {
                    $avatarHtml = '<img src="/uploads/avatars/25036340.png" alt="' . e($name) . '" class="w-[18px] h-[18px] rounded-full object-cover inline-block flex-shrink-0">';
                } elseif (str_contains($lower, 'manjot')) {
                    $avatarHtml = '<img src="/uploads/avatars/22369668.png" alt="' . e($name) . '" class="w-[18px] h-[18px] rounded-full object-cover inline-block flex-shrink-0">';
                } elseif (str_contains($lower, 'prashant') || str_contains($lower, 'nikhil')) {
                    $avatarHtml = '<img src="/uploads/avatars/22369670.png" alt="' . e($name) . '" class="w-[18px] h-[18px] rounded-full object-cover inline-block flex-shrink-0">';
                } else {
                    $words = preg_split('/\s+/', $name);
                    $inits = count($words) >= 2
                        ? strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1))
                        : strtoupper(substr($name, 0, 2));
                    $bg = '#ea580c';
                    if (str_contains($lower, 'gaurav')) $bg = '#d946ef';
                    elseif (str_contains($lower, 'pawandeep')) $bg = '#00897b';
                    elseif (str_contains($lower, 'rajesh')) $bg = '#d81b60';
                    elseif (str_contains($lower, 'shiv')) $bg = '#9333ea';
                    $avatarHtml = '<span class="w-[18px] h-[18px] rounded-full text-white font-bold text-[8.5px] inline-flex items-center justify-center flex-shrink-0 leading-none" style="background-color:' . $bg . '">' . $inits . '</span>';
                }
                return '<span class="inline-flex items-center gap-1 align-baseline font-medium text-[#1d2d35] whitespace-nowrap">' . $avatarHtml . '<span>' . e($name) . '</span></span>';
            },
            $escaped
        );
    };
@endphp

<div x-data="{
         commenting: {{ $isShubhamOct2021 ? 'false' : 'true' }},
         notifyMuted: false,
         editSubscribersOpen: (new URLSearchParams(window.location.search)).get('subscription') === 'edit',
         notifyNewMode: 'next_comment',
         savedSubscribersSnapshot: null,
         subscribers: JSON.parse(localStorage.getItem('bc_disc_subs_{{ $discussion->id }}') || 'null') || [
             { id: 1, name: 'Manjot Singh', role: 'Global VP & Partner', initials: 'M', bg: 'bg-[#1d4ed8]', avatar: '/uploads/avatars/22369668.png', isOoo: true, active: true },
             { id: 60, name: 'Shachish Sneh', role: 'Solution Architect & Technology Master', initials: 'SS', bg: 'bg-[#4f46e5]', avatar: '/uploads/avatars/25036340.png', isOoo: false, active: true },
             { id: 2, name: 'Pawandeep Kaur', role: 'Manager', initials: 'PK', bg: 'bg-[#00897b]', avatar: null, isOoo: false, active: true },
             { id: 3, name: 'Prashant', role: 'Developer', initials: 'P', bg: 'bg-[#0284c7]', avatar: '/uploads/avatars/22369670.png', isOoo: false, active: true },
             { id: 5, name: 'Rajesh Kumar', role: 'Developer', initials: 'RK', bg: 'bg-[#d81b60]', avatar: null, isOoo: false, active: true },
             { id: 4, name: 'Gaurav Khulbey', role: 'Cybersecurity Expert', initials: 'GK', bg: 'bg-[#d946ef]', avatar: null, isOoo: false, active: false },
             { id: 62, name: 'Shiv Narayan', role: 'Developer', initials: 'SN', bg: 'bg-[#9333ea]', avatar: null, isOoo: false, active: false }
         ],
         openSubscribersEditor() {
             this.savedSubscribersSnapshot = JSON.parse(JSON.stringify(this.subscribers));
             this.editSubscribersOpen = true;
             window.scrollTo({ top: 0, behavior: 'smooth' });
         },
         cancelSubscribersEditor() {
             if (this.savedSubscribersSnapshot) {
                 this.subscribers = JSON.parse(JSON.stringify(this.savedSubscribersSnapshot));
             }
             this.editSubscribersOpen = false;
         },
         saveSubscribersEditor() {
             localStorage.setItem('bc_disc_subs_{{ $discussion->id }}', JSON.stringify(this.subscribers));
             const me = this.subscribers.find(s => s.id === 1);
             if (me) this.notifyMuted = !me.active;
             if (this.notifyNewMode === 'immediately' && window.bcPushNotification) {
                 const activeCount = this.subscribers.filter(s => s.active).length;
                 window.bcPushNotification({
                     title: @js($discussion->title),
                     project: @js($project->name),
                     author: @js($user->name),
                     initials: 'MS',
                     badgeBg: '#1d4ed8',
                     type: 'Notified ' + activeCount + ' people right now',
                     url: window.location.pathname
                 });
             }
             this.editSubscribersOpen = false;
             this.showMsgToast('Notification subscribers updated');
         },
         moreMenuOpen: false,
         msgToast: '',
         msgBoostOpen: false,
         msgMoreEmojiOpen: false,
         msgCustomInputOpen: false,
         msgCustomText: '',
         msgBoosts: JSON.parse(localStorage.getItem('bc_msg_boosts_{{ $discussion->id }}') || '[]'),
         commentBoostOpen: {},
         commentMoreEmojiOpen: {},
         commentCustomInputOpen: {},
         commentCustomText: {},
         commentBoosts: JSON.parse(localStorage.getItem('bc_msg_cboosts_{{ $discussion->id }}') || '{}'),
         showMsgToast(t) {
             this.msgToast = t;
             setTimeout(() => { this.msgToast = ''; }, 2400);
         },
         addMsgBoost(emoji) {
             if (!emoji || !emoji.trim()) return;
             this.msgBoosts.push(emoji.trim());
             localStorage.setItem('bc_msg_boosts_{{ $discussion->id }}', JSON.stringify(this.msgBoosts));
             this.msgBoostOpen = false;
             this.msgMoreEmojiOpen = false;
             this.msgCustomInputOpen = false;
             this.msgCustomText = '';
         },
         removeMsgBoost(idx) {
             this.msgBoosts.splice(idx, 1);
             localStorage.setItem('bc_msg_boosts_{{ $discussion->id }}', JSON.stringify(this.msgBoosts));
         },
         addCommentBoost(cId, emoji) {
             if (!emoji || !emoji.trim()) return;
             if (!this.commentBoosts[cId]) this.commentBoosts[cId] = [];
             this.commentBoosts[cId].push(emoji.trim());
             localStorage.setItem('bc_msg_cboosts_{{ $discussion->id }}', JSON.stringify(this.commentBoosts));
             this.commentBoostOpen[cId] = false;
             this.commentMoreEmojiOpen[cId] = false;
             this.commentCustomInputOpen[cId] = false;
             this.commentCustomText[cId] = '';
         },
         removeCommentBoost(cId, idx) {
             if (!this.commentBoosts[cId]) return;
             this.commentBoosts[cId].splice(idx, 1);
             localStorage.setItem('bc_msg_cboosts_{{ $discussion->id }}', JSON.stringify(this.commentBoosts));
         }
     }"
     class="max-w-[880px] mx-auto pb-16 relative">

    <div x-show="msgToast"
         x-cloak
         x-transition.opacity
         class="fixed top-14 left-1/2 -translate-x-1/2 z-50 px-4 py-2 rounded-full bg-[#1d2d35] text-white text-[13px] font-semibold shadow-lg">
        <span x-text="msgToast"></span>
    </div>

    <!-- Basecamp White Paper Sheet (Exact Match to media_1793268370293_23fa70f0.jpg) -->
    <div class="bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-84px)] overflow-visible">

        <!-- Sticky Top Sub-Header Bar: Project ‹ Message Board (‹ Discussion when editing subscription) | Bookmark  Edit  ••• -->
        <div class="px-5 py-2 border-b border-[#ece9e2] flex items-center justify-between text-[13.5px] bg-white sticky top-0 z-10">
            <div class="flex items-center gap-1.5 truncate">
                <a href="{{ route('projects.show', $project->id) }}" class="font-bold text-[#1d2d35] underline decoration-[#8c8881] hover:text-black">
                    {{ $project->name }}
                </a>
                <span class="text-[#6d6a65] text-[12px]">‹</span>
                <a href="{{ route('projects.discussions', $project->id) }}" class="text-[#1d2d35] underline decoration-[#8c8881] hover:text-black">
                    Message Board
                </a>
                <template x-if="editSubscribersOpen">
                    <span class="inline-flex items-center gap-1.5 truncate">
                        <span class="text-[#6d6a65] text-[12px]">‹</span>
                        <button type="button"
                                @click="cancelSubscribersEditor()"
                                class="inline-flex items-center gap-1 text-[#1d2d35] underline decoration-[#8c8881] hover:text-black cursor-pointer truncate">
                            <span>📢</span>
                            <span class="truncate">{{ $discussion->title }}</span>
                        </button>
                    </span>
                </template>
            </div>

            <div x-show="!editSubscribersOpen" class="flex items-center gap-4 text-[13px] text-[#1d2d35] flex-shrink-0">
                <button type="button" onclick="window.toggleBookmark && window.toggleBookmark(this)" class="inline-flex items-center gap-1 text-[#1d2d35] hover:underline cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#4a4640]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                    <span data-bookmark-label>Bookmark</span>
                </button>
                <button type="button" @click="commenting = true" class="text-[#1d2d35] hover:underline cursor-pointer">
                    Edit
                </button>
                <div class="relative inline-block">
                    <button type="button"
                            @click="moreMenuOpen = !moreMenuOpen"
                            :class="moreMenuOpen ? 'bg-[#e5e2dc]' : 'hover:bg-slate-100'"
                            class="w-7 h-7 rounded-full border border-[#d6d2c9] inline-flex items-center justify-center text-[#1d2d35] transition-colors cursor-pointer"
                            title="More options">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                        </svg>
                    </button>

                    <div x-show="moreMenuOpen"
                         x-cloak
                         @click.outside="moreMenuOpen = false"
                         class="absolute right-0 mt-1.5 w-[224px] bg-white rounded-[10px] border border-[#e5e2dc] shadow-[0_8px_28px_rgba(0,0,0,0.12)] py-2 z-50 text-[13.5px] text-[#1d2d35]">
                        <button type="button" @click="moreMenuOpen = false; showMsgToast('Spotlighted on project page')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span>Spotlight on project page</span>
                        </button>
                        <button type="button" @click="moreMenuOpen = false; showMsgToast('Bubbled up to the top')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <svg class="w-4 h-4 text-[#6d6a65] shrink-0 fill-current" viewBox="0 0 24 24">
                                <circle cx="9" cy="14" r="4"/>
                                <circle cx="16" cy="9" r="3"/>
                                <circle cx="10.5" cy="5" r="2"/>
                            </svg>
                            <span>Bubble up</span>
                        </button>
                        <button type="button" @click="navigator.clipboard && navigator.clipboard.writeText(window.location.href); moreMenuOpen = false; showMsgToast('Link copied to clipboard')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <svg class="w-4 h-4 text-[#6d6a65] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            <span>Copy link</span>
                        </button>

                        <div class="my-1.5 border-t border-[#ece9e2]"></div>

                        <button type="button" @click="moreMenuOpen = false; showMsgToast('Viewing change log')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span>View change log</span>
                        </button>
                        <button type="button" @click="moreMenuOpen = false; showMsgToast('Notified 7 people')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span>Notified 7 people</span>
                        </button>

                        <div class="my-1.5 border-t border-[#ece9e2]"></div>

                        <button type="button" @click="moreMenuOpen = false; showMsgToast('Copied to clipboard')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span>Copy...</span>
                        </button>
                        <button type="button" @click="moreMenuOpen = false; showMsgToast('Select destination project to move')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span>Move...</span>
                        </button>
                        <button type="button" @click="moreMenuOpen = false; showMsgToast('Comments closed')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span>Close comments</span>
                        </button>
                        <button type="button" @click="moreMenuOpen = false; showMsgToast('Archived')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span>Archive</span>
                        </button>
                        <button type="button" @click="moreMenuOpen = false; showMsgToast('Moved to trash')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 text-[#1d2d35] cursor-pointer">
                            <svg class="w-4 h-4 text-[#6d6a65] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Put in the trash</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================ -->
        <!-- FULL-SHEET SUBSCRIPTION EDIT VIEW (Exact Match to media_1793345640699_7a96721a.png) -->
        <!-- ================================================================ -->
        <div x-show="editSubscribersOpen"
             x-cloak
             class="px-8 sm:px-14 pt-10 pb-20">
            <div class="max-w-[660px] mx-auto flex flex-col sm:flex-row items-start gap-8">
                <!-- Left Column: Miniature Message Preview Card (w-[188px]) -->
                <div class="w-full sm:w-[188px] flex-shrink-0 rounded-[8px] border border-[#e5e2dc] bg-white p-3.5 text-[10.5px] text-[#1d2d35] leading-[1.35] shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
                    <div class="font-bold text-[11.5px] text-[#1d2d35] leading-snug">
                        {{ $discussion->title }}
                    </div>
                    <div class="mt-1 text-[10px] text-[#1d2d35] leading-snug flex flex-wrap items-center gap-x-1 gap-y-0.5">
                        <span class="relative inline-flex w-[14px] h-[14px] rounded-full overflow-hidden flex-shrink-0">
                            <img src="{{ $currentUserAvatar }}" alt="{{ $parsedAuthorName }}" class="w-full h-full object-cover">
                            <span class="absolute bottom-0 inset-x-0 h-[38%] bg-[#facc15] flex items-center justify-center">
                                <span class="text-[3.5px] font-black text-[#1d1c1b] leading-none">OUT</span>
                            </span>
                        </span>
                        <span>{{ $parsedAuthorName }}</span>
                        <span>•</span>
                        <span>📢 {{ $discussion->category ?: 'Announcement' }}</span>
                        <span>• {{ $parsedShortDateStr }}</span>
                    </div>

                    @if($bodyHeading)
                        <div class="mt-2.5 text-[10.5px] text-[#1d2d35] leading-snug">
                            {{ rtrim($bodyHeading, ' :') }}
                        </div>
                    @endif

                    @if(!empty($bodyLines))
                        <ul class="mt-2.5 pl-3.5 list-disc space-y-1.5 text-[10px] text-[#1d2d35] leading-[1.35]">
                            @foreach(array_slice($bodyLines, 0, 4) as $bLine)
                                <li>{!! $formatMentions($bLine) !!}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="mt-2.5 text-[10px] text-[#1d2d35] leading-[1.35] line-clamp-6">
                            {!! $formatMentions($cleanedBody) !!}
                        </div>
                    @endif
                </div>

                <!-- Right Column: Who should be notified when someone comments on this? -->
                <div class="flex-1 min-w-0">
                    <h1 class="text-[24px] sm:text-[26px] font-extrabold text-[#1d2d35] tracking-[-0.02em] leading-[1.18]">
                        Who should be notified when someone comments on this?
                    </h1>

                    <div class="mt-2 text-[14px] text-[#1d2d35]">
                        <button type="button"
                                @click="subscribers.forEach(s => s.active = true)"
                                class="text-[#1b6ac9] underline hover:text-[#1557a8] cursor-pointer">Select everyone</button>
                        <span class="mx-1 text-[#1d2d35]">•</span>
                        <button type="button"
                                @click="subscribers.forEach(s => s.active = false)"
                                class="text-[#1b6ac9] underline hover:text-[#1557a8] cursor-pointer">Select no one</button>
                    </div>

                    <div class="mt-5 mb-1.5 text-[14.5px] font-bold text-[#1d2d35]">
                        Ajath Infotech Pvt Ltd
                    </div>

                    <div class="divide-y divide-[#ece9e2] border-b border-[#ece9e2]">
                        <template x-for="sub in subscribers" :key="'sub-row-' + sub.id">
                            <div @click="sub.active = !sub.active"
                                 class="py-2.5 flex items-center justify-between gap-3 cursor-pointer select-none hover:bg-[#faf9f6] -mx-1.5 px-1.5 rounded-[6px] transition-colors">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="relative w-[32px] h-[32px] rounded-full overflow-hidden flex-shrink-0 inline-flex items-center justify-center">
                                        <template x-if="sub.avatar">
                                            <img :src="sub.avatar" :alt="sub.name" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!sub.avatar">
                                            <span class="w-full h-full text-white font-bold text-[12px] flex items-center justify-center" :class="sub.bg" x-text="sub.initials"></span>
                                        </template>
                                        <span x-show="sub.isOoo && localStorage.getItem('bc_ooo_active') !== '0'" class="bc-ooo-badge absolute bottom-0 inset-x-0 h-[36%] bg-[#facc15] flex items-center justify-center pointer-events-none">
                                            <span class="text-[6px] font-black text-[#1d1c1b] leading-none tracking-tight">OUT</span>
                                        </span>
                                    </span>

                                    <div class="text-[14.5px] text-[#1d2d35] leading-snug truncate">
                                        <span class="font-bold" x-text="sub.name"></span><span x-show="sub.role" x-text="', ' + sub.role"></span>
                                    </div>
                                </div>

                                <!-- Basecamp Green Rounded Square Checkbox -->
                                <span class="w-[18px] h-[18px] rounded-[4px] flex items-center justify-center flex-shrink-0 transition-colors"
                                      :class="sub.active ? 'bg-[#2da562] border border-[#2da562] text-white' : 'bg-white border border-[#b8b4ac]'">
                                    <svg x-show="sub.active" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </span>
                            </div>
                        </template>
                    </div>

                    <!-- Radio Options Below Member List -->
                    <div class="mt-5 space-y-2 text-[14px] text-[#1d2d35]">
                        <label @click="notifyNewMode = 'next_comment'" class="flex items-center gap-2.5 cursor-pointer select-none">
                            <span class="w-[16px] h-[16px] rounded-full flex items-center justify-center flex-shrink-0"
                                  :class="notifyNewMode === 'next_comment' ? 'border-[5px] border-[#1d2d35] bg-white' : 'border border-[#b8b4ac] bg-white'"></span>
                            <span><strong class="font-bold">Don't notify new people</strong> until the next comment is posted</span>
                        </label>

                        <label @click="notifyNewMode = 'immediately'" class="flex items-center gap-2.5 cursor-pointer select-none">
                            <span class="w-[16px] h-[16px] rounded-full flex items-center justify-center flex-shrink-0"
                                  :class="notifyNewMode === 'immediately' ? 'border-[5px] border-[#1d2d35] bg-white' : 'border border-[#b8b4ac] bg-white'"></span>
                            <span><strong class="font-bold">Send new people the message</strong> with all comments right now</span>
                        </label>
                    </div>

                    <!-- Save / Never mind Action Buttons -->
                    <div class="mt-6 flex items-center gap-2">
                        <button type="button"
                                @click="saveSubscribersEditor()"
                                class="px-4 py-1.5 rounded-[6px] bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-semibold text-[14px] transition-colors cursor-pointer">
                            Save
                        </button>
                        <button type="button"
                                @click="cancelSubscribersEditor()"
                                class="px-4 py-1.5 rounded-[6px] border border-[#93c5fd] bg-white hover:bg-[#eff6ff] text-[#1b6ac9] text-[14px] transition-colors cursor-pointer">
                            Never mind
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Body Container -->
        <div x-show="!editSubscribersOpen" class="px-8 sm:px-12 pt-3 pb-16">

            @if($isShubhamOct2021)
                <!-- ================================================================ -->
                <!-- EXACT MATCH TO media_1793268370293_23fa70f0.jpg (#__recording_4388603795) -->
                <!-- ================================================================ -->
                <div id="__recording_4388603795" class="flex items-start gap-3.5">
                    <!-- Left Green 'S' Avatar Circle (44x44px) -->
                    <button type="button"
                            onclick="window.openUserProfileModal && window.openUserProfileModal({ id: {{ $discussion->author_id ?: 1 }}, name: 'Shubham', initials: 'S', color: '#15b79e', role: 'Member', title: 'Sn. Php developer at Ajath Infotech Pvt Ltd' })"
                            class="w-[44px] h-[44px] rounded-full bg-[#15b79e] text-white font-bold text-[20px] flex items-center justify-center flex-shrink-0 mt-0.5 select-none cursor-pointer hover:opacity-90 transition-opacity">
                        S
                    </button>

                    <!-- Right Content Column -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <div class="text-[16px] text-[#1d2d35] leading-snug">
                                <span class="font-bold">Shubham,</span>
                                <span class="font-normal">Sn. Php developer • Nov 25, 2021</span>
                            </div>
                            <button type="button" class="text-[#1d2d35] hover:bg-slate-100 rounded p-0.5 cursor-pointer" title="Comment options">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                                </svg>
                            </button>
                        </div>

                        <p class="text-[16px] font-bold text-[#1d2d35] mt-1 leading-snug">
                            Work Log 25 Nov 2021 :
                        </p>

                        <ul class="mt-2.5 pl-6 list-disc space-y-1.5 text-[16px] text-[#1d2d35] leading-[1.45]">
                            <li>Working in mayadin superadmin Panel</li>
                            <li>Superadmin can edit password of vendors : <strong class="font-bold">DONE</strong></li>
                            <li>Working on fourmias poject (website is in progress)</li>
                            <li>Analysing the coding structure and flow , and how to implement data from api.</li>
                        </ul>
                    </div>
                </div>
            @else
                <!-- Standard Basecamp Message Board Entry View (Exact match to media_1793328896802_8ceeb03d.png) -->
                <div class="pt-4">
                    <h1 class="text-[28px] sm:text-[31px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-[1.15]">
                        {{ $discussion->title }}
                    </h1>
                    <p class="text-[15.5px] text-[#1d2d35] mt-1 mb-6 leading-snug">
                        {{ $parsedAuthorName }} on {{ $parsedShortDateStr }} • Notified 7 people
                    </p>

                    <div class="flex items-start gap-3.5">
                        <button type="button"
                                onclick="window.openUserProfileModal && window.openUserProfileModal({ id: {{ $discussion->author_id ?: 1 }}, name: '{{ addslashes($parsedAuthorName) }}', initials: '{{ $authorInitials }}', color: '{{ $authorBadgeBg }}', role: 'Member', title: '{{ addslashes($parsedJobTitle) }}' })"
                                class="w-[44px] h-[44px] rounded-full text-white font-bold text-[18px] flex items-center justify-center flex-shrink-0 mt-0.5 select-none cursor-pointer hover:opacity-90 transition-opacity"
                                style="background-color: {{ $authorBadgeBg }}">
                            {{ $authorInitials }}
                        </button>

                        <div class="flex-1 min-w-0">
                            @if($bodyHeading)
                                <p class="text-[16.5px] font-bold text-[#1d2d35] leading-snug">
                                    {{ $bodyHeading }}
                                </p>
                            @endif

                            @if(!empty($bodyLines))
                                <ul class="pl-6 list-disc space-y-1.5 text-[16.5px] text-[#1d2d35] leading-[1.45]">
                                    @foreach($bodyLines as $bLine)
                                        <li>{!! $formatMentions($bLine) !!}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-[16.5px] text-[#1d2d35] leading-[1.45] whitespace-pre-line">{!! $formatMentions($cleanedBody) !!}</div>
                            @endif

                            <!-- Basecamp Boost Rocket Row + Emoji Pill + 10x5 Full Emoji Grid Popover (Matches media_1793328896802 - 8905134) -->
                            <div class="mt-4 flex flex-wrap items-center gap-2 relative">
                                <template x-for="(bEmoji, bIdx) in msgBoosts" :key="bIdx">
                                    <button type="button"
                                            @click="removeMsgBoost(bIdx)"
                                            title="Click to remove boost"
                                            class="inline-flex items-center gap-1.5 pl-1 pr-2.5 py-0.5 rounded-full bg-[#f5f1ea] hover:bg-[#ebe5da] text-[14px] text-[#1d2d35] transition-colors cursor-pointer">
                                        <span class="relative w-5 h-5 rounded-full overflow-hidden flex-shrink-0">
                                            <img src="{{ $currentUserAvatar }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                        </span>
                                        <span x-text="bEmoji"></span>
                                    </button>
                                </template>

                                <button type="button"
                                        x-show="!msgBoostOpen"
                                        @click="msgBoostOpen = true; msgMoreEmojiOpen = false; msgCustomInputOpen = false"
                                        class="w-[28px] h-[28px] rounded-full bg-[#f5f1ea] hover:bg-[#ebe5da] inline-flex items-center justify-center text-[14px] transition-colors cursor-pointer"
                                        title="Give a boost">
                                    🚀
                                </button>

                                <div x-show="msgBoostOpen"
                                     x-cloak
                                     @click.outside="msgBoostOpen = false; msgMoreEmojiOpen = false; msgCustomInputOpen = false"
                                     class="relative inline-flex items-center gap-2.5 px-2.5 py-1 rounded-full bg-[#f5f1ea] text-[16px]">
                                    <!-- Leftmost [I Text-Cursor Boost Icon + Floating Blue Badge Underneath (media_1793328900880_fb98299c.png) -->
                                    <div class="relative inline-flex items-center">
                                        <button type="button"
                                                @click="msgCustomInputOpen = !msgCustomInputOpen; msgMoreEmojiOpen = false; if(msgCustomInputOpen) $nextTick(() => $refs.msgBoostInput && $refs.msgBoostInput.focus())"
                                                class="text-[#1d2d35] hover:opacity-75 inline-flex items-center justify-center w-5 h-5 cursor-pointer"
                                                title="Type a boost">
                                            <svg class="w-[17px] h-[17px]" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 5H5.5A2.5 2.5 0 003 7.5v5A2.5 2.5 0 005.5 15H10"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 4v12M12 4h4M12 16h4"/>
                                            </svg>
                                        </button>

                                        <!-- Floating Blue Indicator Pill Below [I when quick bar is open (media_1793328900880_fb98299c.png) -->
                                        <button type="button"
                                                x-show="!msgMoreEmojiOpen && !msgCustomInputOpen"
                                                @click="msgCustomInputOpen = true; $nextTick(() => $refs.msgBoostInput && $refs.msgBoostInput.focus())"
                                                class="absolute -left-1 top-[calc(100%+4px)] w-[26px] h-[18px] rounded-full bg-[#1967d2] text-white inline-flex items-center justify-center shadow-xs cursor-pointer"
                                                title="Type a boost">
                                            <svg class="w-3 h-3" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5L8 3l5 4.5V13a1 1 0 01-1 1H4a1 1 0 01-1-1V7.5z"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Custom Boost Text Input Mode -->
                                    <form x-show="msgCustomInputOpen"
                                          x-cloak
                                          @submit.prevent="addMsgBoost(msgCustomText)"
                                          class="inline-flex items-center gap-1.5">
                                        <input type="text"
                                               x-ref="msgBoostInput"
                                               x-model="msgCustomText"
                                               maxlength="16"
                                               placeholder="Boost..."
                                               style="border:0 !important;box-shadow:none !important;"
                                               class="w-[120px] bg-white rounded-full px-2.5 py-0.5 text-[13px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none">
                                        <button type="submit" class="w-6 h-6 rounded-full bg-white hover:bg-slate-50 inline-flex items-center justify-center text-[13px] cursor-pointer" title="Send boost">🚀</button>
                                    </form>

                                    <!-- 7 Quick Emojis -->
                                    <div x-show="!msgCustomInputOpen" class="inline-flex items-center gap-2.5">
                                        @foreach(['👍', '👏', '👋', '❤️', '😂', '😊', '🎉'] as $em)
                                            <button type="button" @click="addMsgBoost('{{ $em }}')" class="hover:scale-125 transition-transform cursor-pointer leading-none">{{ $em }}</button>
                                        @endforeach
                                        <button type="button"
                                                @click="msgMoreEmojiOpen = !msgMoreEmojiOpen"
                                                :class="msgMoreEmojiOpen ? 'bg-[#dfd8cc]' : 'hover:bg-[#e8e2d7]'"
                                                class="w-6 h-6 rounded-full inline-flex items-center justify-center text-[12px] text-[#1d2d35] font-bold tracking-widest cursor-pointer transition-colors">
                                            •••
                                        </button>
                                    </div>

                                    <!-- 10x5 Full Emoji Grid Popover (Exact match to media_1793328905134_1d6d23f6.png) -->
                                    <div x-show="msgMoreEmojiOpen"
                                         x-cloak
                                         class="absolute left-[74px] top-[calc(100%+6px)] w-[320px] rounded-[10px] bg-white border border-[#e5e2dc] shadow-[0_6px_24px_rgba(0,0,0,0.14)] px-3.5 py-3 z-50 grid grid-cols-10 gap-x-1.5 gap-y-2 text-[19px]">
                                        @foreach($fullEmojiGrid as $fEm)
                                            <button type="button" @click="addMsgBoost('{{ $fEm }}')" class="hover:scale-125 transition-transform cursor-pointer flex items-center justify-center h-6">{{ $fEm }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Any Newly Added Comments -->
            @if($discussion->comments->isNotEmpty())
                <div class="mt-6 space-y-6 border-t border-[#ece9e2] pt-6">
                    @foreach($discussion->comments as $comment)
                        @php
                            $cName = $comment->author->name ?? 'Team Member';
                            $cWords = preg_split('/\s+/', trim($cName));
                            $cInits = count($cWords) >= 2
                                ? strtoupper(substr($cWords[0], 0, 1) . substr(end($cWords), 0, 1))
                                : strtoupper(substr($cName, 0, 1));
                            $cTitle = $comment->author->job_title ?? 'Member at Ajath Infotech Pvt Ltd';
                        @endphp
                        <div class="flex items-start gap-3.5">
                            @if($comment->author && $comment->author->avatar_url && !str_contains($comment->author->avatar_url, 'dicebear'))
                                <img src="{{ $comment->author->avatar_url }}" alt="{{ $cName }}" class="w-[44px] h-[44px] rounded-full object-cover flex-shrink-0 mt-0.5">
                            @else
                                <span class="w-[44px] h-[44px] rounded-full bg-[#15b79e] text-white font-bold text-[16px] flex items-center justify-center flex-shrink-0 mt-0.5 select-none">
                                    {{ $cInits }}
                                </span>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="text-[16px] text-[#1d2d35] leading-snug">
                                        <span class="font-bold">{{ $cName }},</span>
                                        <span class="font-normal">{{ $cTitle }} • {{ $comment->created_at->format('M j, Y') }}</span>
                                    </div>
                                    <button type="button" class="text-[#1d2d35] hover:bg-slate-100 rounded p-0.5 cursor-pointer">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="mt-1.5 flex items-end justify-between gap-4">
                                    <div class="text-[16px] text-[#1d2d35] leading-[1.45] whitespace-pre-line">{!! $formatMentions($comment->content) !!}</div>

                                    <!-- Comment Reply + Boost Controls -->
                                    <div class="flex items-center gap-1.5 flex-shrink-0 relative">
                                        <template x-for="(cEmoji, cIdx) in (commentBoosts[{{ $comment->id }}] || [])" :key="cIdx">
                                            <button type="button"
                                                    @click="removeCommentBoost({{ $comment->id }}, cIdx)"
                                                    class="inline-flex items-center gap-1.5 pl-1 pr-2.5 py-0.5 rounded-full bg-[#f5f1ea] hover:bg-[#ebe5da] text-[13.5px] text-[#1d2d35] cursor-pointer">
                                                <span class="relative w-4 h-4 rounded-full overflow-hidden flex-shrink-0">
                                                    <img src="{{ $currentUserAvatar }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                                </span>
                                                <span x-text="cEmoji"></span>
                                            </button>
                                        </template>

                                        <button type="button"
                                                x-show="!commentBoostOpen[{{ $comment->id }}]"
                                                @click="commenting = true; $nextTick(() => $refs.commentInput && $refs.commentInput.focus())"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#f5f1ea] hover:bg-[#ebe5da] text-[12.5px] text-[#1d2d35] transition-colors cursor-pointer">
                                            <span>↩</span>
                                            <span>Reply</span>
                                        </button>

                                        <button type="button"
                                                x-show="!commentBoostOpen[{{ $comment->id }}]"
                                                @click="commentBoostOpen[{{ $comment->id }}] = true; commentMoreEmojiOpen[{{ $comment->id }}] = false"
                                                class="w-[28px] h-[28px] rounded-full bg-[#f5f1ea] hover:bg-[#ebe5da] inline-flex items-center justify-center text-[13.5px] transition-colors cursor-pointer"
                                                title="Give a boost">
                                            🚀
                                        </button>

                                        <div x-show="commentBoostOpen[{{ $comment->id }}]"
                                             x-cloak
                                             @click.outside="commentBoostOpen[{{ $comment->id }}] = false; commentMoreEmojiOpen[{{ $comment->id }}] = false"
                                             class="relative inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-[#f5f1ea] text-[15px]">
                                            <button type="button"
                                                    @click="commentBoostOpen[{{ $comment->id }}] = false; commentMoreEmojiOpen[{{ $comment->id }}] = false"
                                                    class="text-[#1d2d35] hover:opacity-75 inline-flex items-center justify-center w-5 h-5 cursor-pointer"
                                                    title="Type a boost">
                                                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 5H5.5A2.5 2.5 0 003 7.5v5A2.5 2.5 0 005.5 15H10"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 4v12M12 4h4M12 16h4"/>
                                                </svg>
                                            </button>
                                            @foreach(['👍', '👏', '👋', '❤️', '😂', '😊', '🎉'] as $em)
                                                <button type="button" @click="addCommentBoost({{ $comment->id }}, '{{ $em }}')" class="hover:scale-125 transition-transform cursor-pointer leading-none">{{ $em }}</button>
                                            @endforeach
                                            <button type="button"
                                                    @click="commentMoreEmojiOpen[{{ $comment->id }}] = !commentMoreEmojiOpen[{{ $comment->id }}]"
                                                    :class="commentMoreEmojiOpen[{{ $comment->id }}] ? 'bg-[#dfd8cc]' : 'hover:bg-[#e8e2d7]'"
                                                    class="w-6 h-6 rounded-full inline-flex items-center justify-center text-[11px] text-[#1d2d35] font-bold tracking-widest cursor-pointer transition-colors">
                                                •••
                                            </button>

                                            <div x-show="commentMoreEmojiOpen[{{ $comment->id }}]"
                                                 x-cloak
                                                 class="absolute right-0 top-[calc(100%+6px)] w-[320px] rounded-[10px] bg-white border border-[#e5e2dc] shadow-[0_6px_24px_rgba(0,0,0,0.14)] px-3.5 py-3 z-50 grid grid-cols-10 gap-x-1.5 gap-y-2 text-[19px]">
                                                @foreach($fullEmojiGrid as $fEm)
                                                    <button type="button" @click="addCommentBoost({{ $comment->id }}, '{{ $fEm }}')" class="hover:scale-125 transition-transform cursor-pointer flex items-center justify-center h-6">{{ $fEm }}</button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Divider Before Add Comment -->
            <div class="border-t border-[#ece9e2] mt-7 pt-6">
                <div class="flex items-start gap-3.5">
                    <!-- Current User Avatar (44x44px) with OUT badge -->
                    <span class="relative w-[44px] h-[44px] rounded-full overflow-hidden flex-shrink-0 mt-0.5 cursor-pointer" title="{{ $user->name }}">
                        <img src="{{ $currentUserAvatar }}"
                             alt="{{ $user->name }}"
                             class="w-full h-full object-cover">
                        <span x-show="localStorage.getItem('bc_ooo_active') !== '0'" class="bc-ooo-badge absolute bottom-0 inset-x-0 h-[36%] bg-[#facc15] flex items-center justify-center pointer-events-none">
                            <span class="text-[7.5px] font-black text-[#1d1c1b] leading-none tracking-tight">OUT</span>
                        </span>
                    </span>

                    <!-- Add your comment... Box / Expandable Rich Text Form (Matches media_1793328896802_8ceeb03d.png) -->
                    <div class="flex-1 min-w-0">
                        <div x-show="!commenting"
                             @click="commenting = true; $nextTick(() => $refs.commentInput.focus())"
                             class="w-full rounded-[8px] border border-[#d8d4cc] bg-white px-4 py-3 text-[16px] text-[#8c8881] cursor-text hover:border-[#b8b4ac] transition-colors">
                            Add your comment...
                        </div>

                        <form x-show="commenting" x-cloak action="{{ route('projects.discussions.comment', [$project->id, $discussion->id]) }}" method="POST" class="space-y-3">
                            @csrf
                            <div class="rounded-[8px] border border-[#d8d4cc] focus-within:border-[#1b6ac9] bg-white overflow-hidden">
                                <!-- Basecamp 17-Icon Monochrome SVG Rich Text Toolbar (Exact match to media_1793328896802_8ceeb03d.png) -->
                                <div class="px-3.5 py-2.5 border-b border-[#ece9e2] flex flex-wrap items-center justify-between gap-2 text-[#1d2d35]">
                                    <div class="flex flex-wrap items-center gap-3.5 text-[#1d2d35]">
                                        <!-- 1. Image -->
                                        <button type="button" class="hover:opacity-70 cursor-pointer inline-flex items-center" title="Insert image">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                                <rect x="3" y="4" width="18" height="16" rx="2"/>
                                                <circle cx="8.5" cy="9.5" r="1.5"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 20"/>
                                            </svg>
                                        </button>
                                        <!-- 2. Paperclip Attachment -->
                                        <button type="button" class="hover:opacity-70 cursor-pointer inline-flex items-center" title="Attach file">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                            </svg>
                                        </button>
                                        <span class="h-4 w-px bg-[#e5e2dc]"></span>
                                        <!-- 3. Bold -->
                                        <button type="button" class="font-extrabold text-[14.5px] hover:opacity-70 cursor-pointer leading-none">B</button>
                                        <!-- 4. Italic -->
                                        <button type="button" class="italic font-serif font-semibold text-[15px] hover:opacity-70 cursor-pointer leading-none">I</button>
                                        <!-- 5. Strikethrough -->
                                        <button type="button" class="line-through font-semibold text-[14.5px] hover:opacity-70 cursor-pointer leading-none">S</button>
                                        <!-- 6. Heading Tt -->
                                        <button type="button" class="font-semibold text-[13.5px] hover:opacity-70 cursor-pointer inline-flex items-center gap-0.5 leading-none">
                                            <span>Tt</span>
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <!-- 7. Color/Highlight Paint Bucket -->
                                        <button type="button" class="hover:opacity-70 cursor-pointer inline-flex items-center gap-0.5" title="Color">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11l-8-8-8.6 8.6a2 2 0 000 2.8l5.2 5.2c.8.8 2 .8 2.8 0L19 11zM5 2l5 5M2 13h15M20 16s2 2.2 2 3.5a2 2 0 11-4 0c0-1.3 2-3.5 2-3.5z"/>
                                            </svg>
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <!-- 8. Link -->
                                        <button type="button" class="hover:opacity-70 cursor-pointer inline-flex items-center" title="Link">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                            </svg>
                                        </button>
                                        <span class="h-4 w-px bg-[#e5e2dc]"></span>
                                        <!-- 9. Quote -->
                                        <button type="button" class="font-serif font-extrabold text-[17px] hover:opacity-70 cursor-pointer leading-none" title="Quote">”</button>
                                        <!-- 10. Code -->
                                        <button type="button" class="font-mono text-[12.5px] font-bold hover:opacity-70 cursor-pointer leading-none" title="Code">&lt;&gt;</button>
                                        <!-- 11. Bullet list -->
                                        <button type="button" class="hover:opacity-70 cursor-pointer inline-flex items-center" title="Bullet list">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
                                            </svg>
                                        </button>
                                        <!-- 12. Numbered list -->
                                        <button type="button" class="hover:opacity-70 cursor-pointer inline-flex items-center" title="Numbered list">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6h11M10 12h11M10 18h11M4 6h1v4M4 10h2M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/>
                                            </svg>
                                        </button>
                                        <!-- 13. Table -->
                                        <button type="button" class="hover:opacity-70 cursor-pointer inline-flex items-center" title="Table">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                <path d="M3 12h18M12 3v18"/>
                                            </svg>
                                        </button>
                                        <!-- 14. Divider Line -->
                                        <button type="button" class="hover:opacity-70 cursor-pointer inline-flex items-center" title="Horizontal line">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" d="M6 7h12M3 12h18M6 17h12"/>
                                            </svg>
                                        </button>
                                        <!-- 15. Microphone -->
                                        <button type="button" class="hover:opacity-70 cursor-pointer inline-flex items-center" title="Voice recording">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 10v2a7 7 0 01-14 0v-2M12 19v4M8 23h8"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <!-- 16 & 17. Undo / Redo -->
                                    <div class="flex items-center gap-3 text-[#b8b4ac]">
                                        <button type="button" class="hover:text-[#1d2d35] cursor-pointer inline-flex items-center" title="Undo">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v2M3 10l4-4M3 10l4 4"/>
                                            </svg>
                                        </button>
                                        <button type="button" class="hover:text-[#1d2d35] cursor-pointer inline-flex items-center" title="Redo">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 10H11a5 5 0 00-5 5v2M21 10l-4-4M21 10l-4 4"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <textarea x-ref="commentInput"
                                          name="content"
                                          rows="5"
                                          required
                                          placeholder="Type your comment here..."
                                          style="border:0 !important;box-shadow:none !important;"
                                          class="w-full px-4 py-3.5 text-[16px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none focus:ring-0 resize-y"></textarea>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="submit" class="px-4 py-1.5 rounded-full bg-[#199849] hover:bg-[#147a3a] text-white font-bold text-[13.5px] transition-colors cursor-pointer">
                                    Add this comment
                                </button>
                                <button type="button" @click="commenting = false" class="px-3.5 py-1.5 rounded-full border border-[#d8d4cc] bg-white hover:bg-slate-50 text-[13.5px] text-[#1d2d35] transition-colors cursor-pointer">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Divider Before Subscribers Footer -->
            <div class="border-t border-[#ece9e2] mt-16 pt-5">
                <p class="text-[14px] text-[#1d2d35] mb-2.5">
                    <span x-text="subscribers.filter(s => s.active).length">5</span> people will be notified when someone comments on this message.
                </p>

                <div class="flex flex-wrap items-center gap-1.5">
                    <template x-for="sub in subscribers.filter(s => s.active)" :key="sub.id">
                        <span class="relative w-[30px] h-[30px] rounded-full overflow-hidden flex-shrink-0 cursor-pointer inline-flex items-center justify-center" :title="sub.name">
                            <template x-if="sub.avatar">
                                <img :src="sub.avatar" :alt="sub.name" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!sub.avatar">
                                <span class="w-full h-full text-white font-bold text-[11px] flex items-center justify-center select-none" :class="sub.bg" x-text="sub.initials"></span>
                            </template>
                            <span x-show="sub.isOoo && localStorage.getItem('bc_ooo_active') !== '0'" class="bc-ooo-badge absolute bottom-0 inset-x-0 h-[36%] bg-[#facc15] flex items-center justify-center pointer-events-none">
                                <span class="text-[6px] font-black text-[#1d1c1b] leading-none tracking-tight">OUT</span>
                            </span>
                        </span>
                    </template>

                    <!-- Add/remove people... Pill -->
                    <button type="button"
                            @click="openSubscribersEditor()"
                            class="ml-1 px-3.5 py-1 rounded-full border border-[#d8d4cc] bg-white hover:bg-[#fbfaf8] text-[13px] text-[#1d2d35] transition-colors cursor-pointer">
                        Add/remove people...
                    </button>

                    <!-- Don't notify me Pill -->
                    <button type="button"
                            @click="notifyMuted = !notifyMuted; subscribers[0].active = !notifyMuted; localStorage.setItem('bc_disc_subs_{{ $discussion->id }}', JSON.stringify(subscribers)); showMsgToast(notifyMuted ? 'You won\'t be notified about new comments' : 'You\'ll be notified about new comments')"
                            class="px-3.5 py-1 rounded-full border border-[#d8d4cc] bg-white hover:bg-[#fbfaf8] text-[13px] text-[#1d2d35] inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019 13H15.82l-2-2H15V8a5 5 0 00-7.536-4.316L6.05 2.27A7.002 7.002 0 0117 8v3.18l-1.293-1.293zM5 8v2.879l-2 2V13h9.879l-2-2H5V8zm3 7a2 2 0 104 0H8z" clip-rule="evenodd"/>
                        </svg>
                        <span x-text="notifyMuted ? 'Notify me about new comments' : 'Don\'t notify me'">Don't notify me</span>
                    </button>
                </div>
            </div>

        </div>

    </div>

    <!-- Bottom-Left Scroll-to-Top Arrow Matching media_1793268370293_23fa70f0.jpg -->
    <button type="button"
            onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-11 left-3 w-6 h-6 rounded border border-[#d8d4cc] bg-[#f6f2eb] hover:bg-white text-[#4a4640] flex items-center justify-center text-xs shadow-2xs cursor-pointer"
            title="Scroll to top">
        ↑
    </button>

</div>
@endsection
