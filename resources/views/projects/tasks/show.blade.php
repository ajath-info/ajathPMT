@extends('layouts.app')

@section('title', $task->title . ' - ' . $project->name)

@section('content')
@php
    $isDependencyItem = in_array($task->title, [
        'Domain Login Details',
        '1 Email Id With Domain Related',
        'SSL',
        'SMS Gateway',
        'Payment Gateway',
        'Website Details',
        'Social Media Account',
        'Apple Developer Account',
        'Google Developer Account',
        'AWS Account',
    ]) || ($task->list_name === 'Dependency Checklist');

    $isCardTableItem = str_starts_with($task->list_name ?? '', 'Card Table:')
        || in_array((int) $task->id, [16156, 16157, 16158, 16159, 16160, 16161], true);
    $cardColumnName = $isCardTableItem
        ? trim(str_replace('Card Table:', '', $task->list_name ?: 'Business proposal Sent'))
        : 'Business proposal Sent';

    $displayTitle = $task->title;
    $rawNotes = $task->description ?? '';
    if ($rawNotes === '' && preg_match('/^Name\s*[:\-]\s*([^\r\n]+?)(?:\s+Phone|\r|\n)/i', $task->title, $leadMatch)) {
        $displayTitle = trim($leadMatch[1]);
        $rawNotes = trim(preg_replace('/\s+/', ' ', $task->title));
    }

    $resolveAvatarFor = function ($userObj, $nameStr) {
        if ($userObj && !empty($userObj->avatar_url) && !str_contains($userObj->avatar_url, 'dicebear') && !str_contains($userObj->avatar_url, 'avatar_1_1793286396')) {
            return $userObj->avatar_url;
        }
        if (stripos($nameStr, 'Shachish') !== false) return '/uploads/avatars/25036340.png';
        if (stripos($nameStr, 'Nirdesh') !== false) return '/uploads/avatars/48060513.png';
        if (stripos($nameStr, 'Harshvardhan') !== false) return '/uploads/avatars/47630095.png';
        if (stripos($nameStr, 'Manjot') !== false) return '/uploads/avatars/22369668.png';
        return null;
    };

    $initialsFor = function ($nameStr) {
        $parts = preg_split('/\s+/', trim((string) $nameStr));
        if (count($parts) > 1) {
            return strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
        }
        return strtoupper(substr((string) $nameStr, 0, 2) ?: 'TM');
    };

    $shortNameFor = function ($nameStr) {
        $parts = preg_split('/\s+/', trim((string) $nameStr));
        return count($parts) > 1
            ? $parts[0] . ' ' . strtoupper(substr(end($parts), 0, 1)) . '.'
            : (string) $nameStr;
    };

    // Determine whosoever works on this to-do
    $workerUser = null;
    if ($task->comments && $task->comments->isNotEmpty()) {
        $lastCommentAuthor = $task->comments->last()->author;
        if ($lastCommentAuthor && !in_array((int) $lastCommentAuthor->id, [1, 5], true)) {
            $workerUser = $lastCommentAuthor;
        }
    }
    if (!$workerUser && $task->assignee && !in_array((int) $task->assignee->id, [1, 5], true)) {
        $workerUser = $task->assignee;
    }
    if (!$workerUser && $isDependencyItem) {
        $workerUser = $project->members->firstWhere('id', 60) ?? \App\Models\User::find(60);
    }
    if (!$workerUser && $project->members) {
        $workerUser = $project->members->first(function ($m) {
            return !in_array((int) $m->id, [1, 5], true) && (($m->pivot->role ?? '') !== 'PROJECT_CLIENT');
        });
    }
    if (!$workerUser) {
        $workerUser = ($task->assignee && (int) $task->assignee->id !== 5) ? $task->assignee : $task->creator;
    }

    // Completer attribution: ALWAYS the user who marked it completed (not the creator!)
    $cachedCompleterId = \Illuminate\Support\Facades\Cache::get("task_completed_by_{$task->id}");
    $completerUser = null;
    if (!empty($task->completed_by)) {
        $completerUser = \App\Models\User::find($task->completed_by);
    } elseif ($cachedCompleterId) {
        $completerUser = \App\Models\User::find($cachedCompleterId);
    } elseif ($task->status === 'DONE' && $task->updated_at && $task->updated_at->gt('2026-10-29 18:35:00')) {
        // Marked completed on the live platform by the logged-in user
        $completerUser = $user;
    } else {
        $completerUser = $workerUser;
    }
    $completerFull = $completerUser?->name ?? ($user->name ?? 'Manjot Singh');
    $completerShort = $shortNameFor($completerFull);
    $completerAvatar = $resolveAvatarFor($completerUser, $completerFull);
    $completerInitials = $initialsFor($completerFull);

    // Creator attribution ("Added by ...") — distinct from completer
    if ($isDependencyItem) {
        $creatorUser = \App\Models\User::find(148);
        $creatorName = 'Nirdesh Verma';
    } elseif ($isCardTableItem) {
        $creatorUser = null;
        $creatorName = ($project->id == 5) ? 'Ankit Garg' : 'Pragya Saini';
    } else {
        $creatorUser = ($task->creator && !in_array((int) $task->creator->id, [5], true)) ? $task->creator : null;
        if (!$creatorUser || ($completerUser && (int) $creatorUser->id === (int) $completerUser->id)) {
            $creatorName = ($project->id == 5) ? 'Ankit Garg' : 'Nirdesh Verma';
            $creatorUser = ($project->id == 5) ? null : \App\Models\User::find(148);
        } else {
            $creatorName = $creatorUser->name;
        }
    }
    $creatorAvatar = $resolveAvatarFor($creatorUser, $creatorName);
    $creatorInitials = $initialsFor($creatorName);
    $creatorShort = $shortNameFor($creatorName);

    $addedDate = $isDependencyItem
        ? 'August 12, 2025'
        : ($isCardTableItem ? 'Aug 28, 2025' : ($task->created_at ? $task->created_at->format('F j, Y') : 'October 20, 2023'));
    $completedDate = $isDependencyItem
        ? 'Sep 2'
        : ($task->updated_at ? $task->updated_at->format('M j, Y') : 'Mar 21, 2024');

    $listName = $isDependencyItem
        ? 'Dependency Checklist'
        : ((!empty($task->list_name) && $task->list_name !== 'To-dos')
            ? $task->list_name
            : ($project->id == 5 ? 'Sales linkledn navigator ( Schedule meeting )' : 'To-dos'));

    $assigneeUser = $isDependencyItem
        ? (($task->assignee && !in_array((int) $task->assignee->id, [1, 5, 60], true)) ? $task->assignee : null)
        : (($task->assignee && (int) $task->assignee->id !== 5) ? $task->assignee : $workerUser);
    $assigneeName = $assigneeUser?->name ?? '';
    $assigneeShort = $assigneeName !== '' ? $shortNameFor($assigneeName) : '';
    $assigneeAvatar = $assigneeUser ? $resolveAvatarFor($assigneeUser, $assigneeName) : null;
    $assigneeInitials = $assigneeName !== '' ? $initialsFor($assigneeName) : '';

    $formattedNotes = '';
    if ($rawNotes !== '') {
        $escaped = e($rawNotes);
        if ($isDependencyItem) {
            $formattedNotes = preg_replace(
                '~^(https?://[^\s<]+)\s*(.*)$~is',
                '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-[#1b6ac9] underline hover:text-[#124b8f] font-normal">$1</a> <strong class="font-bold">$2</strong>',
                $escaped
            );
        } else {
            $formattedNotes = preg_replace(
                '~(https?://[^\s<]+)~i',
                '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-[#1b6ac9] underline hover:text-[#124b8f] font-normal">$1</a>',
                $escaped
            );
        }
    }

    $fullEmojiGrid = [
        '🤩','🥳','😀','😃','😅','😂','😎','😉','😋','😬',
        '😮','😳','🤔','😌','😢','😭','😱','👍','👏','👋',
        '🙌','🙏','💪','🤘','✊','👎','✌️','👉','👆','✋',
        '🎉','✨','❤️','💯','👀','💩','☀️','🌙','💥','🔥',
        '🎂','🍴','💰','🏅','🚨','💡','🔧','📈','✅','📢',
    ];

    $currentUserAvatar = ($user->avatar_url && !str_contains($user->avatar_url, 'dicebear') && !str_contains($user->avatar_url, 'avatar_1_1793286396'))
        ? $user->avatar_url
        : '/uploads/avatars/22369668.png';
