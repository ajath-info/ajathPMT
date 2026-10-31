@extends('layouts.app')

@section('title', $project->name)

@section('content')
@php
    $isFirstRun = request()->boolean('first_run') || ($project->id >= 213 && $project->status !== 'ARCHIVED' && $discussions->isEmpty() && $tasks->isEmpty());
@endphp
<script>
window.bcProjectShowState = function (projectId, showUrl, activityFilter) {
    const defaultNewProjectTools = [
        { id: 1, key: 'message_board', name: 'Message Board', url: '{{ route('projects.discussions', $project->id) }}' },
        { id: 2, key: 'todos', name: 'To-dos', url: '{{ route('projects.tasks', $project->id) }}' }
    ];
    let savedTools = null;
    try {
        const raw = localStorage.getItem('bc_tools_' + projectId);
        if (raw) {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed) && parsed.length > 0) {
                savedTools = parsed;
            }
        }
    } catch (e) {}

    return {
        toolMenu: false,
        projectMenuOpen: false,
        notifyMenuOpen: false,
        gaugeHelpModal: false,
        notifyMode: (function () {
            try { return localStorage.getItem('bc_proj_notify_' + projectId) || 'all'; } catch (e) { return 'all'; }
        })(),
        oooActive: (function () {
            try { return localStorage.getItem('bc_ooo_active') !== '0'; } catch (e) { return true; }
        })(),
        setNotifyMode(mode) {
            this.notifyMode = mode;
            try { localStorage.setItem('bc_proj_notify_' + projectId, mode); } catch (e) {}
            this.notifyMenuOpen = false;
        },
        copiedLink: false,
        filterText: activityFilter || '',
        addToolOpen: false,
        editingDetails: false,
        iconPreview: null,
        wallpaperPreview: null,
        wallpaperMode: 'fill',
        indicateProgress: true,
        datesExpanded: false,
        activePicker: null,
        startDate: '',
        endDate: '',
        startDateDisplay: '',
        endDateDisplay: '',
        pickerMonth: 9,
        pickerYear: 2026,
        monthNames: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        years: [2024, 2025, 2026, 2027, 2028, 2029, 2030],
        addedTools: savedTools || defaultNewProjectTools,
        availableTools: [
            { key: 'message_board', name: 'Message Board', desc: 'Broadcast announcements & updates', url: '{{ route('projects.discussions', $project->id) }}' },
            { key: 'todos', name: 'To-dos', desc: 'Make lists, assign tasks, get stuff done', url: '{{ route('projects.tasks', $project->id) }}' },
            { key: 'docs', name: 'Docs, Files & Images', desc: 'Store docs, files, PDFs, images, etc.', url: '{{ route('projects.docs', $project->id) }}' },
            { key: 'calendar', name: 'Calendar', desc: 'Add events, milestones, deadlines, etc.', url: '{{ route('projects.calendar', $project->id) }}' },
            { key: 'chat', name: 'Chat', desc: 'Add a chat room to the project', url: '{{ route('projects.chat', $project->id) }}' },
            { key: 'card_table', name: 'Card Table', desc: 'Track work with cards and columns', url: '{{ route('projects.kanban', $project->id) }}', hasBadge: true },
            { key: 'checkins', name: 'Automatic Check-ins', desc: 'Automatically ask recurring questions', url: '{{ route('projects.checkins', $project->id) }}' },
            { key: 'email_forwards', name: 'Email Forwards', desc: 'Forward emails in from outside', url: '{{ route('projects.discussions', $project->id) }}' },
            { key: 'external_link', name: 'External link', desc: 'Link directly to other products', url: '{{ route('projects.docs', $project->id) }}' }
        ],
        openAddToolMenu() {
            this.addToolOpen = true;
            if (window.location.search.includes('first_run=true')) {
                window.history.replaceState({}, '', showUrl);
            }
        },
        addTool(tool) {
            this.addedTools.push({
                id: Date.now() + Math.random(),
                key: tool.key,
                name: tool.name,
                url: tool.url
            });
            localStorage.setItem('bc_tools_' + projectId, JSON.stringify(this.addedTools));
        },
        removeTool(id) {
            this.addedTools = this.addedTools.filter(t => t.id !== id);
            localStorage.setItem('bc_tools_' + projectId, JSON.stringify(this.addedTools));
        },
        dragAddedToolIdx: null,
        reorderAddedTools(targetIdx) {
            if (this.dragAddedToolIdx === null || this.dragAddedToolIdx === targetIdx) return;
            const moved = this.addedTools.splice(this.dragAddedToolIdx, 1)[0];
            this.addedTools.splice(targetIdx, 0, moved);
            this.dragAddedToolIdx = targetIdx;
            localStorage.setItem('bc_tools_' + projectId, JSON.stringify(this.addedTools));
        },
        openDetailsEditor() {
            this.editingDetails = true;
            this.$nextTick(() => {
                if (this.$refs.inlineProjectName) {
                    this.$refs.inlineProjectName.focus();
                    this.$refs.inlineProjectName.select();
                }
            });
        },
        handleIconUpload(e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (ev) => { this.iconPreview = ev.target.result; };
            reader.readAsDataURL(file);
        },
        handleWallpaperUpload(e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (ev) => { this.wallpaperPreview = ev.target.result; };
            reader.readAsDataURL(file);
        },
        openDates() {
            this.datesExpanded = true;
            this.activePicker = 'start';
        },
        openPicker(which) {
            this.datesExpanded = true;
            this.activePicker = this.activePicker === which ? null : which;
        },
        removeDates() {
            this.startDate = '';
            this.endDate = '';
            this.startDateDisplay = '';
            this.endDateDisplay = '';
            this.activePicker = null;
            this.datesExpanded = false;
        },
        prevMonth() {
            if (this.pickerMonth === 0) {
                this.pickerMonth = 11;
                this.pickerYear--;
            } else {
                this.pickerMonth--;
            }
        },
        nextMonth() {
            if (this.pickerMonth === 11) {
                this.pickerMonth = 0;
                this.pickerYear++;
            } else {
                this.pickerMonth++;
            }
        },
        getCalendarDays() {
            const firstDay = new Date(this.pickerYear, this.pickerMonth, 1);
            const startOffset = (firstDay.getDay() + 6) % 7;
            const daysInMonth = new Date(this.pickerYear, this.pickerMonth + 1, 0).getDate();
            const daysInPrevMonth = new Date(this.pickerYear, this.pickerMonth, 0).getDate();
            const totalCells = Math.ceil((startOffset + daysInMonth) / 7) * 7;
            const cells = [];

            for (let i = 0; i < totalCells; i++) {
                let d, m = this.pickerMonth, y = this.pickerYear, currentMonth = true;
                if (i < startOffset) {
                    d = daysInPrevMonth - startOffset + 1 + i;
                    currentMonth = false;
                    if (m === 0) { m = 11; y--; } else { m--; }
                } else if (i >= startOffset + daysInMonth) {
                    d = i - (startOffset + daysInMonth) + 1;
                    currentMonth = false;
                    if (m === 11) { m = 0; y++; } else { m++; }
                } else {
                    d = i - startOffset + 1;
                }
                const mm = String(m + 1).padStart(2, '0');
                const dd = String(d).padStart(2, '0');
                const iso = y + '-' + mm + '-' + dd;
                const isToday = (y === 2026 && m === 9 && d === 29);
                const isSelected = (this.activePicker === 'start' && this.startDate === iso) ||
                                   (this.activePicker === 'end' && this.endDate === iso);
                cells.push({
                    key: iso + '-' + i,
                    day: d,
                    month: m,
                    year: y,
                    currentMonth: currentMonth,
                    iso: iso,
                    display: this.monthNames[m] + ' ' + d + ', ' + y,
                    isToday: isToday,
                    isSelected: isSelected
                });
            }
            return cells;
        },
        selectDay(cell) {
            if (this.activePicker === 'start') {
                this.startDate = cell.iso;
                this.startDateDisplay = cell.display;
            } else if (this.activePicker === 'end') {
                this.endDate = cell.iso;
                this.endDateDisplay = cell.display;
            }
            this.activePicker = null;
        }
    };
};
</script>
<div x-data="bcProjectShowState({{ $project->id }}, '{{ route('projects.show', $project->id) }}', '{{ addslashes($activityFilter ?? '') }}')" class="w-full sm:w-[72vw] max-w-[1130px] mx-auto pb-20 relative" id="top">

    @if(!$isFirstRun)
    <!-- Floating Left '↑ Back to top' Pill (Matches Basecamp Screenshot) -->
    <button type="button" 
            @click="window.scrollTo({top: 0, behavior: 'smooth'})" 
            class="hidden lg:inline-flex fixed bottom-11 left-8 z-30 items-center gap-1 px-2.5 py-1 bg-[#f2f0ec] hover:bg-[#e6e3dd] text-[#1d2d35] text-[12px] font-normal rounded-[6px] shadow-[0_1px_2px_rgba(0,0,0,0.05)] transition-colors cursor-pointer">
        <span>↑</span>
        <span>Back to top</span>
    </button>
    @endif

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

    <!-- Single Continuous White Paper Sheet (Exact Basecamp Match) -->
    <div class="bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] overflow-visible {{ $isFirstRun ? 'min-h-[calc(100vh-88px)]' : '' }}">

        <!-- Sticky Top Project Sub-Bar (Avatars on Left, Notifications on Right) -->
        <div class="sticky top-14 z-30 bg-white/95 backdrop-blur-xs border-b border-[#ece9e2] px-4 py-1.5 flex items-center justify-between rounded-t-[10px]">
            
            <!-- Left: People Button + Overlapping Member Avatars + Move the needle gauge (or 'Invite people' pill on new projects) -->
            <div class="flex items-center gap-3">
                @if($isFirstRun)
                    <div class="flex items-center">
                        <a href="{{ route('projects.people', $project->id) }}" class="w-[26px] h-[26px] rounded-full border border-[#d8d4cc] bg-white hover:bg-slate-50 flex items-center justify-center text-[#1d2d35] mr-1.5 transition-colors" title="Set up who's on this project">
                            <i data-lucide="users" class="w-3.5 h-3.5 text-[#1d2d35] stroke-[2.2]"></i>
                        </a>
                        <a href="{{ route('projects.people', $project->id) }}" class="px-2.5 py-0.5 rounded-[5px] bg-[#e9f5e9] hover:bg-[#dcefdc] text-[#1d2d35] text-[13px] font-medium transition-colors" title="Set up who's on this project">
                            Invite people
                        </a>
                    </div>
                @else
                    <a href="{{ route('projects.people', $project->id) }}"
                       class="inline-flex items-center rounded-full p-0.5 border-[1.5px] border-transparent hover:border-[#1b6ac9] transition-colors group"
                       title="Set up who's on this project">
                        <span class="w-[26px] h-[26px] rounded-full border border-[#d8d4cc] bg-white group-hover:bg-slate-50 flex items-center justify-center text-[#1d2d35] mr-1 transition-colors">
                            <i data-lucide="users" class="w-3.5 h-3.5 text-[#1d2d35] stroke-[2.2]"></i>
                        </span>

                        <span class="flex items-center -space-x-1">
                            @if($project->id == 5)
                                <span class="relative w-[26px] h-[26px] rounded-full border border-white overflow-hidden inline-flex flex-shrink-0">
                                    <img src="/uploads/avatars/22369668.png" alt="Manjot Singh" class="w-full h-full object-cover">
                                    <span x-show="oooActive" class="absolute bottom-0 inset-x-0 h-[38%] bg-[#facc15] flex items-center justify-center">
                                        <span class="text-[6px] font-black text-[#1d1c1b] leading-none">OUT</span>
                                    </span>
                                </span>
                                <img src="/uploads/avatars/25036340.png" alt="Shachish Sneh" class="w-[26px] h-[26px] rounded-full border border-white object-cover">
                            @elseif($project->id == 4)
                                <span class="w-[26px] h-[26px] rounded-full bg-[#10b981] border border-white text-white font-bold text-[10px] flex items-center justify-center select-none">AI</span>
                                <span class="w-[26px] h-[26px] rounded-full bg-[#d946ef] border border-white text-white font-bold text-[10px] flex items-center justify-center select-none">GK</span>
                                <img src="/uploads/avatars/47630095.png" alt="Harshvardhan Singh" class="w-[26px] h-[26px] rounded-full border border-white object-cover">
                                <span class="w-[26px] h-[26px] rounded-full bg-[#00897b] border border-white text-white font-bold text-[10px] flex items-center justify-center select-none">PK</span>
                                <img src="/uploads/avatars/25036340.png" alt="Shachish Sneh" class="w-[26px] h-[26px] rounded-full border border-white object-cover">
                                <span class="w-[26px] h-[26px] rounded-full bg-[#ec4899] border border-white text-white font-bold text-[10px] flex items-center justify-center select-none">RK</span>
                                <span class="relative w-[26px] h-[26px] rounded-full border border-white overflow-hidden inline-flex flex-shrink-0">
                                    <img src="/uploads/avatars/22369668.png" alt="Manjot Singh" class="w-full h-full object-cover">
                                    <span x-show="oooActive" class="absolute bottom-0 inset-x-0 h-[38%] bg-[#facc15] flex items-center justify-center">
                                        <span class="text-[6px] font-black text-[#1d1c1b] leading-none">OUT</span>
                                    </span>
                                </span>
                                <span class="w-[26px] h-[26px] rounded-full bg-[#9333ea] border border-white text-white font-bold text-[10px] flex items-center justify-center select-none">SN</span>
                            @elseif($project->id == 26)
                                <img src="/uploads/avatars/52829137.png" alt="Edward" class="w-[26px] h-[26px] rounded-full border border-white object-cover">
                                <img src="/uploads/avatars/53122994.png" alt="Gaurav Khulbey" class="w-[26px] h-[26px] rounded-full border border-white object-cover">
                                <span class="relative w-[26px] h-[26px] rounded-full border border-white overflow-hidden inline-flex flex-shrink-0">
                                    <img src="/uploads/avatars/22369668.png" alt="Manjot Singh" class="w-full h-full object-cover">
                                    <span x-show="oooActive" class="absolute bottom-0 inset-x-0 h-[38%] bg-[#facc15] flex items-center justify-center">
                                        <span class="text-[6px] font-black text-[#1d1c1b] leading-none">OUT</span>
                                    </span>
                                </span>
                                <img src="/uploads/avatars/23053551.png" alt="Pawandeep Kaur" class="w-[26px] h-[26px] rounded-full border border-white object-cover">
                                <img src="/uploads/avatars/22369742.png" alt="Prashant" class="w-[26px] h-[26px] rounded-full border border-white object-cover">
                                <img src="/uploads/avatars/35685578.png" alt="Rajesh Kumar" class="w-[26px] h-[26px] rounded-full border border-white object-cover">
                                <img src="/uploads/avatars/51410494.png" alt="Rishabh Ojha" class="w-[26px] h-[26px] rounded-full border border-white object-cover">
                                <img src="/uploads/avatars/25036340.png" alt="Shachish Sneh" class="w-[26px] h-[26px] rounded-full border border-white object-cover">
                                <img src="/uploads/avatars/52873395.png" alt="Shiv Narayan" class="w-[26px] h-[26px] rounded-full border border-white object-cover">
                            @elseif(isset($projectMemberBadges) && $projectMemberBadges->isNotEmpty())
                                @foreach($projectMemberBadges as $b)
                                    @if(!empty($b->isSelf))
                                        <span class="relative w-[26px] h-[26px] rounded-full border border-white overflow-hidden inline-flex flex-shrink-0">
                                            <img src="{{ $b->avatar ?: '/uploads/avatars/22369668.png' }}" alt="{{ $b->name ?? 'Manjot Singh' }}" class="w-full h-full object-cover">
                                            <span x-show="oooActive" class="absolute bottom-0 inset-x-0 h-[38%] bg-[#facc15] flex items-center justify-center">
                                                <span class="text-[6px] font-black text-[#1d1c1b] leading-none">OUT</span>
                                            </span>
                                        </span>
                                    @elseif(!empty($b->avatar))
                                        <img src="{{ $b->avatar }}" alt="{{ $b->initials }}" class="w-[26px] h-[26px] rounded-full border border-white object-cover">
                                    @else
                                        <span class="w-[26px] h-[26px] rounded-full {{ $b->bg }} border border-white text-white font-bold text-[10px] flex items-center justify-center select-none">
                                            {{ $b->initials }}
                                        </span>
                                    @endif
                                @endforeach
                            @else
                                @foreach($project->members->take(6) as $m)
                                <span class="w-[26px] h-[26px] rounded-full bg-[#0d9488] border border-white text-white font-bold text-[10px] flex items-center justify-center select-none">
                                    {{ strtoupper(substr($m->name, 0, 2)) }}
                                </span>
                                @endforeach
                            @endif
                        </span>
                    </a>
                @endif

                <!-- "Move the needle" Progress Gauge (Exact 1:1 Match to media_1793292992853_0dcf6574.jpg) -->
                <a href="{{ route('projects.gauge', $project->id) }}"
                   x-show="indicateProgress"
                   class="inline-flex flex-col items-center justify-center px-3 py-0.5 border-l border-r border-[#ece9e2] hover:bg-[#f6f4f0]/60 transition-colors cursor-pointer group"
                   title="Move the needle">
                    <svg class="w-[74px] h-[16px] text-[#6d6a65] group-hover:text-[#1d2d35]" viewBox="0 0 84 18" fill="none">
                        <!-- Curved Gauge Arc Band -->
                        <path d="M 6 12 Q 42 2 78 12 L 75 17 Q 42 8 9 17 Z" stroke="currentColor" stroke-width="1" fill="none" opacity="0.65"/>
                        <!-- Tick Marks Inside Arc -->
                        <line x1="16" y1="10" x2="17" y2="15" stroke="currentColor" stroke-width="0.9" opacity="0.55"/>
                        <line x1="25" y1="8.5" x2="26" y2="13.5" stroke="currentColor" stroke-width="0.9" opacity="0.55"/>
                        <line x1="34" y1="7.5" x2="34.5" y2="12.5" stroke="currentColor" stroke-width="0.9" opacity="0.55"/>
                        <line x1="42" y1="7" x2="42" y2="12" stroke="currentColor" stroke-width="0.9" opacity="0.55"/>
                        <line x1="50" y1="7.5" x2="49.5" y2="12.5" stroke="currentColor" stroke-width="0.9" opacity="0.55"/>
                        <line x1="59" y1="8.5" x2="58" y2="13.5" stroke="currentColor" stroke-width="0.9" opacity="0.55"/>
                        <line x1="68" y1="10" x2="67" y2="15" stroke="currentColor" stroke-width="0.9" opacity="0.55"/>
                        <!-- Left Needle Pointer -->
                        <path d="M 5 8 L 9 7 L 9.5 17 L 6.5 17 Z" fill="currentColor"/>
                    </svg>
                    <span class="text-[10.5px] text-[#8c8881] group-hover:text-[#1d2d35] leading-none mt-0.5 whitespace-nowrap">Move the needle</span>
                </a>
            </div>

            <!-- Right: Notifications on Popover & ••• Menu (Exact 1:1 Match to media_1793292992853_0dcf6574.jpg) -->
            <div class="flex items-center gap-2 text-[#1d2d35] relative" @click.outside="projectMenuOpen = false; notifyMenuOpen = false">
                <!-- Notifications on / off Toggle Button -->
                <div class="relative">
                    <button type="button"
                            @click="notifyMenuOpen = !notifyMenuOpen; projectMenuOpen = false"
                            class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[13px] font-normal text-[#1d2d35] border transition-colors cursor-pointer"
                            :class="notifyMenuOpen
                                ? 'border-[#9da7b1] bg-[#f2f0ec]'
                                : 'border-transparent hover:border-[#d6d2c9] hover:bg-[#f6f5f1]'">
                        <svg class="w-3.5 h-3.5 fill-current text-[#283c46]" viewBox="0 0 24 24">
                            <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                        </svg>
                        <span x-text="notifyMode === 'all' ? 'Notifications on' : 'Notifications off'">Notifications on</span>
                    </button>

                    <!-- Notifications Popover Dropdown (Exact 1:1 Match to media_1793292992853_0dcf6574.jpg) -->
                    <div x-show="notifyMenuOpen"
                         x-cloak
                         class="absolute right-0 top-[calc(100%+8px)] w-[280px] rounded-[8px] border border-[#e5e2dc] bg-[#f6f5f1] shadow-[0_10px_28px_rgba(0,0,0,0.28)] p-3.5 z-50 text-left select-none">
                        <!-- Top-Right Pointer Caret -->
                        <span class="absolute -top-[6px] right-6 w-3 h-3 rotate-45 bg-[#f6f5f1] border-l border-t border-[#e5e2dc]"></span>

                        <!-- Option 1: Yes, notify me -->
                        <button type="button"
                                @click="setNotifyMode('all')"
                                class="w-full flex items-start gap-2 text-left rounded-[6px] p-1.5 hover:bg-[#ece9e2]/60 transition-colors cursor-pointer">
                            <span class="w-4 pt-0.5 flex-shrink-0 text-[#1d2d35]">
                                <svg x-show="notifyMode === 'all'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13.5px] font-bold text-[#1d2d35] leading-snug">Yes, notify me</div>
                                <div class="text-[12.5px] text-[#6d6a65] leading-[1.35] mt-0.5">
                                    Loop me in by default when new things are posted to this project.
                                </div>
                            </div>
                        </button>

                        <!-- Option 2: Only notify me when... -->
                        <button type="button"
                                @click="setNotifyMode('mentions')"
                                class="w-full flex items-start gap-2 text-left rounded-[6px] p-1.5 mt-2 hover:bg-[#ece9e2]/60 transition-colors cursor-pointer">
                            <span class="w-4 pt-0.5 flex-shrink-0 text-[#1d2d35]">
                                <svg x-show="notifyMode === 'mentions'" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13.5px] font-bold text-[#1d2d35] leading-snug">Only notify me when...</div>
                                <div class="text-[12.5px] text-[#6d6a65] leading-[1.35] mt-0.5">
                                    ...someone replies to a thread I&rsquo;m participating in, or @mentions me directly.
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
                <button type="button"
                        @click="projectMenuOpen = !projectMenuOpen"
                        class="w-7 h-7 rounded-full flex items-center justify-center text-[#1d2d35] hover:bg-slate-100 border border-transparent transition-colors cursor-pointer"
                        :class="projectMenuOpen ? 'border-[#9da7b1] bg-slate-100' : ''"
                        title="Project actions">
                    <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                </button>

                <!-- Project ••• Dropdown Menu (Exact 1:1 Match to media_1793290645677_3ca49a79.jpg) -->
                <div x-show="projectMenuOpen"
                     x-cloak
                     class="absolute right-0 top-9 w-[206px] rounded-[8px] border border-[#e5e2dc] bg-white shadow-[0_10px_28px_rgba(0,0,0,0.22)] py-1.5 z-50 text-[13.5px] text-[#1d2d35] select-none">
                    <button type="button"
                            @click="projectMenuOpen = false; openDetailsEditor()"
                            class="w-full px-3.5 py-1.5 flex items-center gap-2.5 text-left hover:bg-[#60a5fa] hover:text-[#0f172a] transition-colors cursor-pointer">
                        <svg class="w-4 h-4 shrink-0 opacity-80" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="3"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
                        </svg>
                        <span>Project settings</span>
                    </button>

                    <button type="button"
                            @click="projectMenuOpen = false; openAddToolMenu()"
                            class="w-full px-3.5 py-1.5 pl-10 flex items-center text-left hover:bg-[#60a5fa] hover:text-[#0f172a] transition-colors cursor-pointer">
                        <span>Customize tools</span>
                    </button>

                    <a href="{{ route('templates.index') }}"
                       class="w-full px-3.5 py-1.5 pl-10 flex items-center text-left hover:bg-[#60a5fa] hover:text-[#0f172a] transition-colors">
                        <span>Save as a template...</span>
                    </a>

                    <button type="button"
                            @click="navigator.clipboard.writeText(window.location.href); copiedLink = true; setTimeout(() => { copiedLink = false; projectMenuOpen = false; }, 700)"
                            class="w-full px-3.5 py-1.5 flex items-center gap-2.5 text-left hover:bg-[#60a5fa] hover:text-[#0f172a] transition-colors cursor-pointer">
                        <svg class="w-4 h-4 shrink-0 opacity-80" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        <span x-text="copiedLink ? 'Copied!' : 'Copy link'"></span>
                    </button>

                    <div class="my-1 border-t border-[#ece9e2]"></div>

                    <a href="{{ route('projects.directory') }}"
                       class="w-full px-3.5 py-1.5 pl-10 flex items-center text-left hover:bg-[#60a5fa] hover:text-[#0f172a] transition-colors">
                        <span>Leave this project</span>
                    </a>

                    <button type="button"
                            @click="projectMenuOpen = false"
                            class="w-full px-3.5 py-1.5 pl-10 flex items-center text-left hover:bg-[#60a5fa] hover:text-[#0f172a] transition-colors cursor-pointer">
                        <span>Set up webhooks</span>
                    </button>

                    <a href="{{ route('projects.status', $project->id) }}"
                       class="w-full px-3.5 py-1.5 pl-10 flex items-center text-left hover:bg-[#60a5fa] hover:text-[#0f172a] transition-colors">
                        <span>Archive or delete</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Inline Basecamp Project Details Editor (Toggled by clicking Project Title / Description / •••) -->
        <div x-show="editingDetails" x-cloak class="px-8 sm:px-10 pt-8 pb-10 border-b border-[#ece9e2]">
            <form action="{{ route('projects.update', $project->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Top Row: Left 62x62 Project Icon Upload Box + Right Name & Optional Description -->
                <div class="flex items-start gap-4">
                    <label class="w-[62px] h-[62px] rounded-[8px] border border-[#d6d2c9] hover:border-[#8c8881] bg-white flex items-center justify-center cursor-pointer shrink-0 mt-1 overflow-hidden transition-colors"
                           title="Upload project icon">
                        <input type="file" accept="image/*" class="hidden" @change="handleIconUpload($event)">
                        <template x-if="iconPreview">
                            <img :src="iconPreview" alt="Icon preview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!iconPreview">
                            <svg class="w-[18px] h-[15px] text-[#4a5560]" viewBox="0 0 18 15" fill="none" stroke="currentColor" stroke-width="1.4">
                                <rect x="1" y="1" width="16" height="13" rx="2"/>
                                <circle cx="5.5" cy="5.2" r="1.3"/>
                                <path d="M1.5 12.5l4.2-4.2 3.2 3.2 3.3-3.5 4.3 4.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </template>
                    </label>

                    <div class="flex-1 min-w-0">
                        <div>
                            <input type="text"
                                   name="name"
                                   x-ref="inlineProjectName"
                                   value="{{ $project->name }}"
                                   required
                                   autocomplete="off"
                                   placeholder="Name the project"
                                   class="w-full text-[28px] sm:text-[32px] font-black text-[#1d2d35] placeholder-[#8c8881] tracking-[-0.02em] leading-tight bg-transparent border-0 p-0 focus:outline-none focus:ring-0 selection:bg-[#b4d5fe] selection:text-[#1d2d35]">
                        </div>
                        <div class="mt-1">
                            <input type="text"
                                   name="description"
                                   value="{{ $project->description }}"
                                   autocomplete="off"
                                   placeholder="Add an optional description"
                                   class="w-full text-[16px] text-[#6d6a65] placeholder-[#6d6a65] focus:text-[#1d2d35] bg-transparent border-0 p-0 focus:outline-none focus:ring-0">
                        </div>
                    </div>
                </div>

                <!-- Project Details Rows (Starts/Ends, Indicate progress, Wallpaper) -->
                <div class="mt-7 space-y-4 text-[14px]">

                    <!-- Row 1: Starts/Ends -->
                    <div class="flex items-start gap-3.5">
                        <div class="w-[125px] sm:w-[140px] text-right font-bold text-[#1d2d35] text-[13.5px] shrink-0"
                             :class="datesExpanded ? 'pt-0.5' : ''">
                            Starts/Ends
                        </div>

                        <div class="flex-1 min-w-0">
                            <div x-show="!datesExpanded">
                                <button type="button"
                                        @click="openDates()"
                                        class="text-[14px] text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer">
                                    Select dates...
                                </button>
                            </div>

                            <div x-show="datesExpanded" x-cloak @click.outside="activePicker = null">
                                <div class="flex flex-wrap items-start gap-4">

                                    <!-- Start date column -->
                                    <div class="relative w-[235px]">
                                        <div class="text-[13px] font-medium text-[#1d2d35] mb-1">Start date</div>
                                        <div class="relative cursor-pointer" @click="openPicker('start')">
                                            <input type="text"
                                                   readonly
                                                   :value="startDateDisplay"
                                                   placeholder="Choose date"
                                                   class="w-full h-[32px] px-2.5 pr-8 rounded-[5px] border border-[#d6d2c9] focus:border-[#1b6ac9] bg-white text-[13.5px] text-[#1d2d35] placeholder-[#6d6a65] focus:outline-none cursor-pointer">
                                            <input type="hidden" name="start_date" :value="startDate">
                                            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#6d6a65] pointer-events-none">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.3">
                                                    <rect x="1.5" y="2.5" width="13" height="12" rx="1.5"/>
                                                    <path d="M1.5 6h13M5 1v3M11 1v3"/>
                                                </svg>
                                            </span>
                                        </div>

                                        <div class="mt-1.5">
                                            <button type="button"
                                                    @click="removeDates()"
                                                    class="text-[12.5px] text-[#6d6a65] underline hover:text-[#1d2d35] cursor-pointer">
                                                Remove dates
                                            </button>
                                        </div>

                                        <!-- Start Date Calendar Popover -->
                                        <div x-show="activePicker === 'start'"
                                             x-cloak
                                             class="absolute left-0 top-[58px] w-[228px] rounded-[10px] border border-[#d6d2c9] bg-white shadow-[0_8px_26px_rgba(0,0,0,0.14)] p-3.5 z-40 select-none">
                                            <div class="flex items-center justify-between mb-2.5">
                                                <div class="flex items-center gap-1.5">
                                                    <div class="relative inline-flex items-center">
                                                        <select x-model.number="pickerMonth"
                                                                class="appearance-none bg-transparent pr-3.5 text-[13px] font-bold text-[#1d2d35] focus:outline-none cursor-pointer">
                                                            <template x-for="(mName, mIdx) in monthNames" :key="mIdx">
                                                                <option :value="mIdx" x-text="mName" :selected="mIdx === pickerMonth"></option>
                                                            </template>
                                                        </select>
                                                        <svg class="w-3 h-3 text-[#6d6a65] absolute right-0 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                                    </div>

                                                    <div class="relative inline-flex items-center">
                                                        <select x-model.number="pickerYear"
                                                                class="appearance-none bg-transparent pr-3.5 text-[13px] font-bold text-[#1d2d35] focus:outline-none cursor-pointer">
                                                            <template x-for="yr in years" :key="yr">
                                                                <option :value="yr" x-text="yr" :selected="yr === pickerYear"></option>
                                                            </template>
                                                        </select>
                                                        <svg class="w-3 h-3 text-[#6d6a65] absolute right-0 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-1">
                                                    <button type="button" @click="prevMonth()" class="p-1 text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                                    </button>
                                                    <button type="button" @click="nextMonth()" class="p-1 text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-7 text-center text-[9.5px] font-bold uppercase tracking-wider text-[#6d6a65] mb-1.5">
                                                <span>MO</span><span>TU</span><span>WE</span><span>TH</span><span>FR</span><span>SA</span><span>SU</span>
                                            </div>

                                            <div class="grid grid-cols-7 gap-y-1 text-center text-[12px]">
                                                <template x-for="cell in getCalendarDays()" :key="cell.key">
                                                    <button type="button"
                                                            @click="selectDay(cell)"
                                                            class="w-[26px] h-[26px] mx-auto rounded-full flex items-center justify-center transition-colors cursor-pointer"
                                                            :class="{
                                                                'bg-[#1b6ac9] text-white font-bold': cell.isSelected,
                                                                'bg-[#e5e2dc] text-[#1d2d35] font-bold': !cell.isSelected && cell.isToday,
                                                                'text-[#1d2d35] font-medium hover:bg-[#f2f0ec]': !cell.isSelected && !cell.isToday && cell.currentMonth,
                                                                'text-[#b8b4ac] hover:bg-[#f2f0ec]': !cell.isSelected && !cell.currentMonth
                                                            }"
                                                            x-text="cell.day">
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- End date column -->
                                    <div class="relative w-[235px]">
                                        <div class="text-[13px] font-medium text-[#1d2d35] mb-1">End date</div>
                                        <div class="relative cursor-pointer" @click="openPicker('end')">
                                            <input type="text"
                                                   readonly
                                                   :value="endDateDisplay"
                                                   placeholder="Choose date"
                                                   class="w-full h-[32px] px-2.5 pr-8 rounded-[5px] border border-[#d6d2c9] focus:border-[#1b6ac9] bg-white text-[13.5px] text-[#1d2d35] placeholder-[#6d6a65] focus:outline-none cursor-pointer">
                                            <input type="hidden" name="end_date" :value="endDate">
                                            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#6d6a65] pointer-events-none">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.3">
                                                    <rect x="1.5" y="2.5" width="13" height="12" rx="1.5"/>
                                                    <path d="M1.5 6h13M5 1v3M11 1v3"/>
                                                </svg>
                                            </span>
                                        </div>

                                        <!-- End Date Calendar Popover -->
                                        <div x-show="activePicker === 'end'"
                                             x-cloak
                                             class="absolute left-0 top-[58px] w-[228px] rounded-[10px] border border-[#d6d2c9] bg-white shadow-[0_8px_26px_rgba(0,0,0,0.14)] p-3.5 z-40 select-none">
                                            <div class="flex items-center justify-between mb-2.5">
                                                <div class="flex items-center gap-1.5">
                                                    <div class="relative inline-flex items-center">
                                                        <select x-model.number="pickerMonth"
                                                                class="appearance-none bg-transparent pr-3.5 text-[13px] font-bold text-[#1d2d35] focus:outline-none cursor-pointer">
                                                            <template x-for="(mName, mIdx) in monthNames" :key="mIdx">
                                                                <option :value="mIdx" x-text="mName" :selected="mIdx === pickerMonth"></option>
                                                            </template>
                                                        </select>
                                                        <svg class="w-3 h-3 text-[#6d6a65] absolute right-0 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                                    </div>

                                                    <div class="relative inline-flex items-center">
                                                        <select x-model.number="pickerYear"
                                                                class="appearance-none bg-transparent pr-3.5 text-[13px] font-bold text-[#1d2d35] focus:outline-none cursor-pointer">
                                                            <template x-for="yr in years" :key="yr">
                                                                <option :value="yr" x-text="yr" :selected="yr === pickerYear"></option>
                                                            </template>
                                                        </select>
                                                        <svg class="w-3 h-3 text-[#6d6a65] absolute right-0 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-1">
                                                    <button type="button" @click="prevMonth()" class="p-1 text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                                    </button>
                                                    <button type="button" @click="nextMonth()" class="p-1 text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-7 text-center text-[9.5px] font-bold uppercase tracking-wider text-[#6d6a65] mb-1.5">
                                                <span>MO</span><span>TU</span><span>WE</span><span>TH</span><span>FR</span><span>SA</span><span>SU</span>
                                            </div>

                                            <div class="grid grid-cols-7 gap-y-1 text-center text-[12px]">
                                                <template x-for="cell in getCalendarDays()" :key="cell.key">
                                                    <button type="button"
                                                            @click="selectDay(cell)"
                                                            class="w-[26px] h-[26px] mx-auto rounded-full flex items-center justify-center transition-colors cursor-pointer"
                                                            :class="{
                                                                'bg-[#1b6ac9] text-white font-bold': cell.isSelected,
                                                                'bg-[#e5e2dc] text-[#1d2d35] font-bold': !cell.isSelected && cell.isToday,
                                                                'text-[#1d2d35] font-medium hover:bg-[#f2f0ec]': !cell.isSelected && !cell.isToday && cell.currentMonth,
                                                                'text-[#b8b4ac] hover:bg-[#f2f0ec]': !cell.isSelected && !cell.currentMonth
                                                            }"
                                                            x-text="cell.day">
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Indicate progress -->
                    <div class="flex items-center gap-3.5">
                        <div class="w-[125px] sm:w-[140px] text-right font-bold text-[#1d2d35] text-[13.5px] shrink-0">
                            Indicate progress
                        </div>

                        <div class="flex items-center gap-2.5 flex-wrap">
                            <button type="button"
                                    @click="indicateProgress = !indicateProgress"
                                    class="w-[46px] h-[26px] rounded-[5px] border p-[2px] flex items-center justify-between transition-colors cursor-pointer select-none"
                                    :class="indicateProgress ? 'bg-[#2da562] border-[#2da562]' : 'bg-[#8c969e] border-[#7b858e]'">
                                <template x-if="!indicateProgress">
                                    <div class="flex items-center justify-between w-full px-0.5">
                                        <span class="w-[10px] h-[18px] rounded-[2px] bg-white"></span>
                                        <span class="text-[11px] font-bold text-white pr-0.5">Off</span>
                                    </div>
                                </template>
                                <template x-if="indicateProgress">
                                    <div class="flex items-center justify-between w-full px-0.5">
                                        <span class="text-[11px] font-bold text-white pl-0.5">On</span>
                                        <span class="w-[10px] h-[18px] rounded-[2px] bg-white"></span>
                                    </div>
                                </template>
                            </button>

                            <span class="text-[14px] text-[#1d2d35]">Show a gauge to indicate progress &amp; status</span>

                            <button type="button"
                                    @click="gaugeHelpModal = true"
                                    class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-[4px] border border-[#b8b4ac] text-[11px] font-bold text-[#6d6a65] hover:text-[#1d2d35] hover:border-[#1d2d35] transition-colors cursor-pointer"
                                    title="Learn how the progress & status gauge works">
                                <span>?</span>
                                <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Row 3: Wallpaper -->
                    <div class="flex items-start gap-3.5">
                        <div class="w-[125px] sm:w-[140px] text-right font-bold text-[#1d2d35] text-[13.5px] shrink-0 pt-1">
                            Wallpaper
                        </div>

                        <div class="flex items-start gap-3.5">
                            <label class="w-[132px] h-[78px] rounded-[6px] border border-[#d6d2c9] hover:border-[#8c8881] bg-white flex items-center justify-center cursor-pointer overflow-hidden transition-colors shrink-0"
                                   title="Choose project wallpaper">
                                <input type="file" accept="image/*" class="hidden" @change="handleWallpaperUpload($event)">
                                <template x-if="wallpaperPreview">
                                    <img :src="wallpaperPreview" alt="Wallpaper preview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!wallpaperPreview">
                                    <svg class="w-[18px] h-[15px] text-[#4a5560]" viewBox="0 0 18 15" fill="none" stroke="currentColor" stroke-width="1.4">
                                        <rect x="1" y="1" width="16" height="13" rx="2"/>
                                        <circle cx="5.5" cy="5.2" r="1.3"/>
                                        <path d="M1.5 12.5l4.2-4.2 3.2 3.2 3.3-3.5 4.3 4.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </template>
                            </label>

                            <div class="space-y-1.5 pt-0.5 text-[14px] text-[#1d2d35]">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="wallpaper_mode" value="fill" x-model="wallpaperMode" class="w-4 h-4 text-[#1b6ac9] border-[#b8b4ac] focus:ring-0">
                                    <span>Fill screen</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="wallpaper_mode" value="tile" x-model="wallpaperMode" class="w-4 h-4 text-[#1b6ac9] border-[#b8b4ac] focus:ring-0">
                                    <span>Tile</span>
                                </label>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Action Buttons: "Save" + "Never mind" -->
                <div class="mt-7 flex items-center gap-2.5">
                    <button type="submit"
                            class="bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-semibold text-[14px] px-4 py-1.5 rounded-[6px] transition cursor-pointer">
                        Save
                    </button>
                    <button type="button"
                            @click="editingDetails = false"
                            class="px-4 py-1.5 rounded-[6px] border border-[#d6d2c9] bg-white hover:bg-[#f6f4f0] text-[#1b6ac9] font-normal text-[14px] transition cursor-pointer">
                        Never mind
                    </button>
                </div>
            </form>
        </div>

        @if($isFirstRun)
        <!-- ============================================================ -->
        <!-- NEW PROJECT FIRST-RUN ONBOARDING & INTERACTIVE TOOL BUILDER  -->
        <!-- (Matches 28d6b4e0.jpg, d1b38fed.jpg, 77f7404a.jpg, efb97dd8.jpg) -->
        <!-- ============================================================ -->
        <div class="px-8 sm:px-10 pt-7 pb-16">
            <div x-show="!editingDetails">
                <div @click="openDetailsEditor()" class="cursor-pointer group inline-block">
                    <h1 class="text-[34px] font-extrabold text-[#1d2d35] tracking-[-0.03em] leading-tight group-hover:underline">
                        {{ $project->name }}
                    </h1>
                    @if($project->description)
                    <p class="text-[15.5px] font-normal text-[#1d2d35] mt-1 leading-snug">
                        {{ $project->description }}
                    </p>
                    @endif
                </div>

                <!-- Subheader Activity Line (Matches media_1793290645677_3ca49a79.jpg) -->
                <div class="flex items-center gap-1.5 text-[13.5px] text-[#1d2d35] mt-2">
                    <img src="/uploads/avatars/22369668.png" alt="Manjot Singh" class="w-[22px] h-[22px] rounded-full object-cover shrink-0">
                    <span>active recently, latest {{ $project->created_at ? $project->created_at->diffForHumans() : '4 hours ago' }} -</span>
                    <a href="{{ route('my.activity', ['scope' => 'all']) }}" class="text-[#1b6ac9] underline hover:text-[#124b8f] font-normal">View activity</a>
                </div>
            </div>

            <!-- State 1: Initial First-Run Prompt (Dashed + Button, Curved Yellow Arrow & Dashed Yellow Callout Box) -->
            <div x-show="addedTools.length === 0 && !addToolOpen" class="mt-6">
                <button type="button"
                        @click="openAddToolMenu()"
                        class="w-[44px] h-[44px] rounded-[8px] border border-dashed border-[#b8b4ac] hover:border-[#1d2d35] bg-white flex items-center justify-center text-[#1d2d35] transition-colors cursor-pointer"
                        title="Add a tool">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>

                <!-- Curved Yellow Arrow + Dashed Yellow Callout Box -->
                <div class="relative mt-1 pl-9">
                    <svg class="w-8 h-9 text-[#e5c96b] -ml-4 -mb-3" viewBox="0 0 40 44" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M36 40 C 16 40, 8 26, 8 4"/>
                        <polyline points="3 10 8 3 13 10"/>
                    </svg>

                    <div class="w-[440px] max-w-full rounded-[8px] border border-dashed border-[#e5d58b] bg-[#fffef9] px-6 py-5 text-left">
                        <h3 class="text-[15.5px] font-bold text-[#1d2d35] mb-2.5">
                            Add something to get this project started.
                        </h3>
                        <p class="text-[15px] text-[#4a4640] leading-[1.45] mb-3">
                            Post a message to welcome people, add to-dos to plan work, create a document so everyone has a reference point, track bugs on the Card Table, and more.
                        </p>
                        <p class="text-[15px] text-[#4a4640] leading-[1.45]">
                            As your project grows, you’ll be able to add things via this same menu.
                        </p>
                    </div>
                </div>
            </div>

            <!-- State 2: 3-Column Tool Cards Grid + ADD A TOOL Menu Card -->
            <div x-show="addToolOpen || addedTools.length > 0" x-cloak class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-6 items-start">
                <!-- Added Tool Cards -->
                <template x-for="(tool, toolIdx) in addedTools" :key="tool.id">
                    <div class="flex flex-col cursor-grab active:cursor-grabbing select-none transition-opacity"
                         draggable="true"
                         :class="dragAddedToolIdx === toolIdx ? 'opacity-45 grayscale' : ''"
                         @dragstart="dragAddedToolIdx = toolIdx"
                         @dragover.prevent="reorderAddedTools(toolIdx)"
                         @drop.prevent="dragAddedToolIdx = null"
                         @dragend="dragAddedToolIdx = null">
                        <a :href="tool.url" draggable="false" class="text-[18.5px] font-bold text-[#9c3016] hover:underline tracking-[-0.02em] mb-1.5" x-text="tool.name"></a>

                        <div @click="window.location.href = tool.url"
                             class="h-[340px] rounded-[8px] border border-[#e5e2dc] bg-white p-5 relative hover:shadow-md transition-shadow cursor-pointer overflow-hidden">
                            
                            <!-- Sketch Wireframe: Message Board -->
                            <template x-if="tool.key === 'message_board' || tool.key === 'email_forwards'">
                                <svg class="w-full h-full text-[#f0efed]" viewBox="0 0 240 280" fill="none">
                                    <circle cx="28" cy="26" r="16" fill="currentColor"/>
                                    <path d="M56 26 Q 62 20, 68 26 T 80 26 T 92 26 T 104 26 T 116 26 T 128 26 T 140 26" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
                                    <path d="M16 64 Q 24 54, 32 64 T 48 64 T 64 64 T 80 64 T 96 64 T 112 64 T 128 64 T 144 64 T 160 64 T 176 64 T 192 64" stroke="currentColor" stroke-width="9" stroke-linecap="round"/>
                                    <path d="M16 90 Q 24 82, 32 90 T 48 90 T 64 90 T 80 90 T 96 90 T 112 90 T 128 90" stroke="currentColor" stroke-width="8" stroke-linecap="round"/>
                                    <line x1="16" y1="118" x2="205" y2="118" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                                    <line x1="16" y1="132" x2="210" y2="132" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                                    <line x1="16" y1="146" x2="185" y2="146" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                                    <circle cx="28" cy="184" r="14" fill="currentColor"/>
                                    <path d="M54 184 Q 60 178, 66 184 T 78 184 T 90 184 T 102 184" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
                                    <circle cx="28" cy="232" r="14" fill="currentColor"/>
                                    <path d="M54 232 Q 60 226, 66 232 T 78 232 T 90 232 T 102 232" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
                                </svg>
                            </template>

                            <!-- Sketch Wireframe: To-dos & Other Tools -->
                            <template x-if="tool.key !== 'message_board' && tool.key !== 'email_forwards'">
                                <svg class="w-full h-full text-[#f0efed]" viewBox="0 0 240 280" fill="none">
                                    <path d="M16 24 Q 24 14, 32 24 T 48 24 T 64 24 T 80 24 T 96 24 T 112 24 T 128 24 T 144 24" stroke="currentColor" stroke-width="8" stroke-linecap="round"/>
                                    <rect x="24" y="52" width="16" height="16" rx="3" stroke="currentColor" stroke-width="4"/>
                                    <path d="M52 60 Q 58 54, 64 60 T 76 60 T 88 60 T 100 60 T 112 60 T 124 60" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
                                    <rect x="24" y="84" width="16" height="16" rx="3" stroke="currentColor" stroke-width="4"/>
                                    <path d="M52 92 Q 58 86, 64 92 T 76 92 T 88 92 T 100 92 T 112 92 T 124 92 T 136 92 T 148 92" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
                                    <rect x="24" y="116" width="16" height="16" rx="3" stroke="currentColor" stroke-width="4"/>
                                    <path d="M52 124 Q 58 118, 64 124 T 76 124 T 88 124 T 100 124" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
                                    <rect x="24" y="148" width="16" height="16" rx="3" stroke="currentColor" stroke-width="4"/>
                                    <path d="M52 156 Q 58 150, 64 156 T 76 156 T 88 156 T 100 156 T 112 156 T 124 156" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
                                    <rect x="24" y="180" width="16" height="16" rx="3" stroke="currentColor" stroke-width="4"/>
                                    <path d="M52 188 Q 58 182, 64 188 T 76 188 T 88 188 T 100 188" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
                                    <rect x="16" y="222" width="22" height="22" rx="4" stroke="currentColor" stroke-width="5"/>
                                    <path d="M52 233 Q 60 225, 68 233 T 84 233 T 100 233 T 116 233 T 132 233" stroke="currentColor" stroke-width="8" stroke-linecap="round"/>
                                </svg>
                            </template>

                            <!-- Bottom-right Trash Delete Icon (Matches 77f7404a.jpg) -->
                            <button type="button"
                                    @click.stop="removeTool(tool.id)"
                                    class="absolute bottom-2.5 right-2.5 p-1 text-[#6d6a65] hover:text-red-600 transition-colors cursor-pointer"
                                    title="Remove tool">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Next Grid Slot: ADD A TOOL Card (or + Button if closed) -->
                <div :class="addedTools.length > 0 ? 'mt-[32px]' : ''">
                    <div x-show="addToolOpen"
                         @click.outside="if (addedTools.length > 0) addToolOpen = false"
                         class="w-full max-w-[310px] h-[340px] rounded-[8px] border border-[#f2ece1] bg-white shadow-[0_2px_8px_rgba(0,0,0,0.05)] flex flex-col overflow-hidden">
                        
                        <div class="px-4 pt-3.5 pb-2 text-[11px] font-extrabold uppercase tracking-wider text-[#9c4221] flex-shrink-0">
                            ADD A TOOL
                        </div>

                        <div class="flex-1 overflow-y-auto px-3.5 pb-2 space-y-2.5">
                            <template x-for="t in availableTools" :key="t.key">
                                <button type="button"
                                        @click="addTool(t)"
                                        class="w-full flex items-center justify-between gap-2.5 py-1 px-1 rounded-[6px] hover:bg-[#fbfaf8] text-left transition-colors cursor-pointer group">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <!-- Dark Charcoal Circular Icon -->
                                        <span class="w-[34px] h-[34px] rounded-full bg-[#1d2d35] text-white flex items-center justify-center flex-shrink-0">
                                            <template x-if="t.key === 'message_board'">
                                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18 11v2h4v-2h-4zm-2 6.61c.96.71 2.21 1.65 3.2 2.39.4-.53.8-1.07 1.2-1.6-.99-.74-2.24-1.68-3.2-2.4-.4.54-.8 1.08-1.2 1.61zM20.4 5.6c-.4-.53-.8-1.07-1.2-1.6-.99.74-2.24 1.68-3.2 2.4.4.53.8 1.07 1.2 1.6.96-.72 2.21-1.65 3.2-2.4zM4 9c-1.1 0-2 .9-2 2v2c0 1.1.9 2 2 2h1v4h2v-4h1l5 3V6L8 9H4zm11.5 3c0-1.33-.58-2.53-1.5-3.35v6.69c.92-.81 1.5-2.01 1.5-3.34z"/></svg>
                                            </template>
                                            <template x-if="t.key === 'todos'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            </template>
                                            <template x-if="t.key === 'docs'">
                                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/></svg>
                                            </template>
                                            <template x-if="t.key === 'calendar'">
                                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10z"/></svg>
                                            </template>
                                            <template x-if="t.key === 'chat'">
                                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                                            </template>
                                            <template x-if="t.key === 'card_table'">
                                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M4 5v13h17V5H4zm10 2v9h-3V7h3zM6 7h3v9H6V7zm13 9h-3V7h3v9z"/></svg>
                                            </template>
                                            <template x-if="t.key === 'checkins'">
                                                <span class="text-[16px] font-bold">?</span>
                                            </template>
                                            <template x-if="t.key === 'email_forwards'">
                                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                                            </template>
                                            <template x-if="t.key === 'external_link'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                            </template>
                                        </span>

                                        <div class="min-w-0 leading-tight">
                                            <div class="text-[13.5px] font-bold text-[#1d2d35] truncate" x-text="t.name"></div>
                                            <div class="text-[11.5px] text-[#6d6a65] truncate mt-0.5" x-text="t.desc"></div>
                                        </div>
                                    </div>

                                    <template x-if="t.hasBadge">
                                        <span class="w-4 h-4 rounded-[3px] border border-dashed border-[#6d6a65] flex-shrink-0 mr-1"></span>
                                    </template>
                                </button>
                            </template>

                            <!-- Bottom Divider + Customize tools (Matches efb97dd8.jpg) -->
                            <div class="border-t border-[#ece9e2] pt-2.5 mt-2">
                                <a href="{{ route('projects.settings', $project->id) }}"
                                   class="w-full flex items-center gap-2.5 py-1 px-1 rounded-[6px] hover:bg-[#fbfaf8] text-left transition-colors cursor-pointer">
                                    <span class="w-[34px] h-[34px] rounded-full bg-[#1d2d35] text-white flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58a.49.49 0 00.12-.61l-1.92-3.32a.488.488 0 00-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54a.484.484 0 00-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58a.49.49 0 00-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
                                    </span>
                                    <span class="text-[13.5px] font-bold text-[#1d2d35]">Customize tools</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <button type="button"
                            x-show="!addToolOpen && addedTools.length > 0"
                            @click="addToolOpen = true"
                            class="w-[44px] h-[44px] rounded-[8px] border border-dashed border-[#b8b4ac] hover:border-[#1d2d35] bg-white flex items-center justify-center text-[#1d2d35] transition-colors cursor-pointer"
                            title="Add a tool">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Wavy Bottom Divider (Matches media_1793290645677_3ca49a79.jpg) -->
            <div x-show="addedTools.length > 0" class="mt-14 overflow-hidden">
                <svg class="w-full h-3 text-[#d6d2c9]" viewBox="0 0 1200 12" fill="none" preserveAspectRatio="none">
                    <path d="M0 6 Q 6 0, 12 6 T 24 6 T 36 6 T 48 6 T 60 6 T 72 6 T 84 6 T 96 6 T 108 6 T 120 6 T 132 6 T 144 6 T 156 6 T 168 6 T 180 6 T 192 6 T 204 6 T 216 6 T 228 6 T 240 6 T 252 6 T 264 6 T 276 6 T 288 6 T 300 6 T 312 6 T 324 6 T 336 6 T 348 6 T 360 6 T 372 6 T 384 6 T 396 6 T 408 6 T 420 6 T 432 6 T 444 6 T 456 6 T 468 6 T 480 6 T 492 6 T 504 6 T 516 6 T 528 6 T 540 6 T 552 6 T 564 6 T 576 6 T 588 6 T 600 6 T 612 6 T 624 6 T 636 6 T 648 6 T 660 6 T 672 6 T 684 6 T 696 6 T 708 6 T 720 6 T 732 6 T 744 6 T 756 6 T 768 6 T 780 6 T 792 6 T 804 6 T 816 6 T 828 6 T 840 6 T 852 6 T 864 6 T 876 6 T 888 6 T 900 6 T 912 6 T 924 6 T 936 6 T 948 6 T 960 6 T 972 6 T 984 6 T 996 6 T 1008 6 T 1020 6 T 1032 6 T 1044 6 T 1056 6 T 1068 6 T 1080 6 T 1092 6 T 1104 6 T 1116 6 T 1128 6 T 1140 6 T 1152 6 T 1164 6 T 1176 6 T 1188 6 T 1200 6" stroke="currentColor" stroke-width="1.2" fill="none"/>
                </svg>
            </div>
        </div>
        @else
        <!-- Project Header: Left-Aligned Title, Subtitle & Activity Cue (Matches Screenshot) -->
        <div x-show="!editingDetails" class="px-7 pt-6 pb-5 text-left">
            @if($project->id == 26 || $project->client_subtitle)
            <div class="text-[13.5px] font-normal text-[#6d6a65] mb-0.5 leading-snug">
                {{ $project->client_subtitle ?: 'NDC Homes' }}
            </div>
            @endif
            <h1 @click="openDetailsEditor()" class="text-[28px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-[1.15] flex items-center gap-2 cursor-pointer hover:underline">
                <span>{{ $project->name }}</span>
                @if(($project->is_pinned || $project->id == 5) && $project->id != 26)
                <span class="text-[#f5b642] fill-[#f5b642] flex-shrink-0" title="Pinned project">
                    <svg class="w-[22px] h-[22px] fill-current" viewBox="0 0 24 24">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></polygon>
                    </svg>
                </span>
                @endif
            </h1>

            @if(($project->description || $project->client_subtitle || $project->id == 4) && $project->id != 5)
            <p @click="openDetailsEditor()" class="text-[15.5px] font-normal text-[#1d2d35] mt-1 leading-snug cursor-pointer">
                {{ $project->description ?: ($project->client_subtitle ?: 'Company-wide announcements and stuff everyone needs to know.') }}
            </p>
            @endif

            @if(!empty($latestProjectActivity) && !empty($latestProjectActivity->created_at) && \Carbon\Carbon::parse($latestProjectActivity->created_at)->greaterThanOrEqualTo(now()->subDays(5)))
            @php
                $lBadge = $latestProjectActivity->badge ?? (object)['initials' => 'M', 'bg' => 'bg-[#0d9488]', 'avatar' => null];
                $lTimeText = \Carbon\Carbon::parse($latestProjectActivity->created_at)->diffForHumans();
            @endphp
            <div class="flex items-center gap-1.5 text-[13.5px] text-[#1d2d35] mt-2.5">
                @if(!empty($lBadge->avatar))
                    <img src="{{ $lBadge->avatar }}" alt="" class="w-[22px] h-[22px] rounded-full object-cover select-none">
                @else
                    <span class="w-[22px] h-[22px] rounded-full {{ $lBadge->bg ?? 'bg-[#0d9488]' }} text-white font-bold text-[9.5px] flex items-center justify-center select-none cursor-pointer">{{ $lBadge->initials ?? 'M' }}</span>
                @endif
                <span>active recently, latest {{ $lTimeText }} -</span>
                <a href="#latest-activity" class="text-[#1b6ac9] underline hover:text-[#124b8f] font-normal">View activity</a>
            </div>
            @elseif($project->id == 4)
            <div class="flex items-center gap-1.5 text-[13.5px] text-[#1d2d35] mt-2.5">
                <span class="w-[22px] h-[22px] rounded-full bg-[#d946ef] text-white font-bold text-[9.5px] flex items-center justify-center select-none cursor-pointer">GK</span>
                <span>active recently, latest 5 hours ago -</span>
                <a href="#latest-activity" class="text-[#1b6ac9] underline hover:text-[#124b8f] font-normal">View activity</a>
            </div>
            @elseif($project->id == 26 || $project->id == 5)
            <div class="flex items-center gap-1.5 text-[13.5px] text-[#1d2d35] mt-2">
                <span>No one active in the last 5 days -</span>
                <a href="#latest-activity" class="text-[#1b6ac9] underline hover:text-[#124b8f] font-normal">View past activity</a>
            </div>
            @elseif($discussions->isNotEmpty())
            @php
                $latestAuthor = $discussions->first()->author->name ?? 'Team Member';
                $laParts = preg_split('/\s+/', trim($latestAuthor));
                $laIn = strtoupper(substr($laParts[0] ?? 'T', 0, 1) . (isset($laParts[1]) ? substr($laParts[1], 0, 1) : ''));
            @endphp
            <div class="flex items-center gap-1.5 text-[13.5px] text-[#1d2d35] mt-2.5">
                <span class="w-[22px] h-[22px] rounded-full bg-[#0d9488] text-white font-bold text-[9.5px] flex items-center justify-center select-none cursor-pointer">{{ $laIn }}</span>
                <span>active recently, latest {{ $discussions->first()->created_at ? $discussions->first()->created_at->diffForHumans() : 'recently' }} -</span>
                <a href="#latest-activity" class="text-[#1b6ac9] underline hover:text-[#124b8f] font-normal">View activity</a>
            </div>
            @endif
        </div>

        <!-- ============================================================ -->
        <!-- 6 BASECAMP TOOL CARDS (3 Columns x 2 Rows — Draggable)       -->
        <!-- ============================================================ -->
        <div id="project-tools-grid" class="px-7 pb-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-6">

            <!-- 1. Message Board -->
            <div data-tool-id="message_board" class="bc-tool-card flex flex-col group cursor-grab active:cursor-grabbing select-none">
                <div class="flex items-center justify-between mb-1.5 min-h-[24px]">
                    <a href="{{ route('projects.discussions', $project->id) }}" class="bc-tool-card-title text-[17px] font-bold text-[#a62c21] hover:underline tracking-[-0.015em]">
                        Message Board
                    </a>
                    <a href="{{ route('projects.discussions', ['id' => $project->id, 'new' => 1]) }}" class="px-2.5 py-0.5 text-[11.5px] font-normal text-[#1d2d35] bg-white hover:bg-slate-50 border border-[#d8d4cc] rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                        Add a message
                    </a>
                </div>

                <a href="{{ route('projects.discussions', $project->id) }}" class="bc-tool-card-box h-[316px] rounded-[8px] border border-[#e5e2dc] group-hover:border-[#7e7a73] bg-white group-hover:bg-[#faf8f5] overflow-hidden flex flex-col transition-colors">
                    @if($project->id == 5)
                        <!-- Exact Match to media_1793345782120_729b2cb6.png for Project 5 -->
                        <div class="block p-3 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="relative w-[28px] h-[28px] rounded-full overflow-hidden flex-shrink-0">
                                    <img src="/uploads/avatars/22369668.png" alt="Manjot Singh" class="w-full h-full object-cover">
                                    <span class="absolute bottom-0 inset-x-0 h-[36%] bg-[#facc15] flex items-center justify-center">
                                        <span class="text-[5.5px] font-black text-[#1d1c1b] leading-none">OUT</span>
                                    </span>
                                </span>
                                <div class="leading-[1.12] text-[11.5px]">
                                    <p class="font-normal text-[#1d2d35]">Manjot Singh</p>
                                    <p class="text-[#6d6a65] text-[11px]">Jan 21</p>
                                </div>
                            </div>
                            <p class="text-[13px] font-bold text-[#1d2d35] mt-1.5 leading-snug truncate">
                                Daily Scrum – MoM - 21/1/2026
                            </p>
                            <p class="text-[11.5px] text-[#1d2d35] leading-[1.28] line-clamp-3 mt-0.5 font-normal">
                                Daily Scrum – MoM • Harsh and Jitender are already working on ST1 under the guidance of Shachish Sir. • Nikhil has received a couple of CVs via WhatsApp...
                            </p>
                        </div>

                        <div class="divide-y divide-[#f0ede8] border-t border-[#f0ede8] flex-1">
                            @php
                                $p5DiscRows = [
                                    ['title' => 'Seamless.ai credentials', 'snippet' => 'info@ajath.com p@ssw0rd1111', 'type' => 'manjot'],
                                    ['title' => '121 request for BNI USA', 'snippet' => 'https://www.bniconnectglobal.com/ Manjot...', 'type' => 'manjot'],
                                    ['title' => 'Trading app proposal for Rachita Mi...', 'snippet' => 'Hello Sir, I collaborated the proposal with y...', 'type' => 'pg'],
                                    ['title' => 'Company credentials', 'snippet' => 'Hello Sir, please provide the 2022 company...', 'type' => 'pg'],
                                    ['title' => 'Untitled', 'snippet' => '', 'type' => 'pg'],
                                ];
                            @endphp
                            @foreach($p5DiscRows as $pRow)
                                <div class="px-3 py-1 flex items-center gap-2">
                                    @if($pRow['type'] === 'manjot')
                                        <span class="relative w-[24px] h-[24px] rounded-full overflow-hidden flex-shrink-0">
                                            <img src="/uploads/avatars/22369668.png" alt="Manjot Singh" class="w-full h-full object-cover">
                                            <span class="absolute bottom-0 inset-x-0 h-[36%] bg-[#facc15] flex items-center justify-center">
                                                <span class="text-[5px] font-black text-[#1d1c1b] leading-none">OUT</span>
                                            </span>
                                        </span>
                                    @else
                                        <span class="w-[24px] h-[24px] rounded-full bg-[#eab308] text-white font-bold text-[9.5px] flex items-center justify-center flex-shrink-0 select-none">PG</span>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[12.5px] font-bold text-[#1d2d35] leading-[1.18] truncate">{{ $pRow['title'] }}</p>
                                        @if($pRow['snippet'] !== '')
                                            <p class="text-[11.5px] text-[#283c46] leading-[1.18] truncate font-normal">{{ $pRow['snippet'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                    @php
                        $firstDisc = $discussions->first();
                        $restDiscs = $discussions->slice(1, 5);
                        $discBadgeColors = [
                            'GK' => 'bg-[#d946ef]',
                            'PK' => 'bg-[#00897b]',
                            'MS' => 'bg-[#2563eb]',
                            'M'  => 'bg-[#2563eb]',
                            'SN' => 'bg-[#9333ea]',
                            'RK' => 'bg-[#db2777]',
                            'SS' => 'bg-[#4f46e5]',
                            'E'  => 'bg-[#ea580c]',
                            'RS' => 'bg-[#10b981]',
                        ];
                    @endphp
                    @if($firstDisc)
                        @php
                            $fAuthorName = $firstDisc->author->name ?? 'Gaurav Khulbey';
                            $fParts = preg_split('/\s+/', trim($fAuthorName));
                            $fIn = ($fAuthorName === 'Manjot Singh') ? 'M' : strtoupper(substr($fParts[0] ?? 'G', 0, 1) . (isset($fParts[1]) ? substr($fParts[1], 1, 0) . substr($fParts[1], 0, 1) : ''));
                            $fBg = $discBadgeColors[$fIn] ?? 'bg-[#d946ef]';
                            $fCleanTitle = ($project->id == 26)
                                ? trim(str_replace('📢', '', $firstDisc->title))
                                : $firstDisc->title;
                        @endphp
                        <!-- Featured First Message -->
                        <div class="block p-3 pb-2">
                            <div class="flex items-center gap-2">
                                @if(stripos($fAuthorName, 'Manjot') !== false)
                                    <img src="/uploads/avatars/22369668.png" alt="{{ $fAuthorName }}" class="w-[26px] h-[26px] rounded-full object-cover flex-shrink-0">
                                @elseif(stripos($fAuthorName, 'Shachish') !== false)
                                    <img src="/uploads/avatars/25036340.png" alt="{{ $fAuthorName }}" class="w-[26px] h-[26px] rounded-full object-cover flex-shrink-0">
                                @elseif(stripos($fAuthorName, 'Harshvardhan') !== false)
                                    <img src="/uploads/avatars/47630095.png" alt="{{ $fAuthorName }}" class="w-[26px] h-[26px] rounded-full object-cover flex-shrink-0">
                                @elseif(stripos($fAuthorName, 'Nirdesh') !== false)
                                    <img src="/uploads/avatars/48060513.png" alt="{{ $fAuthorName }}" class="w-[26px] h-[26px] rounded-full object-cover flex-shrink-0">
                                @else
                                    <span class="w-[26px] h-[26px] rounded-full {{ $fBg }} text-white font-bold text-[10.5px] flex items-center justify-center flex-shrink-0 select-none">{{ $fIn }}</span>
                                @endif
                                <div class="leading-[1.12] text-[11.5px]">
                                    <p class="font-normal text-[#1d2d35]">{{ $fAuthorName }}</p>
                                    <p class="text-[#6d6a65] text-[11px]">{{ $firstDisc->created_at ? $firstDisc->created_at->format('M j') : 'Oct 29' }}</p>
                                </div>
                            </div>
                            <p class="text-[13px] font-bold text-[#1d2d35] mt-1.5 leading-snug truncate">
                                {{ $fCleanTitle }}
                            </p>
                            @if(!empty($firstDisc->snippet))
                            <p class="text-[11.5px] text-[#1d2d35] leading-[1.28] line-clamp-3 mt-0.5 font-normal">
                                {{ $firstDisc->snippet }}
                            </p>
                            @endif
                        </div>

                        <!-- Compact Subsequent Rows -->
                        <div class="divide-y divide-[#f0ede8] border-t border-[#f0ede8] flex-1">
                            @foreach($restDiscs as $disc)
                            @php
                                $dAuthorName = $disc->author->name ?? 'Gaurav Khulbey';
                                if ($project->id == 26 && stripos($disc->title ?? '', 'worklog') !== false) {
                                    $dAuthorName = 'Rekha Singh';
                                } elseif ($project->id == 26 && ($disc->title ?? '') === 'Untitled') {
                                    $dAuthorName = 'Edward';
                                }
                                $dParts = preg_split('/\s+/', trim($dAuthorName));
                                $dIn = ($dAuthorName === 'Manjot Singh') ? 'M' : (($dAuthorName === 'Edward') ? 'E' : strtoupper(substr($dParts[0] ?? 'G', 0, 1) . (isset($dParts[1]) ? substr($dParts[1], 0, 1) : '')));
                                $dBg = $discBadgeColors[$dIn] ?? 'bg-[#d946ef]';
                                $rowSnippet = ($project->id == 26 && stripos($disc->title ?? '', 'worklog') !== false && !str_starts_with($disc->snippet ?? '', '•'))
                                    ? '• ' . ($disc->snippet ?? '')
                                    : ($disc->snippet ?? '');
                            @endphp
                            <div class="px-3 py-1 flex items-center gap-2">
                                @if(stripos($dAuthorName, 'Manjot') !== false)
                                    <img src="/uploads/avatars/22369668.png" alt="{{ $dAuthorName }}" class="w-[24px] h-[24px] rounded-full object-cover flex-shrink-0">
                                @elseif(stripos($dAuthorName, 'Shachish') !== false)
                                    <img src="/uploads/avatars/25036340.png" alt="{{ $dAuthorName }}" class="w-[24px] h-[24px] rounded-full object-cover flex-shrink-0">
                                @elseif(stripos($dAuthorName, 'Harshvardhan') !== false)
                                    <img src="/uploads/avatars/47630095.png" alt="{{ $dAuthorName }}" class="w-[24px] h-[24px] rounded-full object-cover flex-shrink-0">
                                @elseif(stripos($dAuthorName, 'Nirdesh') !== false)
                                    <img src="/uploads/avatars/48060513.png" alt="{{ $dAuthorName }}" class="w-[24px] h-[24px] rounded-full object-cover flex-shrink-0">
                                @else
                                    <span class="w-[24px] h-[24px] rounded-full {{ $dBg }} text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0 select-none">{{ $dIn }}</span>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-[12.5px] font-bold text-[#1d2d35] leading-[1.18] truncate">
                                        {{ $disc->title }}
                                    </p>
                                    @if(!empty($rowSnippet))
                                    <p class="text-[11.5px] text-[#283c46] leading-[1.18] truncate font-normal">
                                        {{ $rowSnippet }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex-1 flex items-center justify-center text-[13px] text-[#6d6a65]">
                            Post a message or announcement
                        </div>
                    @endif
                    @endif
                </a>
            </div>

            <!-- 2. Automatic Check-ins -->
            <div data-tool-id="checkins" class="bc-tool-card flex flex-col group cursor-grab active:cursor-grabbing select-none">
                <div class="flex items-center justify-between mb-1.5 min-h-[24px]">
                    <a href="{{ route('projects.checkins', $project->id) }}" class="bc-tool-card-title text-[17px] font-bold text-[#a62c21] hover:underline tracking-[-0.015em]">
                        Automatic Check-ins
                    </a>
                </div>

                <a href="{{ route('projects.checkins', $project->id) }}" class="bc-tool-card-box h-[316px] rounded-[8px] border border-[#e5e2dc] group-hover:border-[#7e7a73] bg-white p-4 relative flex items-center justify-center overflow-hidden transition-colors">
                    <!-- Hand-drawn Scribble & Speech Bubble Illustration (Matches Basecamp Screenshot) -->
                    <svg class="w-48 h-48 text-[#eceae4] group-hover:blur-[2px] group-hover:opacity-45 transition-all" viewBox="0 0 200 200" fill="none">
                        <!-- Scribble lines on top-left -->
                        <path d="M22 45 L32 25 L28 48 L42 26 L38 48 L52 26 L48 48 L62 27 L58 48 L72 28 L68 48 L82 28 L78 48 L92 29 L88 48 L102 30 L98 48 L112 30 L108 48 L122 30" stroke="currentColor" stroke-width="5.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M22 72 L30 56 L27 74 L38 56 L35 74 L47 57 L44 74 L56 57 L53 74 L65 58 L62 74 L74 58 L71 74 L82 59" stroke="currentColor" stroke-width="5.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <!-- Question mark -->
                        <path d="M98 60 C102 53, 112 53, 113 60 C114 66, 105 68, 105 74" stroke="currentColor" stroke-width="5" stroke-linecap="round"/>
                        <circle cx="105" cy="82" r="2.8" fill="currentColor"/>
                        <!-- Avatar circle -->
                        <circle cx="32" cy="112" r="12" fill="currentColor"/>
                        <!-- Speech bubble -->
                        <path d="M55 98 L168 96 C173 96, 176 100, 175 105 L168 166 C167 171, 163 174, 157 174 L75 172 C70 172, 67 168, 66 162 L62 112 L50 102 Z" stroke="currentColor" stroke-width="5" stroke-linejoin="round"/>
                        <!-- Wavy lines inside bubble -->
                        <path d="M82 118 L92 108 L90 120 L102 109 L100 120 L112 110 L110 120 L122 110 L120 120 L132 110" stroke="currentColor" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M82 138 L92 128 L90 140 L102 129 L100 140 L112 130 L110 140 L122 130 L120 140 L132 130 L130 140 L142 130" stroke="currentColor" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M82 156 L92 146 L90 158 L102 147 L100 158 L112 148 L110 158 L122 148" stroke="currentColor" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <!-- Hover Overlay (Matches media_1793335491299_c248e7a5.jpg) -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center opacity-0 group-hover:opacity-100 transition-opacity bg-white/55">
                        <p class="text-[13.5px] text-[#1d2d35] leading-[1.35] max-w-[215px] font-normal">
                            Automatically send questions to your team on a regularly recurring schedule.
                        </p>
                        <span class="mt-3 px-3.5 py-1.5 rounded-full bg-[#256fb4] text-white text-[12.5px] font-semibold">
                            Add your first question
                        </span>
                    </div>
                    <span class="absolute bottom-2.5 right-2.5 text-[#8c8881]">
                        <i data-lucide="{{ ($project->id == 26 || $project->id == 5) ? 'archive' : 'square-pen' }}" class="w-3.5 h-3.5"></i>
                    </span>
                </a>
            </div>

            <!-- 3. Campfire -->
            <div data-tool-id="campfire" class="bc-tool-card flex flex-col group cursor-grab active:cursor-grabbing select-none">
                <div class="flex items-center justify-between mb-1.5 min-h-[24px]">
                    <a href="{{ route('projects.chat', $project->id) }}" class="bc-tool-card-title text-[17px] font-bold text-[#a62c21] hover:underline tracking-[-0.015em]">
                        Campfire
                    </a>
                </div>

                @if($project->id == 5)
                    <!-- Exact Match to media_1793345782120_729b2cb6.png (4 Amit Rai 11:00am shield icon messages) -->
                    <a href="{{ route('projects.chat', $project->id) }}" class="bc-tool-card-box h-[316px] rounded-[8px] border border-[#e5e2dc] group-hover:border-[#7e7a73] bg-white p-3.5 space-y-2 overflow-hidden transition-colors block">
                        @foreach(['mgmt_4.png', 'mgmt_3.png', 'mgmt_1.png', 'mgmt_2.png'] as $shieldImg)
                        <div class="flex items-start gap-2">
                            <img src="/uploads/avatars/36088205.png" alt="Amit Rai" class="w-[24px] h-[24px] rounded-full object-cover flex-shrink-0 mt-0.5">
                            <div class="bg-[#fbf7f2] rounded-[8px] px-2.5 py-1.5 min-w-[116px]">
                                <div class="flex items-baseline gap-1 text-[10px]">
                                    <span class="font-bold text-[#1d2d35]">Amit Rai</span>
                                    <span class="text-[#6d6a65] text-[9.5px]">11:00am</span>
                                </div>
                                <div class="mt-1 w-8 h-8 rounded bg-white flex items-center justify-center overflow-hidden">
                                    <img src="/uploads/campfire/{{ $shieldImg }}?v=20261030_6" alt="{{ $shieldImg }}" class="w-8 h-8 object-contain">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </a>
                @elseif($project->id == 4)
                    <a href="{{ route('projects.chat', $project->id) }}" class="bc-tool-card-box h-[316px] rounded-[8px] border border-[#e5e2dc] group-hover:border-[#7e7a73] bg-white p-3.5 space-y-2.5 overflow-hidden transition-colors block">
                        <!-- Item 1: 9:17pm DOC-20260918-WA0000.pdf -->
                        <div class="flex items-start gap-2">
                            <span class="w-[26px] h-[26px] rounded-full bg-[#d946ef] text-white font-bold text-[10.5px] flex items-center justify-center flex-shrink-0 mt-0.5 select-none">GK</span>
                            <div class="bg-[#fbf7f2] rounded-[10px] px-2.5 py-1.5 flex-1 min-w-0">
                                <div class="flex items-baseline gap-1 text-[10.5px]">
                                    <span class="font-bold text-[#1d2d35]">Gaurav Khulbey</span>
                                    <span class="text-[#6d6a65] text-[10px]">9:17pm</span>
                                </div>
                                <img src="/uploads/campfire/report_screenshot_1.png?v=20261030_3" alt="DOC-20260918-WA0000.pdf" class="mt-1 w-9 h-7 rounded border border-[#e5e2dc] bg-white object-cover object-top">
                            </div>
                        </div>

                        <!-- Item 2: 9:20pm Text -->
                        <div class="flex items-start gap-2">
                            <span class="w-[26px] h-[26px] rounded-full bg-[#d946ef] text-white font-bold text-[10.5px] flex items-center justify-center flex-shrink-0 mt-0.5 select-none">GK</span>
                            <div class="bg-[#fbf7f2] rounded-[10px] px-2.5 py-1.5 flex-1 min-w-0">
                                <div class="flex items-baseline gap-1 text-[10.5px]">
                                    <span class="font-bold text-[#1d2d35]">Gaurav Khulbey</span>
                                    <span class="text-[#6d6a65] text-[10px]">9:20pm</span>
                                </div>
                                <p class="text-[11.5px] text-[#1d2d35] leading-[1.28] mt-0.5 line-clamp-3">
                                    There was 2 mistakes in the report because it's AI testing , one was the report mentions that there was tls 1.1 and 1.2 used...
                                </p>
                            </div>
                        </div>

                        <!-- Item 3: 12:14pm 1000099828.jpg -->
                        <div class="flex items-start gap-2">
                            <span class="w-[26px] h-[26px] rounded-full bg-[#d946ef] text-white font-bold text-[10.5px] flex items-center justify-center flex-shrink-0 mt-0.5 select-none">GK</span>
                            <div class="bg-[#fbf7f2] rounded-[10px] px-2.5 py-1.5 flex-1 min-w-0">
                                <div class="flex items-baseline gap-1 text-[10.5px]">
                                    <span class="font-bold text-[#1d2d35]">Gaurav Khulbey</span>
                                    <span class="text-[#6d6a65] text-[10px]">12:14pm</span>
                                </div>
                                <img src="/uploads/campfire/1000099828.jpg?v=20261030_3" alt="1000099828.jpg" class="mt-1 w-9 h-7 rounded border border-[#d8d4cc] object-cover">
                            </div>
                        </div>

                        <!-- Item 4: 12:15pm 1000099830.jpg -->
                        <div class="flex items-start gap-2">
                            <span class="w-[26px] h-[26px] rounded-full bg-[#d946ef] text-white font-bold text-[10.5px] flex items-center justify-center flex-shrink-0 mt-0.5 select-none">GK</span>
                            <div class="bg-[#fbf7f2] rounded-[10px] px-2.5 py-1.5 flex-1 min-w-0">
                                <div class="flex items-baseline gap-1 text-[10.5px]">
                                    <span class="font-bold text-[#1d2d35]">Gaurav Khulbey</span>
                                    <span class="text-[#6d6a65] text-[10px]">12:15pm</span>
                                </div>
                                <img src="/uploads/campfire/1000099830.jpg?v=20261030_3" alt="1000099830.jpg" class="mt-1 w-9 h-7 rounded border border-[#d8d4cc] object-cover">
                            </div>
                        </div>
                    </a>
                @elseif($messages->isNotEmpty())
                    <a href="{{ route('projects.chat', $project->id) }}" class="bc-tool-card-box h-[316px] rounded-[8px] border border-[#e5e2dc] group-hover:border-[#7e7a73] bg-white p-3.5 space-y-2.5 overflow-hidden transition-colors block">
                        @foreach($messages->take(5) as $msg)
                            @php
                                $senderName = $msg->sender->name ?? 'Manjot Singh';
                                $sParts = preg_split('/\s+/', trim($senderName));
                                $sInitials = strtoupper(substr($sParts[0] ?? 'M', 0, 1) . (isset($sParts[1]) ? substr($sParts[1], 0, 1) : ''));
                                $sAvatar = ($msg->sender && $msg->sender->avatar_url && str_starts_with($msg->sender->avatar_url, '/uploads/avatars/')) ? $msg->sender->avatar_url : null;
                                $previewAttachment = null;
                                if (preg_match('/\[Attachment:\s*([^\]]+)\]/i', $msg->content ?? '', $mAtt)) {
                                    $previewAttachment = trim($mAtt[1]);
                                }
                            @endphp
                            <div class="flex items-start gap-2">
                                @if($sAvatar)
                                    <img src="{{ $sAvatar }}" alt="{{ $senderName }}" class="w-[26px] h-[26px] rounded-full object-cover flex-shrink-0 mt-0.5">
                                @else
                                    <span class="w-[26px] h-[26px] rounded-full bg-[#1b6ac9] text-white font-bold text-[10.5px] flex items-center justify-center flex-shrink-0 mt-0.5 select-none">{{ $sInitials }}</span>
                                @endif
                                <div class="bg-[#fbf7f2] rounded-[10px] px-2.5 py-1.5 flex-1 min-w-0">
                                    <div class="flex items-baseline gap-1 text-[10.5px]">
                                        <span class="font-bold text-[#1d2d35]">{{ $senderName }}</span>
                                        <span class="text-[#6d6a65] text-[10px]">{{ strtolower($msg->created_at->format('g:ia')) }}</span>
                                    </div>
                                    @if($previewAttachment)
                                        <img src="/uploads/campfire/{{ $previewAttachment }}" alt="{{ $previewAttachment }}" class="mt-1 w-9 h-7 rounded border border-[#d8d4cc] bg-white object-cover">
                                    @else
                                        <p class="text-[11.5px] text-[#1d2d35] leading-[1.28] mt-0.5 line-clamp-2">
                                            {{ $msg->content }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </a>
                @else
                    <a href="{{ route('projects.chat', $project->id) }}" class="bc-tool-card-box h-[316px] rounded-[8px] border border-[#e5e2dc] group-hover:border-[#7e7a73] bg-white p-4 relative flex items-center justify-center text-[13px] text-[#6d6a65] overflow-hidden transition-colors">
                        <!-- 5-Row Chat Bubbles Illustration (Matches media_1793335481241_cb1130e5.jpg & media_1793335486725_22754d1f.jpg) -->
                        <svg class="w-52 h-60 text-[#eceae4] group-hover:blur-[2px] group-hover:opacity-45 transition-all" viewBox="0 0 210 230" fill="none">
                            <!-- Row 1 -->
                            <circle cx="24" cy="24" r="11" fill="#efede8"/>
                            <rect x="44" y="10" width="122" height="28" rx="11" fill="#f7f6f3"/>
                            <path d="M56 26 L64 18 L62 27 L72 19 L70 27 L80 19 L78 27 L88 20 L86 27 L96 20 L94 27 L104 20" stroke="#ebe8e2" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <!-- Row 2 -->
                            <circle cx="24" cy="64" r="11" fill="#efede8"/>
                            <rect x="44" y="50" width="122" height="28" rx="11" fill="#f7f6f3"/>
                            <path d="M56 66 L64 58 L62 67 L72 59 L70 67 L80 59 L78 67 L88 60 L86 67 L96 60" stroke="#ebe8e2" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <!-- Row 3 (taller bubble) -->
                            <circle cx="24" cy="108" r="11" fill="#efede8"/>
                            <rect x="44" y="90" width="124" height="50" rx="12" fill="#f7f6f3"/>
                            <path d="M56 108 L64 100 L62 109 L72 101 L70 109 L80 101 L78 109 L88 102 L86 109 L96 102 L94 109 L104 102 L102 109 L112 102" stroke="#ebe8e2" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M56 126 L64 118 L62 127 L72 119 L70 127 L80 119 L78 127 L88 120" stroke="#ebe8e2" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <!-- Row 4 (right-aligned circle) -->
                            <rect x="56" y="152" width="110" height="28" rx="11" fill="#f7f6f3"/>
                            <path d="M98 168 L106 160 L104 169 L114 161 L112 169 L122 161 L120 169 L130 162 L128 169 L138 162 L136 169 L146 162" stroke="#ebe8e2" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="184" cy="166" r="11" fill="#efede8"/>
                            <!-- Row 5 -->
                            <circle cx="24" cy="204" r="11" fill="#efede8"/>
                            <rect x="44" y="190" width="112" height="28" rx="11" fill="#f7f6f3"/>
                            <path d="M56 206 L64 198 L62 207 L72 199 L70 207 L80 199 L78 207 L88 200 L86 207 L96 200" stroke="#ebe8e2" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <!-- Hover Overlay (Matches media_1793335481241_cb1130e5.jpg) -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center opacity-0 group-hover:opacity-100 transition-opacity bg-white/55">
                            <p class="text-[13.5px] text-[#1d2d35] leading-[1.35] max-w-[210px] font-normal">
                                A chat room for anything and everything that needs to be discussed right now.
                            </p>
                            <span class="mt-3 px-3.5 py-1.5 rounded-full bg-[#256fb4] text-white text-[12.5px] font-semibold">
                                Start chatting
                            </span>
                        </div>
                        <span class="absolute bottom-2.5 right-2.5 text-[#8c8881]">
                            <i data-lucide="archive" class="w-3.5 h-3.5"></i>
                        </span>
                    </a>
                @endif
            </div>

            <!-- 4. To-dos -->
            <div data-tool-id="todos" class="bc-tool-card flex flex-col group cursor-grab active:cursor-grabbing select-none">
                <div class="flex items-center justify-between mb-1.5 min-h-[24px]">
                    <a href="{{ route('projects.tasks', $project->id) }}" class="bc-tool-card-title text-[17px] font-bold text-[#a62c21] hover:underline tracking-[-0.015em]">
                        To-dos
                    </a>
                    <a href="{{ route('projects.tasks', $project->id) }}" class="px-2.5 py-0.5 text-[11.5px] font-normal text-[#1d2d35] bg-white hover:bg-slate-50 border border-[#d8d4cc] rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                        Add to-dos
                    </a>
                </div>

                <a href="{{ route('projects.tasks', $project->id) }}" class="bc-tool-card-box h-[316px] rounded-[8px] border border-[#e5e2dc] group-hover:border-[#7e7a73] bg-white group-hover:bg-[#faf8f5] p-4 relative overflow-hidden transition-colors block">
                    @if($project->id == 4 || ($totalTasks > 0 && ($doneTasks ?? 0) >= $totalTasks))
                    <!-- Circular Green 'ALL DONE!' Stamp in Top-Right -->
                    <div class="absolute top-3.5 right-3.5 w-[64px] h-[64px] rounded-full border-2 border-[#199849]/60 flex items-center justify-center -rotate-12 select-none pointer-events-none">
                        <div class="w-[56px] h-[56px] rounded-full border border-[#199849]/40 flex items-center justify-center">
                            <span class="text-[8.5px] font-extrabold text-[#199849]/80 tracking-tight uppercase border-y border-[#199849]/50 py-0.5 px-1 bg-white">
                                ALL DONE!
                            </span>
                        </div>
                    </div>
                    @endif

                    @if($project->id == 5)
                    <!-- Exact Match to media_1793345782120_729b2cb6.png for Project 5 To-dos -->
                    <div class="space-y-2.5 text-[11.5px] text-[#1d2d35] leading-snug">
                        @php
                            $p5TodoGroups = [
                                [
                                    'title' => 'May 2024 leads',
                                    'items' => [
                                        'Name: Salman Phone: 8383033476 Email: Skh...',
                                        'Name: Kunal Phone: 7037257801 Email: ramk0...',
                                        'Name: Varun Phone: 98969 04632 Email: varu...',
                                        'Name: Saurabh Phone: 8826924028 Email: sau...',
                                        'Name: Amit Oswal Phone: 8448655741 Email: a...',
                                    ],
                                ],
                                [
                                    'title' => 'April 2024 leads',
                                    'items' => [
                                        'Name: Daniel Joshua Phone: +917080622378 E...',
                                        'Name: Ved Gupta Phone: 09079915662 Email: ...',
                                        'Name: Richard Tucker Phone: 6185993354 Em...',
                                        'Name: Nisha Phone: 8287217725 Email: nisha8...',
                                        'Name: Syncit Technolabs Private Limited Phone...',
                                    ],
                                ],
                                [
                                    'title' => 'March 2024 Ajath Leads',
                                    'items' => [
                                        'Name: krishna chahal Phone: 7805000005 Em...',
                                        'Name: Master tea Phone: 06296904211 Email: ...',
                                        'Name: Sajid Malik Phone: 9837313100 Email: 1...',
                                    ],
                                ],
                            ];
                        @endphp
                        @foreach($p5TodoGroups as $tg)
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-1.5 text-[12.5px] font-bold text-[#1d2d35]">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" fill="#d9efe0" stroke="#199849" stroke-width="1.5"/>
                                        <path d="M12 12 L12 2 A10 10 0 1 1 3.5 17 Z" fill="#199849"/>
                                    </svg>
                                    <span>{{ $tg['title'] }}</span>
                                </div>
                                @foreach($tg['items'] as $tItem)
                                    <div class="flex items-center gap-1.5 text-[11px] text-[#1d2d35]">
                                        <span class="w-3 h-3 rounded-[2.5px] border border-[#b8b4ac] inline-block flex-shrink-0"></span>
                                        <span class="truncate">{{ $tItem }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    @elseif($project->id == 4)
                    <div class="space-y-1.5 pr-16">
                        <div class="flex items-center gap-1.5 text-[13px] font-bold text-[#1d2d35]">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#199849] flex-shrink-0"></span>
                            <span>Birthday List</span>
                        </div>
                        @for($i = 0; $i < 3; $i++)
                        <div class="flex items-center gap-1.5 text-[12px] text-[#6d6a65]">
                            <span class="w-3.5 h-3.5 rounded-[3px] border border-[#199849]/60 text-[#199849] inline-flex items-center justify-center text-[9px] leading-none">✓</span>
                            <span>Deepti Sharma</span>
                        </div>
                        @endfor
                        <div class="flex items-center gap-1.5 text-[12px] text-[#6d6a65]">
                            <span class="w-3.5 h-3.5 rounded-[3px] border border-[#199849]/60 text-[#199849] inline-flex items-center justify-center text-[9px] leading-none">✓</span>
                            <span>+ 28 more...</span>
                        </div>
                    </div>
                    @elseif($project->id == 26)
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2 text-[13px] font-bold text-[#1d2d35]">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" fill="#e5e2dc"/>
                                <path d="M12 12 L12 2 A10 10 0 1 1 2.49 15.09 Z" fill="#199849"/>
                            </svg>
                            <span>Dependency Checklist</span>
                        </div>
                        @foreach($tasks->take(3) as $t)
                        <div class="flex items-center gap-1.5 text-[12px] text-[#1d2d35]">
                            <span class="w-3.5 h-3.5 rounded-[3px] border border-[#8c8881] inline-block flex-shrink-0"></span>
                            <span class="truncate">{{ $t->title }}</span>
                        </div>
                        @endforeach
                    </div>
                    @elseif($tasks->isNotEmpty())
                    <div class="space-y-1.5 pr-16">
                        <div class="flex items-center gap-1.5 text-[13px] font-bold text-[#1d2d35]">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#199849] flex-shrink-0"></span>
                            <span>{{ $tasks->first()->list_name ?: 'To-dos' }}</span>
                        </div>
                        @foreach($tasks->take(5) as $t)
                        <div class="flex items-center gap-1.5 text-[12px] text-[#1d2d35]">
                            @if($t->status === 'DONE')
                            <span class="w-3.5 h-3.5 rounded-[3px] border border-[#199849]/60 text-[#199849] inline-flex items-center justify-center text-[9px] leading-none flex-shrink-0">✓</span>
                            @else
                            <span class="w-3.5 h-3.5 rounded-[3px] border border-[#8c8881] inline-block flex-shrink-0"></span>
                            @endif
                            <span class="truncate">{{ $t->title }}</span>
                        </div>
                        @endforeach
                        @if($totalTasks > 5)
                        <div class="flex items-center gap-1.5 text-[12px] text-[#6d6a65]">
                            <span>+ {{ $totalTasks - 5 }} more...</span>
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="h-full flex items-center justify-center text-[13px] text-[#6d6a65]">
                        Make lists, assign tasks, get stuff done
                    </div>
                    @endif
                </a>
            </div>

            <!-- 5. Schedule -->
            <div data-tool-id="schedule" class="bc-tool-card flex flex-col group cursor-grab active:cursor-grabbing select-none">
                <div class="flex items-center justify-between mb-1.5 min-h-[24px]">
                    <a href="{{ route('projects.schedule', $project->id) }}" class="bc-tool-card-title text-[17px] font-bold text-[#a62c21] hover:underline tracking-[-0.015em]">
                        Schedule
                    </a>
                </div>

                @if($project->id == 26)
                <a href="{{ route('projects.schedule', $project->id) }}?new_event=1" class="bc-tool-card-box h-[316px] rounded-[8px] border border-[#e5e2dc] group-hover:border-[#7e7a73] bg-white p-4 relative flex items-center justify-center overflow-hidden transition-colors">
                    <!-- Hand-drawn Calendar Grid + 2 Event Rows Illustration (Matches media_1793335481241_cb1130e5.jpg & media_1793335486725_22754d1f.jpg) -->
                    <svg class="w-52 h-56 text-[#eceae4] group-hover:blur-[2px] group-hover:opacity-45 transition-all" viewBox="0 0 210 220" fill="none">
                        <!-- Top scribble header -->
                        <path d="M78 22 L86 10 L82 24 L92 11 L88 24 L98 12 L94 24 L104 12 L100 24 L110 12 L106 24 L116 12" stroke="currentColor" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <!-- 6x4 Calendar Grid -->
                        <path d="M22 38 L184 36 L182 128 L20 132 Z" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/>
                        <line x1="21" y1="61" x2="183" y2="59" stroke="currentColor" stroke-width="3.5"/>
                        <line x1="21" y1="84" x2="183" y2="82" stroke="currentColor" stroke-width="3.5"/>
                        <line x1="20" y1="107" x2="182" y2="105" stroke="currentColor" stroke-width="3.5"/>
                        <line x1="48" y1="38" x2="46" y2="131" stroke="currentColor" stroke-width="3.5"/>
                        <line x1="75" y1="37" x2="73" y2="130" stroke="currentColor" stroke-width="3.5"/>
                        <line x1="102" y1="37" x2="100" y2="130" stroke="currentColor" stroke-width="3.5"/>
                        <line x1="129" y1="37" x2="127" y2="129" stroke="currentColor" stroke-width="3.5"/>
                        <line x1="156" y1="36" x2="154" y2="129" stroke="currentColor" stroke-width="3.5"/>
                        <!-- Event row 1 below calendar -->
                        <rect x="34" y="146" width="28" height="22" rx="4" stroke="currentColor" stroke-width="4"/>
                        <line x1="34" y1="153" x2="62" y2="153" stroke="currentColor" stroke-width="3.5"/>
                        <path d="M74 156 L82 146 L78 158 L90 147 L86 158 L98 148 L94 158 L106 148" stroke="currentColor" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <!-- Event row 2 below calendar -->
                        <rect x="34" y="180" width="28" height="22" rx="4" stroke="currentColor" stroke-width="4"/>
                        <line x1="34" y1="187" x2="62" y2="187" stroke="currentColor" stroke-width="3.5"/>
                        <path d="M74 190 L82 180 L78 192 L90 181 L86 192 L98 182 L94 192 L106 182 L102 192 L114 182" stroke="currentColor" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <!-- Hover Overlay (Matches media_1793335486725_22754d1f.jpg) -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center opacity-0 group-hover:opacity-100 transition-opacity bg-white/55">
                        <p class="text-[13.5px] text-[#1d2d35] leading-[1.35] max-w-[215px] font-normal">
                            Put meetings, milestones, deadlines, and other important events on a shared calendar.
                        </p>
                        <span class="mt-3 px-3.5 py-1.5 rounded-full bg-[#256fb4] text-white text-[12.5px] font-semibold">
                            Add your first event
                        </span>
                    </div>
                    <span class="absolute bottom-2.5 right-2.5 text-[#8c8881]">
                        <i data-lucide="archive" class="w-3.5 h-3.5"></i>
                    </span>
                </a>
                @else
                <a href="{{ route('projects.schedule', $project->id) }}?new_event=1"
                   x-data="{
                       localEvents: [],
                       init() {
                           try { this.localEvents = JSON.parse(localStorage.getItem('bc_schedule_events_{{ $project->id }}') || '[]'); } catch (e) {}
                       }
                   }"
                   class="bc-tool-card-box h-[316px] rounded-[8px] border border-[#e5e2dc] group-hover:border-[#7e7a73] bg-white p-4 block transition-colors">
                    <p class="text-[13px] font-bold text-[#1d2d35] text-center mb-2">October 2026</p>

                    <div class="grid grid-cols-7 text-center text-[9.5px] text-[#6d6a65] pb-1">
                        <span>MON</span><span>TUE</span><span>WED</span><span>THU</span><span>FRI</span><span>SAT</span><span>SUN</span>
                    </div>

                    <div class="grid grid-cols-7 text-center text-[11px] font-bold text-[#1d2d35] border-t border-l border-[#f0ede8]">
                        @php
                            $calCells = [
                                ['d' => 28, 'muted' => true], ['d' => 29, 'muted' => true], ['d' => 30, 'muted' => true], ['d' => 1], ['d' => 2], ['d' => 3], ['d' => 4],
                                ['d' => 5], ['d' => 6], ['d' => 7], ['d' => 8], ['d' => 9], ['d' => 10], ['d' => 11],
                                ['d' => 12], ['d' => 13], ['d' => 14], ['d' => 15], ['d' => 16], ['d' => 17], ['d' => 18],
                                ['d' => 19], ['d' => 20], ['d' => 21], ['d' => 22], ['d' => 23], ['d' => 24], ['d' => 25],
                                ['d' => 26], ['d' => 27], ['d' => 28], ['d' => 29, 'today' => true], ['d' => 30], ['d' => 31], ['d' => 1, 'muted' => true],
                            ];
                        @endphp
                        @foreach($calCells as $c)
                            <span class="py-1.5 border-r border-b border-[#f0ede8] {{ !empty($c['muted']) ? 'text-[#d8d4cc] font-normal' : '' }} {{ !empty($c['today']) ? 'bg-[#f7f2e7]' : '' }}">
                                {{ $c['d'] }}
                            </span>
                        @endforeach
                    </div>

                    <div x-show="localEvents.length === 0" class="text-[12px] text-[#6d6a65] text-center mt-3.5 font-normal">
                        No upcoming events
                    </div>
                    <div x-show="localEvents.length > 0" x-cloak class="mt-2.5 space-y-1">
                        <template x-for="ev in localEvents.slice(0, 2)" :key="ev.id">
                            <div class="text-[11.5px] text-[#1d2d35] truncate flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#1b6ac9] flex-shrink-0"></span>
                                <span class="font-bold truncate" x-text="ev.title"></span>
                                <span class="text-[#6d6a65] flex-shrink-0" x-text="ev.startTime"></span>
                            </div>
                        </template>
                    </div>
                </a>
                @endif
            </div>

            <!-- 6. Docs & Files -->
            <div data-tool-id="docs" class="bc-tool-card flex flex-col group cursor-grab active:cursor-grabbing select-none">
                <div class="flex items-center justify-between mb-1.5 min-h-[24px]">
                    <a href="{{ route('projects.docs', $project->id) }}" class="bc-tool-card-title text-[17px] font-bold text-[#a62c21] hover:underline tracking-[-0.015em]">
                        Docs & Files
                    </a>
                    <a href="{{ route('projects.docs', $project->id) }}" class="px-2.5 py-0.5 text-[11.5px] font-normal text-[#1d2d35] bg-white hover:bg-slate-50 border border-[#d8d4cc] rounded-full {{ $project->id == 26 ? 'opacity-0 group-hover:opacity-100' : '' }} transition-opacity">
                        Add a document
                    </a>
                </div>

                @if($project->id == 5)
                <a href="{{ route('projects.docs', $project->id) }}" class="bc-tool-card-box h-[316px] rounded-[8px] border border-[#e5e2dc] group-hover:border-[#7e7a73] bg-white p-3.5 space-y-3.5 overflow-hidden block transition-colors">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-6 text-[#d6d2c9] flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5z"/>
                            <rect x="8" y="12" width="8" height="1.4" rx="0.7" fill="#9a968e"/>
                            <rect x="8" y="15" width="8" height="1.4" rx="0.7" fill="#9a968e"/>
                            <rect x="8" y="18" width="6" height="1.4" rx="0.7" fill="#9a968e"/>
                        </svg>
                        <div class="min-w-0 flex-1 leading-[1.2]">
                            <p class="text-[13px] font-bold text-[#1d2d35] truncate">+919810935250-2310201131.amr</p>
                            <p class="text-[11.5px] text-[#6d6a65] font-normal">Manjot Singh • Oct 20, 2023 • 801 KB</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-6 flex-shrink-0" viewBox="0 0 54 68" fill="none">
                            <path d="M0 4C0 1.79086 1.79086 0 4 0H38L54 16V64C54 66.2091 52.2091 68 50 68H4C1.79086 68 0 66.2091 0 64V4Z" fill="#bce3fa"/>
                            <path d="M38 0L54 16H42C39.7909 16 38 14.2091 38 12V0Z" fill="#89c9f4"/>
                            <rect x="9" y="21" width="36" height="2.6" rx="1.3" fill="#5aa9e6"/>
                            <rect x="9" y="29" width="36" height="2.6" rx="1.3" fill="#5aa9e6"/>
                            <rect x="9" y="37" width="36" height="2.6" rx="1.3" fill="#5aa9e6"/>
                            <rect x="7" y="48" width="40" height="14" rx="3" fill="#1d74d6"/>
                            <text x="27" y="58.2" text-anchor="middle" fill="#ffffff" font-family="system-ui, -apple-system, sans-serif" font-size="10.5" font-weight="800" letter-spacing="0.4">DOCX</text>
                        </svg>
                        <div class="min-w-0 flex-1 leading-[1.2]">
                            <p class="text-[13px] font-bold text-[#1d2d35] truncate">Flutter App Business Proposal 2.0.1.docx</p>
                            <p class="text-[11.5px] text-[#6d6a65] font-normal">Manjot Singh • Jan 30, 2023 • 60.9 KB</p>
                        </div>
                    </div>
                </a>
                @elseif($project->id == 4)
                <a href="{{ route('projects.docs', $project->id) }}" class="bc-tool-card-box h-[316px] rounded-[8px] border border-[#9e9a91] bg-[#f9f8f5] p-3.5 space-y-2.5 overflow-hidden block">
                    @php
                        $folderPreviews = [
                            ['name' => 'Diwali Celebration 2023', 'count' => '30 items'],
                            ['name' => 'Inauguration Day 2023 kirti nagar', 'count' => '343 items'],
                            ['name' => 'Independence day 2023', 'count' => '13 items'],
                            ['name' => 'Janmaashtami 2022 team outing', 'count' => '17 items'],
                            ['name' => '4TH Foundation Day Images', 'count' => '514 items'],
                            ['name' => 'Independence Day Celebration 2022', 'count' => '79 items'],
                        ];
                    @endphp
                    @foreach($folderPreviews as $f)
                    <div class="flex items-center gap-2.5">
                        <svg class="w-6 h-5 text-[#c7c3bc] fill-[#d8d4cc] flex-shrink-0" viewBox="0 0 24 20">
                            <path d="M2 3a2 2 0 012-2h5l2 2h9a2 2 0 012 2v11a2 2 0 01-2 2H4a2 2 0 01-2-2V3z" stroke="currentColor" stroke-width="1.2"/>
                        </svg>
                        <div class="min-w-0 flex-1 leading-[1.2]">
                            <p class="text-[13px] font-bold text-[#1d2d35] truncate">{{ $f['name'] }}</p>
                            <p class="text-[11.5px] text-[#6d6a65] font-normal">{{ $f['count'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </a>
                @elseif($project->id == 26)
                <a href="{{ route('projects.docs', $project->id) }}" class="bc-tool-card-box h-[316px] rounded-[8px] border border-[#e5e2dc] group-hover:border-[#7e7a73] bg-white group-hover:bg-[#faf8f5] p-3.5 space-y-3 overflow-hidden block transition-colors">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-6 h-6 text-[#d6d2c9] flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5z"/>
                        </svg>
                        <div class="min-w-0 flex-1 leading-[1.2]">
                            <p class="text-[13px] font-bold text-[#1d2d35] truncate">WEB PROPOSAL .pdf</p>
                            <p class="text-[11.5px] text-[#6d6a65] font-normal">Nirdesh Verma • Aug 12, 2025 • 572 KB</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <svg class="w-6 h-5 text-[#c7c3bc] fill-[#d8d4cc] flex-shrink-0" viewBox="0 0 24 20">
                            <path d="M2 3a2 2 0 012-2h5l2 2h9a2 2 0 012 2v11a2 2 0 01-2 2H4a2 2 0 01-2-2V3z" stroke="currentColor" stroke-width="1.2"/>
                        </svg>
                        <div class="min-w-0 flex-1 leading-[1.2]">
                            <p class="text-[13px] font-bold text-[#1d2d35] truncate">Configuration Management (CM)</p>
                            <p class="text-[11.5px] text-[#6d6a65] font-normal">0 items</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <svg class="w-6 h-5 text-[#c7c3bc] fill-[#d8d4cc] flex-shrink-0" viewBox="0 0 24 20">
                            <path d="M2 3a2 2 0 012-2h5l2 2h9a2 2 0 012 2v11a2 2 0 01-2 2H4a2 2 0 01-2-2V3z" stroke="currentColor" stroke-width="1.2"/>
                        </svg>
                        <div class="min-w-0 flex-1 leading-[1.2]">
                            <p class="text-[13px] font-bold text-[#1d2d35] truncate">Project Planning (PP)</p>
                            <p class="text-[11.5px] text-[#6d6a65] font-normal">2 items</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <svg class="w-6 h-5 text-[#c7c3bc] fill-[#d8d4cc] flex-shrink-0" viewBox="0 0 24 20">
                            <path d="M2 3a2 2 0 012-2h5l2 2h9a2 2 0 012 2v11a2 2 0 01-2 2H4a2 2 0 01-2-2V3z" stroke="currentColor" stroke-width="1.2"/>
                        </svg>
                        <div class="min-w-0 flex-1 leading-[1.2]">
                            <p class="text-[13px] font-bold text-[#1d2d35] truncate">Project Monitoring and Control (PMC)</p>
                            <p class="text-[11.5px] text-[#6d6a65] font-normal">5 items</p>
                        </div>
                    </div>
                </a>
                @elseif($docs->isNotEmpty())
                <a href="{{ route('projects.docs', $project->id) }}" class="bc-tool-card-box h-[316px] rounded-[8px] border border-[#e5e2dc] bg-white p-3.5 space-y-2.5 overflow-hidden block hover:border-[#d8d4cc] transition-colors">
                    @foreach($docs->take(6) as $doc)
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-[#6d6a65] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div class="min-w-0 flex-1 leading-[1.2]">
                            <p class="text-[13px] font-bold text-[#1d2d35] truncate">{{ $doc->title }}</p>
                            <p class="text-[11.5px] text-[#6d6a65] font-normal">{{ $doc->author->name ?? 'Team Member' }} · {{ $doc->created_at ? $doc->created_at->format('M j') : '' }}</p>
                        </div>
                    </div>
                    @endforeach
                </a>
                @else
                <a href="{{ route('projects.docs', $project->id) }}" class="bc-tool-card-box h-[316px] rounded-[8px] border border-[#e5e2dc] bg-white p-4 flex items-center justify-center text-[13px] text-[#6d6a65] hover:border-[#d8d4cc] transition-colors">
                    Share docs, files, images, and spreadsheets
                </a>
                @endif
            </div>

            @if($project->id == 5)
            <!-- 7. Empty Card Table (Row 3, Col 1 for Project 5 — Matches media_1793345788794_f1b3ef7f.png) -->
            <div data-tool-id="card_table_empty" class="bc-tool-card flex flex-col group cursor-grab active:cursor-grabbing select-none">
                <div class="flex items-center justify-between mb-1.5 min-h-[24px]">
                    <a href="{{ route('projects.kanban', $project->id) }}" class="bc-tool-card-title text-[17px] font-bold text-[#a62c21] hover:underline tracking-[-0.015em]">
                        Card Table
                    </a>
                </div>

                <a href="{{ route('projects.kanban', $project->id) }}" class="bc-tool-card-box h-[316px] rounded-[8px] border border-[#e5e2dc] group-hover:border-[#7e7a73] bg-white p-4 relative flex items-center justify-center overflow-hidden transition-colors">
                    <svg class="w-60 h-64 text-[#eceae4]" viewBox="0 0 240 250" fill="none">
                        <!-- Top Triage Bar Illustration -->
                        <rect x="8" y="8" width="224" height="32" rx="14" fill="#f5f4f0"/>
                        <path d="M22 25 L30 16 L28 26 L38 17 L36 26 L46 17 L44 26 L54 18 L52 26 L62 18 L60 26 L70 18 L68 26 L78 18" stroke="#eceae4" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                        <!-- 6 Vertical Columns Illustration -->
                        @foreach([8, 46, 84, 122, 160, 198] as $cIdx => $cx)
                            <rect x="{{ $cx }}" y="48" width="32" height="194" rx="14" fill="#f5f4f0"/>
                            <path d="M{{ $cx + 16 }} 64 L{{ $cx + 22 }} 72 L{{ $cx + 14 }} 70 L{{ $cx + 22 }} 80 L{{ $cx + 14 }} 78 L{{ $cx + 22 }} 88 L{{ $cx + 14 }} 86 L{{ $cx + 22 }} 96 L{{ $cx + 14 }} 94 L{{ $cx + 22 }} 104 L{{ $cx + 14 }} 102 L{{ $cx + 22 }} 112" stroke="#eceae4" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                        @endforeach
                    </svg>
                    <span class="absolute bottom-2.5 right-2.5 text-[#8c8881]">
                        <i data-lucide="archive" class="w-3.5 h-3.5"></i>
                    </span>
                </a>
            </div>

            <!-- 8. Populated 9-Column Lead Pipeline Card Table (Row 3, Col 2 for Project 5 — Matches media_1793345788794_f1b3ef7f.png) -->
            <div data-tool-id="card_table" class="bc-tool-card flex flex-col group cursor-grab active:cursor-grabbing select-none">
                <div class="flex items-center justify-between mb-1.5 min-h-[24px]">
                    <a href="{{ route('projects.kanban', $project->id) }}" class="bc-tool-card-title text-[17px] font-bold text-[#a62c21] hover:underline tracking-[-0.015em]">
                        Card Table
                    </a>
                </div>

                <a href="{{ route('projects.kanban', $project->id) }}" class="bc-tool-card-box h-[316px] rounded-[8px] border border-[#e5e2dc] bg-white overflow-hidden flex flex-col hover:border-[#d8d4cc] transition-colors">
                    <div class="px-3.5 py-2.5 bg-[#f5f3ef] border-b border-[#e5e2dc] text-[10px] font-bold tracking-[0.04em] text-[#1d2d35] uppercase">
                        TRIAGE <span class="text-[#8c8881] font-normal">(0)</span>
                    </div>
                    <div class="flex-1 grid grid-cols-9 gap-[2px] bg-white px-0.5 pb-0.5 pt-0.5">
                        @php
                            $p5MiniCols = [
                                ['count' => 1,  'label' => 'NEW',                     'bar' => 'bg-[#9b72f2]', 'bg' => 'bg-[#f6f4fb]'],
                                ['count' => 24, 'label' => 'NO',                      'bar' => 'bg-[#9ca3af]', 'bg' => 'bg-[#f6f6f6]'],
                                ['count' => 68, 'label' => 'CONTACTED',               'bar' => 'bg-[#f59e0b]', 'bg' => 'bg-[#fef7ec]'],
                                ['count' => 11, 'label' => 'REQUIREMENT DISCUSSION',  'bar' => 'bg-[#9ca3af]', 'bg' => 'bg-[#f6f6f6]'],
                                ['count' => 3,  'label' => 'ESTIMATION DISCUSSION',   'bar' => 'bg-[#9ca3af]', 'bg' => 'bg-[#f6f6f6]'],
                                ['count' => 22, 'label' => 'BUSINESS PROPOSAL SENT',  'bar' => 'bg-[#9ca3af]', 'bg' => 'bg-[#f6f6f6]'],
                                ['count' => 1,  'label' => 'FEEDBACK FROM CLIENT',    'bar' => 'bg-[#9ca3af]', 'bg' => 'bg-[#f6f6f6]'],
                                ['count' => 0,  'label' => 'ADVANCED INVOICE SENT',   'bar' => 'bg-[#9ca3af]', 'bg' => 'bg-[#f6f6f6]'],
                                ['count' => 0,  'label' => 'INTERNAL KICK-OFF',       'bar' => 'bg-[#9ca3af]', 'bg' => 'bg-[#f6f6f6]'],
                            ];
                        @endphp
                        @foreach($p5MiniCols as $mc)
                            <div class="flex flex-col">
                                <div class="h-[3px] {{ $mc['bar'] }} rounded-t-sm"></div>
                                <div class="flex-1 {{ $mc['bg'] }} flex flex-col items-center pt-1.5 overflow-hidden">
                                    <span class="text-[8.5px] text-[#8c8881] mb-1">({{ $mc['count'] }})</span>
                                    <span class="text-[8.5px] font-bold text-[#1d2d35] tracking-[0.03em] uppercase whitespace-nowrap" style="writing-mode: vertical-rl;">{{ $mc['label'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </a>
            </div>

            <!-- Inline Dashed [ + ] / ADD A TOOL Card next to Card Table (Matches media_1793346464790_eb5055ca.jpg 1:1) -->
            <div id="project-tools-add-slot" class="flex flex-col pt-[30px] relative" x-data="{ toolOpen: false }">
                <button type="button"
                        x-show="!toolOpen"
                        @click="toolOpen = true"
                        class="w-[38px] h-[38px] border border-dashed border-[#bfbcb4] hover:border-[#8c8881] bg-white rounded-[6px] flex items-center justify-center text-[#6d6a65] hover:text-[#1d2d35] text-[22px] font-light transition-colors cursor-pointer"
                        title="Add a tool">
                    +
                </button>
                <div x-show="toolOpen"
                     x-cloak
                     @click.outside="toolOpen = false"
                     class="h-[316px] rounded-[8px] border border-[#e5e2dc] bg-white overflow-y-auto px-5 py-4 shadow-xs">
                    <div class="text-[11px] font-bold tracking-[0.04em] text-[#a62c21] uppercase mb-3">ADD A TOOL</div>
                    <div class="space-y-3">
                        <a href="{{ route('projects.discussions', $project->id) }}" class="flex items-center gap-3 group/tool">
                            <span class="w-[36px] h-[36px] rounded-full bg-[#1d2d35] text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M4 4h16v12H5.17L4 17.17V4z"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13.5px] font-bold text-[#1d2d35] group-hover/tool:text-[#1b6ac9] leading-tight">Message Board</div>
                                <div class="text-[11.5px] text-[#6d6a65] leading-snug">Post announcements, pitch ideas, progress updates, etc.</div>
                            </div>
                        </a>
                        <a href="{{ route('projects.tasks', $project->id) }}" class="flex items-center gap-3 group/tool">
                            <span class="w-[36px] h-[36px] rounded-full bg-[#1d2d35] text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13.5px] font-bold text-[#1d2d35] group-hover/tool:text-[#1b6ac9] leading-tight">To-dos</div>
                                <div class="text-[11.5px] text-[#6d6a65] leading-snug">Make lists, assign tasks, get stuff done</div>
                            </div>
                        </a>
                        <a href="{{ route('projects.docs', $project->id) }}" class="flex items-center gap-3 group/tool">
                            <span class="w-[36px] h-[36px] rounded-full bg-[#1d2d35] text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M10 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13.5px] font-bold text-[#1d2d35] group-hover/tool:text-[#1b6ac9] leading-tight">Docs, Files &amp; Images</div>
                                <div class="text-[11.5px] text-[#6d6a65] leading-snug">Store docs, files, PDFs, images, etc.</div>
                            </div>
                        </a>
                        <a href="{{ route('projects.schedule', $project->id) }}" class="flex items-center gap-3 group/tool">
                            <span class="w-[36px] h-[36px] rounded-full bg-[#1d2d35] text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10z"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13.5px] font-bold text-[#1d2d35] group-hover/tool:text-[#1b6ac9] leading-tight">Calendar</div>
                                <div class="text-[11.5px] text-[#6d6a65] leading-snug">Add events, milestones, deadlines, etc.</div>
                            </div>
                        </a>
                        <a href="{{ route('projects.chat', $project->id) }}" class="flex items-center gap-3 group/tool">
                            <span class="w-[36px] h-[36px] rounded-full bg-[#1d2d35] text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13.5px] font-bold text-[#1d2d35] group-hover/tool:text-[#1b6ac9] leading-tight">Chat</div>
                                <div class="text-[11.5px] text-[#6d6a65] leading-snug">Add a chat room to the project</div>
                            </div>
                        </a>
                        <a href="{{ route('projects.kanban', $project->id) }}" class="flex items-center gap-3 group/tool">
                            <span class="w-[36px] h-[36px] rounded-full bg-[#1d2d35] text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><rect x="4" y="4" width="7" height="16" rx="1.5"/><rect x="13" y="4" width="7" height="16" rx="1.5"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13.5px] font-bold text-[#1d2d35] group-hover/tool:text-[#1b6ac9] leading-tight">Card Table</div>
                                <div class="text-[11.5px] text-[#6d6a65] leading-snug">Track work with cards and columns</div>
                            </div>
                            <span class="w-5 h-4 rounded-[3px] border border-dashed border-[#6d6a65] flex-shrink-0"></span>
                        </a>
                        <a href="{{ route('projects.checkins', $project->id) }}" class="flex items-center gap-3 group/tool">
                            <span class="w-[36px] h-[36px] rounded-full bg-[#1d2d35] text-white flex items-center justify-center flex-shrink-0 font-bold text-[15px]">?</span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13.5px] font-bold text-[#1d2d35] group-hover/tool:text-[#1b6ac9] leading-tight">Automatic Check-ins</div>
                                <div class="text-[11.5px] text-[#6d6a65] leading-snug">Automatically ask recurring questions</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            @else
            <!-- 7. Card Table + Inline ADD A TOOL Card (3rd Row — Matches media_1793346464790_eb5055ca.jpg 1:1) -->
            <div data-tool-id="card_table"
                 x-data="{
                    hasCardTable: {{ $project->id == 26 ? 'true' : "localStorage.getItem('bc_added_card_table_" . $project->id . "') !== '0'" }}
                 }"
                 x-show="hasCardTable"
                 @bc-add-card-table.window="hasCardTable = true"
                 class="bc-tool-card flex flex-col group cursor-grab active:cursor-grabbing select-none">
                <div class="flex items-center justify-between mb-1.5 min-h-[24px]">
                    <a href="{{ route('projects.kanban', $project->id) }}" class="bc-tool-card-title text-[17px] font-bold text-[#a62c21] hover:underline tracking-[-0.015em]">
                        Card Table
                    </a>
                </div>

                <a href="{{ route('projects.kanban', $project->id) }}" class="bc-tool-card-box h-[316px] rounded-[8px] border border-[#e5e2dc] bg-white overflow-hidden flex flex-col hover:border-[#d8d4cc] transition-colors">
                    <div class="px-3.5 py-2.5 bg-[#f5f3ef] border-b border-[#e5e2dc] text-[10px] font-bold tracking-[0.04em] text-[#1d2d35] uppercase">
                        TRIAGE <span class="text-[#8c8881] font-normal">({{ $project->id == 26 ? 11 : max(11, $project->tasks->where('status', '!=', 'DONE')->count()) }})</span>
                    </div>
                    <div class="flex-1 grid grid-cols-4 gap-[2px] bg-white px-0.5 pb-0.5 pt-0.5">
                        <!-- Figuring it out (0) -->
                        <div class="flex flex-col">
                            <div class="h-[3px] bg-[#9b72f2] rounded-t-sm"></div>
                            <div class="flex-1 bg-[#f6f4fb] flex flex-col items-center pt-2">
                                <span class="text-[10px] text-[#8c8881] mb-1">(0)</span>
                                <span class="text-[9.5px] font-bold text-[#1d2d35] tracking-[0.04em] uppercase" style="writing-mode: vertical-rl;">FIGURING IT OUT</span>
                            </div>
                        </div>
                        <!-- In progress (0) -->
                        <div class="flex flex-col">
                            <div class="h-[3px] bg-[#f59e0b] rounded-t-sm"></div>
                            <div class="flex-1 bg-[#fef7ec] flex flex-col items-center pt-2">
                                <span class="text-[10px] text-[#8c8881] mb-1">(0)</span>
                                <span class="text-[9.5px] font-bold text-[#1d2d35] tracking-[0.04em] uppercase" style="writing-mode: vertical-rl;">IN PROGRESS</span>
                            </div>
                        </div>
                        <!-- Completed (0) -->
                        <div class="flex flex-col">
                            <div class="h-[3px] bg-[#9ca3af] rounded-t-sm"></div>
                            <div class="flex-1 bg-[#f6f6f6] flex flex-col items-center pt-2">
                                <span class="text-[10px] text-[#8c8881] mb-1">(0)</span>
                                <span class="text-[9.5px] font-bold text-[#1d2d35] tracking-[0.04em] uppercase" style="writing-mode: vertical-rl;">COMPLETED</span>
                            </div>
                        </div>
                        <!-- Done (4) -->
                        <div class="flex flex-col">
                            <div class="h-[3px] bg-[#22c55e] rounded-t-sm"></div>
                            <div class="flex-1 bg-[#eff9f1] flex flex-col items-center pt-2">
                                <span class="text-[10px] text-[#8c8881] mb-1">({{ $project->id == 26 ? 4 : max(4, $project->tasks->where('status', 'DONE')->count()) }})</span>
                                <span class="text-[9.5px] font-bold text-[#1d2d35] tracking-[0.04em] uppercase" style="writing-mode: vertical-rl;">DONE</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Inline Dashed [ + ] / ADD A TOOL Card next to Card Table (Matches media_1793346464790_eb5055ca.jpg 1:1) -->
            <div id="project-tools-add-slot" class="flex flex-col pt-[30px] relative" x-data="{ toolOpen: false }">
                <button type="button"
                        x-show="!toolOpen"
                        @click="toolOpen = true"
                        class="w-[38px] h-[38px] border border-dashed border-[#bfbcb4] hover:border-[#8c8881] bg-white rounded-[6px] flex items-center justify-center text-[#6d6a65] hover:text-[#1d2d35] text-[22px] font-light transition-colors cursor-pointer"
                        title="Add a tool">
                    +
                </button>
                <div x-show="toolOpen"
                     x-cloak
                     @click.outside="toolOpen = false"
                     class="h-[316px] rounded-[8px] border border-[#e5e2dc] bg-white overflow-y-auto px-5 py-4 shadow-xs">
                    <div class="text-[11px] font-bold tracking-[0.04em] text-[#a62c21] uppercase mb-3">ADD A TOOL</div>
                    <div class="space-y-3">
                        <a href="{{ route('projects.discussions', $project->id) }}" class="flex items-center gap-3 group/tool">
                            <span class="w-[36px] h-[36px] rounded-full bg-[#1d2d35] text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M4 4h16v12H5.17L4 17.17V4z"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13.5px] font-bold text-[#1d2d35] group-hover/tool:text-[#1b6ac9] leading-tight">Message Board</div>
                                <div class="text-[11.5px] text-[#6d6a65] leading-snug">Post announcements, pitch ideas, progress updates, etc.</div>
                            </div>
                        </a>
                        <a href="{{ route('projects.tasks', $project->id) }}" class="flex items-center gap-3 group/tool">
                            <span class="w-[36px] h-[36px] rounded-full bg-[#1d2d35] text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13.5px] font-bold text-[#1d2d35] group-hover/tool:text-[#1b6ac9] leading-tight">To-dos</div>
                                <div class="text-[11.5px] text-[#6d6a65] leading-snug">Make lists, assign tasks, get stuff done</div>
                            </div>
                        </a>
                        <a href="{{ route('projects.docs', $project->id) }}" class="flex items-center gap-3 group/tool">
                            <span class="w-[36px] h-[36px] rounded-full bg-[#1d2d35] text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M10 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13.5px] font-bold text-[#1d2d35] group-hover/tool:text-[#1b6ac9] leading-tight">Docs, Files &amp; Images</div>
                                <div class="text-[11.5px] text-[#6d6a65] leading-snug">Store docs, files, PDFs, images, etc.</div>
                            </div>
                        </a>
                        <a href="{{ route('projects.schedule', $project->id) }}" class="flex items-center gap-3 group/tool">
                            <span class="w-[36px] h-[36px] rounded-full bg-[#1d2d35] text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10z"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13.5px] font-bold text-[#1d2d35] group-hover/tool:text-[#1b6ac9] leading-tight">Calendar</div>
                                <div class="text-[11.5px] text-[#6d6a65] leading-snug">Add events, milestones, deadlines, etc.</div>
                            </div>
                        </a>
                        <a href="{{ route('projects.chat', $project->id) }}" class="flex items-center gap-3 group/tool">
                            <span class="w-[36px] h-[36px] rounded-full bg-[#1d2d35] text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13.5px] font-bold text-[#1d2d35] group-hover/tool:text-[#1b6ac9] leading-tight">Chat</div>
                                <div class="text-[11.5px] text-[#6d6a65] leading-snug">Add a chat room to the project</div>
                            </div>
                        </a>
                        <button type="button"
                                @click="localStorage.setItem('bc_added_card_table_{{ $project->id }}', '1'); window.dispatchEvent(new CustomEvent('bc-add-card-table')); toolOpen = false"
                                class="w-full text-left flex items-center gap-3 group/tool cursor-pointer">
                            <span class="w-[36px] h-[36px] rounded-full bg-[#1d2d35] text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><rect x="4" y="4" width="7" height="16" rx="1.5"/><rect x="13" y="4" width="7" height="16" rx="1.5"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13.5px] font-bold text-[#1d2d35] group-hover/tool:text-[#1b6ac9] leading-tight">Card Table</div>
                                <div class="text-[11.5px] text-[#6d6a65] leading-snug">Track work with cards and columns</div>
                            </div>
                            <span class="w-5 h-4 rounded-[3px] border border-dashed border-[#6d6a65] flex-shrink-0"></span>
                        </button>
                        <a href="{{ route('projects.checkins', $project->id) }}" class="flex items-center gap-3 group/tool">
                            <span class="w-[36px] h-[36px] rounded-full bg-[#1d2d35] text-white flex items-center justify-center flex-shrink-0 font-bold text-[15px]">?</span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[13.5px] font-bold text-[#1d2d35] group-hover/tool:text-[#1b6ac9] leading-tight">Automatic Check-ins</div>
                                <div class="text-[11.5px] text-[#6d6a65] leading-snug">Automatically ask recurring questions</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            @endif

        </div>

        <!-- Full-Width Wavy Zig-Zag Divider Across White Sheet (Matches Screenshot) -->
        <div class="w-full overflow-hidden text-[#d8d4cc] select-none">
            <svg class="w-full h-3 stroke-current fill-none stroke-[1.3]" viewBox="0 0 920 12" preserveAspectRatio="none">
                <path d="M0 6 Q 6 0, 12 6 T 24 6 T 36 6 T 48 6 T 60 6 T 72 6 T 84 6 T 96 6 T 108 6 T 120 6 T 132 6 T 144 6 T 156 6 T 168 6 T 180 6 T 192 6 T 204 6 T 216 6 T 228 6 T 240 6 T 252 6 T 264 6 T 276 6 T 288 6 T 300 6 T 312 6 T 324 6 T 336 6 T 348 6 T 360 6 T 372 6 T 384 6 T 396 6 T 408 6 T 420 6 T 432 6 T 444 6 T 456 6 T 468 6 T 480 6 T 492 6 T 504 6 T 516 6 T 528 6 T 540 6 T 552 6 T 564 6 T 576 6 T 588 6 T 600 6 T 612 6 T 624 6 T 636 6 T 648 6 T 660 6 T 672 6 T 684 6 T 696 6 T 708 6 T 720 6 T 732 6 T 744 6 T 756 6 T 768 6 T 780 6 T 792 6 T 804 6 T 816 6 T 828 6 T 840 6 T 852 6 T 864 6 T 876 6 T 888 6 T 900 6 T 912 6 T 924 6"/>
            </svg>
        </div>

        <!-- ============================================================ -->
        <!-- LATEST ACTIVITY (Exact Match to media_1793335531018_1589f4d3.jpg) -->
        <!-- ============================================================ -->
        <div id="latest-activity" class="px-7 pt-6 pb-12 space-y-6" x-data="{ activitySearch: '{{ addslashes($activityFilter ?? '') }}' }">
            
            <!-- Left-aligned 'Latest Activity' + Inline 'Filter...' Input -->
            <div class="flex items-center gap-3">
                <h2 class="text-[24px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-none">
                    Latest Activity
                </h2>

                <form method="GET" action="{{ route('projects.show', $project->id) }}#latest-activity" class="relative inline-flex items-center">
                    <input type="text" 
                           name="filter" 
                           x-model="activitySearch"
                           placeholder="Filter..." 
                           :class="activitySearch.trim().length > 0 ? 'bg-[#fdf4d1] border-[#e6d79c] pr-7' : 'bg-white border-[#d8d4cc]'"
                           class="px-2.5 py-1 border rounded-[6px] text-[13px] text-[#1d2d35] placeholder-[#8c8881] w-[130px] transition-all focus:outline-none focus:border-[#1b6ac9]">
                    <button type="button"
                            x-show="activitySearch.trim().length > 0"
                            x-cloak
                            @click="activitySearch = ''; @if($activityFilter) window.location.href = '{{ route('projects.show', $project->id) }}#latest-activity'; @endif"
                            class="w-4 h-4 rounded-full bg-[#b8b199] hover:bg-[#9c957e] text-white text-[10px] font-bold flex items-center justify-center absolute right-2 top-1/2 -translate-y-1/2 cursor-pointer"
                            title="Clear filter">
                        ✕
                    </button>
                </form>
            </div>

            <!-- Timeline Days Container -->
            <div class="space-y-6">
                @if($project->id == 26)
                    <p x-show="!activitySearch.trim()" class="text-[13px] text-[#6d6a65] font-normal">No activity for 6 days</p>
                @endif
                @forelse($daysTimeline as $day)
                    @php
                        $daySearchCorpus = strtolower($day->items->map(fn($it) => $it->author_name . ' ' . $it->action . ' ' . $it->title . ' ' . $it->snippet)->implode(' '));
                    @endphp

                    <div data-day-search="{{ $daySearchCorpus }}"
                         @if($day->is_empty)
                             x-show="!activitySearch.trim()"
                         @else
                             x-show="!activitySearch.trim() || $el.dataset.daySearch.includes(activitySearch.trim().toLowerCase())"
                         @endif>
                        @if($day->is_empty)
                            <!-- Empty Yesterday Row (Outlined Pill + 'No activity' next to it) -->
                            <div class="flex items-center gap-2.5 py-1">
                                <span class="px-2.5 py-1 bg-white border border-[#d8d4cc] text-[#6d6a65] text-[11px] font-bold uppercase tracking-[0.02em] rounded-[4px] select-none">
                                    {{ $day->label }}
                                </span>
                                <span class="text-[13px] text-[#6d6a65] font-normal">No activity</span>
                            </div>
                        @else
                            <!-- Active Day Header Row -->
                            <div class="flex items-center justify-between gap-3">
                                <span class="px-2.5 py-1 bg-[#283c46] text-white text-[11px] font-bold uppercase tracking-[0.02em] rounded-[4px] select-none">
                                    {{ $day->label }}
                                </span>

                                <div class="h-px bg-[#e5e2dc] flex-1"></div>

                                <div class="flex items-center gap-1.5 text-[13px] text-[#1d2d35] font-normal">
                                    <span>{{ $day->active_text }}</span>
                                    @foreach($day->active_badges as $b)
                                        @if(!empty($b->avatar))
                                            <img src="{{ $b->avatar }}" alt="{{ $b->initials }}" class="w-[22px] h-[22px] rounded-full object-cover select-none cursor-pointer">
                                        @else
                                            <span class="w-[22px] h-[22px] rounded-full {{ $b->bg ?? 'bg-[#d946ef]' }} text-white font-bold text-[9.5px] flex items-center justify-center select-none cursor-pointer">
                                                {{ $b->initials }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <!-- Day Items List with Left Vertical Line -->
                            <div class="ml-4 pl-5 border-l border-[#e5e2dc] pt-3 pb-1 space-y-5">
                                @foreach($day->items as $it)
                                @php
                                    $itemSearchCorpus = strtolower($it->author_name . ' ' . $it->action . ' ' . $it->title . ' ' . $it->snippet);
                                @endphp
                                <div class="relative flex items-start gap-2.5"
                                     data-search="{{ $itemSearchCorpus }}"
                                     x-show="!activitySearch.trim() || $el.dataset.search.includes(activitySearch.trim().toLowerCase())">
                                    @if(($it->icon_type ?? 'message') === 'todo_comment')
                                        <!-- Green Checkmark Circle Sitting on the Vertical Line -->
                                        <span class="w-[16px] h-[16px] rounded-full bg-[#22c55e] text-white inline-flex items-center justify-center flex-shrink-0 -ml-[28.5px] mt-1.5 shadow-[0_0_0_3px_#fff]">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </span>
                                    @elseif(($it->icon_type ?? 'message') === 'person_added')
                                        <!-- Green Person Circle Sitting on the Vertical Line -->
                                        <span class="w-[16px] h-[16px] rounded-full bg-[#22c55e] text-white inline-flex items-center justify-center flex-shrink-0 -ml-[28.5px] mt-1.5 shadow-[0_0_0_3px_#fff]">
                                            <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 20 20">
                                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                                            </svg>
                                        </span>
                                    @else
                                        <!-- Blue Paper-Plane Circle Sitting on the Vertical Line -->
                                        <span class="w-[16px] h-[16px] rounded-full bg-[#4a90e2] text-white inline-flex items-center justify-center flex-shrink-0 -ml-[28.5px] mt-1.5 shadow-[0_0_0_3px_#fff]">
                                            <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24">
                                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                            </svg>
                                        </span>
                                    @endif

                                    <!-- Timestamp (e.g. 7:26am) -->
                                    <span class="text-[12.5px] font-normal text-[#6d6a65] w-[48px] flex-shrink-0 pt-1">
                                        {{ $it->time_str }}
                                    </span>

                                    @if(!empty($it->badge->avatar))
                                        <img src="{{ $it->badge->avatar }}" alt="{{ $it->author_name }}" class="w-[32px] h-[32px] rounded-full object-cover flex-shrink-0 select-none cursor-pointer">
                                    @else
                                        <span class="w-[32px] h-[32px] rounded-full {{ $it->badge->bg ?? 'bg-[#d946ef]' }} text-white font-bold text-[13px] flex items-center justify-center flex-shrink-0 select-none cursor-pointer">
                                            {{ $it->badge->initials }}
                                        </span>
                                    @endif

                                    <!-- Message Title & Full Snippet -->
                                    <div class="flex-1 min-w-0 leading-[1.36]">
                                        <div class="text-[14.5px] text-[#1d2d35]">
                                            <span class="font-bold text-[#1d2d35]">{{ $it->author_name }} {{ $it->action }}</span>
                                            @if(!empty($it->title))
                                                <a href="{{ $it->url }}" class="font-bold text-[#1b6ac9] underline hover:text-[#124b8f]">{{ $it->title }}</a>
                                            @endif
                                        </div>

                                        @if($it->snippet)
                                        <p class="text-[14.5px] font-normal text-[#1d2d35] leading-[1.38] mt-0.5">
                                            {{ $it->snippet }}
                                        </p>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                @empty
                    <div class="py-8 text-center text-[#6d6a65] text-[13.5px]">
                        No activities match your filter.
                    </div>
                @endforelse
            </div>

            <!-- Pagination Links -->
            @if($paginatedActivities->hasPages())
            <div class="pt-6 border-t border-[#ece9e2] flex items-center justify-between gap-4 text-[13px]">
                <span class="text-[#6d6a65]">
                    Page {{ $paginatedActivities->currentPage() }} of {{ $paginatedActivities->lastPage() }}
                </span>

                <div class="flex items-center gap-1.5">
                    @if(!$paginatedActivities->onFirstPage())
                    <a href="{{ $paginatedActivities->previousPageUrl() }}#latest-activity" class="px-3 py-1 rounded-md border border-[#d8d4cc] bg-white text-[#1d2d35] hover:bg-slate-50 font-semibold">← Previous</a>
                    @endif

                    @for($p = max(1, $paginatedActivities->currentPage() - 2); $p <= min($paginatedActivities->lastPage(), $paginatedActivities->currentPage() + 2); $p++)
                        @if($p == $paginatedActivities->currentPage())
                        <span class="w-7 h-7 rounded-md bg-[#283c46] text-white font-bold flex items-center justify-center">{{ $p }}</span>
                        @else
                        <a href="{{ $paginatedActivities->url($p) }}#latest-activity" class="w-7 h-7 rounded-md border border-[#d8d4cc] bg-white text-[#1d2d35] hover:bg-slate-50 font-semibold flex items-center justify-center">{{ $p }}</a>
                        @endif
                    @endfor

                    @if($paginatedActivities->hasMorePages())
                    <a href="{{ $paginatedActivities->nextPageUrl() }}#latest-activity" class="px-3 py-1 rounded-md border border-[#d8d4cc] bg-white text-[#1d2d35] hover:bg-slate-50 font-semibold">Next →</a>
                    @endif
                </div>
            </div>
            @endif

        </div>
        @endif

    </div>

    <!-- Bottom-Left "↑ Back to top" Pill Button (Matches media_1793345788794_f1b3ef7f.png) -->
    <button type="button"
            onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-12 left-7 z-30 px-3 py-1 rounded-full bg-[#f4f1ea]/95 hover:bg-white border border-[#dedad1] text-[12px] text-[#1d2d35] font-normal shadow-2xs inline-flex items-center gap-1 transition-colors cursor-pointer">
        <span>↑</span>
        <span>Back to top</span>
    </button>

    <!-- Basecamp "Move the Needle — Progress & Status Gauge" Video & Explainer Modal -->
    <div x-show="gaugeHelpModal"
         x-cloak
         class="fixed inset-0 z-50 bg-black/55 flex items-center justify-center p-4"
         @click.self="gaugeHelpModal = false">
        <div class="w-full max-w-[680px] rounded-[12px] bg-white border border-[#e5e2dc] shadow-2xl overflow-hidden text-[#1d2d35]">
            <div class="px-6 py-4 border-b border-[#ece9e2] flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <svg class="w-[54px] h-[16px] text-[#199849]" viewBox="0 0 84 18" fill="none">
                        <path d="M 6 12 Q 42 2 78 12 L 75 17 Q 42 8 9 17 Z" stroke="currentColor" stroke-width="1.3" fill="none"/>
                        <line x1="25" y1="8.5" x2="26" y2="13.5" stroke="currentColor" stroke-width="1.1"/>
                        <line x1="42" y1="7" x2="42" y2="12" stroke="currentColor" stroke-width="1.1"/>
                        <line x1="59" y1="8.5" x2="58" y2="13.5" stroke="currentColor" stroke-width="1.1"/>
                        <path d="M 40 6 L 44 6 L 43 16 L 41 16 Z" fill="currentColor"/>
                    </svg>
                    <h3 class="text-[17px] font-extrabold text-[#1d2d35]">Move the Needle: Progress &amp; Status Gauge</h3>
                </div>
                <button type="button" @click="gaugeHelpModal = false" class="p-1 text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer text-[18px] leading-none">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <video controls autoplay playsinline
                       src="/uploads/docs/sample_video_preview.mp4"
                       poster="/uploads/docs/inaug_mov_0141.jpg"
                       class="w-full rounded-[8px] border border-[#e5e2dc] bg-black max-h-[340px] object-contain">
                </video>
                <p class="text-[14px] text-[#4a5560] leading-relaxed">
                    When <strong class="text-[#1d2d35]">Indicate progress</strong> is turned <strong class="text-[#2da562]">On</strong>, a visual gauge appears at the top of your project so you can move the needle and post high-level status updates (<span class="font-semibold text-[#199849]">On track</span>, <span class="font-semibold text-[#d97706]">Some risk</span>, or <span class="font-semibold text-[#dc2626]">Concerned</span>) for everyone on the project.
                </p>
                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button"
                            @click="indicateProgress = true; gaugeHelpModal = false"
                            class="px-4 py-1.5 rounded-full bg-[#199849] hover:bg-[#147a3a] text-white text-[13.5px] font-bold cursor-pointer">
                        Turn gauge On
                    </button>
                    <button type="button"
                            @click="gaugeHelpModal = false"
                            class="px-4 py-1.5 rounded-full border border-[#d6d2c9] bg-white text-[13.5px] text-[#1d2d35] cursor-pointer">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .bc-tool-card.bc-card-dragging .bc-tool-card-title {
        color: #9e9a93 !important;
    }
    .bc-tool-card.bc-card-dragging .bc-tool-card-box {
        opacity: 0.36 !important;
        filter: grayscale(100%);
    }
    .bc-tool-card-floating {
        position: fixed;
        z-index: 9999;
        pointer-events: none;
        transform: scale(0.87);
        transform-origin: top left;
        transition: transform 0.08s ease-out;
    }
    .bc-tool-card-floating .bc-tool-card-box {
        background: #ffffff !important;
        border-color: #d8d4cc !important;
        box-shadow: 0 14px 36px rgba(0, 0, 0, 0.16), 0 3px 10px rgba(0, 0, 0, 0.08) !important;
    }
</style>

<script>
(function () {
    const projectId = {{ (int) $project->id }};
    const storageKey = 'bc_project_tools_order_v2_' + projectId;
    const defaultOrder = projectId === 26
        ? ['message_board', 'todos', 'docs', 'campfire', 'schedule', 'checkins', 'card_table']
        : (projectId === 5
            ? ['campfire', 'message_board', 'todos', 'schedule', 'checkins', 'docs', 'card_table_empty', 'card_table']
            : ['message_board', 'checkins', 'campfire', 'todos', 'schedule', 'docs']);

    function initDraggableProjectCards() {
        const grid = document.getElementById('project-tools-grid');
        if (!grid) return;

        const addSlot = document.getElementById('project-tools-add-slot');

        function getCards() {
            return Array.from(grid.querySelectorAll('.bc-tool-card'));
        }

        function applyOrder(orderArr) {
            if (!Array.isArray(orderArr) || orderArr.length === 0) return;
            const map = {};
            getCards().forEach(card => {
                const id = card.getAttribute('data-tool-id');
                if (id) map[id] = card;
            });
            orderArr.forEach(id => {
                if (map[id]) {
                    grid.appendChild(map[id]);
                    delete map[id];
                }
            });
            Object.values(map).forEach(card => grid.appendChild(card));
            if (addSlot) grid.appendChild(addSlot);
        }

        function saveCurrentOrder() {
            const currentOrder = getCards()
                .map(c => c.getAttribute('data-tool-id'))
                .filter(Boolean);
            try {
                localStorage.setItem(storageKey, JSON.stringify(currentOrder));
            } catch (e) {}
        }

        // Restore saved order (or project default order)
        let saved = null;
        try {
            const raw = localStorage.getItem(storageKey);
            if (raw) saved = JSON.parse(raw);
        } catch (e) {}
        applyOrder(Array.isArray(saved) && saved.length > 0 ? saved : defaultOrder);

        // Prevent native browser link/image ghost dragging inside cards so whole card drags cleanly
        grid.querySelectorAll('.bc-tool-card a, .bc-tool-card img').forEach(el => {
            el.setAttribute('draggable', 'false');
        });

        let dragCard = null;
        let floatingEl = null;
        let startX = 0;
        let startY = 0;
        let offsetX = 0;
        let offsetY = 0;
        let isDragging = false;

        function swapCards(sourceCard, targetCard) {
            if (!sourceCard || !targetCard || sourceCard === targetCard) return;
            const cards = getCards();
            const srcIdx = cards.indexOf(sourceCard);
            const tgtIdx = cards.indexOf(targetCard);
            if (srcIdx === -1 || tgtIdx === -1) return;

            if (srcIdx < tgtIdx) {
                grid.insertBefore(sourceCard, targetCard.nextSibling);
            } else {
                grid.insertBefore(sourceCard, targetCard);
            }
            if (addSlot) grid.appendChild(addSlot);
        }

        function onPointerMove(e) {
            if (!dragCard) return;
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;

            if (!isDragging) {
                if (Math.hypot(dx, dy) < 6) return;
                isDragging = true;

                const rect = dragCard.getBoundingClientRect();
                offsetX = startX - rect.left;
                offsetY = startY - rect.top;

                floatingEl = dragCard.cloneNode(true);
                floatingEl.classList.add('bc-tool-card-floating');
                floatingEl.style.width = rect.width + 'px';
                floatingEl.style.left = rect.left + 'px';
                floatingEl.style.top = rect.top + 'px';
                document.body.appendChild(floatingEl);

                dragCard.classList.add('bc-card-dragging');
            }

            if (floatingEl) {
                floatingEl.style.left = (e.clientX - offsetX * 0.87) + 'px';
                floatingEl.style.top = (e.clientY - offsetY * 0.87) + 'px';
            }

            const elUnder = document.elementFromPoint(e.clientX, e.clientY);
            const overCard = elUnder ? elUnder.closest('.bc-tool-card') : null;
            if (overCard && overCard !== dragCard && grid.contains(overCard)) {
                swapCards(dragCard, overCard);
            }
        }

        function onPointerUp() {
            if (!dragCard) return;
            const wasDragging = isDragging;

            if (floatingEl && floatingEl.parentNode) {
                floatingEl.parentNode.removeChild(floatingEl);
            }
            floatingEl = null;

            dragCard.classList.remove('bc-card-dragging');
            dragCard = null;
            isDragging = false;

            window.removeEventListener('pointermove', onPointerMove);
            window.removeEventListener('pointerup', onPointerUp);
            window.removeEventListener('pointercancel', onPointerUp);

            if (wasDragging) {
                saveCurrentOrder();
                const suppressClick = function (ev) {
                    ev.preventDefault();
                    ev.stopPropagation();
                };
                window.addEventListener('click', suppressClick, { capture: true, once: true });
            }
        }

        getCards().forEach(card => {
            card.setAttribute('draggable', 'true');

            card.addEventListener('dragstart', function (e) {
                // Prevent browser native translucent ghost when pointer-dragging
                if (dragCard) {
                    e.preventDefault();
                }
            });

            card.addEventListener('pointerdown', function (e) {
                if (e.button !== 0) return;
                if (e.target.closest('button, input, textarea, select')) return;

                dragCard = card;
                startX = e.clientX;
                startY = e.clientY;
                isDragging = false;

                window.addEventListener('pointermove', onPointerMove);
                window.addEventListener('pointerup', onPointerUp);
                window.addEventListener('pointercancel', onPointerUp);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDraggableProjectCards);
    } else {
        initDraggableProjectCards();
    }
})();
</script>
@endsection
