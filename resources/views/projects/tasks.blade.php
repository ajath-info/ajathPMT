@extends('layouts.app')

@section('title', 'To-dos - ' . $project->name)

@section('content')
@php
    $hasDependencyChecklist = $tasks->contains(function ($t) {
        return trim((string) ($t->list_name ?? '')) === 'Dependency Checklist';
    });
    $defaultListName = $project->id == 4
        ? 'Birthday List'
        : ($hasDependencyChecklist ? 'Dependency Checklist' : 'To-dos');

    $groupedTasks = $tasks->groupBy(function ($task) use ($project, $defaultListName) {
        $name = trim((string) ($task->list_name ?? ''));
        if ($name === '' || $name === 'To-dos' || $name === 'Dependency Checklist') {
            return $defaultListName;
        }
        return $name;
    });

    $hasCompletedLists = $groupedTasks->contains(function ($group) {
        return $group->isNotEmpty() && $group->where('status', '!=', 'DONE')->isEmpty();
    });

    $assignableMembers = $project->members->isNotEmpty()
        ? $project->members
        : \App\Models\User::orderBy('name')->take(12)->get();

    // Exact Basecamp Birthday List metadata for Project 4
    $birthdayMetaMap = [
        0 => ['two_assignees' => true, 'date' => 'Oct 18, 2024', 'show_on_card' => true],
        1 => ['two_assignees' => true, 'date' => 'Sep 18, 2024', 'show_on_card' => true],
        2 => ['two_assignees' => true, 'date' => 'Aug 18, 2024', 'show_on_card' => true],
        3 => ['two_assignees' => true, 'date' => 'Sep 18, 2024', 'show_on_card' => true],
        4 => ['two_assignees' => true, 'date' => 'Jul 1, 2024', 'show_on_card' => true],
        5 => ['two_assignees' => false, 'date' => 'Oct 21, 2024', 'show_on_card' => true, 'inline_on_card' => true],
        6 => ['two_assignees' => true, 'date' => 'Jun 23, 2024', 'show_on_card' => true],
        7 => ['two_assignees' => true, 'date' => 'Jul 4, 2024', 'show_on_card' => true],
        8 => ['two_assignees' => true, 'date' => 'Sep 24, 2024', 'show_on_card' => false],
        9 => ['two_assignees' => true, 'date' => 'Dec 5, 2024', 'show_on_card' => false],
    ];
@endphp

<style>
.bc-client-badge {
    background-color: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}
html.bc-theme-dark .bc-client-badge {
    background-color: #4d3816 !important;
    color: #eec06b !important;
    border-color: #6b4e20 !important;
}
</style>

<script>
window.bcTodoForm = function () {
    return {
        assignName: '',
        assignId: '',
        assignOpen: false,
        assignHover: 0,
        notifyName: '',
        notifyOpen: false,
        notifyHover: 0,
        dueExpanded: false,
        dueMode: 'none',
        dueDate: '',
        dueStartDate: '',
        dueEndDate: '',
        notesExpanded: false,
        notesText: '',
        subtasksExpanded: false,
        subtaskTitle: '',
        subtaskAssignName: '',
        subtaskAssignOpen: false,
        subtaskAssignHover: 0,
        subtaskDue: '',
        subtasksList: [],
        people: [
            { id: 60, name: 'Shachish Sneh', initials: 'SS', bg: '#00a884', avatar: '/uploads/avatars/25036340.png', ooo: null },
            { id: 62, name: 'Shiv Narayan', initials: 'SN', bg: '#9333ea', avatar: null, ooo: null },
            { id: 1, name: 'Manjot Singh', initials: 'MS', bg: '#1d4ed8', avatar: '/uploads/avatars/22369668.png', ooo: 'OUT (back on Nov 06)' },
            { id: 4, name: 'Gaurav Khulbey', initials: 'GK', bg: '#e879f9', avatar: null, ooo: null },
            { id: 2, name: 'Pawandeep Kaur', initials: 'PK', bg: '#00897b', avatar: null, ooo: null },
            { id: 3, name: 'Prashant', initials: 'P', bg: '#0284c7', avatar: '/uploads/avatars/22369670.png', ooo: null },
            { id: 5, name: 'Rajesh Kumar', initials: 'RK', bg: '#e879f9', avatar: null, ooo: null },
            { id: 6, name: 'Rishabh Ojha', initials: 'RO', bg: '#00897b', avatar: null, ooo: null },
            { id: 47, name: 'Harshvardhan Singh', initials: 'HS', bg: '#c026d3', avatar: '/uploads/avatars/47630095.png', ooo: null },
            { id: 148, name: 'Nirdesh Verma', initials: 'NV', bg: '#0d9488', avatar: '/uploads/avatars/48060513.png', ooo: null },
            { id: 147, name: 'Nikhil Aneja', initials: 'NA', bg: '#0284c7', avatar: '/uploads/avatars/48443395.png', ooo: null }
        ],
        filterPeople(q) {
            const query = (q || '').trim().toLowerCase();
            if (!query) return this.people;
            const starts = [];
            const others = [];
            this.people.forEach((p) => {
                const lower = p.name.toLowerCase();
                if (lower.startsWith(query)) {
                    starts.push(p);
                } else if (lower.includes(query)) {
                    others.push(p);
                }
            });
            return starts.concat(others);
        },
        addSubtaskRow() {
            if (!this.subtaskTitle.trim()) return;
            this.subtasksList.push({
                title: this.subtaskTitle.trim(),
                assignee: this.subtaskAssignName.trim(),
                due: this.subtaskDue
            });
            this.subtaskTitle = '';
            this.subtaskAssignName = '';
            this.subtaskDue = '';
        }
    };
};
</script>

