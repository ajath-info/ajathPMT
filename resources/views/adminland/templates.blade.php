@extends('layouts.app')

@section('title', 'Project Templates')

@section('content')
<div class="max-w-[820px] mx-auto px-4 sm:px-6 pb-20 pt-2" x-data="{
    tab: '{{ $initialTab ?? 'project' }}',
    viewMode: '{{ $initialView ?? 'list' }}',
    activeTemplateName: 'Development Template',
    filterQuery: '',
    newTemplateTitle: '',
    newTemplateDesc: '',
    bookmarked: false,
    addingTodo: false,
    newTodoText: '',
    todoChecklist: [
        { text: 'Content Requirement - 15/09/23', assignee: 'Sukhmeet S.', initials: 'SS', done: false }
    ],
    projectTemplates: [
        { id: 16875791, name: 'Development Template' },
        ...JSON.parse(localStorage.getItem('bc_custom_project_templates') || '[]')
    ],
    todoTemplates: [
        { id: 6552486990, name: 'Content Requirement - 15/09/23', items: ['Content Requirement - 15/09/23'] }
    ],
    cardTableTemplates: [],
    saveCardTableTemplate() {
        if (!this.newTemplateTitle.trim()) return;
        this.cardTableTemplates.push({ id: Date.now(), name: this.newTemplateTitle.trim() });
        this.newTemplateTitle = '';
        this.tab = 'card_tables';
        this.viewMode = 'list';
    },
    saveTodoListTemplate() {
        if (!this.newTemplateTitle.trim()) return;
        const name = this.newTemplateTitle.trim();
        this.todoTemplates.push({ id: Date.now(), name, items: [name] });
        this.newTemplateTitle = '';
        this.newTemplateDesc = '';
        this.tab = 'todolists';
        this.viewMode = 'todo_detail';
    },
    saveProjectTemplate() {
        if (!this.newTemplateTitle.trim()) return;
        const name = this.newTemplateTitle.trim();
        const newObj = { id: Date.now(), name };
        this.projectTemplates.push(newObj);
        try {
            const existing = JSON.parse(localStorage.getItem('bc_custom_project_templates') || '[]');
            existing.push(newObj);
            localStorage.setItem('bc_custom_project_templates', JSON.stringify(existing));
        } catch (e) {}
        this.activeTemplateName = name;
        this.newTemplateTitle = '';
        this.tab = 'project';
        this.viewMode = 'detail';
    },
    addTodoItem() {
        if (!this.newTodoText.trim()) return;
        this.todoChecklist.push({ text: this.newTodoText.trim(), assignee: 'Manjot S.', initials: 'MS', done: false });
        this.newTodoText = '';
        this.addingTodo = false;
    }
}">
    <div class="bc-sheet bg-white overflow-hidden text-[#1d2d35]"
         :class="(viewMode === 'new_card_table' || viewMode === 'new_project') ? 'min-h-[260px]' : 'min-h-[640px]'">

        {{-- ================================================================= --}}
        {{-- VIEW 1: TEMPLATES LIST (Project / To-do List / Card Table)        --}}
        {{-- ================================================================= --}}
        <div x-show="viewMode === 'list'" class="px-8 sm:px-14 pt-9 pb-16">
            {{-- Dynamic Heading & Subtitle per Tab --}}
            <h1 class="text-[28px] sm:text-[32px] font-black text-[#1d1c1b] tracking-tight leading-tight mb-1.5"
                x-text="tab === 'project' ? 'Project Templates' : (tab === 'todolists' ? 'To-do List Templates' : 'Card Table Templates')"></h1>

            <p x-show="tab === 'project'" class="text-[15px] text-[#1d1c1b] leading-relaxed mb-5">
                Save yourself time by creating project templates with frequently-used tools, to-do lists, files, and more. Anyone on your account who can create projects can use and edit these templates.
            </p>
            <p x-show="tab === 'todolists'" x-cloak class="text-[15px] text-[#1d1c1b] leading-relaxed mb-5">
                Save yourself time by creating to-do templates you can drop into any project. Everyone on your account can edit and create their own lists from these templates.
            </p>
            <p x-show="tab === 'card_tables'" x-cloak class="text-[15px] text-[#1d1c1b] leading-relaxed mb-5">
                Save yourself time by creating card table templates you can drop into any project. Everyone on your account can edit these and start new card tables from them.
            </p>

            {{-- Segmented Tabs + Filter Input --}}
            <div class="flex flex-wrap items-center gap-2.5 mb-7">
                <div class="inline-flex items-center rounded-[7px] border border-[#d4d0c8] bg-[#f7f5f2] p-0.5 text-[12.5px]">
                    <button type="button" @click="tab = 'project'"
                        class="px-3 py-1 rounded-[5px] transition-colors cursor-pointer"
                        :class="tab === 'project' ? 'bg-[#1d1c1b] text-white font-bold shadow-2xs' : 'text-[#5c5955] hover:text-[#1d1c1b] font-medium'">
                        Project Templates
                    </button>
                    <button type="button" @click="tab = 'todolists'"
                        class="px-3 py-1 rounded-[5px] transition-colors cursor-pointer"
                        :class="tab === 'todolists' ? 'bg-[#1d1c1b] text-white font-bold shadow-2xs' : 'text-[#5c5955] hover:text-[#1d1c1b] font-medium'">
                        To-do List Templates
                    </button>
                    <button type="button" @click="tab = 'card_tables'"
                        class="px-3 py-1 rounded-[5px] transition-colors cursor-pointer"
                        :class="tab === 'card_tables' ? 'bg-[#1d1c1b] text-white font-bold shadow-2xs' : 'text-[#5c5955] hover:text-[#1d1c1b] font-medium'">
                        Card Table Templates
                    </button>
                </div>

                <input x-show="tab !== 'card_tables' || cardTableTemplates.length > 0"
                       type="text" x-model="filterQuery" placeholder="Filter..."
                       class="px-3 py-1 rounded-[6px] border border-[#d4d0c8] bg-white text-[12.5px] text-[#1d1c1b] placeholder-[#7c7872] focus:outline-none focus:border-[#3b82f6] w-36">
            </div>

            {{-- TAB A: Project Templates Grid (media_1793282148062_7f806d9a.jpg) --}}
            <div x-show="tab === 'project'" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {{-- + New Template Blueprint Card --}}
                <button type="button" @click="viewMode = 'new_project'"
                    style="background-image: linear-gradient(to right, rgba(59,130,246,0.12) 1px, transparent 1px), linear-gradient(to bottom, rgba(59,130,246,0.12) 1px, transparent 1px); background-size: 12px 12px;"
                    class="min-h-[126px] rounded-[8px] border border-[#93c5fd] bg-[#eff6ff]/70 hover:bg-[#dbeafe]/60 flex flex-col items-center justify-center gap-1.5 transition-colors cursor-pointer">
                    <span class="text-[28px] leading-none font-light text-[#2563eb]">+</span>
                    <span class="text-[13px] font-semibold text-[#2563eb]">New Template</span>
                </button>

                {{-- Development Template Card --}}
                <template x-for="pt in projectTemplates" :key="pt.id">
                    <div x-show="!filterQuery || pt.name.toLowerCase().includes(filterQuery.toLowerCase())"
                         @click="activeTemplateName = pt.name; viewMode = 'detail'"
                         class="min-h-[126px] rounded-[8px] border border-[#93c5fd] bg-white hover:shadow-sm p-3.5 flex flex-col justify-between transition-all cursor-pointer">
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-[13.5px] font-bold text-[#2563eb] leading-snug" x-text="pt.name"></span>
                            <span class="text-[#7c7872] hover:text-[#1d1c1b] text-[12px] font-bold leading-none px-1">&bull;&bull;&bull;</span>
                        </div>
                    </div>
                </template>
            </div>

            {{-- TAB B: To-do List Templates Grid (media_1793282161261_41b39737.jpg) --}}
            <div x-show="tab === 'todolists'" x-cloak class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {{-- + New Template Blueprint Card --}}
                <button type="button" @click="viewMode = 'new_todolist'"
                    style="background-image: linear-gradient(to right, rgba(59,130,246,0.12) 1px, transparent 1px), linear-gradient(to bottom, rgba(59,130,246,0.12) 1px, transparent 1px); background-size: 12px 12px;"
                    class="min-h-[126px] rounded-[8px] border border-[#93c5fd] bg-[#eff6ff]/70 hover:bg-[#dbeafe]/60 flex flex-col items-center justify-center gap-1.5 transition-colors cursor-pointer">
                    <span class="text-[28px] leading-none font-light text-[#2563eb]">+</span>
                    <span class="text-[13px] font-semibold text-[#2563eb]">New Template</span>
                </button>

                {{-- Content Requirement - 15/09/23 Card --}}
                <template x-for="tt in todoTemplates" :key="tt.id">
                    <div x-show="!filterQuery || tt.name.toLowerCase().includes(filterQuery.toLowerCase())"
                         @click="viewMode = 'todo_detail'"
                         class="min-h-[126px] rounded-[8px] border border-[#93c5fd] bg-white hover:shadow-sm p-3.5 flex flex-col justify-start transition-all cursor-pointer">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <span class="text-[13px] font-bold text-[#2563eb] leading-snug" x-text="tt.name"></span>
                            <span class="text-[#7c7872] hover:text-[#1d1c1b] text-[12px] font-bold leading-none px-1">&bull;&bull;&bull;</span>
                        </div>
                        <template x-for="(item, idx) in tt.items" :key="idx">
                            <div class="flex items-center gap-1.5 text-[11.5px] text-[#2563eb]">
                                <span class="w-3 h-3 rounded-[2px] border border-[#93c5fd] inline-block shrink-0"></span>
                                <span class="truncate" x-text="item"></span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- TAB C: Card Table Templates Grid (media_1793282166026_d5b697f6.jpg) --}}
            <div x-show="tab === 'card_tables'" x-cloak class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {{-- + New Template Blueprint Card --}}
                <button type="button" @click="viewMode = 'new_card_table'"
                    style="background-image: linear-gradient(to right, rgba(59,130,246,0.12) 1px, transparent 1px), linear-gradient(to bottom, rgba(59,130,246,0.12) 1px, transparent 1px); background-size: 12px 12px;"
                    class="min-h-[155px] rounded-[8px] border border-[#93c5fd] bg-[#eff6ff]/70 hover:bg-[#dbeafe]/60 flex flex-col items-center justify-center gap-1.5 transition-colors cursor-pointer">
                    <span class="text-[28px] leading-none font-light text-[#2563eb]">+</span>
                    <span class="text-[13px] font-semibold text-[#2563eb]">New Template</span>
                </button>

                <template x-for="ct in cardTableTemplates" :key="ct.id">
                    <div class="min-h-[155px] rounded-[8px] border border-[#93c5fd] bg-white hover:shadow-sm p-3.5 flex flex-col justify-between transition-all cursor-pointer">
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-[13.5px] font-bold text-[#2563eb] leading-snug" x-text="ct.name"></span>
                            <span class="text-[#7c7872] hover:text-[#1d1c1b] text-[12px] font-bold leading-none px-1">&bull;&bull;&bull;</span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- VIEW 2: DEVELOPMENT TEMPLATE DETAIL (Matches media_1793346420705_692edbe6.jpg) --}}
        {{-- ================================================================= --}}
        <div x-show="viewMode === 'detail'" x-cloak>
            <div class="px-6 py-3 border-b border-[#e5e2dc] bg-white flex items-center justify-between text-[13px]">
                <button type="button" @click="viewMode = 'list'; tab = 'project'" class="font-bold text-[#1d2d35] hover:underline cursor-pointer">
                    Project Templates
                </button>
                <div class="flex items-center gap-3 text-[#1d2d35]">
                    <button type="button" @click="newTemplateTitle = activeTemplateName; viewMode = 'new_project'" class="hover:underline font-medium cursor-pointer">Edit</button>
                    <button type="button" @click="viewMode = 'list'" class="hover:opacity-75 font-bold cursor-pointer">&bull;&bull;&bull;</button>
                </div>
            </div>

            <div class="px-8 sm:px-12 pt-7 pb-16">
                <div class="rounded-[10px] bg-[#fbf7f0] px-6 py-5 text-center mb-8">
                    <p class="text-[14.5px] text-[#1d2d35] leading-relaxed">
                        <strong class="font-bold">Tip:</strong> Edit this template like any other Basecamp project. When anyone creates a new project using this template, we&rsquo;ll copy everything over.
                    </p>
                    <div class="mt-3.5">
                        <a href="{{ route('projects.create', ['template' => 'development']) }}"
                           class="inline-block px-4 py-1.5 rounded-[6px] border border-[#d8d4cc] bg-white hover:bg-[#f2efe9] text-[13px] font-medium text-[#1d2d35] transition-colors">
                            Start a new project from this template
                        </a>
                    </div>
                </div>

                <h1 class="text-[28px] sm:text-[32px] font-black text-[#1d2d35] tracking-tight leading-tight mb-6"
                    x-text="activeTemplateName || 'Development Template'">
                    Development Template
                </h1>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-7">
                    {{-- 1. Message Board --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <a href="{{ route('projects.discussions', 4) }}" class="text-[15px] font-bold text-[#a64b2a] hover:underline">Message Board</a>
                        </div>
                        <div class="rounded-[8px] border border-[#e5e2dc] bg-white p-4 min-h-[290px] flex flex-col justify-between relative">
                            <a href="{{ route('projects.discussions', 4) }}" class="space-y-4 opacity-25 pt-2 block">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-6 h-6 rounded-full bg-[#9ca3af] shrink-0"></span>
                                    <div class="space-y-1.5 flex-1">
                                        <div class="h-2.5 bg-[#9ca3af] rounded w-3/4"></div>
                                        <div class="h-2 bg-[#9ca3af] rounded w-1/2"></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2.5">
                                    <span class="w-6 h-6 rounded-full bg-[#9ca3af] shrink-0"></span>
                                    <div class="space-y-1.5 flex-1">
                                        <div class="h-2.5 bg-[#9ca3af] rounded w-5/6"></div>
                                        <div class="h-2 bg-[#9ca3af] rounded w-2/3"></div>
                                    </div>
                                </div>
                                <div class="space-y-1.5 pt-2">
                                    <div class="h-2 bg-[#9ca3af] rounded w-full"></div>
                                    <div class="h-2 bg-[#9ca3af] rounded w-11/12"></div>
                                    <div class="h-2 bg-[#9ca3af] rounded w-4/5"></div>
                                </div>
                                <div class="flex items-center gap-2.5 pt-2">
                                    <span class="w-6 h-6 rounded-full bg-[#9ca3af] shrink-0"></span>
                                    <div class="h-2.5 bg-[#9ca3af] rounded w-2/3"></div>
                                </div>
                                <div class="flex items-center gap-2.5">
                                    <span class="w-6 h-6 rounded-full bg-[#9ca3af] shrink-0"></span>
                                    <div class="h-2.5 bg-[#9ca3af] rounded w-3/5"></div>
                                </div>
                            </a>
                            <div class="flex justify-end pt-2">
                                <span class="text-[#7c7872] hover:text-[#1d2d35] cursor-pointer" title="Remove tool">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. To-dos --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <a href="{{ route('projects.tasks', 26) }}" class="text-[15px] font-bold text-[#a64b2a] hover:underline">To-dos</a>
                        </div>
                        <div class="rounded-[8px] border border-[#e5e2dc] bg-white p-4 min-h-[290px]">
                            <a href="{{ route('projects.tasks', 26) }}" class="flex items-center gap-1.5 text-[12.5px] font-bold text-[#1d2d35] hover:underline mb-2">
                                <span class="w-3 h-3 rounded-full bg-[#74b886] inline-block shrink-0"></span>
                                <span>Dependency Checklist</span>
                            </a>
                            <div class="space-y-1.5 text-[11.5px] text-[#1d2d35]">
                                @foreach([
                                    'AWS Account',
                                    'Google Developer Account',
                                    'Domain Login Details',
                                    'Apple Developer Account',
                                    '1 Email Id With Domain Related',
                                    'SSL',
                                    'Instagram Account',
                                    'Facebook Account',
                                    'Youtube Account',
                                    'Twitter Account',
                                ] as $todoItem)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" class="w-3.5 h-3.5 rounded-[3px] border-[#9ca3af]">
                                    <span>{{ $todoItem }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- 3. Docs & Files --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <a href="{{ route('projects.docs', 26) }}" class="text-[15px] font-bold text-[#a64b2a] hover:underline">Docs &amp; Files</a>
                        </div>
                        <div class="rounded-[8px] border border-[#e5e2dc] bg-white p-4 min-h-[290px] space-y-3.5">
                            @foreach([
                                ['name' => 'Configuration Management (CM)', 'count' => '0 items'],
                                ['name' => 'Project Planning (PP)', 'count' => '2 items'],
                                ['name' => 'Project Monitoring and Control (PMC)', 'count' => '5 items'],
                            ] as $folder)
                            <a href="{{ route('projects.docs', ['id' => 26, 'folder' => $folder['name']]) }}" class="flex items-start gap-2.5 group">
                                <svg class="w-6 h-6 text-[#d4d0c8] shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M3 6a2 2 0 012-2h4l2 2h8a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V6z"/>
                                </svg>
                                <div class="min-w-0">
                                    <div class="text-[12px] font-bold text-[#1d2d35] group-hover:underline leading-snug">{{ $folder['name'] }}</div>
                                    <div class="text-[11px] text-[#7c7872]">{{ $folder['count'] }}</div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- 4. Campfire --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <a href="{{ route('projects.chat', 26) }}" class="text-[15px] font-bold text-[#a64b2a] hover:underline">Campfire</a>
                        </div>
                        <div class="rounded-[8px] border border-[#e5e2dc] bg-white p-4 min-h-[290px] flex flex-col justify-between relative">
                            <a href="{{ route('projects.chat', 26) }}" class="space-y-3.5 opacity-20 pt-2 block">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-5 h-5 rounded-full bg-[#9ca3af] shrink-0"></span>
                                    <div class="h-6 bg-[#9ca3af] rounded-xl flex-1"></div>
                                </div>
                                <div class="flex items-center gap-2.5">
                                    <span class="w-5 h-5 rounded-full bg-[#9ca3af] shrink-0"></span>
                                    <div class="h-5 bg-[#9ca3af] rounded-xl w-4/5"></div>
                                </div>
                                <div class="flex items-center gap-2.5">
                                    <span class="w-5 h-5 rounded-full bg-[#9ca3af] shrink-0"></span>
                                    <div class="h-10 bg-[#9ca3af] rounded-xl flex-1"></div>
                                </div>
                                <div class="flex items-center justify-end gap-2.5 pt-1">
                                    <div class="h-5 bg-[#9ca3af] rounded-xl w-2/3"></div>
                                    <span class="w-5 h-5 rounded-full bg-[#9ca3af] shrink-0"></span>
                                </div>
                                <div class="flex items-center gap-2.5">
                                    <span class="w-5 h-5 rounded-full bg-[#9ca3af] shrink-0"></span>
                                    <div class="h-5 bg-[#9ca3af] rounded-xl w-3/5"></div>
                                </div>
                            </a>
                            <div class="flex justify-end pt-2">
                                <span class="text-[#7c7872] hover:text-[#1d2d35] cursor-pointer" title="Remove tool">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Schedule --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <a href="{{ route('projects.schedule', 26) }}" class="text-[15px] font-bold text-[#a64b2a] hover:underline">Schedule</a>
                        </div>
                        <div class="rounded-[8px] border border-[#e5e2dc] bg-white p-4 min-h-[290px] flex flex-col justify-between relative">
                            <a href="{{ route('projects.schedule', 26) }}" class="space-y-3 opacity-20 pt-2 block">
                                <div class="h-3 bg-[#9ca3af] rounded w-1/3 mx-auto"></div>
                                <div class="grid grid-cols-7 gap-1 border border-[#9ca3af] p-1.5 rounded">
                                    @for($cIdx = 0; $cIdx < 28; $cIdx++)
                                        <div class="h-3.5 border border-[#9ca3af]/60"></div>
                                    @endfor
                                </div>
                                <div class="flex items-center gap-2.5 pt-2">
                                    <span class="w-6 h-5 rounded bg-[#9ca3af] shrink-0"></span>
                                    <div class="h-2.5 bg-[#9ca3af] rounded w-2/3"></div>
                                </div>
                                <div class="flex items-center gap-2.5">
                                    <span class="w-6 h-5 rounded bg-[#9ca3af] shrink-0"></span>
                                    <div class="h-2.5 bg-[#9ca3af] rounded w-1/2"></div>
                                </div>
                            </a>
                            <div class="flex justify-end pt-2">
                                <span class="text-[#7c7872] hover:text-[#1d2d35] cursor-pointer" title="Remove tool">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- 6. Automatic Check-ins --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <a href="{{ route('projects.checkins', 26) }}" class="text-[15px] font-bold text-[#a64b2a] hover:underline">Automatic Check-ins</a>
                        </div>
                        <div class="rounded-[8px] border border-[#e5e2dc] bg-white p-4 min-h-[290px] flex flex-col justify-between relative">
                            <div class="pt-4 text-center flex-1 flex flex-col items-center justify-center px-2">
                                <div class="w-full space-y-2 opacity-20 mb-4">
                                    <div class="h-3 bg-[#9ca3af] rounded w-3/4 mx-auto"></div>
                                    <div class="h-3 bg-[#9ca3af] rounded w-1/2 mx-auto"></div>
                                </div>
                                <p class="text-[12.5px] text-[#1d2d35] leading-snug mb-3.5">
                                    Automatically send questions to your team on a regularly recurring schedule.
                                </p>
                                <a href="{{ route('projects.checkins.questions.new', 26) }}"
                                   class="inline-block px-3.5 py-1.5 rounded-[6px] bg-[#1b6ac9] hover:bg-[#1557a6] text-white text-[12px] font-semibold transition-colors">
                                    Add your first question
                                </a>
                            </div>
                            <div class="flex justify-end pt-2">
                                <span class="text-[#7c7872] hover:text-[#1d2d35] cursor-pointer" title="Remove tool">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bottom-left [ + ] Dashed Add Tool Button (Matches media_1793346420705_692edbe6.jpg) --}}
                <div class="mt-6">
                    <button type="button"
                            @click="window.location.href = '{{ route('projects.create', ['template' => 'development']) }}'"
                            class="w-9 h-9 rounded-[6px] border border-dashed border-[#c7c3bb] hover:border-[#1d2d35] bg-white inline-flex items-center justify-center text-[20px] font-light text-[#1d2d35] transition-colors cursor-pointer"
                            title="Add another tool">
                        +
                    </button>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- VIEW 3: TO-DO LIST TEMPLATE DETAIL (media_1793282180324_1e49ee2e) --}}
        {{-- ================================================================= --}}
        <div x-show="viewMode === 'todo_detail'" x-cloak>
            {{-- Top Breadcrumb Header Bar --}}
            <div class="px-6 py-3 border-b border-[#e5e2dc] bg-[#f7f5f2]/60 flex items-center justify-between text-[13px]">
                <button type="button" @click="viewMode = 'list'; tab = 'todolists'" class="font-medium text-[#5c5955] hover:text-[#1d1c1b] hover:underline cursor-pointer">
                    To-do List Templates
                </button>
                <div class="flex items-center gap-3 text-[#5c5955]">
                    <button type="button" @click="bookmarked = !bookmarked" class="inline-flex items-center gap-1.5 hover:text-[#1d1c1b] font-medium cursor-pointer">
                        <svg class="w-3.5 h-3.5" :fill="bookmarked ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                        <span x-text="bookmarked ? 'Bookmarked' : 'Bookmark'"></span>
                    </button>
                    <button type="button" @click="viewMode = 'list'; tab = 'todolists'" class="hover:text-[#1d1c1b] font-bold cursor-pointer">&bull;&bull;&bull;</button>
                </div>
            </div>

            {{-- Sheet Body --}}
            <div class="px-8 sm:px-14 pt-7 pb-16">
                {{-- Tip Banner Box --}}
                <div class="rounded-[8px] border border-[#e5e2dc] bg-[#f7f5f2]/90 px-6 py-5 text-center mb-8">
                    <p class="text-[13.5px] text-[#1d1c1b] leading-relaxed">
                        <strong class="font-bold">Tip:</strong> Edit this template like any other to-do list. When anyone creates a new list using this template, we&rsquo;ll copy everything over.
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('my.tasks') }}"
                           class="inline-block px-4 py-1.5 rounded-[6px] border border-[#d4d0c8] bg-white hover:bg-[#f2efe9] text-[12.5px] font-medium text-[#1d1c1b] transition-colors">
                            Create a to-do list from this template
                        </a>
                    </div>
                </div>

                {{-- Heading & Google Sheets Link --}}
                <h1 class="text-[26px] sm:text-[30px] font-black text-[#1d1c1b] tracking-tight leading-tight mb-1.5">
                    Content Requirement - 15/09/23
                </h1>
                <p class="text-[14.5px] text-[#1d1c1b] leading-relaxed mb-6">
                    Kindly check:-
                    <a href="https://docs.google.com/spreadsheets/d/14yswQkSOjdJCPQc-1L8Hp4ZVOpmukSl6B_7MVPjEx5M/edit?usp=sharing"
                       target="_blank" rel="noopener"
                       class="underline text-[#2563eb] break-all">https://docs.google.com/spreadsheets/d/14yswQkSOjdJCPQc-1L8Hp4ZVOpmukSl6B_7MVPjEx5M/edit?usp=sharing</a>
                </p>

                {{-- Checklist Section --}}
                <div class="mb-12">
                    <div class="flex items-center gap-2 text-[13.5px] text-[#5c5955] mb-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-[#199849] inline-block shrink-0"></span>
                        <span x-text="todoChecklist.filter(i => i.done).length + '/' + todoChecklist.length + ' Completed'"></span>
                    </div>

                    <div class="space-y-2">
                        <template x-for="(item, idx) in todoChecklist" :key="idx">
                            <div class="flex items-center gap-2 text-[14.5px] text-[#1d1c1b] group">
                                <span class="text-[#9ca3af] text-[12px] select-none">&vellip;</span>
                                <input type="checkbox" x-model="item.done" class="w-4 h-4 rounded-[3px] border-[#9ca3af] cursor-pointer">
                                <span :class="item.done ? 'line-through text-[#7c7872]' : ''" x-text="item.text"></span>
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-[#f2efe9] text-[12px] text-[#5c5955]">
                                    <span class="w-4 h-4 rounded-full bg-[#b45309] text-white text-[8.5px] font-bold inline-flex items-center justify-center" x-text="item.initials"></span>
                                    <span x-text="item.assignee"></span>
                                    <span class="text-[11px]">📄</span>
                                </span>
                            </div>
                        </template>
                    </div>

                    <div class="mt-2.5 pl-5">
                        <button type="button" x-show="!addingTodo" @click="addingTodo = true"
                                class="text-[13.5px] text-[#2563eb] underline hover:opacity-80 cursor-pointer">
                            Add a to-do
                        </button>
                        <div x-show="addingTodo" x-cloak class="flex items-center gap-2 mt-1">
                            <input type="text" x-model="newTodoText" @keydown.enter.prevent="addTodoItem()"
                                   placeholder="Describe this to-do..."
                                   class="px-3 py-1 rounded-[6px] border border-[#3b82f6] text-[13.5px] text-[#1d1c1b] focus:outline-none w-64">
                            <button type="button" @click="addTodoItem()" class="px-3 py-1 rounded-[6px] bg-[#1f883d] text-white text-[12px] font-semibold cursor-pointer">Add</button>
                            <button type="button" @click="addingTodo = false" class="px-2.5 py-1 rounded-[6px] border border-[#d4d0c8] text-[12px] text-[#5c5955] cursor-pointer">Cancel</button>
                        </div>
                    </div>
                </div>

                {{-- Bottom Add Your Comment Row --}}
                <div class="pt-6 border-t border-[#e5e2dc] flex items-center gap-3">
                    <img src="/uploads/avatars/22369668.png" alt="Manjot Singh" class="w-8 h-8 rounded-full object-cover shrink-0">
                    <input type="text" placeholder="Add your comment..."
                           class="flex-1 px-3.5 py-2 rounded-[6px] border border-[#d4d0c8] bg-white text-[14px] text-[#1d1c1b] placeholder-[#7c7872] focus:outline-none focus:border-[#3b82f6]">
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- VIEW 4: CREATE A NEW CARD TABLE TEMPLATE (media_1793282172997)    --}}
        {{-- ================================================================= --}}
        <div x-show="viewMode === 'new_card_table'" x-cloak class="px-8 sm:px-14 pt-8 pb-10">
            <h1 class="text-[26px] sm:text-[30px] font-black text-[#1d1c1b] tracking-tight leading-tight mb-5">
                Create a new card table template
            </h1>

            <label class="block text-[14px] font-bold text-[#1d1c1b] mb-1.5">Name the template</label>
            <input type="text" x-model="newTemplateTitle" @keydown.enter.prevent="saveCardTableTemplate()"
                   placeholder="e.g. Client Onboarding"
                   class="w-full px-3.5 py-2 rounded-[6px] border border-[#3b82f6] bg-white text-[14px] text-[#1d1c1b] placeholder-[#7c7872] focus:outline-none mb-4">

            <div class="flex items-center gap-2.5">
                <button type="button" @click="saveCardTableTemplate()"
                        class="px-4 py-1.5 rounded-[6px] bg-[#60a5fa] hover:bg-[#3b82f6] text-[#0f172a] text-[13px] font-semibold transition-colors cursor-pointer">
                    Next: Set up this template &rarr;
                </button>
                <button type="button" @click="viewMode = 'list'; tab = 'card_tables'"
                        class="px-3.5 py-1.5 rounded-[6px] border border-[#d4d0c8] bg-white hover:bg-[#f7f5f2] text-[13px] font-medium text-[#5c5955] hover:text-[#1d1c1b] transition-colors cursor-pointer">
                    Never mind
                </button>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- VIEW 5: CREATE A NEW LIST TEMPLATE (media_1793282186458_ad38e2c5) --}}
        {{-- ================================================================= --}}
        <div x-show="viewMode === 'new_todolist'" x-cloak>
            <div class="px-6 py-3 border-b border-[#e5e2dc] bg-[#f7f5f2]/60 flex items-center text-[13px]">
                <button type="button" @click="viewMode = 'list'; tab = 'todolists'" class="font-medium text-[#5c5955] hover:text-[#1d1c1b] hover:underline cursor-pointer">
                    To-do List Templates
                </button>
            </div>

            <div class="px-8 sm:px-14 pt-8 pb-16">
                <h1 class="text-[28px] sm:text-[32px] font-black text-[#1d1c1b] tracking-tight leading-tight mb-6">
                    Create a new list template
                </h1>

                <div class="flex items-center gap-2.5 pb-2 border-b border-[#e5e2dc] mb-3">
                    <span class="w-4 h-4 rounded-full bg-[#9ca3af]/60 shrink-0"></span>
                    <input type="text" x-model="newTemplateTitle" @keydown.enter.prevent="saveTodoListTemplate()"
                           placeholder="Name this list..."
                           class="w-full text-[18px] font-bold text-[#1d1c1b] placeholder-[#7c7872] focus:outline-none bg-transparent">
                </div>

                <div class="flex items-center gap-3 text-[13.5px] mb-5 pl-6">
                    <span class="font-bold text-[#1d1c1b]">Description</span>
                    <input type="text" x-model="newTemplateDesc"
                           placeholder="Add extra details or attach a file..."
                           class="flex-1 text-[#1d1c1b] placeholder-[#7c7872] focus:outline-none bg-transparent">
                </div>

                <div class="flex items-center gap-2.5">
                    <button type="button" @click="saveTodoListTemplate()"
                            class="px-4 py-1.5 rounded-[6px] bg-[#60a5fa] hover:bg-[#3b82f6] text-[#0f172a] text-[13px] font-semibold transition-colors cursor-pointer">
                        Next: Set up this template &rarr;
                    </button>
                    <button type="button" @click="viewMode = 'list'; tab = 'todolists'"
                            class="px-3.5 py-1.5 rounded-[6px] border border-[#d4d0c8] bg-white hover:bg-[#f7f5f2] text-[13px] font-medium text-[#5c5955] hover:text-[#1d1c1b] transition-colors cursor-pointer">
                        Never mind
                    </button>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- VIEW 6: CREATE A NEW PROJECT TEMPLATE                             --}}
        {{-- ================================================================= --}}
        <div x-show="viewMode === 'new_project'" x-cloak class="px-8 sm:px-14 pt-8 pb-10">
            <h1 class="text-[26px] sm:text-[30px] font-black text-[#1d1c1b] tracking-tight leading-tight mb-5">
                Create a new project template
            </h1>

            <label class="block text-[14px] font-bold text-[#1d1c1b] mb-1.5">Name the template</label>
            <input type="text" x-model="newTemplateTitle" @keydown.enter.prevent="saveProjectTemplate()"
                   placeholder="e.g. Client Website Launch"
                   class="w-full px-3.5 py-2 rounded-[6px] border border-[#3b82f6] bg-white text-[14px] text-[#1d1c1b] placeholder-[#7c7872] focus:outline-none mb-4">

            <div class="flex items-center gap-2.5">
                <button type="button" @click="saveProjectTemplate()"
                        class="px-4 py-1.5 rounded-[6px] bg-[#60a5fa] hover:bg-[#3b82f6] text-[#0f172a] text-[13px] font-semibold transition-colors cursor-pointer">
                    Next: Set up this template &rarr;
                </button>
                <button type="button" @click="viewMode = 'list'; tab = 'project'"
                        class="px-3.5 py-1.5 rounded-[6px] border border-[#d4d0c8] bg-white hover:bg-[#f7f5f2] text-[13px] font-medium text-[#5c5955] hover:text-[#1d1c1b] transition-colors cursor-pointer">
                    Never mind
                </button>
            </div>
        </div>

    </div>
</div>
@endsection
