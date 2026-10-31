@extends('layouts.app')

@section('title', (request()->boolean('new') ? 'New message - ' : 'Message Board - ') . $project->name)

@section('content')
@php
    $badgeColors = [
        'S'  => '#15b79e',
        'GK' => '#d946ef',
        'PK' => '#00897b',
        'RK' => '#d81b60',
        'AI' => '#10b981',
        'SN' => '#9333ea',
        'MS' => '#2563eb',
        'M'  => '#2563eb',
        'SS' => '#4f46e5',
    ];
    $notifyCount = 7;
@endphp

<div x-data="{
    storageKey: 'bc_msg_draft_project_{{ $project->id }}',
    newMsgOpen: {{ request()->boolean('new') ? 'true' : 'false' }},
    draftTitle: '',
    draftContent: '',
    selectedCat: '',
    visibilityMode: 'team',
    notifyMode: 'project',
    notifySelectedIds: [1, 2, 3, 4, 5, 60, 62],
    notifyPeopleList: [
        { id: 4, name: 'Gaurav Khulbey', role: 'Cybersecurity Expert', initials: 'GK', bg: '#d946ef', avatar: null },
        { id: 1, name: 'Manjot Singh', role: 'Global VP & Partner', initials: 'M', bg: '#1d4ed8', avatar: '/uploads/avatars/22369668.png', isOoo: true },
        { id: 2, name: 'Pawandeep Kaur', role: 'Manager', initials: 'PK', bg: '#00897b', avatar: null },
        { id: 3, name: 'Prashant', role: 'Developer', initials: 'P', bg: '#0284c7', avatar: '/uploads/avatars/22369670.png' },
        { id: 5, name: 'Rajesh Kumar', role: 'Developer', initials: 'RK', bg: '#d81b60', avatar: null },
        { id: 60, name: 'Shachish Sneh', role: 'Solution Architect & Technology Master', initials: 'RO', bg: '#0d9488', avatar: null },
        { id: 62, name: 'Shiv Narayan', role: 'Developer', initials: 'SN', bg: '#9333ea', avatar: null }
    ],
    pickCatMenu: false,
    schedulePopover: false,
    scheduleBottomPopover: false,
    scheduleDay: 'Tomorrow',
    scheduleTime: '9:00am',
    draftSavedToast: false,
    filterCat: '',
    catMenu: false,
    sortMenu: false,
    sortOrder: 'newest',
    msgFilter: '',
    init() {
        try {
            const raw = localStorage.getItem(this.storageKey);
            if (raw) {
                const parsed = JSON.parse(raw);
                if (parsed && typeof parsed === 'object') {
                    this.draftTitle = parsed.title || '';
                    this.draftContent = parsed.content || '';
                    this.selectedCat = parsed.category || '';
                    this.visibilityMode = parsed.visibilityMode || 'team';
                    this.notifyMode = parsed.notifyMode || 'project';
                    if (parsed.isOpen || (this.draftTitle.trim() !== '' || this.draftContent.trim() !== '')) {
                        if ({{ request()->has('new') ? 'false' : 'true' }} && parsed.isOpen) {
                            this.newMsgOpen = true;
                        }
                    }
                }
            }
        } catch (e) {}
    },
    persistDraft(openState = null) {
        try {
            const isOpenVal = openState !== null ? openState : this.newMsgOpen;
            localStorage.setItem(this.storageKey, JSON.stringify({
                title: this.draftTitle || '',
                content: this.draftContent || '',
                category: this.selectedCat || '',
                visibilityMode: this.visibilityMode || 'team',
                notifyMode: this.notifyMode || 'project',
                isOpen: !!isOpenVal,
                updatedAt: Date.now()
            }));
        } catch (e) {}
    },
    saveDraftExplicit() {
        this.persistDraft(true);
        this.schedulePopover = false;
        this.scheduleBottomPopover = false;
        this.draftSavedToast = true;
        setTimeout(() => { this.draftSavedToast = false; }, 2400);
    },
    clearDraftOnPost() {
        try {
            localStorage.removeItem(this.storageKey);
        } catch (e) {}
    },
    openComposer() {
        this.newMsgOpen = true;
        this.persistDraft(true);
        try {
            const url = new URL(window.location.href);
            url.searchParams.set('new', '1');
            window.history.replaceState({}, '', url.toString());
        } catch (e) {}
        this.$nextTick(() => {
            const titleEl = document.getElementById('bc-new-msg-title');
            if (titleEl) titleEl.focus();
        });
    },
    closeComposerToBoard() {
        this.newMsgOpen = false;
        this.persistDraft(false);
        try {
            const url = new URL(window.location.href);
            url.searchParams.delete('new');
            window.history.replaceState({}, '', url.toString());
        } catch (e) {}
    },
    insertFormat(prefix, suffix = '') {
        const area = document.getElementById('bc-new-msg-body');
        if (!area) return;
        const start = area.selectionStart || 0;
        const end = area.selectionEnd || 0;
        const text = this.draftContent || '';
        const selected = text.substring(start, end);
        this.draftContent = text.substring(0, start) + prefix + selected + suffix + text.substring(end);
        this.persistDraft(true);
        this.$nextTick(() => {
            area.focus();
            const cursor = start + prefix.length + selected.length + suffix.length;
            area.setSelectionRange(cursor, cursor);
        });
    }
}" class="max-w-[920px] mx-auto pb-16">

    <!-- Basecamp White Paper Sheet -->
    <div class="bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-84px)] bc-main-sheet">

        <!-- ========================================================================= -->
        <!-- MODE 1: FULL-SHEET NEW MESSAGE COMPOSER (Matches media_1793345323115.png) -->
        <!-- ========================================================================= -->
        <template x-if="newMsgOpen">
            <form action="{{ route('projects.discussions.store', $project->id) }}"
                  method="POST"
                  @submit="if (!draftTitle.trim()) { draftTitle = 'Untitled'; } clearDraftOnPost()"
                  class="flex flex-col min-h-[calc(100vh-84px)]">
                @csrf
                <input type="hidden" name="category" :value="selectedCat || 'General'">

                <!-- Sticky Top Bar: Project ‹ Message Board | Clock | Save a draft | Post this message -->
                <div class="px-5 py-2 border-b border-[#ece9e2] flex items-center justify-between text-[13.5px] bg-white rounded-t-[10px] sticky top-0 z-30">
                    <div class="flex items-center gap-1.5 truncate">
                        <a href="{{ route('projects.show', $project->id) }}" class="font-bold text-[#1d2d35] hover:underline">
                            {{ $project->name }}
                        </a>
                        <span class="text-[#6d6a65] text-[12px]">‹</span>
                        <a href="{{ route('projects.discussions', $project->id) }}"
                           @click.prevent="closeComposerToBoard()"
                           class="text-[#1d2d35] hover:underline cursor-pointer">
                            Message Board
                        </a>
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0 relative">
                        <span x-show="draftSavedToast"
                              x-cloak
                              class="text-[12px] font-semibold text-[#199849] mr-1">
                            Draft saved ✓
                        </span>

                        <!-- Schedule / Clock Button + Popover (Matches media_1793345323115_81aeb5f5.png) -->
                        <div class="relative" @click.outside="schedulePopover = false">
                            <button type="button"
                                    @click="schedulePopover = !schedulePopover; scheduleBottomPopover = false"
                                    class="p-1.5 text-[#1b6ac9] hover:bg-[#f0f6ff] rounded-full transition-colors cursor-pointer"
                                    title="Schedule message">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="9"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>
                                </svg>
                            </button>

                            <!-- Schedule Popover Card -->
                            <div x-show="schedulePopover"
                                 x-cloak
                                 class="absolute right-[-165px] top-[calc(100%+10px)] w-[328px] rounded-[12px] bg-white border border-[#e5e2dc] shadow-[0_10px_32px_rgba(0,0,0,0.14)] p-5 z-40 text-left">
                                <!-- Top Pointer Triangle -->
                                <div class="absolute -top-2 right-[172px] w-3.5 h-3.5 bg-white border-l border-t border-[#e5e2dc] rotate-45"></div>

                                <h4 class="text-[15.5px] font-bold text-[#1d2d35] mb-3.5">
                                    When would you like to post this?
                                </h4>

                                <div class="flex items-center gap-2 mb-4">
                                    <select x-model="scheduleDay"
                                            class="flex-1 h-[36px] px-3 rounded-[6px] border border-[#d8d4cc] bg-white text-[13.5px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                                        <option value="Tomorrow">Tomorrow</option>
                                        <option value="Monday">Monday</option>
                                        <option value="Next week">Next week</option>
                                    </select>
                                    <span class="text-[13.5px] text-[#1d2d35]">at</span>
                                    <select x-model="scheduleTime"
                                            class="w-[116px] h-[36px] px-3 rounded-[6px] border border-[#d8d4cc] bg-white text-[13.5px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                                        <option value="8:00am">8:00am</option>
                                        <option value="9:00am">9:00am</option>
                                        <option value="10:00am">10:00am</option>
                                        <option value="12:00pm">12:00pm</option>
                                        <option value="2:00pm">2:00pm</option>
                                        <option value="5:00pm">5:00pm</option>
                                    </select>
                                </div>

                                <button type="button"
                                        @click="saveDraftExplicit()"
                                        class="w-full py-2 rounded-[6px] bg-[#1b6ac9] hover:bg-[#1557a6] text-white font-semibold text-[13.5px] transition-colors cursor-pointer">
                                    Schedule and save
                                </button>

                                <p class="mt-3.5 text-[12px] text-[#6d6a65] leading-snug">
                                    Note: Messages scheduled to post later are saved in <a href="{{ route('my.drafts') }}" class="underline hover:text-[#1d2d35]">My Drafts</a>.
                                </p>
                            </div>
                        </div>

                        <!-- Save a draft -->
                        <button type="button"
                                @click="saveDraftExplicit()"
                                class="px-3 py-1 rounded-[5px] border border-[#bfd7f6] bg-white hover:bg-[#f5f9ff] text-[#1b6ac9] text-[13px] font-medium transition-colors cursor-pointer">
                            Save a draft
                        </button>

                        <!-- Post this message -->
                        <button type="submit"
                                class="px-3.5 py-1 rounded-[5px] bg-[#1b6ac9] hover:bg-[#1557a6] text-white text-[13px] font-bold transition-colors cursor-pointer">
                            Post this message
                        </button>
                    </div>
                </div>

                <!-- Category Picker & Large Title Input -->
                <div class="px-8 sm:px-14 pt-7 pb-5 border-b border-[#ece9e2]">
                    <!-- [Pick a category (optional) v] -->
                    <div class="relative inline-block" @click.outside="pickCatMenu = false">
                        <button type="button"
                                @click="pickCatMenu = !pickCatMenu"
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-[6px] border border-[#d8d4cc] bg-white hover:bg-slate-50 text-[13px] text-[#1d2d35] cursor-pointer">
                            <span x-text="selectedCat ? selectedCat : 'Pick a category (optional)'">Pick a category (optional)</span>
                            <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="pickCatMenu"
                             x-cloak
                             class="absolute left-0 top-[calc(100%+6px)] w-[210px] rounded-[8px] bg-white border border-[#d8d4cc] shadow-[0_8px_24px_rgba(0,0,0,0.12)] py-2 px-3 z-30 text-left">
                            <button type="button" @click="selectedCat = ''; pickCatMenu = false; persistDraft(true)" class="w-full py-1.5 text-left text-[13px] text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer">
                                No category
                            </button>
                            <button type="button" @click="selectedCat = 'Announcement'; pickCatMenu = false; persistDraft(true)" class="w-full py-1.5 text-left text-[13px] text-[#1d2d35] hover:text-[#1b6ac9] cursor-pointer">
                                📢 Announcement
                            </button>
                            <button type="button" @click="selectedCat = 'FYI / Update'; pickCatMenu = false; persistDraft(true)" class="w-full py-1.5 text-left text-[13px] text-[#1d2d35] hover:text-[#1b6ac9] cursor-pointer">
                                👋 FYI / Update
                            </button>
                            <button type="button" @click="selectedCat = 'General'; pickCatMenu = false; persistDraft(true)" class="w-full py-1.5 text-left text-[13px] text-[#1d2d35] hover:text-[#1b6ac9] cursor-pointer">
                                💬 General
                            </button>
                        </div>
                    </div>

                    <!-- Type a title... (Auto-saved in localStorage on input) -->
                    <input type="text"
                           id="bc-new-msg-title"
                           name="title"
                           x-model="draftTitle"
                           @input="persistDraft(true)"
                           placeholder="Type a title..."
                           class="mt-3 w-full bg-transparent border-0 p-0 text-[30px] sm:text-[34px] font-extrabold text-[#1d2d35] placeholder-[#9e9a93] tracking-[-0.025em] leading-[1.15] focus:outline-none focus:ring-0">
                </div>

                <!-- Sticky Basecamp Rich Text Formatting Toolbar (Matches media_1793345369741_fb358d4f.png) -->
                <div class="px-8 sm:px-14 py-2.5 border-b border-[#ece9e2] flex flex-wrap items-center justify-between gap-2 text-[#1d2d35] bg-white sticky top-[45px] z-20">
                    <div class="flex flex-wrap items-center gap-1 sm:gap-1.5">
                        <!-- Image -->
                        <button type="button" @click="insertFormat('[Image: ', ']')" class="p-1.5 rounded hover:bg-[#f2f0ec] text-[#1d2d35] cursor-pointer" title="Insert image">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="16" rx="2"/>
                                <circle cx="8.5" cy="9.5" r="1.5"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 20"/>
                            </svg>
                        </button>
                        <!-- Paperclip -->
                        <button type="button" @click="insertFormat('[Attachment: ', ']')" class="p-1.5 rounded hover:bg-[#f2f0ec] text-[#1d2d35] cursor-pointer" title="Attach a file">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                        </button>

                        <span class="h-4 w-px bg-[#e5e2dc] mx-1"></span>

                        <!-- Bold -->
                        <button type="button" @click="insertFormat('**', '**')" class="px-2 py-1 rounded hover:bg-[#f2f0ec] font-extrabold text-[14px] text-[#1d2d35] cursor-pointer" title="Bold">B</button>
                        <!-- Italic -->
                        <button type="button" @click="insertFormat('_', '_')" class="px-2 py-1 rounded hover:bg-[#f2f0ec] italic font-serif text-[14.5px] text-[#1d2d35] cursor-pointer" title="Italic">I</button>
                        <!-- Strikethrough -->
                        <button type="button" @click="insertFormat('~~', '~~')" class="px-2 py-1 rounded hover:bg-[#f2f0ec] line-through text-[14px] font-semibold text-[#1d2d35] cursor-pointer" title="Strikethrough">S</button>
                        <!-- Heading Tt v -->
                        <button type="button" @click="insertFormat('### ')" class="px-1.5 py-1 rounded hover:bg-[#f2f0ec] inline-flex items-center gap-0.5 text-[13px] font-bold text-[#1d2d35] cursor-pointer" title="Heading">
                            <span>Tt</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <!-- Color droplet v -->
                        <button type="button" class="px-1.5 py-1 rounded hover:bg-[#f2f0ec] inline-flex items-center gap-0.5 text-[#1d2d35] cursor-pointer" title="Text color">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z"/>
                            </svg>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <!-- Link -->
                        <button type="button" @click="insertFormat('[', '](https://)')" class="p-1.5 rounded hover:bg-[#f2f0ec] text-[#1d2d35] cursor-pointer" title="Link">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                        </button>

                        <span class="h-4 w-px bg-[#e5e2dc] mx-1"></span>

                        <!-- Quote -->
                        <button type="button" @click="insertFormat('> ')" class="px-2 py-1 rounded hover:bg-[#f2f0ec] font-serif font-black text-[16px] leading-none text-[#1d2d35] cursor-pointer" title="Quote">”</button>
                        <!-- Code -->
                        <button type="button" @click="insertFormat('`', '`')" class="px-1.5 py-1 rounded hover:bg-[#f2f0ec] font-mono text-[12.5px] font-bold text-[#1d2d35] cursor-pointer" title="Code">&lt;&gt;</button>
                        <!-- Bullet list -->
                        <button type="button" @click="insertFormat('• ')" class="p-1.5 rounded hover:bg-[#f2f0ec] text-[#1d2d35] cursor-pointer" title="Bulleted list">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
                            </svg>
                        </button>
                        <!-- Numbered list -->
                        <button type="button" @click="insertFormat('1. ')" class="p-1.5 rounded hover:bg-[#f2f0ec] text-[#1d2d35] cursor-pointer" title="Numbered list">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6h11M10 12h11M10 18h11M4 6h1v4M4 10h2M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/>
                            </svg>
                        </button>
                        <!-- Table -->
                        <button type="button" class="p-1.5 rounded hover:bg-[#f2f0ec] text-[#1d2d35] cursor-pointer" title="Insert table">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <path d="M3 9h18M3 15h18M9 3v18M15 3v18"/>
                            </svg>
                        </button>
                        <!-- Divider -->
                        <button type="button" @click="insertFormat('\n---\n')" class="p-1.5 rounded hover:bg-[#f2f0ec] text-[#1d2d35] cursor-pointer" title="Horizontal divider">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" d="M4 12h16M8 8h8M8 16h8"/>
                            </svg>
                        </button>
                        <!-- Microphone -->
                        <button type="button" class="p-1.5 rounded hover:bg-[#f2f0ec] text-[#1d2d35] cursor-pointer" title="Record audio clip">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 10v2a7 7 0 01-14 0v-2M12 19v4m-4 0h8"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Right Undo / Redo -->
                    <div class="flex items-center gap-1 text-[#b0aca5]">
                        <button type="button" onclick="document.execCommand('undo')" class="p-1.5 rounded hover:bg-[#f2f0ec] hover:text-[#1d2d35] cursor-pointer" title="Undo">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v2M3 10l4-4M3 10l4 4"/>
                            </svg>
                        </button>
                        <button type="button" onclick="document.execCommand('redo')" class="p-1.5 rounded hover:bg-[#f2f0ec] hover:text-[#1d2d35] cursor-pointer" title="Redo">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 10H11a5 5 0 00-5 5v2m15-7l-4-4m4 4l-4 4"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Write away... Body (Auto-saved in localStorage on input) -->
                <div class="px-8 sm:px-14 pt-6 pb-8 flex-1 flex flex-col">
                    <textarea id="bc-new-msg-body"
                              name="content"
                              rows="11"
                              required
                              x-model="draftContent"
                              @input="persistDraft(true)"
                              placeholder="Write away..."
                              class="w-full flex-1 bg-transparent border-0 p-0 text-[16.5px] text-[#1d2d35] placeholder-[#8c8881] leading-relaxed focus:outline-none focus:ring-0 resize-none"></textarea>
                </div>

                <!-- Bottom "Who should see this?" & "When I post this, notify..." Box (Matches media_1793346426529_ed8b694d.jpg) -->
                <div class="mx-8 sm:mx-14 mb-12 rounded-[10px] bg-[#faf7f2] px-6 py-5">
                    <!-- Who should see this? -->
                    <p class="text-[14px] font-bold text-[#1d2d35] mb-2">
                        Who should see this?
                    </p>
                    <div class="space-y-2 text-[14.5px] text-[#1d2d35] mb-5">
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input type="radio" name="visibility_option" value="team" x-model="visibilityMode" @change="persistDraft(true)" class="w-4 h-4 accent-[#1d2d35]">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#dbeafe] text-[#1d2d35] text-[13.5px] font-medium">
                                <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Our team only—private to us</span>
                            </span>
                        </label>

                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input type="radio" name="visibility_option" value="client" x-model="visibilityMode" @change="persistDraft(true)" class="w-4 h-4 accent-[#1d2d35]">
                            <span class="inline-flex items-center gap-1.5 px-1 text-[#1d2d35] text-[14px]">
                                <svg class="w-4 h-4 text-[#1d2d35]" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Allow the client to see this too</span>
                            </span>
                        </label>
                    </div>

                    <!-- When I post this, notify... -->
                    <p class="text-[14px] font-bold text-[#1d2d35] mb-2">
                        When I post this, notify...
                    </p>
                    <div class="space-y-2 text-[14.5px] text-[#1d2d35]">
                        <!-- Option 1: Only the 7 people + Avatar Row -->
                        <div>
                            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                                <input type="radio" name="notify_option" value="project" x-model="notifyMode" @change="persistDraft(true)" class="w-4 h-4 accent-[#1d2d35]">
                                <span>Only the {{ $notifyCount }} people who asked to be notified about this project</span>
                            </label>
                            <div class="pl-6 mt-1.5 mb-1 flex items-center gap-1">
                                <span class="w-[24px] h-[24px] rounded-full bg-[#e879f9] text-white font-bold text-[9.5px] flex items-center justify-center select-none">GK</span>
                                <span class="relative w-[24px] h-[24px] rounded-full overflow-hidden inline-flex items-center justify-center">
                                    <img src="/uploads/avatars/22369668.png" alt="Manjot Singh" class="w-full h-full object-cover">
                                    <span class="absolute bottom-0 inset-x-0 h-[38%] bg-[#facc15] flex items-center justify-center">
                                        <span class="text-[4.5px] font-black text-[#1d1c1b] leading-none">OUT</span>
                                    </span>
                                </span>
                                <span class="w-[24px] h-[24px] rounded-full bg-[#00897b] text-white font-bold text-[9.5px] flex items-center justify-center select-none">PK</span>
                                <img src="/uploads/avatars/22369670.png" alt="Prashant" class="w-[24px] h-[24px] rounded-full object-cover">
                                <span class="w-[24px] h-[24px] rounded-full bg-[#e879f9] text-white font-bold text-[9.5px] flex items-center justify-center select-none">RK</span>
                                <span class="w-[24px] h-[24px] rounded-full bg-[#00897b] text-white font-bold text-[9.5px] flex items-center justify-center select-none">RO</span>
                                <span class="w-[24px] h-[24px] rounded-full bg-[#9333ea] text-white font-bold text-[9.5px] flex items-center justify-center select-none">SN</span>
                            </div>
                        </div>

                        <!-- Option 2: Everyone I select... -->
                        <div>
                            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                                <input type="radio" name="notify_option" value="select" x-model="notifyMode" @change="persistDraft(true)" class="w-4 h-4 accent-[#1d2d35]">
                                <span>Everyone I select...</span>
                            </label>
                            <div x-show="notifyMode === 'select'" x-cloak class="pl-6 mt-2 mb-2 grid grid-cols-1 sm:grid-cols-2 gap-2 max-w-[520px]">
                                <template x-for="np in notifyPeopleList" :key="np.id">
                                    <label class="flex items-center gap-2 px-2.5 py-1.5 rounded-[6px] bg-white border border-[#e5e2dc] text-[13px] cursor-pointer select-none">
                                        <input type="checkbox" :value="np.id" x-model="notifySelectedIds" class="w-3.5 h-3.5 accent-[#1b6ac9]">
                                        <span class="font-semibold text-[#1d2d35] truncate" x-text="np.name"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- Option 3: No one -->
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input type="radio" name="notify_option" value="none" x-model="notifyMode" @change="persistDraft(true)" class="w-4 h-4 accent-[#1d2d35]">
                            <span>No one</span>
                        </label>
                    </div>

                    <!-- Bottom Buttons inside Notify Box: [Post this message] [Save a draft] [Clock] -->
                    <div class="mt-5 flex items-center gap-2.5 relative">
                        <button type="submit"
                                class="px-4 py-2 rounded-[6px] bg-[#1b6ac9] hover:bg-[#1557a6] text-white text-[14px] font-bold transition-colors cursor-pointer">
                            Post this message
                        </button>

                        <button type="button"
                                @click="saveDraftExplicit()"
                                class="px-4 py-2 rounded-[6px] border border-[#bfd7f6] bg-white hover:bg-[#f5f9ff] text-[#1b6ac9] text-[14px] font-medium transition-colors cursor-pointer">
                            Save a draft
                        </button>

                        <button type="button"
                                @click="schedulePopover = true; window.scrollTo({ top: 0, behavior: 'smooth' })"
                                class="p-1.5 text-[#1b6ac9] hover:bg-[#f0f6ff] rounded-full transition-colors cursor-pointer"
                                title="Schedule message">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </template>

        <!-- ========================================================================= -->
        <!-- MODE 2: MESSAGE BOARD LIST VIEW (Matches media_1793345240284_1a440234.png) -->
        <!-- ========================================================================= -->
        <template x-if="!newMsgOpen">
            <div>
                <!-- Sticky Top Sub-Header Bar: Project | Bookmark ••• -->
                <div class="px-5 py-2.5 border-b border-[#ece9e2] flex items-center justify-between text-[13.5px] bg-white rounded-t-[10px] sticky top-0 z-10">
                    <div class="flex items-center gap-1.5 truncate">
                        <a href="{{ route('projects.show', $project->id) }}" class="font-bold text-[#1d2d35] hover:underline">
                            {{ $project->name }}
                        </a>
                    </div>

                    <div class="flex items-center gap-4 text-[13px] text-[#1d2d35] flex-shrink-0">
                        <button type="button" onclick="window.toggleBookmark && window.toggleBookmark(this)" class="inline-flex items-center gap-1 text-[#1d2d35] hover:underline cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-[#4a4640]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                            </svg>
                            <span data-bookmark-label>Bookmark</span>
                        </button>
                        <button type="button" class="text-[#1d2d35] hover:bg-slate-100 rounded p-0.5 transition-colors cursor-pointer" title="More options">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Main Message Board Content -->
                <div class="px-8 sm:px-14 pt-8 pb-16">

                    <!-- Title: Message Board -->
                    <h1 class="text-[28px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-[1.15]">
                        Message Board
                    </h1>

                    <!-- Basecamp Toolbar Row: [+ New message] [All messages | Categories v] [Newest post v] [Filter...] -->
                    <div class="mt-3 flex flex-wrap items-center gap-2 relative">
                        <button type="button"
                                @click="openComposer()"
                                class="inline-flex items-center gap-1 px-3 py-1 rounded-[5px] bg-[#1b6ac9] hover:bg-[#1557a6] text-white font-bold text-[13px] transition-colors cursor-pointer">
                            <span>+ New message</span>
                        </button>

                        <!-- [All messages | Categories v] -->
                        <div class="relative inline-flex items-center" @click.outside="catMenu = false">
                            <div class="inline-flex items-center border border-[#d8d4cc] rounded-[5px] bg-white text-[13px] text-[#1d2d35] overflow-hidden">
                                <button type="button"
                                        @click="filterCat = ''; catMenu = false"
                                        :class="!filterCat ? 'font-bold bg-[#f2f0ec]' : 'font-normal bg-white hover:bg-slate-50'"
                                        class="px-3 py-1 text-[#1d2d35] cursor-pointer transition-colors">
                                    All messages
                                </button>
                                <button type="button"
                                        @click="catMenu = !catMenu; sortMenu = false"
                                        :class="filterCat ? 'font-bold bg-[#f2f0ec]' : 'bg-white hover:bg-slate-50'"
                                        class="px-3 py-1 border-l border-[#d8d4cc] inline-flex items-center gap-1 text-[#1d2d35] cursor-pointer transition-colors">
                                    <span x-text="filterCat ? filterCat : 'Categories'">Categories</span>
                                    <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>

                            <div x-show="catMenu"
                                 x-cloak
                                 class="absolute left-[96px] top-[calc(100%+6px)] w-[184px] rounded-[8px] bg-white border border-[#d8d4cc] shadow-[0_8px_24px_rgba(0,0,0,0.12)] py-2.5 px-3.5 z-30 text-left">
                                <div class="text-[13px] font-bold text-[#1d2d35] mb-1.5">Categories</div>
                                <button type="button" @click="filterCat = 'Announcement'; catMenu = false" class="w-full py-1.5 flex items-center justify-between text-[13px] text-[#1d2d35] hover:text-[#1b6ac9] cursor-pointer">
                                    <span>Announcement</span>
                                    <svg x-show="filterCat === 'Announcement'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button type="button" @click="filterCat = 'General'; catMenu = false" class="w-full py-1.5 flex items-center justify-between text-[13px] text-[#1d2d35] hover:text-[#1b6ac9] cursor-pointer">
                                    <span>General</span>
                                    <svg x-show="filterCat === 'General'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button type="button" @click="filterCat = 'FYI / Update'; catMenu = false" class="w-full py-1.5 flex items-center justify-between text-[13px] text-[#1d2d35] hover:text-[#1b6ac9] cursor-pointer">
                                    <span>FYI / Update</span>
                                    <svg x-show="filterCat === 'FYI / Update'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- [Newest post v] -->
                        <div class="relative inline-flex items-center" @click.outside="sortMenu = false">
                            <button type="button"
                                    @click="sortMenu = !sortMenu; catMenu = false"
                                    class="inline-flex items-center gap-1 px-3 py-1 border border-[#d8d4cc] rounded-[5px] bg-white hover:bg-slate-50 text-[13px] text-[#1d2d35] cursor-pointer">
                                <span x-text="sortOrder === 'newest' ? 'Newest post' : 'Latest comment'">Newest post</span>
                                <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="sortMenu"
                                 x-cloak
                                 class="absolute left-0 top-[calc(100%+6px)] w-[184px] rounded-[8px] bg-white border border-[#d8d4cc] shadow-[0_8px_24px_rgba(0,0,0,0.12)] py-2.5 px-3.5 z-30 text-left">
                                <div class="text-[13px] font-bold text-[#1d2d35] mb-1.5">Sort by...</div>
                                <button type="button" @click="sortOrder = 'newest'; sortMenu = false" class="w-full py-1.5 flex items-center justify-between text-[13px] text-[#1d2d35] hover:text-[#1b6ac9] cursor-pointer">
                                    <span>Newest post</span>
                                    <svg x-show="sortOrder === 'newest'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button type="button" @click="sortOrder = 'comments'; sortMenu = false" class="w-full py-1.5 flex items-center justify-between text-[13px] text-[#1d2d35] hover:text-[#1b6ac9] cursor-pointer">
                                    <span>Latest comment</span>
                                    <svg x-show="sortOrder === 'comments'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- [Filter...] -->
                        <input type="text"
                               x-model="msgFilter"
                               placeholder="Filter..."
                               class="px-3 py-1 bg-white border border-[#d8d4cc] rounded-[5px] text-[13px] text-[#1d2d35] placeholder-[#8c8881] w-[155px] focus:outline-none focus:border-[#1b6ac9]">
                    </div>

                    <!-- Messages List (Matches media_1793345240284_1a440234.png) -->
                    <div class="mt-6 border-t border-[#ece9e2] divide-y divide-[#ece9e2]">
                        @forelse($discussions as $disc)
                            @php
                                $raw = trim($disc->content ?? '');
                                $aName = $disc->author->name ?? 'Team Member';
                                $dDate = $disc->created_at
                                    ? ($disc->created_at->format('Y') === '2026' ? $disc->created_at->format('M j') : $disc->created_at->format('M j, Y'))
                                    : 'Oct 29';
                                $snippet = $raw;

                                if (preg_match('/^([^•\n]+)\s*•\s*([A-Za-z]{3}\s+\d{1,2},\s+\d{4})(?:\s+at\s+\d{1,2}:\d{2}\s*[AP]M)?\s*•?\s*(.*)$/is', $raw, $m)) {
                                    if ($aName === 'Ajath Infotech' || empty($disc->author)) {
                                        $aName = trim($m[1]);
                                    }
                                    $parsedDate = preg_replace('/\s+0(\d),/', ' $1,', trim($m[2]));
                                    $dDate = str_ends_with($parsedDate, ', 2026')
                                        ? substr($parsedDate, 0, -6)
                                        : $parsedDate;
                                    $snippet = trim($m[3]);
                                }
                                $snippet = ltrim($snippet, "•· \t\n\r");

                                $words = preg_split('/\s+/', trim($aName));
                                $inits = count($words) >= 2
                                    ? strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1))
                                    : strtoupper(substr($aName, 0, 1));
                                $bg = $badgeColors[$inits] ?? '#d946ef';
                                $searchBlob = strtolower(addslashes($disc->title . ' ' . $aName . ' ' . $snippet));
                            @endphp

                            <a href="{{ route('projects.discussions.show', [$project->id, $disc->id]) }}"
                               x-show="(!filterCat || '{{ $disc->category }}' === filterCat) && (!msgFilter || '{{ $searchBlob }}'.includes(msgFilter.toLowerCase()))"
                               class="py-5 flex items-start gap-4 hover:bg-[#fbfaf8] -mx-3 px-3 rounded-lg transition-colors group">
                                @if(stripos($aName, 'Manjot') !== false)
                                    <img src="/uploads/avatars/22369668.png" alt="{{ $aName }}" class="w-[44px] h-[44px] rounded-full object-cover flex-shrink-0 mt-0.5 select-none">
                                @elseif(stripos($aName, 'Shachish') !== false)
                                    <img src="/uploads/avatars/25036340.png" alt="{{ $aName }}" class="w-[44px] h-[44px] rounded-full object-cover flex-shrink-0 mt-0.5 select-none">
                                @elseif(stripos($aName, 'Harshvardhan') !== false)
                                    <img src="/uploads/avatars/47630095.png" alt="{{ $aName }}" class="w-[44px] h-[44px] rounded-full object-cover flex-shrink-0 mt-0.5 select-none">
                                @elseif(stripos($aName, 'Nirdesh') !== false)
                                    <img src="/uploads/avatars/48060513.png" alt="{{ $aName }}" class="w-[44px] h-[44px] rounded-full object-cover flex-shrink-0 mt-0.5 select-none">
                                @else
                                    <span class="w-[44px] h-[44px] rounded-full text-white font-bold text-[16px] flex items-center justify-center flex-shrink-0 mt-0.5 select-none"
                                          style="background-color: {{ $bg }}">
                                        {{ $inits }}
                                    </span>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <h3 class="text-[17px] font-bold text-[#1d2d35] group-hover:underline leading-snug truncate">
                                        {{ $disc->title }}
                                    </h3>
                                    <p class="text-[15px] leading-[1.38] mt-0.5 line-clamp-2">
                                        <span class="text-[#856d47]">{{ $aName }} • {{ $dDate }}{{ $snippet ? ' •' : '' }}</span>
                                        @if($snippet)
                                            <span class="text-[#1d2d35]"> {{ $snippet }}</span>
                                        @endif
                                    </p>
                                </div>

                                @if($disc->comments->count() > 0)
                                    <span class="w-6 h-6 rounded-full bg-[#1b6ac9] text-white font-bold text-[11px] flex items-center justify-center flex-shrink-0 mt-1">
                                        {{ $disc->comments->count() }}
                                    </span>
                                @endif
                            </a>
                        @empty
                            <div class="py-16 text-center text-[15px] text-[#6d6a65]">
                                No messages posted on this Message Board yet.
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>
        </template>

    </div>

</div>
@endsection
