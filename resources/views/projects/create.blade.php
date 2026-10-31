@extends('layouts.app')

@section('title', 'Name the project')

@section('content')
<script>
window.bcCreateProjectEditor = function () {
    const urlParams = new URLSearchParams(window.location.search);
    const initialTemplate = urlParams.get('template') || '';
    const templateMap = {
        development: {
            key: 'development',
            label: 'Development Template',
            desc: 'Dependency Checklist (10 to-dos), Configuration Management (CM), Project Planning (PP), and Project Monitoring (PMC).',
            defaultTitle: 'Development Project'
        },
        client_project: {
            key: 'client_project',
            label: 'Client Project',
            desc: 'Deliverables, milestones, and client updates.',
            defaultTitle: 'Client Project'
        },
        product_launch: {
            key: 'product_launch',
            label: 'Product Launch',
            desc: 'Tasks, schedule, and assets for the upcoming launch.',
            defaultTitle: 'Product Launch'
        },
        internal_team: {
            key: 'internal_team',
            label: 'Internal Team',
            desc: 'Announcements, check-ins, and shared docs.',
            defaultTitle: 'Internal Team'
        }
    };
    let customTemplates = [];
    try {
        customTemplates = JSON.parse(localStorage.getItem('bc_custom_project_templates') || '[]');
    } catch (e) {}

    return {
        templateMenuOpen: false,
        notifyMenuOpen: false,
        moreMenuOpen: false,
        gaugeHelpModal: false,
        notifyMode: 'all',
        selectedTemplate: initialTemplate,
        selectedTemplateLabel: templateMap[initialTemplate] ? templateMap[initialTemplate].label : '',
        customTemplates: customTemplates,
        iconPreview: null,
        wallpaperPreview: null,
        wallpaperMode: 'fill',
        indicateProgress: false,
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
        setNotifyMode(mode) {
            this.notifyMode = mode;
            this.notifyMenuOpen = false;
        },
        applyTemplate(key, title, desc) {
            this.selectedTemplate = key;
            this.selectedTemplateLabel = title;
            const defaultNames = ['Name the project', 'Development Template', 'Development Project', 'Client Project', 'Product Launch', 'Internal Team', ''];
            const currentVal = (this.$refs.projectNameInput.value || '').trim();
            if (defaultNames.includes(currentVal)) {
                this.$refs.projectNameInput.value = title;
            }
            if (desc && !(this.$refs.projectDescInput.value || '').trim()) {
                this.$refs.projectDescInput.value = desc;
            }
            this.templateMenuOpen = false;
        },
        clearTemplate() {
            this.selectedTemplate = '';
            this.selectedTemplateLabel = '';
            this.templateMenuOpen = false;
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
            const startOffset = (firstDay.getDay() + 6) % 7; // Monday = 0
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

<!-- Exact Basecamp Project Details & Creation Sheet Matching media_1793288291842_c8fbc9f6.jpg, 2fb42a39.jpg, 6b19d7dc.jpg -->
<div class="w-full sm:w-[72vw] max-w-[1130px] mx-auto font-sans text-[#1d2d35]"
     x-data="bcCreateProjectEditor()"
     x-init="$nextTick(() => {
         if (selectedTemplate === 'development') {
             applyTemplate('development', 'Development Template', 'Dependency Checklist, Configuration Management (CM), Project Planning (PP), and Project Monitoring (PMC).');
         }
         if ($refs.projectNameInput) {
             $refs.projectNameInput.focus();
             $refs.projectNameInput.select();
         }
     })">

    <!-- Basecamp White Paper Sheet -->
    <div class="bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-92px)]">

        <!-- Top Sub-Bar: Left "Invite people" pill + Right "Use a template ⌄" / "Notifications on" & "•••" -->
        <div class="px-4 py-1.5 border-b border-[#ece9e2] flex items-center justify-between text-[13px] relative">
            <!-- Left: People Icon + Invite people pill -->
            <div class="flex items-center">
                <a href="{{ route('enrollments.create') }}" class="w-[26px] h-[26px] rounded-full border border-[#d8d4cc] bg-white hover:bg-slate-50 flex items-center justify-center text-[#1d2d35] mr-1.5 transition-colors" title="Invite people">
                    <i data-lucide="users" class="w-3.5 h-3.5 text-[#1d2d35] stroke-[2.2]"></i>
                </a>
                <a href="{{ route('enrollments.create') }}" class="px-2.5 py-0.5 rounded-[5px] bg-[#e9f5e9] hover:bg-[#dcefdc] text-[#1d2d35] text-[13px] font-medium transition-colors">
                    Invite people
                </a>
            </div>

            <!-- Right: Use a template ⌄ + Notifications on + ••• -->
            <div class="flex items-center gap-2.5 text-[#1d2d35] flex-shrink-0">
                <div class="relative" @click.outside="templateMenuOpen = false">
                    <button type="button"
                            @click="templateMenuOpen = !templateMenuOpen; notifyMenuOpen = false; moreMenuOpen = false"
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-[6px] bg-[#f2f0ec] hover:bg-[#e5e2dc] text-[#1d2d35] text-[13px] font-normal cursor-pointer transition-colors">
                        <span x-text="selectedTemplateLabel ? selectedTemplateLabel : 'Use a template'">Use a template</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Template Dropdown Menu (Matches media_1793346412056_c3b64170.jpg 1:1) -->
                    <div x-show="templateMenuOpen"
                         x-cloak
                         class="absolute right-0 mt-1.5 w-[235px] bg-white rounded-[10px] border border-[#e5e2dc] shadow-[0_8px_24px_rgba(0,0,0,0.14)] py-2.5 px-4 text-[14px] text-[#1d2d35] z-40">
                        <button type="button"
                                @click="clearTemplate()"
                                class="w-full py-1.5 text-left hover:text-[#1b6ac9] flex items-center justify-between cursor-pointer"
                                :class="!selectedTemplate ? 'font-bold text-[#1d2d35]' : 'font-normal text-[#1d2d35]'">
                            <span>No template</span>
                            <svg x-show="!selectedTemplate" class="w-4 h-4 text-[#1d2d35] shrink-0" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </button>

                        <button type="button"
                                @click="applyTemplate('development', 'Development Template', '')"
                                class="w-full py-1.5 text-left hover:text-[#1b6ac9] flex items-center justify-between cursor-pointer"
                                :class="selectedTemplate === 'development' ? 'font-bold text-[#1d2d35]' : 'font-normal text-[#1d2d35]'">
                            <span>Development Template</span>
                            <svg x-show="selectedTemplate === 'development'" class="w-4 h-4 text-[#1d2d35] shrink-0" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </button>

                        <template x-for="ct in customTemplates" :key="ct.id">
                            <button type="button"
                                    @click="applyTemplate('development', ct.name, ct.desc || '')"
                                    class="w-full py-1.5 text-left hover:text-[#1b6ac9] flex items-center justify-between cursor-pointer"
                                    :class="selectedTemplateLabel === ct.name ? 'font-bold text-[#1d2d35]' : 'font-normal text-[#1d2d35]'">
                                <span x-text="ct.name"></span>
                                <svg x-show="selectedTemplateLabel === ct.name" class="w-4 h-4 text-[#1d2d35] shrink-0" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </template>

                        <div class="my-2 border-t border-[#ece9e2]"></div>

                        <a href="{{ route('templates.index') }}" class="block w-full py-1 text-left text-[14px] text-[#1d2d35] hover:text-[#1b6ac9]">
                            See all templates
                        </a>
                    </div>
                </div>

                <!-- Notifications on / off Popover Dropdown -->
                <div class="relative" @click.outside="notifyMenuOpen = false">
                    <button type="button"
                            @click="notifyMenuOpen = !notifyMenuOpen; templateMenuOpen = false; moreMenuOpen = false"
                            class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[13px] font-normal text-[#1d2d35] border transition-colors cursor-pointer"
                            :class="notifyMenuOpen
                                ? 'border-[#9da7b1] bg-[#f2f0ec]'
                                : 'border-transparent hover:border-[#d6d2c9] hover:bg-[#f6f5f1]'">
                        <svg class="w-3.5 h-3.5 fill-current text-[#283c46]" viewBox="0 0 24 24">
                            <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                        </svg>
                        <span x-text="notifyMode === 'all' ? 'Notifications on' : 'Notifications off'">Notifications on</span>
                    </button>

                    <div x-show="notifyMenuOpen"
                         x-cloak
                         class="absolute right-0 top-[calc(100%+8px)] w-[280px] rounded-[8px] border border-[#e5e2dc] bg-[#f6f5f1] shadow-[0_10px_28px_rgba(0,0,0,0.28)] p-3.5 z-50 text-left select-none">
                        <span class="absolute -top-[6px] right-6 w-3 h-3 rotate-45 bg-[#f6f5f1] border-l border-t border-[#e5e2dc]"></span>

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

                <!-- ••• Options Menu -->
                <div class="relative" @click.outside="moreMenuOpen = false">
                    <button type="button"
                            @click="moreMenuOpen = !moreMenuOpen; templateMenuOpen = false; notifyMenuOpen = false"
                            class="p-1 text-[#1d2d35] hover:bg-slate-100 rounded-md transition-colors cursor-pointer"
                            title="Project options">
                        <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                    </button>

                    <div x-show="moreMenuOpen"
                         x-cloak
                         class="absolute right-0 top-[calc(100%+6px)] w-[195px] bg-white rounded-[8px] border border-[#e5e2dc] shadow-[0_8px_24px_rgba(0,0,0,0.14)] py-1.5 z-40 text-[13px] text-[#1d2d35]">
                        <button type="button" @click="moreMenuOpen = false; templateMenuOpen = true" class="w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0] cursor-pointer">
                            Use a template...
                        </button>
                        <a href="{{ route('templates.index') }}" class="block w-full text-left px-3.5 py-1.5 hover:bg-[#f6f4f0]">
                            Manage templates...
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Content -->
        <div class="px-8 sm:px-10 pt-8 pb-16">
            @if ($errors->any())
                <div class="mb-5 max-w-[540px] rounded-[8px] border border-rose-200 bg-rose-50 px-4 py-2.5 text-[13px] text-rose-700">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="template_key" :value="selectedTemplate">
                <input type="hidden" name="notify_mode" :value="notifyMode">

                <!-- Active Template Banner -->
                <div x-show="selectedTemplate"
                     x-cloak
                     class="mb-5 rounded-[8px] border border-[#93c5fd] bg-[#eff6ff]/80 px-4 py-3 flex items-center justify-between gap-3 text-[13.5px] text-[#1d2d35]">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-full bg-[#1b6ac9] text-white font-bold text-[12px] flex items-center justify-center shrink-0">✓</span>
                        <div>
                            <span class="font-bold">Using template: <span x-text="selectedTemplateLabel"></span></span>
                            <span class="text-[#4a5560] ml-1">— We’ll copy its Message Board, To-do lists (Dependency Checklist), and Docs &amp; Files into this project when you save.</span>
                        </div>
                    </div>
                    <button type="button" @click="clearTemplate()" class="text-[12.5px] text-[#6d6a65] hover:text-[#1d2d35] underline shrink-0 cursor-pointer">
                        Remove template
                    </button>
                </div>

                <!-- Top Row: Left 64x64 Project Icon Upload Box + Right Name & Optional Description -->
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
                                   x-ref="projectNameInput"
                                   value="{{ old('name', 'Name the project') }}"
                                   required
                                   autocomplete="off"
                                   placeholder="Name the project"
                                   class="w-full text-[28px] sm:text-[32px] font-black text-[#1d2d35] placeholder-[#8c8881] tracking-[-0.02em] leading-tight bg-transparent border-0 p-0 focus:outline-none focus:ring-0 selection:bg-[#b4d5fe] selection:text-[#1d2d35]">
                        </div>
                        <div class="mt-1">
                            <input type="text"
                                   name="description"
                                   x-ref="projectDescInput"
                                   value="{{ old('description') }}"
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
                            <!-- Collapsed: "Select dates..." -->
                            <div x-show="!datesExpanded">
                                <button type="button"
                                        @click="openDates()"
                                        class="text-[14px] text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer">
                                    Select dates...
                                </button>
                            </div>

                            <!-- Expanded: Start date & End date + Calendar Popover + Remove dates -->
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
                                            <!-- Month / Year / Prev / Next -->
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

                                            <!-- Weekday Headers (MO TU WE TH FR SA SU) -->
                                            <div class="grid grid-cols-7 text-center text-[9.5px] font-bold uppercase tracking-wider text-[#6d6a65] mb-1.5">
                                                <span>MO</span><span>TU</span><span>WE</span><span>TH</span><span>FR</span><span>SA</span><span>SU</span>
                                            </div>

                                            <!-- Days Grid -->
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
                                            <!-- Month / Year / Prev / Next -->
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

                                            <!-- Weekday Headers (MO TU WE TH FR SA SU) -->
                                            <div class="grid grid-cols-7 text-center text-[9.5px] font-bold uppercase tracking-wider text-[#6d6a65] mb-1.5">
                                                <span>MO</span><span>TU</span><span>WE</span><span>TH</span><span>FR</span><span>SA</span><span>SU</span>
                                            </div>

                                            <!-- Days Grid -->
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
                            <!-- Basecamp Binary Toggle Switch ([Off] / [On]) -->
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
                            <input type="hidden" name="indicate_progress" :value="indicateProgress ? '1' : '0'">

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
                    <a href="{{ route('home') }}"
                       class="px-4 py-1.5 rounded-[6px] border border-[#d6d2c9] bg-white hover:bg-[#f6f4f0] text-[#1b6ac9] font-normal text-[14px] transition">
                        Never mind
                    </a>
                </div>
            </form>
        </div>

    </div>

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
@endsection