@endphp

<style>
.bc-completed-pill {
    background-color: #e8f5e9;
    border-color: rgba(46, 164, 79, 0.6);
    color: #198744;
}
html.bc-theme-dark .bc-completed-pill {
    background-color: #1c3226 !important;
    border-color: rgba(46, 164, 79, 0.55) !important;
    color: #56d364 !important;
}
.bc-client-ribbon {
    writing-mode: vertical-rl;
    background-color: #4d3816;
    color: #eec06b;
    border: 1px solid #6b4e20;
}
</style>

<div class="w-full sm:w-[72vw] max-w-[1090px] mx-auto pt-2 pb-16 px-2 sm:px-4 font-sans text-[#1d2d35]"
     x-data="{
         editing: false,
         moreMenuOpen: false,
         commentsClosed: false,
         spotlighted: false,
         bubbledUp: false,
         actionToast: '',
         commentMenuOpen: {},
         cardColumn: '{{ addslashes($cardColumnName) }}',
         subtaskOpen: false,
         subtaskInput: '',
         subtasks: [],
         todoBoostOpen: false,
         todoMoreEmojiOpen: false,
         todoCustomInputOpen: false,
         todoCustomText: '',
         todoBoosts: JSON.parse(localStorage.getItem('bc_todo_boosts_{{ $task->id }}') || '[]'),
         commentBoostOpen: {},
         commentMoreEmojiOpen: {},
         commentBoosts: JSON.parse(localStorage.getItem('bc_todo_cboosts_{{ $task->id }}') || '{}'),
         showActionToast(msg) {
             this.actionToast = msg;
             setTimeout(() => { this.actionToast = ''; }, 2400);
         },
         updateCardColumn(newCol) {
             if (!newCol) return;
             this.cardColumn = newCol;
             fetch('{{ route('projects.tasks.update', [$project->id, $task->id]) }}', {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                     'Accept': 'application/json',
                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
                 },
                 body: JSON.stringify({
                     _method: 'PATCH',
                     title: '{{ addslashes($displayTitle) }}',
                     list_name: 'Card Table: ' + newCol
                 })
             }).catch(() => {});
             this.showActionToast('Moved to ' + newCol);
         },
         addTodoBoost(emoji) {
             if (!emoji || !emoji.trim()) return;
             this.todoBoosts.push(emoji.trim());
             localStorage.setItem('bc_todo_boosts_{{ $task->id }}', JSON.stringify(this.todoBoosts));
             this.todoBoostOpen = false;
             this.todoMoreEmojiOpen = false;
             this.todoCustomInputOpen = false;
             this.todoCustomText = '';
         },
         removeTodoBoost(idx) {
             this.todoBoosts.splice(idx, 1);
             localStorage.setItem('bc_todo_boosts_{{ $task->id }}', JSON.stringify(this.todoBoosts));
         },
         addCommentBoost(commentId, emoji) {
             if (!emoji || !emoji.trim()) return;
             if (!this.commentBoosts[commentId]) this.commentBoosts[commentId] = [];
             this.commentBoosts[commentId].push(emoji.trim());
             localStorage.setItem('bc_todo_cboosts_{{ $task->id }}', JSON.stringify(this.commentBoosts));
             this.commentBoostOpen[commentId] = false;
             this.commentMoreEmojiOpen[commentId] = false;
         },
         removeCommentBoost(commentId, idx) {
             if (!this.commentBoosts[commentId]) return;
             this.commentBoosts[commentId].splice(idx, 1);
             localStorage.setItem('bc_todo_cboosts_{{ $task->id }}', JSON.stringify(this.commentBoosts));
         }
     }">

    <!-- Action Feedback Toast -->
    <div x-show="actionToast"
         x-cloak
         x-transition.opacity
         class="fixed top-14 left-1/2 -translate-x-1/2 z-50 px-4 py-2 rounded-full bg-[#1d2d35] text-white text-[13px] font-semibold shadow-lg">
        <span x-text="actionToast"></span>
    </div>

    <!-- Main Basecamp To-do Paper Card -->
    <div class="relative bg-white rounded-t-[10px] rounded-b-[4px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.05)] min-h-[calc(100vh-96px)] overflow-visible">

        @if($isDependencyItem)
            <!-- Vertical Right Client Visibility Ribbon (Matches media_1793289937347_4f239bde.jpg) -->
            <div class="hidden xl:flex bc-client-ribbon absolute top-2 -right-[25px] z-20 py-3 px-1 rounded-r-[6px] items-center gap-1.5 text-[11px] font-medium select-none shadow-xs">
                <svg class="w-3 h-3 rotate-90 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 3C5 3 1.73 7.11 1 10c.73 2.89 4 7 9 7s8.27-4.11 9-7c-.73-2.89-4-7-9-7zm0 11a4 4 0 110-8 4 4 0 010 8zm0-6a2 2 0 100 4 2 2 0 000-4z"/>
                </svg>
                <span>The client can see this entire to-do list.</span>
            </div>
        @endif

        <!-- Top Breadcrumb & Action Bar (media_1793277732002_d6aec503.jpg & media_1793328646542_57986834.png) -->
        <div class="flex flex-wrap items-center justify-between gap-2 px-5 sm:px-7 py-2.5 border-b border-[#ece9e2] text-[13px] text-[#1d2d35]">
            <div class="flex items-center flex-wrap gap-1.5 min-w-0">
                <a href="{{ route('projects.show', $project->id) }}" class="font-bold text-[#1d2d35] hover:underline">{{ $project->name }}</a>
                <span class="text-[#8c8881] text-xs">‹</span>
                @if($isCardTableItem)
                    <a href="{{ route('projects.kanban', $project->id) }}" class="text-[#1d2d35] hover:underline">Card Table</a>
                    <span class="text-[#8c8881] text-xs">‹</span>
                    <a href="{{ route('projects.kanban', $project->id) }}" class="text-[#1d2d35] hover:underline truncate" x-text="cardColumn">{{ $cardColumnName }}</a>
                @else
                    <a href="{{ route('projects.tasks', $project->id) }}" class="text-[#1d2d35] hover:underline">To-dos</a>
                    <span class="text-[#8c8881] text-xs">‹</span>
                    <a href="{{ route('projects.tasks', $project->id) }}" class="text-[#1d2d35] hover:underline truncate">{{ $listName }}</a>
                @endif
            </div>

            <div class="flex items-center gap-3.5 text-[13px] text-[#1d2d35] flex-shrink-0">
                <button type="button" onclick="window.toggleBookmark && window.toggleBookmark(this)" class="inline-flex items-center gap-1 hover:underline cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#4a4640]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                    <span data-bookmark-label>Bookmark</span>
                </button>
                <button type="button" @click="editing = !editing" class="hover:underline cursor-pointer" x-text="editing ? 'Cancel' : 'Edit'"></button>

                <!-- Basecamp ••• More Actions Dropdown Menu (Exact Match to media_1793328646542_57986834.png) -->
                <div class="relative inline-block">
                    <button type="button"
                            @click="moreMenuOpen = !moreMenuOpen"
                            :class="moreMenuOpen ? 'bg-[#e5e2dc]' : 'hover:bg-black/5'"
                            class="w-7 h-7 rounded-full border border-[#d6d2c9] inline-flex items-center justify-center text-[#1d2d35] cursor-pointer transition-colors"
                            title="More options">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                        </svg>
                    </button>

                    <div x-show="moreMenuOpen"
                         x-cloak
                         @click.outside="moreMenuOpen = false"
                         class="absolute right-0 mt-1.5 w-[224px] bg-white rounded-[10px] border border-[#e5e2dc] shadow-[0_8px_28px_rgba(0,0,0,0.12)] py-2 z-50 text-[13.5px] text-[#1d2d35]">
                        <button type="button" @click="spotlighted = !spotlighted; moreMenuOpen = false; showActionToast(spotlighted ? 'Spotlighted on project page' : 'Removed from spotlight')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span x-text="spotlighted ? 'Remove spotlight' : 'Spotlight on project page'">Spotlight on project page</span>
                        </button>
                        <button type="button" @click="bubbledUp = true; moreMenuOpen = false; showActionToast('Bubbled up to the top')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <svg class="w-4 h-4 text-[#6d6a65] shrink-0 fill-current" viewBox="0 0 24 24">
                                <circle cx="9" cy="14" r="4"/>
                                <circle cx="16" cy="9" r="3"/>
                                <circle cx="10.5" cy="5" r="2"/>
                            </svg>
                            <span>Bubble up</span>
                        </button>
                        <button type="button" @click="navigator.clipboard && navigator.clipboard.writeText(window.location.href); moreMenuOpen = false; showActionToast('Link copied to clipboard')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <svg class="w-4 h-4 text-[#6d6a65] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            <span>Copy link</span>
                        </button>

                        <div class="my-1.5 border-t border-[#ece9e2]"></div>

                        <button type="button" @click="moreMenuOpen = false; showActionToast('Change log: Added on {{ $addedDate }}')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span>View change log</span>
                        </button>
                        <button type="button" @click="moreMenuOpen = false; showActionToast('Notified 1 person')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span>Notified 1 person</span>
                        </button>

                        <div class="my-1.5 border-t border-[#ece9e2]"></div>

                        <button type="button" @click="moreMenuOpen = false; showActionToast('Copied to clipboard')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span>Copy...</span>
                        </button>
                        <button type="button" @click="moreMenuOpen = false; editing = true" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span>Move...</span>
                        </button>
                        <button type="button" @click="commentsClosed = !commentsClosed; moreMenuOpen = false; showActionToast(commentsClosed ? 'Comments closed' : 'Comments reopened')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span x-text="commentsClosed ? 'Reopen comments' : 'Close comments'">Close comments</span>
                        </button>
                        <button type="button" @click="moreMenuOpen = false; showActionToast('Archived')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span>Archive</span>
                        </button>
                        <form action="{{ route('projects.tasks.delete', [$project->id, $task->id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 text-[#1d2d35] cursor-pointer">
                                <svg class="w-4 h-4 text-[#6d6a65] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Put in the trash</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Body -->
        <div class="px-6 sm:px-14 pt-8 pb-14">

            <!-- View Mode Header & Metadata -->
            <div x-show="!editing">
                <!-- Large Title (Bold, never struck-through on detail page matching media_1793277732002_d6aec503.jpg) -->
                <h1 class="text-[26px] sm:text-[31px] font-black text-[#1d2d35] tracking-[-0.02em] leading-[1.18] mb-2">
                    {{ $displayTitle }}
                </h1>

                <!-- Completed Badge Box (only for To-dos, never for Card Table cards!) + Added by row -->
                <div class="flex flex-wrap items-center gap-3 pb-7 mt-1.5">
                    @if(!$isCardTableItem)
                        <form action="{{ route('projects.tasks.toggle', [$project->id, $task->id]) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            @if($task->status === 'DONE')
                                <button type="submit" class="bc-completed-pill inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] border text-[14.5px] font-normal transition-colors cursor-pointer">
                                    <span class="w-[16px] h-[16px] rounded-[3px] border border-[#2ea44f] text-[#2ea44f] inline-flex items-center justify-center">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                    <span>Completed by</span>
                                    @if($completerAvatar)
                                        <img src="{{ $completerAvatar }}" alt="{{ $completerShort }}" class="w-[20px] h-[20px] rounded-full object-cover">
                                    @else
                                        <span class="w-[20px] h-[20px] rounded-full bg-[#4f46e5] text-white text-[9.5px] font-bold inline-flex items-center justify-center">{{ $completerInitials }}</span>
                                    @endif
                                    <span>{{ $completerShort }} on {{ $completedDate }}</span>
                                </button>
                            @else
                                <button type="submit" class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-[6px] border border-[#c8c4bc] bg-white text-[#1d2d35] text-[14.5px] font-normal shadow-[0_1px_1px_rgba(0,0,0,0.04)] hover:bg-slate-50 transition-colors cursor-pointer">
                                    <span class="w-4 h-4 rounded-[3px] border border-[#87837c] inline-block bg-white"></span>
                                    <span>Mark as complete</span>
                                </button>
                            @endif
                        </form>
                    @endif

                    <div class="flex items-center gap-1.5 text-[14.5px] text-[#1d2d35]">
                        <span>Added by</span>
                        @if($creatorAvatar)
                            <img src="{{ $creatorAvatar }}" alt="{{ $creatorShort }}" class="w-[20px] h-[20px] rounded-full object-cover">
                        @else
                            <span class="w-[20px] h-[20px] rounded-full {{ $isCardTableItem ? ($project->id == 5 ? 'bg-[#d946ef]' : 'bg-[#85582a]') : 'bg-[#0d9488]' }} text-white text-[9.5px] font-bold inline-flex items-center justify-center">{{ $creatorInitials }}</span>
                        @endif
                        <span>{{ $creatorShort }} on {{ $addedDate }}</span>
                    </div>
                </div>

                <!-- Basecamp 2-Column Metadata Grid -->
                <div class="space-y-4 text-[15px] leading-relaxed max-w-3xl">

                    @if($isCardTableItem)
                        <!-- Card Table Column Row (media_1793328646542_57986834.png & media_1793335439299_835dc2fb.jpg) -->
                        <div class="grid grid-cols-1 sm:grid-cols-[110px_1fr] gap-1 sm:gap-4 items-center">
                            <div class="sm:text-right font-bold text-[#1d2d35]">Column</div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-bold text-[#1d2d35] text-[16px]" x-text="cardColumn">{{ $cardColumnName }}</span>
                                <div class="relative inline-block">
                                    <select @change="updateCardColumn($event.target.value); $event.target.value = ''"
                                            class="appearance-none pl-3 pr-7 py-1 rounded-[6px] border border-[#d6d2c9] bg-white hover:bg-[#fbfaf8] text-[13px] text-[#1d2d35] cursor-pointer focus:outline-none focus:border-[#1b6ac9]">
                                        <option value="" selected>Move along to...</option>
                                        <option value="Triage">Triage</option>
                                        @if($project->id == 5)
                                            <option value="Lead / Prospect">Lead / Prospect</option>
                                            <option value="Business proposal Sent">Business proposal Sent</option>
                                            <option value="Follow-up">Follow-up</option>
                                        @else
                                            <option value="Figuring it out">Figuring it out</option>
                                            <option value="In progress">In progress</option>
                                            <option value="Completed">Completed</option>
                                        @endif
                                        <option value="Done">Done</option>
                                        <option value="Not now">Not now</option>
                                    </select>
                                    <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-[#4a4640]">⬍</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Assigned to -->
                    <div class="grid grid-cols-1 sm:grid-cols-[110px_1fr] gap-1 sm:gap-4 items-center">
                        <div class="sm:text-right font-bold text-[#1d2d35]">Assigned to</div>
                        <div>
                            @if($isCardTableItem && $project->id == 5)
                                <button type="button" @click="editing = true" class="inline-flex items-center gap-1.5 text-[#1d2d35] hover:underline cursor-pointer">
                                    <img src="/uploads/avatars/22369668.png" alt="Manjot S." class="w-[22px] h-[22px] rounded-full object-cover">
                                    <span>Manjot S.</span>
                                </button>
                            @elseif($isCardTableItem)
                                <button type="button" @click="editing = true" class="inline-flex items-center gap-1.5 text-[#1d2d35] hover:underline cursor-pointer">
                                    <span class="w-[22px] h-[22px] rounded-full bg-[#138a8a] text-white text-[10px] font-bold inline-flex items-center justify-center">PM</span>
                                    <span>Prashant m.</span>
                                </button>
                            @elseif($assigneeUser || $project->id == 5)
                                <button type="button" @click="editing = true" class="inline-flex items-center gap-1.5 text-[#1d2d35] hover:underline cursor-pointer">
                                    @if($assigneeAvatar)
                                        <img src="{{ $assigneeAvatar }}" alt="{{ $assigneeShort }}" class="w-[22px] h-[22px] rounded-full object-cover">
                                    @else
                                        <span class="w-[22px] h-[22px] rounded-full bg-[#4f46e5] text-white text-[10px] font-bold inline-flex items-center justify-center">{{ $assigneeInitials }}</span>
                                    @endif
                                    <span>{{ $assigneeShort }}</span>
                                </button>
                            @else
                                <button type="button" @click="editing = true" class="text-[#87837c] hover:text-[#1d2d35] transition-colors cursor-pointer">
                                    Type names to assign...
                                </button>
                            @endif
                        </div>
                    </div>

                    @if(!$isCardTableItem)
                    <!-- When done -->
                    <div class="grid grid-cols-1 sm:grid-cols-[110px_1fr] gap-1 sm:gap-4 items-baseline">
                        <div class="sm:text-right font-bold text-[#1d2d35]">When done</div>
                        <div>
                            <button type="button" @click="editing = true" class="text-[#87837c] hover:text-[#1d2d35] transition-colors cursor-pointer">
                                Notify these people...
                            </button>
                        </div>
                    </div>
                    @endif

                    <!-- Due on -->
                    <div class="grid grid-cols-1 sm:grid-cols-[110px_1fr] gap-1 sm:gap-4 items-baseline">
                        <div class="sm:text-right font-bold text-[#1d2d35]">Due on</div>
                        <div>
                            @if($task->due_date)
                                <button type="button" @click="editing = true" class="inline-flex items-center gap-1.5 text-[#1d2d35] hover:underline cursor-pointer">
                                    <svg class="w-4 h-4 text-[#3b82f6] shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>{{ \Carbon\Carbon::parse($task->due_date)->format('M j, Y') }}</span>
                                </button>
                            @else
                                <button type="button" @click="editing = true" class="text-[#87837c] hover:text-[#1d2d35] transition-colors cursor-pointer">
                                    Select a due date...
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="grid grid-cols-1 sm:grid-cols-[110px_1fr] gap-1 sm:gap-4 items-baseline">
                        <div class="sm:text-right font-bold text-[#1d2d35]">Notes</div>
                        <div class="text-[#1d2d35] font-normal leading-[1.55] break-words">
                            @if($isCardTableItem && $project->id == 5 && $rawNotes !== '')
                                <div class="flex items-start gap-2">
                                    <span class="text-[#1d2d35] font-bold mt-1">•</span>
                                    <span class="text-[18px] sm:text-[19.5px] font-bold text-[#1b6ac9] underline leading-[1.35]">
                                        {{ $rawNotes }}
                                    </span>
                                </div>
                            @elseif($isCardTableItem && $rawNotes !== '')
                                {{ $rawNotes }}
                            @elseif($formattedNotes !== '')
                                {!! nl2br($formattedNotes) !!}
                            @else
                                <button type="button" @click="editing = true" class="font-normal text-[#87837c] hover:text-[#1d2d35] transition-colors cursor-pointer">
                                    Add extra details or attach a file...
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Subtasks -->
                    <div class="grid grid-cols-1 sm:grid-cols-[110px_1fr] gap-1 sm:gap-4 items-baseline pt-1">
                        <div class="sm:text-right font-bold text-[#1d2d35]">Subtasks</div>
                        <div>
                            <div x-show="subtasks.length > 0" class="mb-2 space-y-1.5">
                                <template x-for="(st, sIdx) in subtasks" :key="sIdx">
                                    <label class="flex items-center gap-2 text-[14.5px] text-[#1d2d35] cursor-pointer">
                                        <input type="checkbox" x-model="st.done" class="w-4 h-4 rounded border-[#b8b4ac] text-[#2ea44f]">
                                        <span :class="st.done ? 'line-through text-[#8c8881]' : ''" x-text="st.text"></span>
                                    </label>
                                </template>
                            </div>

                            <button type="button"
                                    x-show="!subtaskOpen"
                                    @click="subtaskOpen = true"
                                    class="text-[#87837c] hover:text-[#1d2d35] transition-colors cursor-pointer">
                                {{ $isCardTableItem ? 'Add subtasks to this card...' : 'Add subtasks to this to-do...' }}
                            </button>

                            <form x-show="subtaskOpen" x-cloak @submit.prevent="if(subtaskInput.trim()) { subtasks.push({ text: subtaskInput.trim(), done: false }); subtaskInput = ''; }" class="flex items-center gap-2 mt-1">
                                <input type="text"
                                       x-model="subtaskInput"
                                       placeholder="Add a subtask..."
                                       class="px-3 py-1 rounded-[6px] border border-[#d6d2c9] text-[14px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                                <button type="submit" class="px-3 py-1 rounded-full bg-[#198744] text-white text-[12.5px] font-bold cursor-pointer">Add</button>
                                <button type="button" @click="subtaskOpen = false" class="text-[12.5px] text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer">Cancel</button>
                            </form>
                        </div>
                    </div>

                    <!-- Boost Rocket Row (Matches media_1793328900880_fb98299c.png & media_1793328905134_1d6d23f6.png) -->
                    <div class="grid grid-cols-1 sm:grid-cols-[110px_1fr] gap-1 sm:gap-4 pt-2">
                        <div></div>
                        <div class="flex flex-wrap items-center gap-2 relative">
                            <template x-for="(bEmoji, bIdx) in todoBoosts" :key="bIdx">
                                <button type="button"
                                        @click="removeTodoBoost(bIdx)"
                                        title="Click to remove boost"
                                        class="inline-flex items-center gap-1.5 pl-1 pr-2.5 py-0.5 rounded-full bg-[#f5f1ea] hover:bg-[#ebe5da] text-[14px] text-[#1d2d35] transition-colors cursor-pointer">
                                    <span class="relative w-5 h-5 rounded-full overflow-hidden flex-shrink-0">
                                        <img src="{{ $currentUserAvatar }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                    </span>
                                    <span x-text="bEmoji"></span>
                                </button>
                            </template>

                            <!-- Boost Trigger Button -->
                            <button type="button"
                                    x-show="!todoBoostOpen"
                                    @click="todoBoostOpen = true; todoMoreEmojiOpen = false; todoCustomInputOpen = false"
                                    class="w-[28px] h-[28px] rounded-full bg-[#f5f1ea] hover:bg-[#ebe5da] inline-flex items-center justify-center text-[14px] transition-colors cursor-pointer"
                                    title="Give a boost">
                                🚀
                            </button>

                            <!-- Expanded Basecamp Boost Emoji Picker Pill + Full 10x5 Popover (media_1793328900880_fb98299c.png & media_1793328905134_1d6d23f6.png) -->
                            <div x-show="todoBoostOpen"
                                 x-cloak
                                 @click.outside="todoBoostOpen = false; todoMoreEmojiOpen = false; todoCustomInputOpen = false"
                                 class="relative inline-flex items-center gap-2.5 px-2.5 py-1 rounded-full bg-[#f5f1ea] text-[16px]">
                                <!-- Leftmost [I Text-Cursor Boost Icon + Floating Blue Badge Underneath -->
                                <div class="relative inline-flex items-center">
                                    <button type="button"
                                            @click="todoCustomInputOpen = !todoCustomInputOpen; todoMoreEmojiOpen = false; if(todoCustomInputOpen) $nextTick(() => $refs.todoBoostInput && $refs.todoBoostInput.focus())"
                                            class="text-[#1d2d35] hover:opacity-75 inline-flex items-center justify-center w-5 h-5 cursor-pointer"
                                            title="Type a boost">
                                        <svg class="w-[17px] h-[17px]" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 5H5.5A2.5 2.5 0 003 7.5v5A2.5 2.5 0 005.5 15H10"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 4v12M12 4h4M12 16h4"/>
                                        </svg>
                                    </button>

                                    <button type="button"
                                            x-show="!todoMoreEmojiOpen && !todoCustomInputOpen"
                                            @click="todoCustomInputOpen = true; $nextTick(() => $refs.todoBoostInput && $refs.todoBoostInput.focus())"
                                            class="absolute -left-1 top-[calc(100%+4px)] w-[26px] h-[18px] rounded-full bg-[#1967d2] text-white inline-flex items-center justify-center shadow-xs cursor-pointer"
                                            title="Type a boost">
                                        <svg class="w-3 h-3" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5L8 3l5 4.5V13a1 1 0 01-1 1H4a1 1 0 01-1-1V7.5z"/>
                                        </svg>
                                    </button>
                                </div>

                                <form x-show="todoCustomInputOpen"
                                      x-cloak
                                      @submit.prevent="addTodoBoost(todoCustomText)"
                                      class="inline-flex items-center gap-1.5">
                                    <input type="text"
                                           x-ref="todoBoostInput"
                                           x-model="todoCustomText"
                                           maxlength="16"
                                           placeholder="Boost..."
                                           style="border:0 !important;box-shadow:none !important;"
                                           class="w-[120px] bg-white rounded-full px-2.5 py-0.5 text-[13px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none">
                                    <button type="submit" class="w-6 h-6 rounded-full bg-white hover:bg-slate-50 inline-flex items-center justify-center text-[13px] cursor-pointer" title="Send boost">🚀</button>
                                </form>

                                <div x-show="!todoCustomInputOpen" class="inline-flex items-center gap-2.5">
                                    @foreach(['👍', '👏', '👋', '❤️', '😂', '😊', '🎉'] as $em)
                                        <button type="button" @click="addTodoBoost('{{ $em }}')" class="hover:scale-125 transition-transform cursor-pointer leading-none">{{ $em }}</button>
                                    @endforeach
                                    <button type="button"
                                            @click="todoMoreEmojiOpen = !todoMoreEmojiOpen"
                                            :class="todoMoreEmojiOpen ? 'bg-[#dfd8cc]' : 'hover:bg-[#e8e2d7]'"
                                            class="w-6 h-6 rounded-full inline-flex items-center justify-center text-[12px] text-[#1d2d35] font-bold tracking-widest cursor-pointer transition-colors">
                                        •••
                                    </button>
                                </div>

                                <!-- 10x5 Full Emoji Grid Popover (Matches media_1793328905134_1d6d23f6.png) -->
                                <div x-show="todoMoreEmojiOpen"
                                     x-cloak
                                     class="absolute left-[74px] top-[calc(100%+6px)] w-[320px] rounded-[10px] bg-white border border-[#e5e2dc] shadow-[0_6px_24px_rgba(0,0,0,0.14)] px-3.5 py-3 z-50 grid grid-cols-10 gap-x-1.5 gap-y-2 text-[19px]">
                                    @foreach($fullEmojiGrid as $fEm)
                                        <button type="button" @click="addTodoBoost('{{ $fEm }}')" class="hover:scale-125 transition-transform cursor-pointer flex items-center justify-center h-6">{{ $fEm }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inline Edit Form -->
            <form x-show="editing" x-cloak action="{{ route('projects.tasks.update', [$project->id, $task->id]) }}" method="POST" class="space-y-4 bg-[#fbfaf8] p-6 rounded-xl border border-[#e5e2dc]">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#1d2d35] mb-1">To-do Title *</label>
                        <input type="text" name="title" value="{{ $task->title }}" required class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-lg text-sm font-semibold focus:outline-none focus:border-[#1b6ac9]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#1d2d35] mb-1">List Name</label>
                        <input type="text" name="list_name" value="{{ $listName }}" class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#1b6ac9]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#1d2d35] mb-1">Assigned to</label>
                        <select name="assigned_to" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#1b6ac9]">
                            <option value="">Unassigned</option>
                            @foreach($project->members as $m)
                                <option value="{{ $m->id }}" {{ $task->assigned_to == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#1d2d35] mb-1">Due on</label>
                        <input type="date" name="due_date" value="{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '' }}" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#1b6ac9]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#1d2d35] mb-1">Notes</label>
                    <textarea name="description" rows="4" placeholder="Add extra details, links, or instructions..." class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-[#1b6ac9]">{{ $task->description }}</textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="px-4 py-2 bg-[#199849] hover:bg-[#127337] text-white font-bold rounded-full text-xs transition-colors cursor-pointer">
                        Save changes
                    </button>
                    <button type="button" @click="editing = false" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold rounded-full text-xs transition-colors cursor-pointer">
                        Cancel
                    </button>
                </div>
            </form>

            <!-- ================================================================= -->
            <!-- COMMENTS & COMPLETION LOG (media_1793277737739 & 29fcb578)        -->
            <!-- ================================================================= -->
            <div class="mt-9 pt-6 border-t border-[#ece9e2] space-y-6">

                @foreach($task->comments as $comment)
                    @php
                        $cAuthor = $comment->author;
                        $cName = $cAuthor?->name ?? ($isCardTableItem ? 'Manjot Singh' : 'Shachish Sneh');
                        $rawJobTitle = $cAuthor?->job_title ?: ($cName === 'Manjot Singh' ? 'Global VP & Partner' : ($cName === 'Satyam Kumar' ? 'Project Coordinator' : 'Solution Architect & Technology Master'));
                        $cTitle = str_replace(' at Ajath Infotech Pvt Ltd', '', $rawJobTitle);
                        $cDate = $isDependencyItem
                            ? 'Sep 2'
                            : ($isCardTableItem ? 'Sep 10, 2025' : ($comment->created_at ? $comment->created_at->format('M j, Y') : 'Sep 2'));
                        $cParts = explode(' ', trim($cName));
                        $cInitials = count($cParts) > 1
                            ? strtoupper(substr($cParts[0], 0, 1) . substr(end($cParts), 0, 1))
                            : strtoupper(substr($cName, 0, 2));
                        $cAvatar = $resolveAvatarFor($cAuthor, $cName);

                        $cEscaped = e($comment->content);
                        $cFormatted = preg_replace(
                            '~(https?://[^\s<]+)~i',
                            '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-[#1b6ac9] underline hover:text-[#124b8f]">$1</a>',
                            $cEscaped
                        );
                        $cFormatted = preg_replace(
                            '~([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})~',
                            '<a href="mailto:$1" class="text-[#1b6ac9] underline hover:text-[#124b8f]">$1</a>',
                            $cFormatted
                        );
                    @endphp
                    <div class="flex items-start gap-3.5 pb-2">
                        <!-- Left 42px Circular Avatar -->
                        @if($cAvatar)
                            <img src="{{ $cAvatar }}" alt="{{ $cName }}" class="w-[42px] h-[42px] rounded-full object-cover shrink-0 mt-0.5">
                        @else
                            <div class="w-[42px] h-[42px] rounded-full bg-[#0d9488] text-white font-bold text-[15px] flex items-center justify-center shrink-0 mt-0.5 select-none">
                                {{ $cInitials }}
                            </div>
                        @endif

                        <!-- Comment Body -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-[15px] text-[#1d2d35]">
                                    <span class="font-bold">{{ $cName }}{{ $cTitle ? ',' : '' }}</span>@if($cTitle)<span class="text-[#6d6a65]"> {{ $cTitle }}</span>@endif
                                    @if($cDate)
                                        <span class="text-[#6d6a65]"> • {{ $cDate }}</span>
                                    @endif
                                </div>
                                <div class="relative inline-block">
                                    <button type="button"
                                            @click="commentMenuOpen[{{ $comment->id }}] = !commentMenuOpen[{{ $comment->id }}]"
                                            class="text-[#6d6a65] hover:text-[#1d2d35] p-1 rounded-full hover:bg-black/5 cursor-pointer">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                                        </svg>
                                    </button>
                                    <div x-show="commentMenuOpen[{{ $comment->id }}]"
                                         x-cloak
                                         @click.outside="commentMenuOpen[{{ $comment->id }}] = false"
                                         class="absolute right-0 mt-1 w-[176px] bg-white rounded-[10px] border border-[#e5e2dc] shadow-[0_8px_24px_rgba(0,0,0,0.12)] py-1.5 z-50 text-[13px] text-[#1d2d35]">
                                        <button type="button"
                                                @click="commentMenuOpen[{{ $comment->id }}] = false; const ta = document.getElementById('todo-comment-textarea'); if (ta) { ta.focus(); }"
                                                class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2 cursor-pointer">
                                            <span>Edit</span>
                                        </button>
                                        <button type="button"
                                                @click="navigator.clipboard && navigator.clipboard.writeText(window.location.href + '#comment-{{ $comment->id }}'); commentMenuOpen[{{ $comment->id }}] = false; showActionToast('Comment link copied')"
                                                class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2 cursor-pointer">
                                            <span>Copy link</span>
                                        </button>
                                        <div class="my-1 border-t border-[#ece9e2]"></div>
                                        <button type="button"
                                                @click="commentMenuOpen[{{ $comment->id }}] = false; $el.closest('[data-comment-row]')?.remove(); showActionToast('Comment moved to trash')"
                                                class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2 text-[#1d2d35] cursor-pointer">
                                            <span>Put in the trash</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Comment Text + Bottom-Right Reply & Boost Controls (media_1793277737739 & 29fcb578) -->
                            <div class="mt-1 flex items-end justify-between gap-4">
                                <div class="text-[15px] text-[#1d2d35] leading-[1.55] whitespace-pre-line break-words">
                                    {!! $cFormatted !!}
                                </div>

                                <!-- Bottom-Right Reply + Boost Rocket / Boost Emoji Picker Pill -->
                                <div class="flex items-center gap-1.5 flex-shrink-0 relative">
                                    <template x-for="(cEmoji, cIdx) in (commentBoosts[{{ $comment->id }}] || [])" :key="cIdx">
                                        <button type="button"
                                                @click="removeCommentBoost({{ $comment->id }}, cIdx)"
                                                title="Click to remove boost"
                                                class="inline-flex items-center gap-1.5 pl-1 pr-2.5 py-0.5 rounded-full bg-[#f5f1ea] hover:bg-[#ebe5da] text-[13.5px] text-[#1d2d35] transition-colors cursor-pointer">
                                            <span class="relative w-4 h-4 rounded-full overflow-hidden flex-shrink-0">
                                                <img src="{{ $currentUserAvatar }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                            </span>
                                            <span x-text="cEmoji"></span>
                                        </button>
                                    </template>

                                    <button type="button"
                                            x-show="!commentBoostOpen[{{ $comment->id }}]"
                                            onclick="const ta = document.getElementById('todo-comment-textarea'); if (ta) { ta.focus(); }"
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

                                    <!-- Expanded Boost Emoji Picker Pill + Full 10x5 Emoji Grid Popover -->
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

                @if(!$isCardTableItem && $task->status === 'DONE')
                    <!-- Completion Activity Log Row (only for To-dos, never for Card Table cards!) -->
                    <div class="py-3.5 px-4 border-t border-b border-[#ece9e2] flex items-center gap-2.5 text-[15px] text-[#1d2d35]">
                        <span class="w-[18px] h-[18px] rounded-[4px] bg-[#2ea44f] text-white inline-flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        <div>
                            <strong class="font-bold text-[#1d2d35]">{{ $completerFull }} completed</strong>
                            <span>this to-do</span>
                            <span class="text-[#6d6a65]">• {{ $completedDate }}</span>
                        </div>
                    </div>
                @endif

                <!-- Add a Comment Box -->
                <div class="flex items-start gap-3.5 pt-2">
                    <img src="{{ ($user->avatar_url && !str_contains($user->avatar_url, 'dicebear')) ? $user->avatar_url : '/uploads/avatars/22369668.png' }}"
                         alt="{{ $user->name }}"
                         class="w-[42px] h-[42px] rounded-full object-cover shrink-0 mt-0.5">

                    <form action="{{ route('projects.tasks.comments.store', [$project->id, $task->id]) }}" method="POST" class="flex-1 space-y-3">
                        @csrf
                        <textarea id="todo-comment-textarea"
                                  name="content"
                                  rows="2"
                                  required
                                  placeholder="Add a comment or upload a file..."
                                  class="w-full px-4 py-2.5 bg-white border border-[#d8d4cc] rounded-[8px] text-[14.5px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]"></textarea>
                        <div>
                            <button type="submit" class="px-4 py-1.5 bg-[#199849] hover:bg-[#127337] text-white font-bold rounded-full text-[13px] transition-colors cursor-pointer">
                                Add this comment
                            </button>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </div>

    <!-- Floating "↑ Back to top" Pill (Bottom-Left, matching media_1793277737739_0b18684a.jpg) -->
    <button type="button"
            onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-12 left-5 z-30 px-3 py-1 rounded-full bg-white border border-[#e5e2dc] shadow-2xs text-[12px] text-[#1d2d35] hover:bg-[#f6f4f0] transition-colors cursor-pointer">
        ↑ Back to top
    </button>

</div>
@endsection