<div class="min-h-screen bg-[#f6f2ec] pb-16 pt-1 px-3 sm:px-6"
     x-data="{
        viewMode: '{{ $project->id == 4 ? 'cards' : 'list' }}',
        showCompletedLists: window.location.hash === '#show-completed',
        activeList: new URLSearchParams(window.location.search).get('list') || null,
        showAllCompletedInList: false,
        expandedCompleted: {},
        isCompletedExpanded(name, defaultExpanded) {
            if (typeof this.expandedCompleted[name] === 'boolean') {
                return this.expandedCompleted[name];
            }
            return defaultExpanded;
        },
        toggleCompletedInList(name, defaultExpanded) {
            this.expandedCompleted[name] = !this.isCompletedExpanded(name, defaultExpanded);
        },
        filterQuery: '',
        newListOpen: false,
        newListName: '',
        addTodoOpen: false,
        moreMenuOpen: false,
        todosToast: '',
        addTodoListName: '{{ addslashes($defaultListName) }}',
        showTodosToast(msg) {
            this.todosToast = msg;
            setTimeout(() => { this.todosToast = ''; }, 2400);
        },
        toggleCompletedLists() {
            this.showCompletedLists = !this.showCompletedLists;
            if (this.showCompletedLists) {
                history.replaceState(null, '', '#show-completed');
            } else {
                history.replaceState(null, '', window.location.pathname + window.location.search);
            }
        },
        openList(name) {
            this.activeList = name;
            this.showAllCompletedInList = false;
            this.addTodoOpen = false;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        closeList() {
            this.activeList = null;
            this.showAllCompletedInList = false;
            this.addTodoOpen = false;
        },
        openAddTodo(listName) {
            this.addTodoListName = listName || '{{ addslashes($defaultListName) }}';
            this.addTodoOpen = true;
            this.$nextTick(() => {
                if (this.$refs.todoTitleInput) {
                    this.$refs.todoTitleInput.focus();
                }
            });
        },
        matchesFilter(text) {
            if (!this.filterQuery.trim()) return true;
            return text.toLowerCase().includes(this.filterQuery.trim().toLowerCase());
        }
     }">

    <!-- Action Feedback Toast -->
    <div x-show="todosToast"
         x-cloak
         x-transition.opacity
         class="fixed top-14 left-1/2 -translate-x-1/2 z-50 px-4 py-2 rounded-full bg-[#1d2d35] text-white text-[13px] font-semibold shadow-lg">
        <span x-text="todosToast"></span>
    </div>

    <!-- Basecamp White Paper Sheet -->
    <div class="w-full sm:w-[72vw] max-w-[1090px] mx-auto bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-92px)]">

        <!-- Top Sub-Bar -->
        <div class="px-5 py-2.5 border-b border-[#ece9e2] flex items-center justify-between text-[13px]">
            <div class="flex items-center flex-wrap gap-1">
                <a href="{{ route('projects.show', $project->id) }}" class="font-bold text-[#1d2d35] hover:underline">
                    {{ $project->name }}
                </a>
                <template x-if="activeList !== null">
                    <span class="inline-flex items-center gap-1">
                        <span class="text-[#9b978f] mx-0.5">‹</span>
                        <button type="button" @click="closeList()" class="text-[#1d2d35] hover:underline cursor-pointer">
                            To-dos
                        </button>
                    </span>
                </template>
            </div>

            <div class="flex items-center gap-3 text-[#6d6a65]">
                <button type="button" onclick="window.toggleBookmark && window.toggleBookmark(this)" class="inline-flex items-center gap-1 text-[12.5px] hover:text-[#1d2d35] hover:underline cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#6d6a65]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                    </svg>
                    <span data-bookmark-label>Bookmark</span>
                </button>
                <div class="relative inline-block">
                    <button type="button"
                            @click="moreMenuOpen = !moreMenuOpen"
                            :class="moreMenuOpen ? 'bg-[#e5e2dc]' : 'hover:bg-[#f6f2ec]'"
                            class="w-7 h-7 rounded-full border border-[#d6d2c9] inline-flex items-center justify-center text-[#1d2d35] cursor-pointer transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm4 2a2 2 0 100-4 2 2 0 000 4z"/>
                        </svg>
                    </button>

                    <div x-show="moreMenuOpen"
                         x-cloak
                         @click.outside="moreMenuOpen = false"
                         class="absolute right-0 mt-1.5 w-[224px] bg-white rounded-[10px] border border-[#e5e2dc] shadow-[0_8px_28px_rgba(0,0,0,0.12)] py-2 z-50 text-[13.5px] text-[#1d2d35]">
                        <button type="button" @click="moreMenuOpen = false; showTodosToast('Spotlighted on project page')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span>Spotlight on project page</span>
                        </button>
                        <button type="button" @click="moreMenuOpen = false; showTodosToast('Bubbled up to the top')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <svg class="w-4 h-4 text-[#6d6a65] shrink-0 fill-current" viewBox="0 0 24 24">
                                <circle cx="9" cy="14" r="4"/>
                                <circle cx="16" cy="9" r="3"/>
                                <circle cx="10.5" cy="5" r="2"/>
                            </svg>
                            <span>Bubble up</span>
                        </button>
                        <button type="button" @click="navigator.clipboard && navigator.clipboard.writeText(window.location.href); moreMenuOpen = false; showTodosToast('Link copied to clipboard')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <svg class="w-4 h-4 text-[#6d6a65] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            <span>Copy link</span>
                        </button>

                        <div class="my-1.5 border-t border-[#ece9e2]"></div>

                        <button type="button" @click="moreMenuOpen = false; showTodosToast('Viewing change log')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span>View change log</span>
                        </button>
                        <button type="button" @click="moreMenuOpen = false; newListOpen = true" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span>New list from template...</span>
                        </button>

                        <div class="my-1.5 border-t border-[#ece9e2]"></div>

                        <button type="button" @click="moreMenuOpen = false; showTodosToast('Copied to clipboard')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span>Copy...</span>
                        </button>
                        <button type="button" @click="moreMenuOpen = false; showTodosToast('Select destination project to move')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span>Move...</span>
                        </button>
                        <button type="button" @click="moreMenuOpen = false; showTodosToast('Archived')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 cursor-pointer">
                            <span class="w-4 shrink-0"></span>
                            <span>Archive</span>
                        </button>
                        <button type="button" @click="moreMenuOpen = false; showTodosToast('Moved to trash')" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] flex items-center gap-2.5 text-[#1d2d35] cursor-pointer">
                            <svg class="w-4 h-4 text-[#6d6a65] shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Put in the trash</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paper Content -->
        <div class="px-8 sm:px-14 pt-8 pb-16">

            <!-- ========================================================= -->
            <!-- VIEW A: MAIN TO-DOS INDEX (activeList === null)           -->
            <!-- ========================================================= -->
            <div x-show="activeList === null">

                <!-- Main Heading -->
                <h1 class="text-[28px] sm:text-[31px] font-black text-[#1d2d35] tracking-[-0.02em] leading-tight">
                    To-dos
                </h1>

                <!-- Toolbar Row -->
                <div class="mt-2.5 flex flex-wrap items-center gap-2.5">
                    <!-- + New list button -->
                    <button type="button"
                            @click="newListOpen = !newListOpen"
                            class="bg-[#2573d8] hover:bg-[#1e5eb3] text-white font-semibold text-[13px] px-3.5 py-1.5 rounded-[5px] inline-flex items-center gap-1 transition cursor-pointer">
                        <span class="text-[15px] leading-none font-normal">+</span>
                        <span>New list</span>
                    </button>

                    <!-- Filter... input -->
                    <input type="text"
                           x-model="filterQuery"
                           placeholder="Filter..."
                           class="w-[155px] px-3 py-1 bg-white border border-[#d6d2c9] rounded-[6px] text-[13.5px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none focus:border-[#1b6ac9]">

                    <!-- View Switcher: List vs Cards -->
                    <div class="inline-flex items-center rounded-[6px] border border-[#d6d2c9] bg-white overflow-hidden">
                        <!-- List view button (left) -->
                        <button type="button"
                                @click="viewMode = 'list'"
                                :class="viewMode === 'list' ? 'bg-[#e5e2dc] text-[#1d2d35]' : 'bg-white text-[#6d6a65] hover:text-[#1d2d35]'"
                                class="px-2 py-1.5 inline-flex items-center justify-center border-r border-[#d6d2c9] transition cursor-pointer"
                                title="View as list">
                            <svg class="w-4 h-4" viewBox="0 0 16 16" fill="currentColor">
                                <circle cx="2.5" cy="3.5" r="1.2"/>
                                <rect x="5" y="2.6" width="9" height="1.8" rx="0.6"/>
                                <circle cx="2.5" cy="8" r="1.2"/>
                                <rect x="5" y="7.1" width="9" height="1.8" rx="0.6"/>
                                <circle cx="2.5" cy="12.5" r="1.2"/>
                                <rect x="5" y="11.6" width="9" height="1.8" rx="0.6"/>
                            </svg>
                        </button>
                        <!-- Card/Grid view button (right) -->
                        <button type="button"
                                @click="viewMode = 'cards'"
                                :class="viewMode === 'cards' ? 'bg-[#e5e2dc] text-[#1d2d35]' : 'bg-white text-[#6d6a65] hover:text-[#1d2d35]'"
                                class="px-2 py-1.5 inline-flex items-center justify-center transition cursor-pointer"
                                title="View as cards">
                            <svg class="w-4 h-4" viewBox="0 0 16 16" fill="currentColor">
                                <rect x="2" y="2" width="5" height="5" rx="0.8"/>
                                <rect x="9" y="2" width="5" height="5" rx="0.8"/>
                                <rect x="2" y="9" width="5" height="5" rx="0.8"/>
                                <rect x="9" y="9" width="5" height="5" rx="0.8"/>
                            </svg>
                        </button>
                    </div>

                    @if($hasCompletedLists)
                    <!-- Show completed lists Switch -->
                    <div class="inline-flex items-center gap-2 ml-0.5">
                        <button type="button"
                                @click="toggleCompletedLists()"
                                :class="showCompletedLists ? 'bg-[#198744] ring-2 ring-[#1b6ac9]/35' : 'bg-[#8c8881]'"
                                class="w-[44px] h-[23px] rounded-[6px] p-[3px] inline-flex items-center justify-between text-white font-bold text-[11px] transition cursor-pointer select-none"
                                :aria-pressed="showCompletedLists.toString()">
                            <template x-if="!showCompletedLists">
                                <span class="w-full flex items-center justify-between">
                                    <span class="w-[12px] h-[17px] rounded-[3px] bg-white shadow-xs"></span>
                                    <span class="pr-0.5 leading-none">Off</span>
                                </span>
                            </template>
                            <template x-if="showCompletedLists">
                                <span class="w-full flex items-center justify-between">
                                    <span class="pl-0.5 leading-none">On</span>
                                    <span class="w-[12px] h-[17px] rounded-[3px] bg-white shadow-xs"></span>
                                </span>
                            </template>
                        </button>
                        <span @click="toggleCompletedLists()" class="text-[13.5px] text-[#1d2d35] cursor-pointer select-none">
                            Show completed lists
                        </span>
                    </div>
                    @endif
                </div>

                <!-- Inline "+ New list" Creation Card -->
                <div x-show="newListOpen" x-transition class="mt-5 max-w-[540px] rounded-[8px] border border-[#d6d2c9] bg-[#fbf9f5] p-4">
                    <form action="{{ route('projects.tasks.store', $project->id) }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="hidden" name="priority" value="MEDIUM">
                        <div>
                            <input type="text"
                                   name="list_name"
                                   x-model="newListName"
                                   required
                                   placeholder="Name this list..."
                                   class="w-full px-3 py-1.5 bg-white border border-[#d6d2c9] rounded-[6px] text-[14px] font-bold text-[#1d2d35] placeholder-[#8c8881] focus:outline-none focus:border-[#1b6ac9]">
                        </div>
                        <div>
                            <input type="text"
                                   name="title"
                                   required
                                   placeholder="First to-do in this list..."
                                   class="w-full px-3 py-1.5 bg-white border border-[#d6d2c9] rounded-[6px] text-[13.5px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none focus:border-[#1b6ac9]">
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                            <button type="submit" class="px-3.5 py-1.5 rounded-full bg-[#198744] hover:bg-[#146c36] text-white font-bold text-[12.5px] cursor-pointer">
                                Add this list
                            </button>
                            <button type="button" @click="newListOpen = false" class="px-3 py-1.5 rounded-full border border-[#d6d2c9] bg-white text-[#6d6a65] text-[12.5px] hover:text-[#1d2d35] cursor-pointer">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Top "Add a to-do" Composer & Row (Matches media_1793346443493_c5650083.jpg & media_1793346448256_2c099007.jpg) -->
                <div class="mt-7" x-data="bcTodoForm()">
                    <!-- Inline Add a To-do Form (Basecamp 1:1 Layout) -->
                    <div x-show="addTodoOpen && activeList === null" x-cloak class="mb-5">
                        <form action="{{ route('projects.tasks.store', $project->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="list_name" :value="addTodoListName">
                            <input type="hidden" name="priority" value="MEDIUM">
                            <input type="hidden" name="assigned_to" :value="assignId">

                            <!-- Top Row: Checkbox + Describe this to-do... with full-width bottom border -->
                            <div class="pb-2 border-b border-[#ece9e2] flex items-center gap-2.5 relative">
                                <span class="w-[18px] h-[18px] rounded-[4px] border border-[#6d6a65] bg-transparent flex-shrink-0"></span>
                                <input type="text"
                                       name="title"
                                       x-ref="todoTitleInput"
                                       required
                                       placeholder="Describe this to-do..."
                                       style="background:transparent !important;border:0 !important;box-shadow:none !important;"
                                       class="w-full text-[15.5px] text-[#1d2d35] placeholder-[#8c8881] p-0 focus:outline-none focus:ring-0">
                            </div>

                            <!-- 5 Field Rows -->
                            <div class="mt-3.5 space-y-3.5 text-[13.5px]">
                                <!-- 1. Assigned to (Matches media_1793346443493_c5650083.jpg 1:1) -->
                                <div class="flex items-center gap-3 relative" @click.outside="assignOpen = false">
                                    <label class="w-[92px] text-right font-bold text-[#1d2d35] flex-shrink-0">Assigned to</label>
                                    <div class="flex-1 relative">
                                        <input type="text"
                                               x-model="assignName"
                                               @focus="assignOpen = true; assignHover = 0"
                                               @input="assignOpen = true; assignHover = 0; assignId = ''"
                                               placeholder="Type names to assign..."
                                               style="background:transparent !important;border:0 !important;box-shadow:none !important;"
                                               class="w-full text-[13.5px] text-[#1d2d35] placeholder-[#8c8881] p-0 focus:outline-none focus:ring-0">
                                        <div x-show="assignOpen && filterPeople(assignName).length > 0"
                                             x-cloak
                                             class="absolute left-0 top-[calc(100%+4px)] w-[330px] max-h-[210px] overflow-y-auto rounded-[8px] border border-[#d6d2c9] bg-white shadow-[0_8px_24px_rgba(0,0,0,0.14)] p-1 z-30">
                                            <template x-for="(person, pIdx) in filterPeople(assignName)" :key="person.id">
                                                <button type="button"
                                                        @mouseenter="assignHover = pIdx"
                                                        @click="assignName = person.name; assignId = person.id; assignOpen = false"
                                                        :class="assignHover === pIdx ? 'bg-[#1b6ac9] text-white' : 'bg-white text-[#1d2d35]'"
                                                        class="w-full px-2.5 py-1.5 rounded-[5px] flex items-center gap-2.5 text-left text-[13.5px] transition-colors cursor-pointer">
                                                    <template x-if="person.avatar">
                                                        <span class="relative w-[24px] h-[24px] rounded-full flex-shrink-0 inline-flex">
                                                            <img :src="person.avatar" alt="" class="w-[24px] h-[24px] rounded-full object-cover">
                                                            <span x-show="person.ooo" class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 bg-[#ffd500] text-[#1d2d35] text-[6.5px] font-black uppercase px-1 rounded-[2px] leading-tight">OUT</span>
                                                        </span>
                                                    </template>
                                                    <template x-if="!person.avatar">
                                                        <span class="w-[24px] h-[24px] rounded-full text-white text-[10px] font-bold inline-flex items-center justify-center flex-shrink-0"
                                                              :style="'background:' + person.bg"
                                                              x-text="person.initials"></span>
                                                    </template>
                                                    <span class="font-medium truncate" x-text="person.name"></span>
                                                    <span x-show="person.ooo"
                                                          :class="assignHover === pIdx ? 'text-white/85' : 'text-[#8c8881]'"
                                                          class="text-[11.5px] font-normal whitespace-nowrap"
                                                          x-text="person.ooo"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. When done -->
                                <div class="flex items-center gap-3 relative" @click.outside="notifyOpen = false">
                                    <label class="w-[92px] text-right font-bold text-[#1d2d35] flex-shrink-0">When done</label>
                                    <div class="flex-1 relative">
                                        <input type="text"
                                               x-model="notifyName"
                                               @focus="notifyOpen = true; notifyHover = 0"
                                               @input="notifyOpen = true; notifyHover = 0"
                                               placeholder="Notify these people..."
                                               style="background:transparent !important;border:0 !important;box-shadow:none !important;"
                                               class="w-full text-[13.5px] text-[#1d2d35] placeholder-[#8c8881] p-0 focus:outline-none focus:ring-0">
                                        <div x-show="notifyOpen && filterPeople(notifyName).length > 0"
                                             x-cloak
                                             class="absolute left-0 top-[calc(100%+4px)] w-[330px] max-h-[210px] overflow-y-auto rounded-[8px] border border-[#d6d2c9] bg-white shadow-[0_8px_24px_rgba(0,0,0,0.14)] p-1 z-30">
                                            <template x-for="(person, nIdx) in filterPeople(notifyName)" :key="'n-' + person.id">
                                                <button type="button"
                                                        @mouseenter="notifyHover = nIdx"
                                                        @click="notifyName = person.name; notifyOpen = false"
                                                        :class="notifyHover === nIdx ? 'bg-[#1b6ac9] text-white' : 'bg-white text-[#1d2d35]'"
                                                        class="w-full px-2.5 py-1.5 rounded-[5px] flex items-center gap-2.5 text-left text-[13.5px] transition-colors cursor-pointer">
                                                    <template x-if="person.avatar">
                                                        <span class="relative w-[24px] h-[24px] rounded-full flex-shrink-0 inline-flex">
                                                            <img :src="person.avatar" alt="" class="w-[24px] h-[24px] rounded-full object-cover">
                                                            <span x-show="person.ooo" class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 bg-[#ffd500] text-[#1d2d35] text-[6.5px] font-black uppercase px-1 rounded-[2px] leading-tight">OUT</span>
                                                        </span>
                                                    </template>
                                                    <template x-if="!person.avatar">
                                                        <span class="w-[24px] h-[24px] rounded-full text-white text-[10px] font-bold inline-flex items-center justify-center flex-shrink-0"
                                                              :style="'background:' + person.bg"
                                                              x-text="person.initials"></span>
                                                    </template>
                                                    <span class="font-medium truncate" x-text="person.name"></span>
                                                    <span x-show="person.ooo"
                                                          :class="notifyHover === nIdx ? 'text-white/85' : 'text-[#8c8881]'"
                                                          class="text-[11.5px] font-normal whitespace-nowrap"
                                                          x-text="person.ooo"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Due on (Matches media_1793346448256_2c099007.jpg 1:1) -->
                                <div class="flex items-start gap-3">
                                    <label class="w-[92px] text-right font-bold text-[#1d2d35] flex-shrink-0 pt-0.5">Due on</label>
                                    <div class="flex-1">
                                        <div x-show="!dueExpanded"
                                             @click="dueExpanded = true"
                                             class="text-[13.5px] text-[#8c8881] cursor-pointer py-0.5 hover:text-[#1d2d35]">
                                            Select a due date...
                                        </div>
                                        <div x-show="dueExpanded" x-cloak class="space-y-1.5 pt-0.5">
                                            <label class="flex items-center gap-2 cursor-pointer text-[13.5px] text-[#1d2d35]">
                                                <input type="radio" value="none" x-model="dueMode" class="w-4 h-4 accent-[#1d2d35]">
                                                <span>No due date</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer text-[13.5px] text-[#1d2d35]">
                                                <input type="radio" value="single" x-model="dueMode" class="w-4 h-4 accent-[#1d2d35]">
                                                <span>A specific day</span>
                                            </label>
                                            <div x-show="dueMode === 'single'" x-cloak class="pl-6 pt-1 pb-1">
                                                <input type="date" name="due_date" x-model="dueDate" class="px-2.5 py-1 border border-[#d6d2c9] rounded-[5px] text-[13px] text-[#1d2d35] bg-white focus:outline-none focus:border-[#1b6ac9]">
                                            </div>
                                            <label class="flex items-center gap-2 cursor-pointer text-[13.5px] text-[#1d2d35]">
                                                <input type="radio" value="range" x-model="dueMode" class="w-4 h-4 accent-[#1d2d35]">
                                                <span>Runs for multiple days</span>
                                            </label>
                                            <div x-show="dueMode === 'range'" x-cloak class="pl-6 pt-1 pb-1 flex items-center gap-2">
                                                <input type="date" x-model="dueStartDate" class="px-2.5 py-1 border border-[#d6d2c9] rounded-[5px] text-[13px] text-[#1d2d35] bg-white focus:outline-none focus:border-[#1b6ac9]">
                                                <span class="text-[#6d6a65] text-[12.5px]">to</span>
                                                <input type="date" x-model="dueEndDate" @change="dueDate = dueEndDate" class="px-2.5 py-1 border border-[#d6d2c9] rounded-[5px] text-[13px] text-[#1d2d35] bg-white focus:outline-none focus:border-[#1b6ac9]">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 4. Notes (Rich Text Box matching media_1793346448256_2c099007.jpg 1:1) -->
                                <div class="flex items-start gap-3">
                                    <label class="w-[92px] text-right font-bold text-[#1d2d35] flex-shrink-0 pt-0.5">Notes</label>
                                    <div class="flex-1">
                                        <div x-show="!notesExpanded"
                                             @click="notesExpanded = true; $nextTick(() => $refs.todoNotesArea && $refs.todoNotesArea.focus())"
                                             class="text-[13.5px] text-[#8c8881] cursor-text py-0.5 hover:text-[#1d2d35]">
                                            Add extra details or attach a file...
                                        </div>
                                        <div x-show="notesExpanded" x-cloak class="rounded-[8px] border border-[#e5e2dc] bg-white overflow-hidden">
                                            <!-- Basecamp 15-Icon Formatting Toolbar + Right Undo/Redo -->
                                            <div class="px-2.5 py-1.5 border-b border-[#ece9e2] flex items-center justify-between gap-1 flex-wrap text-[#1d2d35]">
                                                <div class="flex items-center gap-0.5 flex-wrap">
                                                    <button type="button" class="p-1.5 rounded hover:bg-[#f5f3ef] cursor-pointer" title="Insert image">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                                                    </button>
                                                    <button type="button" class="p-1.5 rounded hover:bg-[#f5f3ef] cursor-pointer" title="Attach a file">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
                                                    </button>
                                                    <span class="w-[1px] h-4 bg-[#e5e2dc] mx-1"></span>
                                                    <button type="button" class="px-2 py-1 rounded hover:bg-[#f5f3ef] font-extrabold text-[13.5px] cursor-pointer" title="Bold">B</button>
                                                    <button type="button" class="px-2 py-1 rounded hover:bg-[#f5f3ef] italic font-serif text-[14px] cursor-pointer" title="Italic">I</button>
                                                    <button type="button" class="px-2 py-1 rounded hover:bg-[#f5f3ef] line-through text-[13.5px] cursor-pointer" title="Strikethrough">S</button>
                                                    <button type="button" class="px-1.5 py-1 rounded hover:bg-[#f5f3ef] text-[12.5px] font-bold inline-flex items-center gap-0.5 cursor-pointer" title="Heading">Tt <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg></button>
                                                    <button type="button" class="px-1.5 py-1 rounded hover:bg-[#f5f3ef] inline-flex items-center gap-0.5 cursor-pointer" title="Color">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z"/></svg>
                                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                                                    </button>
                                                    <span class="w-[1px] h-4 bg-[#e5e2dc] mx-1"></span>
                                                    <button type="button" class="p-1.5 rounded hover:bg-[#f5f3ef] cursor-pointer" title="Link">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                                                    </button>
                                                    <button type="button" class="px-2 py-1 rounded hover:bg-[#f5f3ef] font-serif font-bold text-[15px] cursor-pointer" title="Quote">”</button>
                                                    <button type="button" class="px-1.5 py-1 rounded hover:bg-[#f5f3ef] font-mono text-[12px] font-bold cursor-pointer" title="Code">&lt;&gt;</button>
                                                    <button type="button" class="p-1.5 rounded hover:bg-[#f5f3ef] cursor-pointer" title="Bulleted list">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                                                    </button>
                                                    <button type="button" class="p-1.5 rounded hover:bg-[#f5f3ef] cursor-pointer" title="Numbered list">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/></svg>
                                                    </button>
                                                    <button type="button" class="p-1.5 rounded hover:bg-[#f5f3ef] cursor-pointer" title="Table">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="12" y1="3" x2="12" y2="21"/></svg>
                                                    </button>
                                                    <button type="button" class="p-1.5 rounded hover:bg-[#f5f3ef] cursor-pointer" title="Divider">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><line x1="8" y1="8" x2="16" y2="8"/><line x1="8" y1="16" x2="16" y2="16"/></svg>
                                                    </button>
                                                    <button type="button" class="p-1.5 rounded hover:bg-[#f5f3ef] cursor-pointer" title="Record audio clip">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3z"/><path d="M19 10v2a7 7 0 01-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
                                                    </button>
                                                </div>
                                                <div class="flex items-center gap-1 text-[#b5b1a8]">
                                                    <button type="button" class="p-1 hover:text-[#6d6a65] cursor-pointer" title="Undo">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 00-9-9 9 9 0 00-6 2.3L3 13"/></svg>
                                                    </button>
                                                    <button type="button" class="p-1 hover:text-[#6d6a65] cursor-pointer" title="Redo">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 7v6h-6"/><path d="M3 17a9 9 0 019-9 9 9 0 016 2.3l3 2.7"/></svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <textarea name="description"
                                                      x-ref="todoNotesArea"
                                                      x-model="notesText"
                                                      rows="5"
                                                      placeholder="Add extra details or attach a file..."
                                                      style="background:transparent !important;border:0 !important;box-shadow:none !important;"
                                                      class="w-full px-3.5 py-3 text-[14px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none focus:ring-0 resize-y"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- 5. Subtasks (Expandable Subtask Builder matching media_1793346448256_2c099007.jpg 1:1) -->
                                <div class="flex items-start gap-3">
                                    <label class="w-[92px] text-right font-bold text-[#1d2d35] flex-shrink-0 pt-0.5">Subtasks</label>
                                    <div class="flex-1">
                                        <div x-show="!subtasksExpanded"
                                             @click="subtasksExpanded = true; $nextTick(() => $refs.subtaskTitleInput && $refs.subtaskTitleInput.focus())"
                                             class="text-[13.5px] text-[#8c8881] cursor-text py-0.5 hover:text-[#1d2d35]">
                                            Add subtasks
                                        </div>
                                        <div x-show="subtasksExpanded" x-cloak class="space-y-3">
                                            <!-- Added subtasks preview list -->
                                            <template x-for="(st, stIdx) in subtasksList" :key="stIdx">
                                                <div class="flex items-center gap-2.5 text-[13.5px] text-[#1d2d35] pb-1.5 border-b border-[#ece9e2]">
                                                    <span class="w-[16px] h-[16px] rounded-full border border-[#bfbcb4] flex-shrink-0"></span>
                                                    <span class="font-medium" x-text="st.title"></span>
                                                    <span x-show="st.assignee" class="text-[12px] text-[#6d6a65]" x-text="'· ' + st.assignee"></span>
                                                    <span x-show="st.due" class="text-[12px] text-[#6d6a65]" x-text="'· ' + st.due"></span>
                                                </div>
                                            </template>

                                            <!-- Active Subtask Row (◯ Describe this subtask...) -->
                                            <div class="pb-1.5 border-b border-[#ece9e2] flex items-center gap-2.5">
                                                <span class="w-[16px] h-[16px] rounded-full border border-[#bfbcb4] bg-transparent flex-shrink-0"></span>
                                                <input type="text"
                                                       x-ref="subtaskTitleInput"
                                                       x-model="subtaskTitle"
                                                       @keydown.enter.prevent="addSubtaskRow()"
                                                       placeholder="Describe this subtask..."
                                                       style="background:transparent !important;border:0 !important;box-shadow:none !important;"
                                                       class="w-full text-[14px] text-[#1d2d35] placeholder-[#8c8881] p-0 focus:outline-none focus:ring-0">
                                            </div>

                                            <!-- Subtask Assigned to -->
                                            <div class="flex items-center gap-3 pl-5 relative" @click.outside="subtaskAssignOpen = false">
                                                <label class="w-[82px] text-right text-[13px] text-[#1d2d35] flex-shrink-0">Assigned to</label>
                                                <div class="flex-1 relative">
                                                    <input type="text"
                                                           x-model="subtaskAssignName"
                                                           @focus="subtaskAssignOpen = true; subtaskAssignHover = 0"
                                                           @input="subtaskAssignOpen = true; subtaskAssignHover = 0"
                                                           placeholder="Type names to assign..."
                                                           style="background:transparent !important;border:0 !important;box-shadow:none !important;"
                                                           class="w-full text-[13px] text-[#1d2d35] placeholder-[#8c8881] p-0 focus:outline-none focus:ring-0">
                                                    <div x-show="subtaskAssignOpen && filterPeople(subtaskAssignName).length > 0"
                                                         x-cloak
                                                         class="absolute left-0 top-[calc(100%+4px)] w-[300px] max-h-[190px] overflow-y-auto rounded-[8px] border border-[#d6d2c9] bg-white shadow-lg p-1 z-30">
                                                        <template x-for="(person, sIdx) in filterPeople(subtaskAssignName)" :key="'st-' + person.id">
                                                            <button type="button"
                                                                    @mouseenter="subtaskAssignHover = sIdx"
                                                                    @click="subtaskAssignName = person.name; subtaskAssignOpen = false"
                                                                    :class="subtaskAssignHover === sIdx ? 'bg-[#1b6ac9] text-white' : 'bg-white text-[#1d2d35]'"
                                                                    class="w-full px-2.5 py-1.5 rounded-[5px] flex items-center gap-2 text-left text-[13px] cursor-pointer">
                                                                <template x-if="person.avatar">
                                                                    <img :src="person.avatar" alt="" class="w-[22px] h-[22px] rounded-full object-cover flex-shrink-0">
                                                                </template>
                                                                <template x-if="!person.avatar">
                                                                    <span class="w-[22px] h-[22px] rounded-full text-white text-[9.5px] font-bold inline-flex items-center justify-center flex-shrink-0"
                                                                          :style="'background:' + person.bg"
                                                                          x-text="person.initials"></span>
                                                                </template>
                                                                <span class="truncate" x-text="person.name"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Subtask Due on (Choose date input with calendar icon) -->
                                            <div class="flex items-center gap-3 pl-5">
                                                <label class="w-[82px] text-right text-[13px] text-[#1d2d35] flex-shrink-0">Due on</label>
                                                <div class="relative w-[178px]">
                                                    <input type="text"
                                                           x-model="subtaskDue"
                                                           onfocus="this.type='date'"
                                                           onblur="if(!this.value)this.type='text'"
                                                           placeholder="Choose date"
                                                           class="w-full px-3 py-1 pr-8 rounded-[6px] border border-[#e5e2dc] bg-white text-[13px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none focus:border-[#1b6ac9]">
                                                    <svg class="w-3.5 h-3.5 text-[#8c8881] absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons: Add this to-do & Cancel -->
                            <div class="pl-[18px] mt-4 flex items-center gap-2">
                                <button type="submit" class="px-3.5 py-1.5 rounded-[5px] bg-[#2573d8] hover:bg-[#1e5eb3] text-white font-semibold text-[13px] transition-colors cursor-pointer">
                                    Add this to-do
                                </button>
                                <button type="button" @click="addTodoOpen = false" class="px-3 py-1.5 rounded-[5px] border border-[#d6d2c9] bg-transparent text-[#2573d8] font-medium text-[13px] hover:bg-[#fbfaf8] transition-colors cursor-pointer">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- "☐ Add a to-do" Row (Hidden while the inline Add a to-do form is open, matching media_1793328977612_1c568b79.png) -->
                    <div x-show="!addTodoOpen" class="flex items-center gap-2.5">
                        <span class="w-[16px] h-[16px] rounded-[3px] border border-[#8c8881]/70 bg-transparent flex-shrink-0"></span>
                        <button type="button"
                                @click="openAddTodo('{{ addslashes($defaultListName) }}')"
                                class="text-[15px] text-[#1b6ac9] underline hover:text-[#1557a8] cursor-pointer">
                            Add a to-do
                        </button>
                    </div>
                </div>

                <!-- ===================================================== -->
                <!-- LIST MODE (viewMode === 'list')                       -->
                <!-- ===================================================== -->
                <div x-show="viewMode === 'list'" class="mt-7 space-y-8">
                    @foreach($groupedTasks as $listName => $group)
                        @php
                            $openTasks = $group->where('status', '!=', 'DONE')->values();
                            $completedTasks = $group->where('status', 'DONE')->values();
                            $totalCount = $group->count();
                            $isListAllDone = $totalCount > 0 && $openTasks->isEmpty();
                            $defaultExpandedCompleted = $completedTasks->count() <= 15 ? 'true' : 'false';
                        @endphp
                        <div x-show="(!{{ $isListAllDone ? 'true' : 'false' }} || showCompletedLists) && matchesFilter('{{ addslashes(strtolower($listName)) }}')"
                             class="relative">
                            <!-- List Title Row (Matches media_1793289930913_bd8e7a73.jpg) -->
                            <div class="flex flex-wrap items-center gap-2 relative">
                                <span class="w-[15px] h-[15px] rounded-full bg-[#2ea44f] flex-shrink-0 inline-block"></span>
                                <button type="button"
                                        @click="openList('{{ addslashes($listName) }}')"
                                        class="text-[16.5px] font-extrabold text-[#1d2d35] hover:underline text-left cursor-pointer">
                                    {{ $listName }}
                                </button>
                                @if($listName === 'Dependency Checklist')
                                    <span class="bc-client-badge inline-flex items-center gap-1 px-2 py-[1px] rounded-full text-[11px] font-medium">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 3C5 3 1.73 7.11 1 10c.73 2.89 4 7 9 7s8.27-4.11 9-7c-.73-2.89-4-7-9-7zm0 11a4 4 0 110-8 4 4 0 010 8zm0-6a2 2 0 100 4 2 2 0 000-4z"/>
                                        </svg>
                                        <span>The client sees this</span>
                                    </span>
                                @endif
                            </div>

                            <!-- Open To-dos (if any) -->
                            @if($openTasks->isNotEmpty())
                                <div class="mt-2 space-y-2">
                                    @foreach($openTasks as $task)
                                        <div class="flex flex-wrap items-center gap-2">
                                            <form action="{{ route('projects.tasks.toggle', [$project->id, $task->id]) }}" method="POST" class="inline-flex">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="w-[16px] h-[16px] rounded-[3px] border border-[#8c969e] bg-transparent hover:border-[#2ea44f] flex-shrink-0 cursor-pointer"></button>
                                            </form>
                                            <a href="{{ route('projects.tasks.show', [$project->id, $task->id]) }}" class="text-[15.5px] text-[#1d2d35] hover:underline">
                                                {{ $task->title }}
                                            </a>
                                            @if(($task->comments_count ?? 0) > 0)
                                                <a href="{{ route('projects.tasks.show', [$project->id, $task->id]) }}"
                                                   class="w-[18px] h-[18px] rounded-full bg-[#388bfd] text-white text-[11px] font-bold inline-flex items-center justify-center leading-none flex-shrink-0">
                                                    {{ $task->comments_count }}
                                                </a>
                                            @endif
                                            @if($task->assignee || $task->due_date)
                                                <span class="inline-flex items-center gap-1 bg-[#f2f0ec] rounded-[5px] px-1.5 py-[1px] text-[12px] text-[#6d6a65]">
                                                    @if($task->assignee)
                                                        <span>{{ $task->assignee->name }}</span>
                                                    @endif
                                                    @if($task->due_date)
                                                        <svg class="w-3 h-3 text-[#6d6a65]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="1.8"/>
                                                            <path d="M16 2v4M8 2v4M3 10h18" stroke-width="1.8"/>
                                                        </svg>
                                                        <span>{{ \Carbon\Carbon::parse($task->due_date)->format('M j, Y') }}</span>
                                                    @endif
                                                </span>
                                            @endif
                                            @if(!empty($task->description))
                                                <a href="{{ route('projects.tasks.show', [$project->id, $task->id]) }}"
                                                   class="inline-flex items-center justify-center bg-[#f2f0ec] rounded-[5px] px-1.5 py-[2px] text-[#6d6a65] hover:text-[#1d2d35]"
                                                   title="Has notes">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Indented Add a to-do link -->
                            <div class="mt-2 pl-6">
                                <button type="button"
                                        @click="openAddTodo('{{ addslashes($listName) }}')"
                                        class="text-[15px] text-[#1b6ac9] underline hover:text-[#1557a8] cursor-pointer">
                                    Add a to-do
                                </button>
                            </div>

                            <!-- Inline Collapsible Completed To-dos Section (Matches media_1793289930913_bd8e7a73.jpg) -->
                            @if($completedTasks->isNotEmpty())
                                <div class="mt-2.5">
                                    <button type="button"
                                            @click="toggleCompletedInList('{{ addslashes($listName) }}', {{ $defaultExpandedCompleted }})"
                                            class="inline-flex items-center gap-2 text-[15px] text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer select-none">
                                        <span class="w-[16px] h-[16px] rounded-[3px] border border-[#6d6a65]/50 inline-flex items-center justify-center text-[#6d6a65]">
                                            <svg x-show="isCompletedExpanded('{{ addslashes($listName) }}', {{ $defaultExpandedCompleted }})" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                            </svg>
                                            <svg x-show="!isCompletedExpanded('{{ addslashes($listName) }}', {{ $defaultExpandedCompleted }})" x-cloak class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </span>
                                        <span x-text="isCompletedExpanded('{{ addslashes($listName) }}', {{ $defaultExpandedCompleted }}) ? 'Hide completed' : '{{ $completedTasks->count() }} completed'">Hide completed</span>
                                    </button>

                                    <div x-show="isCompletedExpanded('{{ addslashes($listName) }}', {{ $defaultExpandedCompleted }})" class="mt-2 space-y-2">
                                        @foreach($completedTasks as $task)
                                            <div class="flex flex-wrap items-center gap-2">
                                                <form action="{{ route('projects.tasks.toggle', [$project->id, $task->id]) }}" method="POST" class="inline-flex">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="w-[16px] h-[16px] rounded-[3px] border border-[#2ea44f]/75 text-[#2ea44f] inline-flex items-center justify-center flex-shrink-0 hover:bg-[#2ea44f]/10 cursor-pointer"
                                                            title="Mark incomplete">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </button>
                                                </form>

                                                <a href="{{ route('projects.tasks.show', [$project->id, $task->id]) }}"
                                                   class="text-[15.5px] text-[#6d6a65] hover:text-[#1d2d35] hover:underline">
                                                    {{ $task->title }}
                                                </a>

                                                @if(($task->comments_count ?? 0) > 0)
                                                    <a href="{{ route('projects.tasks.show', [$project->id, $task->id]) }}"
                                                       class="w-[18px] h-[18px] rounded-full bg-[#388bfd] text-white text-[11px] font-bold inline-flex items-center justify-center leading-none flex-shrink-0">
                                                        {{ $task->comments_count }}
                                                    </a>
                                                @endif

                                                @if($task->assignee || $task->due_date)
                                                    <span class="inline-flex items-center gap-1 bg-[#f2f0ec] rounded-[5px] px-1.5 py-[1px] text-[12px] text-[#6d6a65]">
                                                        @if($task->assignee)
                                                            <span>{{ $task->assignee->name }}</span>
                                                        @endif
                                                        @if($task->due_date)
                                                            <svg class="w-3 h-3 text-[#6d6a65]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="1.8"/>
                                                                <path d="M16 2v4M8 2v4M3 10h18" stroke-width="1.8"/>
                                                            </svg>
                                                            <span>{{ \Carbon\Carbon::parse($task->due_date)->format('M j, Y') }}</span>
                                                        @endif
                                                    </span>
                                                @endif

                                                @if(!empty($task->description))
                                                    <a href="{{ route('projects.tasks.show', [$project->id, $task->id]) }}"
                                                       class="inline-flex items-center justify-center bg-[#f2f0ec] rounded-[5px] px-1.5 py-[2px] text-[#6d6a65] hover:text-[#1d2d35]"
                                                       title="Has notes">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- ===================================================== -->
                <!-- CARDS / GRID MODE (viewMode === 'cards')              -->
                <!-- ===================================================== -->
                <div x-show="viewMode === 'cards'" class="mt-8 flex flex-wrap gap-5">
                    @foreach($groupedTasks as $listName => $group)
                        @php
                            $openTasks = $group->where('status', '!=', 'DONE')->values();
                            $completedTasks = $group->where('status', 'DONE')->values();
                            $totalCount = $group->count();
                            $isListAllDone = $totalCount > 0 && $openTasks->isEmpty();
                            $previewItems = $group->values()->take(9);
                        @endphp
                        <div x-show="(!{{ $isListAllDone ? 'true' : 'false' }} || showCompletedLists) && matchesFilter('{{ addslashes(strtolower($listName)) }}')"
                             @click="openList('{{ addslashes($listName) }}')"
                             class="w-[272px] rounded-[8px] border border-[#e5e2dc] bg-white p-3.5 relative hover:border-[#c7c3bc] shadow-[0_1px_2px_rgba(0,0,0,0.03)] transition cursor-pointer overflow-hidden">

                            @if($isListAllDone)
                                <!-- Angled Circular Green 'ALL DONE!' Stamp -->
                                <div class="absolute top-2.5 right-2.5 w-[64px] h-[64px] rounded-full border-2 border-[#199849]/55 flex items-center justify-center rotate-[14deg] select-none pointer-events-none bg-white/80 z-10">
                                    <div class="w-[56px] h-[56px] rounded-full border border-[#199849]/35 flex items-center justify-center">
                                        <span class="text-[8.5px] font-extrabold text-[#199849]/85 tracking-tight uppercase border-y border-[#199849]/45 py-0.5 px-1 bg-white">
                                            ALL DONE!
                                        </span>
                                    </div>
                                </div>
                            @endif

                            <!-- Card Header -->
                            <div class="flex items-center gap-1.5 pr-16 mb-1.5">
                                <span class="w-3 h-3 rounded-full bg-[#198744] flex-shrink-0"></span>
                                <span class="text-[13px] font-bold text-[#1d2d35] truncate">{{ $listName }}</span>
                            </div>

                            <!-- Card Preview Items (First 9 items) -->
                            <div class="space-y-1">
                                @foreach($previewItems as $idx => $item)
                                    @php
                                        $bMeta = ($project->id == 4 && isset($birthdayMetaMap[$idx])) ? $birthdayMetaMap[$idx] : null;
                                        $itemDone = $item->status === 'DONE';
                                    @endphp
                                    <div class="text-[11.5px] leading-[1.35]">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            @if($itemDone)
                                                <span class="w-3.5 h-3.5 rounded-[3px] border border-[#198744]/65 text-[#198744] inline-flex items-center justify-center flex-shrink-0 text-[9px] leading-none">✓</span>
                                            @else
                                                <span class="w-3.5 h-3.5 rounded-[3px] border border-[#b5b1a8] bg-white inline-flex flex-shrink-0"></span>
                                            @endif
                                            <span class="{{ $itemDone ? 'text-[#6d6a65]' : 'text-[#1d2d35]' }}">{{ $item->title }}</span>

                                            @if($bMeta && !empty($bMeta['inline_on_card']))
                                                <span class="inline-flex items-center gap-1 bg-[#f6f4f0] rounded-full px-1.5 py-[1px] text-[9.5px] text-[#6d6a65]">
                                                    <span class="w-3 h-3 rounded-full bg-[#be185d] text-white text-[7px] font-bold inline-flex items-center justify-center">MS</span>
                                                    <span>Manjot S.</span>
                                                    <svg class="w-2.5 h-2.5 text-[#8c8881]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"/><path d="M16 2v4M8 2v4M3 10h18" stroke-width="2"/></svg>
                                                    <span>{{ $bMeta['date'] }}</span>
                                                </span>
                                            @endif
                                        </div>

                                        @if($bMeta && $bMeta['show_on_card'] && empty($bMeta['inline_on_card']))
                                            <div class="pl-5 mt-0.5">
                                                <span class="inline-flex items-center gap-1 bg-[#f6f4f0] rounded-full px-1.5 py-[1px] text-[9.5px] text-[#6d6a65]">
                                                    <span class="w-3 h-3 rounded-full bg-[#be185d] text-white text-[7px] font-bold inline-flex items-center justify-center">MS</span>
                                                    <span>Manjot S.,</span>
                                                    <span class="w-3 h-3 rounded-full bg-[#78716c] text-white text-[7px] font-bold inline-flex items-center justify-center">SS</span>
                                                    <span>Shachish S.</span>
                                                    <svg class="w-2.5 h-2.5 text-[#8c8881]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"/><path d="M16 2v4M8 2v4M3 10h18" stroke-width="2"/></svg>
                                                    <span>{{ $bMeta['date'] }}</span>
                                                </span>
                                            </div>
                                        @elseif(!$bMeta && ($item->assignee || $item->due_date))
                                            <div class="pl-5 mt-0.5">
                                                <span class="inline-flex items-center gap-1 bg-[#f6f4f0] rounded-full px-1.5 py-[1px] text-[9.5px] text-[#6d6a65]">
                                                    @if($item->assignee)
                                                        <span>{{ \Illuminate\Support\Str::limit($item->assignee->name, 14) }}</span>
                                                    @endif
                                                    @if($item->due_date)
                                                        <span>{{ \Carbon\Carbon::parse($item->due_date)->format('M j, Y') }}</span>
                                                    @endif
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>

            <!-- ========================================================= -->
            <!-- VIEW B: SINGLE TO-DO LIST DETAIL VIEW (activeList !== null) -->
            <!-- ========================================================= -->
            @foreach($groupedTasks as $listName => $group)
                @php
                    $openTasks = $group->where('status', '!=', 'DONE')->values();
                    $completedTasks = $group->where('status', 'DONE')->values();
                    $totalCount = $group->count();
                    $isListAllDone = $totalCount > 0 && $openTasks->isEmpty();
                @endphp
                <div x-show="activeList === '{{ addslashes($listName) }}'" style="display: none;">

                    <!-- List Heading -->
                    <h1 class="text-[28px] sm:text-[32px] font-black text-[#1d2d35] tracking-[-0.02em] leading-tight">
                        {{ $listName }}
                    </h1>

                    <!-- Status Row ("All done!" or "X/Y completed") -->
                    <div class="mt-4 flex items-center gap-2">
                        <span class="w-[15px] h-[15px] rounded-full bg-[#198744] flex-shrink-0"></span>
                        @if($isListAllDone)
                            <span class="text-[15.5px] text-[#198744]">All done!</span>
                        @else
                            <span class="text-[15.5px] text-[#6d6a65]">{{ $completedTasks->count() }}/{{ $totalCount }} completed</span>
                        @endif
                    </div>

                    <!-- Inline Add a To-do Form inside List Detail View (Matches media_1793346443493_c5650083.jpg & media_1793346448256_2c099007.jpg) -->
                    <div class="mt-3 pl-[23px]" x-data="bcTodoForm()">
                        <div x-show="addTodoOpen && activeList === '{{ addslashes($listName) }}'" x-cloak class="mb-4">
                            <form action="{{ route('projects.tasks.store', $project->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="list_name" value="{{ $listName }}">
                                <input type="hidden" name="priority" value="MEDIUM">
                                <input type="hidden" name="assigned_to" :value="assignId">

                                <div class="pb-2 border-b border-[#ece9e2] flex items-center gap-2.5">
                                    <span class="w-[18px] h-[18px] rounded-[4px] border border-[#6d6a65] bg-transparent flex-shrink-0"></span>
                                    <input type="text"
                                           name="title"
                                           required
                                           placeholder="Describe this to-do..."
                                           style="background:transparent !important;border:0 !important;box-shadow:none !important;"
                                           class="w-full text-[15.5px] text-[#1d2d35] placeholder-[#8c8881] p-0 focus:outline-none focus:ring-0">
                                </div>

                                <div class="mt-3.5 space-y-3.5 text-[13.5px]">
                                    <!-- 1. Assigned to -->
                                    <div class="flex items-center gap-3 relative" @click.outside="assignOpen = false">
                                        <label class="w-[92px] text-right font-bold text-[#1d2d35] flex-shrink-0">Assigned to</label>
                                        <div class="flex-1 relative">
                                            <input type="text"
                                                   x-model="assignName"
                                                   @focus="assignOpen = true; assignHover = 0"
                                                   @input="assignOpen = true; assignHover = 0; assignId = ''"
                                                   placeholder="Type names to assign..."
                                                   style="background:transparent !important;border:0 !important;box-shadow:none !important;"
                                                   class="w-full text-[13.5px] text-[#1d2d35] placeholder-[#8c8881] p-0 focus:outline-none focus:ring-0">
                                            <div x-show="assignOpen && filterPeople(assignName).length > 0"
                                                 x-cloak
                                                 class="absolute left-0 top-[calc(100%+4px)] w-[330px] max-h-[210px] overflow-y-auto rounded-[8px] border border-[#d6d2c9] bg-white shadow-[0_8px_24px_rgba(0,0,0,0.14)] p-1 z-30">
                                                <template x-for="(person, pIdx) in filterPeople(assignName)" :key="'ld-' + person.id">
                                                    <button type="button"
                                                            @mouseenter="assignHover = pIdx"
                                                            @click="assignName = person.name; assignId = person.id; assignOpen = false"
                                                            :class="assignHover === pIdx ? 'bg-[#1b6ac9] text-white' : 'bg-white text-[#1d2d35]'"
                                                            class="w-full px-2.5 py-1.5 rounded-[5px] flex items-center gap-2.5 text-left text-[13.5px] transition-colors cursor-pointer">
                                                        <template x-if="person.avatar">
                                                            <span class="relative w-[24px] h-[24px] rounded-full flex-shrink-0 inline-flex">
                                                                <img :src="person.avatar" alt="" class="w-[24px] h-[24px] rounded-full object-cover">
                                                                <span x-show="person.ooo" class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 bg-[#ffd500] text-[#1d2d35] text-[6.5px] font-black uppercase px-1 rounded-[2px] leading-tight">OUT</span>
                                                            </span>
                                                        </template>
                                                        <template x-if="!person.avatar">
                                                            <span class="w-[24px] h-[24px] rounded-full text-white text-[10px] font-bold inline-flex items-center justify-center flex-shrink-0"
                                                                  :style="'background:' + person.bg"
                                                                  x-text="person.initials"></span>
                                                        </template>
                                                        <span class="font-medium truncate" x-text="person.name"></span>
                                                        <span x-show="person.ooo"
                                                              :class="assignHover === pIdx ? 'text-white/85' : 'text-[#8c8881]'"
                                                              class="text-[11.5px] font-normal whitespace-nowrap"
                                                              x-text="person.ooo"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 2. When done -->
                                    <div class="flex items-center gap-3 relative" @click.outside="notifyOpen = false">
                                        <label class="w-[92px] text-right font-bold text-[#1d2d35] flex-shrink-0">When done</label>
                                        <div class="flex-1 relative">
                                            <input type="text"
                                                   x-model="notifyName"
                                                   @focus="notifyOpen = true; notifyHover = 0"
                                                   @input="notifyOpen = true; notifyHover = 0"
                                                   placeholder="Notify these people..."
                                                   style="background:transparent !important;border:0 !important;box-shadow:none !important;"
                                                   class="w-full text-[13.5px] text-[#1d2d35] placeholder-[#8c8881] p-0 focus:outline-none focus:ring-0">
                                            <div x-show="notifyOpen && filterPeople(notifyName).length > 0"
                                                 x-cloak
                                                 class="absolute left-0 top-[calc(100%+4px)] w-[330px] max-h-[210px] overflow-y-auto rounded-[8px] border border-[#d6d2c9] bg-white shadow-[0_8px_24px_rgba(0,0,0,0.14)] p-1 z-30">
                                                <template x-for="(person, nIdx) in filterPeople(notifyName)" :key="'ldn-' + person.id">
                                                    <button type="button"
                                                            @mouseenter="notifyHover = nIdx"
                                                            @click="notifyName = person.name; notifyOpen = false"
                                                            :class="notifyHover === nIdx ? 'bg-[#1b6ac9] text-white' : 'bg-white text-[#1d2d35]'"
                                                            class="w-full px-2.5 py-1.5 rounded-[5px] flex items-center gap-2.5 text-left text-[13.5px] transition-colors cursor-pointer">
                                                        <template x-if="person.avatar">
                                                            <span class="relative w-[24px] h-[24px] rounded-full flex-shrink-0 inline-flex">
                                                                <img :src="person.avatar" alt="" class="w-[24px] h-[24px] rounded-full object-cover">
                                                                <span x-show="person.ooo" class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 bg-[#ffd500] text-[#1d2d35] text-[6.5px] font-black uppercase px-1 rounded-[2px] leading-tight">OUT</span>
                                                            </span>
                                                        </template>
                                                        <template x-if="!person.avatar">
                                                            <span class="w-[24px] h-[24px] rounded-full text-white text-[10px] font-bold inline-flex items-center justify-center flex-shrink-0"
                                                                  :style="'background:' + person.bg"
                                                                  x-text="person.initials"></span>
                                                        </template>
                                                        <span class="font-medium truncate" x-text="person.name"></span>
                                                        <span x-show="person.ooo"
                                                              :class="notifyHover === nIdx ? 'text-white/85' : 'text-[#8c8881]'"
                                                              class="text-[11.5px] font-normal whitespace-nowrap"
                                                              x-text="person.ooo"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 3. Due on -->
                                    <div class="flex items-start gap-3">
                                        <label class="w-[92px] text-right font-bold text-[#1d2d35] flex-shrink-0 pt-0.5">Due on</label>
                                        <div class="flex-1">
                                            <div x-show="!dueExpanded"
                                                 @click="dueExpanded = true"
                                                 class="text-[13.5px] text-[#8c8881] cursor-pointer py-0.5 hover:text-[#1d2d35]">
                                                Select a due date...
                                            </div>
                                            <div x-show="dueExpanded" x-cloak class="space-y-1.5 pt-0.5">
                                                <label class="flex items-center gap-2 cursor-pointer text-[13.5px] text-[#1d2d35]">
                                                    <input type="radio" value="none" x-model="dueMode" class="w-4 h-4 accent-[#1d2d35]">
                                                    <span>No due date</span>
                                                </label>
                                                <label class="flex items-center gap-2 cursor-pointer text-[13.5px] text-[#1d2d35]">
                                                    <input type="radio" value="single" x-model="dueMode" class="w-4 h-4 accent-[#1d2d35]">
                                                    <span>A specific day</span>
                                                </label>
                                                <div x-show="dueMode === 'single'" x-cloak class="pl-6 pt-1 pb-1">
                                                    <input type="date" name="due_date" x-model="dueDate" class="px-2.5 py-1 border border-[#d6d2c9] rounded-[5px] text-[13px] text-[#1d2d35] bg-white focus:outline-none focus:border-[#1b6ac9]">
                                                </div>
                                                <label class="flex items-center gap-2 cursor-pointer text-[13.5px] text-[#1d2d35]">
                                                    <input type="radio" value="range" x-model="dueMode" class="w-4 h-4 accent-[#1d2d35]">
                                                    <span>Runs for multiple days</span>
                                                </label>
                                                <div x-show="dueMode === 'range'" x-cloak class="pl-6 pt-1 pb-1 flex items-center gap-2">
                                                    <input type="date" x-model="dueStartDate" class="px-2.5 py-1 border border-[#d6d2c9] rounded-[5px] text-[13px] text-[#1d2d35] bg-white focus:outline-none focus:border-[#1b6ac9]">
                                                    <span class="text-[#6d6a65] text-[12.5px]">to</span>
                                                    <input type="date" x-model="dueEndDate" @change="dueDate = dueEndDate" class="px-2.5 py-1 border border-[#d6d2c9] rounded-[5px] text-[13px] text-[#1d2d35] bg-white focus:outline-none focus:border-[#1b6ac9]">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 4. Notes -->
                                    <div class="flex items-start gap-3">
                                        <label class="w-[92px] text-right font-bold text-[#1d2d35] flex-shrink-0 pt-0.5">Notes</label>
                                        <div class="flex-1">
                                            <div x-show="!notesExpanded"
                                                 @click="notesExpanded = true"
                                                 class="text-[13.5px] text-[#8c8881] cursor-text py-0.5 hover:text-[#1d2d35]">
                                                Add extra details or attach a file...
                                            </div>
                                            <div x-show="notesExpanded" x-cloak class="rounded-[8px] border border-[#e5e2dc] bg-white overflow-hidden">
                                                <div class="px-2.5 py-1.5 border-b border-[#ece9e2] flex items-center justify-between gap-1 flex-wrap text-[#1d2d35]">
                                                    <div class="flex items-center gap-0.5 flex-wrap">
                                                        <button type="button" class="p-1.5 rounded hover:bg-[#f5f3ef] cursor-pointer" title="Insert image">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                                                        </button>
                                                        <button type="button" class="p-1.5 rounded hover:bg-[#f5f3ef] cursor-pointer" title="Attach a file">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
                                                        </button>
                                                        <span class="w-[1px] h-4 bg-[#e5e2dc] mx-1"></span>
                                                        <button type="button" class="px-2 py-1 rounded hover:bg-[#f5f3ef] font-extrabold text-[13.5px] cursor-pointer" title="Bold">B</button>
                                                        <button type="button" class="px-2 py-1 rounded hover:bg-[#f5f3ef] italic font-serif text-[14px] cursor-pointer" title="Italic">I</button>
                                                        <button type="button" class="px-2 py-1 rounded hover:bg-[#f5f3ef] line-through text-[13.5px] cursor-pointer" title="Strikethrough">S</button>
                                                        <button type="button" class="px-1.5 py-1 rounded hover:bg-[#f5f3ef] text-[12.5px] font-bold inline-flex items-center gap-0.5 cursor-pointer" title="Heading">Tt <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg></button>
                                                        <button type="button" class="px-1.5 py-1 rounded hover:bg-[#f5f3ef] inline-flex items-center gap-0.5 cursor-pointer" title="Color">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z"/></svg>
                                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                                                        </button>
                                                        <span class="w-[1px] h-4 bg-[#e5e2dc] mx-1"></span>
                                                        <button type="button" class="p-1.5 rounded hover:bg-[#f5f3ef] cursor-pointer" title="Link">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                                                        </button>
                                                        <button type="button" class="px-2 py-1 rounded hover:bg-[#f5f3ef] font-serif font-bold text-[15px] cursor-pointer" title="Quote">”</button>
                                                        <button type="button" class="px-1.5 py-1 rounded hover:bg-[#f5f3ef] font-mono text-[12px] font-bold cursor-pointer" title="Code">&lt;&gt;</button>
                                                        <button type="button" class="p-1.5 rounded hover:bg-[#f5f3ef] cursor-pointer" title="Bulleted list">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                                                        </button>
                                                        <button type="button" class="p-1.5 rounded hover:bg-[#f5f3ef] cursor-pointer" title="Numbered list">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/></svg>
                                                        </button>
                                                        <button type="button" class="p-1.5 rounded hover:bg-[#f5f3ef] cursor-pointer" title="Table">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="12" y1="3" x2="12" y2="21"/></svg>
                                                        </button>
                                                        <button type="button" class="p-1.5 rounded hover:bg-[#f5f3ef] cursor-pointer" title="Divider">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><line x1="8" y1="8" x2="16" y2="8"/><line x1="8" y1="16" x2="16" y2="16"/></svg>
                                                        </button>
                                                        <button type="button" class="p-1.5 rounded hover:bg-[#f5f3ef] cursor-pointer" title="Record audio clip">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3z"/><path d="M19 10v2a7 7 0 01-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
                                                        </button>
                                                    </div>
                                                    <div class="flex items-center gap-1 text-[#b5b1a8]">
                                                        <button type="button" class="p-1 hover:text-[#6d6a65] cursor-pointer" title="Undo">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 00-9-9 9 9 0 00-6 2.3L3 13"/></svg>
                                                        </button>
                                                        <button type="button" class="p-1 hover:text-[#6d6a65] cursor-pointer" title="Redo">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 7v6h-6"/><path d="M3 17a9 9 0 019-9 9 9 0 016 2.3l3 2.7"/></svg>
                                                        </button>
                                                    </div>
                                                </div>
                                                <textarea name="description"
                                                          x-model="notesText"
                                                          rows="5"
                                                          placeholder="Add extra details or attach a file..."
                                                          style="background:transparent !important;border:0 !important;box-shadow:none !important;"
                                                          class="w-full px-3.5 py-3 text-[14px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none focus:ring-0 resize-y"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 5. Subtasks -->
                                    <div class="flex items-start gap-3">
                                        <label class="w-[92px] text-right font-bold text-[#1d2d35] flex-shrink-0 pt-0.5">Subtasks</label>
                                        <div class="flex-1">
                                            <div x-show="!subtasksExpanded"
                                                 @click="subtasksExpanded = true"
                                                 class="text-[13.5px] text-[#8c8881] cursor-text py-0.5 hover:text-[#1d2d35]">
                                                Add subtasks
                                            </div>
                                            <div x-show="subtasksExpanded" x-cloak class="space-y-3">
                                                <template x-for="(st, stIdx) in subtasksList" :key="stIdx">
                                                    <div class="flex items-center gap-2.5 text-[13.5px] text-[#1d2d35] pb-1.5 border-b border-[#ece9e2]">
                                                        <span class="w-[16px] h-[16px] rounded-full border border-[#bfbcb4] flex-shrink-0"></span>
                                                        <span class="font-medium" x-text="st.title"></span>
                                                        <span x-show="st.assignee" class="text-[12px] text-[#6d6a65]" x-text="'· ' + st.assignee"></span>
                                                        <span x-show="st.due" class="text-[12px] text-[#6d6a65]" x-text="'· ' + st.due"></span>
                                                    </div>
                                                </template>

                                                <div class="pb-1.5 border-b border-[#ece9e2] flex items-center gap-2.5">
                                                    <span class="w-[16px] h-[16px] rounded-full border border-[#bfbcb4] bg-transparent flex-shrink-0"></span>
                                                    <input type="text"
                                                           x-model="subtaskTitle"
                                                           @keydown.enter.prevent="addSubtaskRow()"
                                                           placeholder="Describe this subtask..."
                                                           style="background:transparent !important;border:0 !important;box-shadow:none !important;"
                                                           class="w-full text-[14px] text-[#1d2d35] placeholder-[#8c8881] p-0 focus:outline-none focus:ring-0">
                                                </div>

                                                <div class="flex items-center gap-3 pl-5 relative" @click.outside="subtaskAssignOpen = false">
                                                    <label class="w-[82px] text-right text-[13px] text-[#1d2d35] flex-shrink-0">Assigned to</label>
                                                    <div class="flex-1 relative">
                                                        <input type="text"
                                                               x-model="subtaskAssignName"
                                                               @focus="subtaskAssignOpen = true; subtaskAssignHover = 0"
                                                               @input="subtaskAssignOpen = true; subtaskAssignHover = 0"
                                                               placeholder="Type names to assign..."
                                                               style="background:transparent !important;border:0 !important;box-shadow:none !important;"
                                                               class="w-full text-[13px] text-[#1d2d35] placeholder-[#8c8881] p-0 focus:outline-none focus:ring-0">
                                                        <div x-show="subtaskAssignOpen && filterPeople(subtaskAssignName).length > 0"
                                                             x-cloak
                                                             class="absolute left-0 top-[calc(100%+4px)] w-[300px] max-h-[190px] overflow-y-auto rounded-[8px] border border-[#d6d2c9] bg-white shadow-lg p-1 z-30">
                                                            <template x-for="(person, sIdx) in filterPeople(subtaskAssignName)" :key="'ldst-' + person.id">
                                                                <button type="button"
                                                                        @mouseenter="subtaskAssignHover = sIdx"
                                                                        @click="subtaskAssignName = person.name; subtaskAssignOpen = false"
                                                                        :class="subtaskAssignHover === sIdx ? 'bg-[#1b6ac9] text-white' : 'bg-white text-[#1d2d35]'"
                                                                        class="w-full px-2.5 py-1.5 rounded-[5px] flex items-center gap-2 text-left text-[13px] cursor-pointer">
                                                                    <template x-if="person.avatar">
                                                                        <img :src="person.avatar" alt="" class="w-[22px] h-[22px] rounded-full object-cover flex-shrink-0">
                                                                    </template>
                                                                    <template x-if="!person.avatar">
                                                                        <span class="w-[22px] h-[22px] rounded-full text-white text-[9.5px] font-bold inline-flex items-center justify-center flex-shrink-0"
                                                                              :style="'background:' + person.bg"
                                                                              x-text="person.initials"></span>
                                                                    </template>
                                                                    <span class="truncate" x-text="person.name"></span>
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-3 pl-5">
                                                    <label class="w-[82px] text-right text-[13px] text-[#1d2d35] flex-shrink-0">Due on</label>
                                                    <div class="relative w-[178px]">
                                                        <input type="text"
                                                               x-model="subtaskDue"
                                                               onfocus="this.type='date'"
                                                               onblur="if(!this.value)this.type='text'"
                                                               placeholder="Choose date"
                                                               class="w-full px-3 py-1 pr-8 rounded-[6px] border border-[#e5e2dc] bg-white text-[13px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none focus:border-[#1b6ac9]">
                                                        <svg class="w-3.5 h-3.5 text-[#8c8881] absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="pl-[18px] mt-4 flex items-center gap-2">
                                    <button type="submit" class="px-3.5 py-1.5 rounded-[5px] bg-[#2573d8] hover:bg-[#1e5eb3] text-white font-semibold text-[13px] transition-colors cursor-pointer">
                                        Add this to-do
                                    </button>
                                    <button type="button" @click="addTodoOpen = false" class="px-3 py-1.5 rounded-[5px] border border-[#d6d2c9] bg-transparent text-[#2573d8] font-medium text-[13px] hover:bg-[#fbfaf8] transition-colors cursor-pointer">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div x-show="!addTodoOpen">
                            <button type="button"
                                    @click="openAddTodo('{{ addslashes($listName) }}')"
                                    class="text-[15.5px] text-[#1b6ac9] underline hover:text-[#1557a8] cursor-pointer">
                                Add a to-do
                            </button>
                        </div>
                    </div>

                    <!-- Open Tasks in This List -->
                    @if($openTasks->isNotEmpty())
                        <div class="mt-6 space-y-2.5">
                            @foreach($openTasks as $task)
                                <div class="flex flex-wrap items-center gap-2">
                                    <form action="{{ route('projects.tasks.toggle', [$project->id, $task->id]) }}" method="POST" class="inline-flex">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="w-[16px] h-[16px] rounded-[3px] border border-[#b5b1a8] bg-white hover:border-[#198744] flex-shrink-0 cursor-pointer"></button>
                                    </form>
                                    <a href="{{ route('projects.tasks.show', [$project->id, $task->id]) }}" class="text-[15.5px] text-[#1d2d35] hover:underline">
                                        {{ $task->title }}
                                    </a>
                                    @if($task->assignee || $task->due_date)
                                        <span class="inline-flex items-center gap-1.5 bg-[#f6f4f0] rounded-full px-2.5 py-0.5 text-[12px] text-[#6d6a65]">
                                            @if($task->assignee)
                                                <span>{{ $task->assignee->name }}</span>
                                            @endif
                                            @if($task->due_date)
                                                <span>{{ \Carbon\Carbon::parse($task->due_date)->format('M j, Y') }}</span>
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Completed Tasks in This List -->
                    @if($completedTasks->isNotEmpty())
                        <div class="mt-7 space-y-2.5">
                            @foreach($completedTasks as $idx => $task)
                                @php
                                    $bMeta = ($project->id == 4 && isset($birthdayMetaMap[$idx])) ? $birthdayMetaMap[$idx] : null;
                                @endphp
                                <div x-show="{{ $idx < 10 ? 'true' : 'showAllCompletedInList' }}"
                                     class="flex flex-wrap items-center gap-2">
                                    <!-- Green Checked Box -->
                                    <form action="{{ route('projects.tasks.toggle', [$project->id, $task->id]) }}" method="POST" class="inline-flex">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="w-[16px] h-[16px] rounded-[3px] border border-[#198744]/70 text-[#198744] inline-flex items-center justify-center flex-shrink-0 hover:bg-[#198744]/10 cursor-pointer"
                                                title="Mark incomplete">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    </form>

                                    <!-- Task Title Link (no strikethrough in Basecamp) -->
                                    <a href="{{ route('projects.tasks.show', [$project->id, $task->id]) }}"
                                       class="text-[15.5px] text-[#6d6a65] hover:text-[#1d2d35] hover:underline">
                                        {{ $task->title }}
                                    </a>

                                    @if(($task->comments_count ?? 0) > 0)
                                        <a href="{{ route('projects.tasks.show', [$project->id, $task->id]) }}"
                                           class="w-[18px] h-[18px] rounded-full bg-[#388bfd] text-white text-[11px] font-bold inline-flex items-center justify-center leading-none flex-shrink-0">
                                            {{ $task->comments_count }}
                                        </a>
                                    @endif

                                    <!-- Metadata Pill -->
                                    @if($bMeta)
                                        <span class="inline-flex items-center gap-1.5 bg-[#f6f4f0] rounded-full px-2.5 py-0.5 text-[12px] text-[#6d6a65]">
                                            <span class="w-4 h-4 rounded-full bg-[#be185d] text-white text-[8.5px] font-bold inline-flex items-center justify-center flex-shrink-0">MS</span>
                                            <span>{{ $bMeta['two_assignees'] ? 'Manjot S.,' : 'Manjot S.' }}</span>
                                            @if($bMeta['two_assignees'])
                                                <span class="w-4 h-4 rounded-full bg-[#78716c] text-white text-[8.5px] font-bold inline-flex items-center justify-center flex-shrink-0">SS</span>
                                                <span>Shachish S.</span>
                                            @endif
                                            <svg class="w-3.5 h-3.5 text-[#6d6a65] ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="1.8"/>
                                                <path d="M16 2v4M8 2v4M3 10h18" stroke-width="1.8"/>
                                            </svg>
                                            <span>{{ $bMeta['date'] }}</span>
                                        </span>
                                    @elseif($project->id == 4)
                                        <span class="inline-flex items-center gap-1.5 bg-[#f6f4f0] rounded-full px-2.5 py-0.5 text-[12px] text-[#6d6a65]">
                                            <span class="w-4 h-4 rounded-full bg-[#be185d] text-white text-[8.5px] font-bold inline-flex items-center justify-center flex-shrink-0">MS</span>
                                            <span>Manjot S.,</span>
                                            <span class="w-4 h-4 rounded-full bg-[#78716c] text-white text-[8.5px] font-bold inline-flex items-center justify-center flex-shrink-0">SS</span>
                                            <span>Shachish S.</span>
                                        </span>
                                    @elseif($task->assignee || $task->due_date)
                                        <span class="inline-flex items-center gap-1.5 bg-[#f6f4f0] rounded-full px-2.5 py-0.5 text-[12px] text-[#6d6a65]">
                                            @if($task->assignee)
                                                @php
                                                    $aParts = preg_split('/\s+/', trim($task->assignee->name));
                                                    $aIn = strtoupper(substr($aParts[0] ?? 'U', 0, 1) . (isset($aParts[1]) ? substr($aParts[1], 0, 1) : ''));
                                                    $aShort = ($aParts[0] ?? '') . (isset($aParts[1]) ? ' ' . substr($aParts[1], 0, 1) . '.' : '');
                                                @endphp
                                                <span class="w-4 h-4 rounded-full bg-[#0d9488] text-white text-[8.5px] font-bold inline-flex items-center justify-center flex-shrink-0">{{ $aIn }}</span>
                                                <span>{{ $aShort }}</span>
                                            @endif
                                            @if($task->due_date)
                                                <svg class="w-3.5 h-3.5 text-[#6d6a65] ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="1.8"/>
                                                    <path d="M16 2v4M8 2v4M3 10h18" stroke-width="1.8"/>
                                                </svg>
                                                <span>{{ \Carbon\Carbon::parse($task->due_date)->format('M j, Y') }}</span>
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            @endforeach

                            @if($completedTasks->count() > 10)
                                <button type="button"
                                        x-show="!showAllCompletedInList"
                                        @click="showAllCompletedInList = true"
                                        class="text-[15.5px] text-[#6d6a65] hover:text-[#1d2d35] hover:underline pt-1 block cursor-pointer">
                                    And {{ $completedTasks->count() - 10 }} more completed to-dos...
                                </button>
                            @endif
                        </div>
                    @endif

                </div>
            @endforeach

        </div>
    </div>
</div>
@endsection
