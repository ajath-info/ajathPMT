@extends('layouts.app')

@section('title', 'Schedule - ' . $project->name)

@section('content')
@php
    $dbEvents = $tasks->map(function ($t) {
        $dt = \Carbon\Carbon::parse($t->due_date);
        return [
            'id' => 'task-' . $t->id,
            'title' => $t->title,
            'dateKey' => $dt->format('Y-m-d'),
            'dateLabel' => $dt->format('D, M j, Y'),
            'startTime' => '9:00am',
            'endTime' => '10:00am',
            'allDay' => true,
            'circled' => false,
            'assignee' => $t->assignee->name ?? 'Shachish Sneh',
        ];
    })->values()->all();
@endphp

<script>
window.bcScheduleApp = function (projectId, initialEvents) {
    const storageKey = 'bc_schedule_events_' + projectId;
    let saved = [];
    try {
        saved = JSON.parse(localStorage.getItem(storageKey) || '[]');
    } catch (e) {
        saved = [];
    }
    return {
        viewTab: 'calendar',
        filterQuery: '',
        newEventOpen: new URLSearchParams(window.location.search).get('new_event') === '1',
        privacyMenuOpen: false,
        eventPrivacy: 'private',
        eventTitle: '',
        startDateLabel: 'Fri, Oct 30, 2026',
        endDateLabel: 'Fri, Oct 30, 2026',
        startDateIso: '2026-10-30',
        endDateIso: '2026-10-30',
        startTime: '1:00pm',
        endTime: '1:30pm',
        allDay: false,
        showLinkField: false,
        eventLink: '',
        showRepeatField: false,
        eventRepeat: 'Does not repeat',
        showNotesField: false,
        eventNotes: '',
        circleEvent: false,
        attendeeDropdownOpen: false,
        attendees: [
            { id: 60, name: 'Shachish Sneh', avatar: '/uploads/avatars/25036340.png', initials: 'SS', bg: '#00a884' }
        ],
        allPeople: [
            { id: 60, name: 'Shachish Sneh', avatar: '/uploads/avatars/25036340.png', initials: 'SS', bg: '#00a884' },
            { id: 1, name: 'Manjot Singh', avatar: '/uploads/avatars/22369668.png', initials: 'MS', bg: '#1d4ed8' },
            { id: 4, name: 'Gaurav Khulbey', avatar: null, initials: 'GK', bg: '#e879f9' },
            { id: 2, name: 'Pawandeep Kaur', avatar: null, initials: 'PK', bg: '#00897b' },
            { id: 3, name: 'Prashant', avatar: '/uploads/avatars/22369670.png', initials: 'P', bg: '#0284c7' },
            { id: 5, name: 'Rajesh Kumar', avatar: null, initials: 'RK', bg: '#e879f9' },
            { id: 6, name: 'Rishabh Ojha', avatar: null, initials: 'RO', bg: '#00897b' },
            { id: 62, name: 'Shiv Narayan', avatar: null, initials: 'SN', bg: '#9333ea' }
        ],
        events: initialEvents.concat(saved),
        calendarWeeks: [
            [
                { day: 26, month: 'oct', iso: '2026-10-26', label: 'Mon, Oct 26, 2026' },
                { day: 27, month: 'oct', iso: '2026-10-27', label: 'Tue, Oct 27, 2026' },
                { day: 28, month: 'oct', iso: '2026-10-28', label: 'Wed, Oct 28, 2026' },
                { day: 29, month: 'oct', iso: '2026-10-29', label: 'Thu, Oct 29, 2026' },
                { day: 30, month: 'oct', iso: '2026-10-30', label: 'Fri, Oct 30, 2026', isToday: true },
                { day: 31, month: 'oct', iso: '2026-10-31', label: 'Sat, Oct 31, 2026' },
                { day: 1, month: 'nov', iso: '2026-11-01', label: 'Sun, Nov 1, 2026', headerLabel: 'Nov 1, 2026' }
            ],
            [
                { day: 2, month: 'nov', iso: '2026-11-02', label: 'Mon, Nov 2, 2026' },
                { day: 3, month: 'nov', iso: '2026-11-03', label: 'Tue, Nov 3, 2026' },
                { day: 4, month: 'nov', iso: '2026-11-04', label: 'Wed, Nov 4, 2026' },
                { day: 5, month: 'nov', iso: '2026-11-05', label: 'Thu, Nov 5, 2026' },
                { day: 6, month: 'nov', iso: '2026-11-06', label: 'Fri, Nov 6, 2026' },
                { day: 7, month: 'nov', iso: '2026-11-07', label: 'Sat, Nov 7, 2026' },
                { day: 8, month: 'nov', iso: '2026-11-08', label: 'Sun, Nov 8, 2026' }
            ],
            [
                { day: 9, month: 'nov', iso: '2026-11-09', label: 'Mon, Nov 9, 2026' },
                { day: 10, month: 'nov', iso: '2026-11-10', label: 'Tue, Nov 10, 2026' },
                { day: 11, month: 'nov', iso: '2026-11-11', label: 'Wed, Nov 11, 2026' },
                { day: 12, month: 'nov', iso: '2026-11-12', label: 'Thu, Nov 12, 2026' },
                { day: 13, month: 'nov', iso: '2026-11-13', label: 'Fri, Nov 13, 2026' },
                { day: 14, month: 'nov', iso: '2026-11-14', label: 'Sat, Nov 14, 2026' },
                { day: 15, month: 'nov', iso: '2026-11-15', label: 'Sun, Nov 15, 2026' }
            ],
            [
                { day: 16, month: 'nov', iso: '2026-11-16', label: 'Mon, Nov 16, 2026' },
                { day: 17, month: 'nov', iso: '2026-11-17', label: 'Tue, Nov 17, 2026' },
                { day: 18, month: 'nov', iso: '2026-11-18', label: 'Wed, Nov 18, 2026' },
                { day: 19, month: 'nov', iso: '2026-11-19', label: 'Thu, Nov 19, 2026' },
                { day: 20, month: 'nov', iso: '2026-11-20', label: 'Fri, Nov 20, 2026' },
                { day: 21, month: 'nov', iso: '2026-11-21', label: 'Sat, Nov 21, 2026' },
                { day: 22, month: 'nov', iso: '2026-11-22', label: 'Sun, Nov 22, 2026' }
            ]
        ],
        openNewEventForCell(cell) {
            if (cell) {
                this.startDateIso = cell.iso;
                this.endDateIso = cell.iso;
                this.startDateLabel = cell.label;
                this.endDateLabel = cell.label;
            } else {
                this.startDateIso = '2026-10-30';
                this.endDateIso = '2026-10-30';
                this.startDateLabel = 'Fri, Oct 30, 2026';
                this.endDateLabel = 'Fri, Oct 30, 2026';
            }
            this.eventTitle = '';
            this.newEventOpen = true;
            this.$nextTick(() => {
                if (this.$refs.eventTitleInput) {
                    this.$refs.eventTitleInput.focus();
                }
            });
        },
        updateDateFromPicker(which, val) {
            if (!val) return;
            const d = new Date(val + 'T12:00:00');
            const fmt = d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
            if (which === 'start') {
                this.startDateIso = val;
                this.startDateLabel = fmt;
                if (this.endDateIso < val) {
                    this.endDateIso = val;
                    this.endDateLabel = fmt;
                }
            } else {
                this.endDateIso = val;
                this.endDateLabel = fmt;
            }
        },
        removeAttendee(id) {
            this.attendees = this.attendees.filter(a => a.id !== id);
        },
        addAttendee(person) {
            if (!this.attendees.some(a => a.id === person.id)) {
                this.attendees.push(person);
            }
            this.attendeeDropdownOpen = false;
        },
        eventsForDate(iso) {
            const q = (this.filterQuery || '').trim().toLowerCase();
            return this.events.filter(ev => {
                if (ev.dateKey !== iso) return false;
                if (q && !ev.title.toLowerCase().includes(q)) return false;
                return true;
            });
        },
        filteredEvents() {
            const q = (this.filterQuery || '').trim().toLowerCase();
            return this.events.filter(ev => !q || ev.title.toLowerCase().includes(q));
        },
        postEvent() {
            const title = (this.eventTitle || '').trim() || 'Team Event';
            const newEv = {
                id: 'ev-' + Date.now(),
                title: title,
                dateKey: this.startDateIso || '2026-10-30',
                dateLabel: this.startDateLabel || 'Fri, Oct 30, 2026',
                startTime: this.allDay ? 'All day' : this.startTime,
                endTime: this.allDay ? '' : this.endTime,
                allDay: this.allDay,
                circled: this.circleEvent,
                assignee: this.attendees.map(a => a.name).join(', ')
            };
            this.events.push(newEv);
            try {
                const existing = JSON.parse(localStorage.getItem(storageKey) || '[]');
                existing.push(newEv);
                localStorage.setItem(storageKey, JSON.stringify(existing));
            } catch (e) {}
            this.newEventOpen = false;
            this.eventTitle = '';
        }
    };
};
</script>

<div class="w-full sm:w-[78vw] max-w-[1040px] mx-auto font-sans text-[#1d2d35] pb-16"
     x-data="bcScheduleApp({{ $project->id }}, {{ json_encode($dbEvents) }})">

    <!-- Basecamp White Paper Sheet -->
    <div class="bg-white rounded-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] overflow-hidden min-h-[760px]">

        <!-- Top Sub-Bar: Breadcrumb on Left, Bookmark + ••• on Right -->
        <div class="px-6 py-2.5 border-b border-[#ece9e2] flex items-center justify-between text-[13px]">
            <div class="flex items-center gap-1.5">
                <a href="{{ route('projects.show', $project->id) }}" class="font-bold text-[#1d2d35] hover:underline transition-colors">
                    {{ $project->name }}
                </a>
            </div>

            <div class="flex items-center gap-3.5 text-[#1d2d35]">
                <button type="button" onclick="window.toggleBookmark && window.toggleBookmark(this)" class="inline-flex items-center gap-1 text-[12.5px] hover:underline transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#4a4640]" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                    <span data-bookmark-label>Bookmark</span>
                </button>
                <button type="button" class="w-7 h-7 rounded-full hover:bg-black/[0.05] flex items-center justify-center text-[#1d2d35] cursor-pointer">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Main Schedule Body -->
        <div class="px-8 sm:px-12 pt-8 pb-12">
            <h1 class="text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-tight">
                Schedule
            </h1>

            <!-- Toolbar: [+ New event] [Calendar | Agenda] [Filter...] -->
            <div class="mt-3 flex flex-wrap items-center gap-2.5 pb-5 border-b border-[#ece9e2]">
                <button type="button"
                        @click="openNewEventForCell(null)"
                        class="px-3.5 py-1.5 rounded-[6px] bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-bold text-[13.5px] inline-flex items-center gap-1 transition-colors cursor-pointer">
                    <span>+ New event</span>
                </button>

                <div class="inline-flex items-center rounded-[6px] border border-[#d8d4cc] bg-white overflow-hidden text-[13px]">
                    <button type="button"
                            @click="viewTab = 'calendar'"
                            :class="viewTab === 'calendar' ? 'bg-[#e6e3dd] font-bold text-[#1d2d35]' : 'bg-white text-[#6d6a65] hover:text-[#1d2d35]'"
                            class="px-3 py-1.5 cursor-pointer transition-colors">
                        Calendar
                    </button>
                    <button type="button"
                            @click="viewTab = 'agenda'"
                            :class="viewTab === 'agenda' ? 'bg-[#e6e3dd] font-bold text-[#1d2d35]' : 'bg-white text-[#6d6a65] hover:text-[#1d2d35]'"
                            class="px-3 py-1.5 border-l border-[#d8d4cc] cursor-pointer transition-colors">
                        Agenda
                    </button>
                </div>

                <input type="text"
                       x-model="filterQuery"
                       placeholder="Filter..."
                       class="px-3 py-1.5 rounded-[6px] border border-[#d8d4cc] bg-white text-[13px] text-[#1d2d35] placeholder-[#8c8881] w-[160px] focus:outline-none focus:border-[#1b6ac9]">
            </div>

            <!-- VIEW 1: 7-COLUMN BASECAMP CALENDAR GRID (Matches media_1793346493394_e42454a0.jpg 1:1) -->
            <div x-show="viewTab === 'calendar'" class="mt-6">
                <!-- Day-of-Week Header Row -->
                <div class="grid grid-cols-7 text-center text-[11px] font-semibold tracking-wider text-[#6d6a65] uppercase pb-2">
                    <div>MON</div>
                    <div>TUE</div>
                    <div>WED</div>
                    <div>THU</div>
                    <div>FRI</div>
                    <div>SAT</div>
                    <div>SUN</div>
                </div>

                <!-- Calendar Weeks Grid -->
                <div class="border-t border-l border-[#d8d4cc]">
                    <template x-for="(week, wIdx) in calendarWeeks" :key="wIdx">
                        <div class="grid grid-cols-7">
                            <template x-for="cell in week" :key="cell.iso">
                                <div @click="openNewEventForCell(cell)"
                                     :class="cell.month === 'nov' ? 'bg-[#f4f1ea]' : 'bg-white'"
                                     class="min-h-[124px] border-r border-b border-[#d8d4cc] flex flex-col cursor-pointer hover:bg-[#faf8f4] transition-colors relative">
                                    <!-- Optional Month Transition Header Bar (e.g. Nov 1, 2026 in brown #7d6a55) -->
                                    <template x-if="cell.headerLabel">
                                        <div class="bg-[#7d6a55] text-white text-[11.5px] font-bold text-right px-2 py-0.5 leading-tight"
                                             x-text="cell.headerLabel"></div>
                                    </template>
                                    <template x-if="!cell.headerLabel">
                                        <div class="text-right px-2.5 pt-1.5 text-[12px] font-bold text-[#1d2d35]">
                                            <span :class="cell.isToday ? 'inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#1b6ac9] text-white' : ''"
                                                  x-text="cell.day"></span>
                                        </div>
                                    </template>

                                    <!-- Events in this Day Cell -->
                                    <div class="px-1.5 py-1 space-y-1 flex-1">
                                        <template x-for="ev in eventsForDate(cell.iso)" :key="ev.id">
                                            <div @click.stop
                                                 :class="ev.circled ? 'border-2 border-[#a62c21] rounded-full px-2 py-0.5 bg-[#fff8f6]' : 'rounded-[4px] px-1.5 py-0.5 bg-[#e6f2fa]'"
                                                 class="text-[11.5px] text-[#1d2d35] truncate font-semibold">
                                                <span x-show="!ev.allDay" class="text-[#1b6ac9] font-normal mr-1" x-text="ev.startTime"></span>
                                                <span x-text="ev.title"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- VIEW 2: AGENDA LIST VIEW -->
            <div x-show="viewTab === 'agenda'" x-cloak class="mt-6 space-y-3">
                <template x-for="ev in filteredEvents()" :key="'ag-' + ev.id">
                    <div class="flex items-center justify-between py-3 px-4 rounded-[8px] border border-[#e5e2dc] bg-[#fbfaf8]">
                        <div>
                            <div class="text-[15px] font-bold text-[#1d2d35]" x-text="ev.title"></div>
                            <div class="text-[12.5px] text-[#6d6a65]">
                                <span x-text="ev.dateLabel"></span>
                                <span x-show="ev.startTime" x-text="' · ' + ev.startTime + (ev.endTime ? ' - ' + ev.endTime : '')"></span>
                                <span x-show="ev.assignee" x-text="' · ' + ev.assignee"></span>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="filteredEvents().length === 0" class="py-12 text-center text-[14.5px] text-[#6d6a65]">
                    No upcoming events on the schedule. Click <button type="button" @click="openNewEventForCell(null)" class="text-[#1b6ac9] underline font-semibold cursor-pointer">+ New event</button> to add one.
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- BASECAMP NEW EVENT MODAL (Matches media_1793346493394_e42454a0.jpg 1:1) -->
    <!-- ===================================================================== -->
    <div x-show="newEventOpen"
         x-cloak
         @click.self="newEventOpen = false"
         @keydown.escape.window="newEventOpen = false"
         class="fixed inset-0 z-50 bg-black/45 flex items-center justify-center p-4">

        <div class="w-[485px] max-w-[94vw] bg-white rounded-[14px] shadow-[0_14px_44px_rgba(0,0,0,0.28)] px-6 py-5 text-[#1d2d35] relative">

            <!-- Top Row: Left Privacy Pill Dropdown + Right Dark Circular Close Button -->
            <div class="flex items-center justify-between">
                <div class="relative" @click.outside="privacyMenuOpen = false">
                    <button type="button"
                            @click="privacyMenuOpen = !privacyMenuOpen"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#d6eaf8] hover:bg-[#c5e2f6] text-[#1d2d35] text-[12px] font-semibold transition-colors cursor-pointer">
                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        <span x-text="eventPrivacy === 'private' ? 'Private to our team' : 'Client can see this too'">Private to our team</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="privacyMenuOpen"
                         x-cloak
                         class="absolute left-0 top-[calc(100%+4px)] w-[215px] rounded-[8px] bg-white border border-[#d8d4cc] shadow-lg py-1.5 z-30 text-[13px]">
                        <button type="button"
                                @click="eventPrivacy = 'private'; privacyMenuOpen = false"
                                class="w-full px-3 py-1.5 text-left hover:bg-[#f2f0ec] flex items-center justify-between cursor-pointer">
                            <span>🔒 Private to our team</span>
                            <span x-show="eventPrivacy === 'private'">✓</span>
                        </button>
                        <button type="button"
                                @click="eventPrivacy = 'client'; privacyMenuOpen = false"
                                class="w-full px-3 py-1.5 text-left hover:bg-[#f2f0ec] flex items-center justify-between cursor-pointer">
                            <span>👁 Client can see this too</span>
                            <span x-show="eventPrivacy === 'client'">✓</span>
                        </button>
                    </div>
                </div>

                <button type="button"
                        @click="newEventOpen = false"
                        class="w-[26px] h-[26px] rounded-full bg-[#1d2d35] hover:bg-black text-white flex items-center justify-center text-[13px] font-bold cursor-pointer"
                        aria-label="Close">
                    ✕
                </button>
            </div>

            <!-- Event Title Input -->
            <div class="mt-3 mb-3">
                <input type="text"
                       x-ref="eventTitleInput"
                       x-model="eventTitle"
                       @keydown.enter.prevent="postEvent()"
                       placeholder="Type the name of the event..."
                       style="background:transparent !important;border:0 !important;box-shadow:none !important;"
                       class="w-full text-[21px] font-bold text-[#1d2d35] placeholder-[#8c8881] p-0 focus:outline-none focus:ring-0">
            </div>

            <!-- Date & Time Range Box -->
            <div class="rounded-[8px] border border-[#ece9e2] px-4 py-3 mb-3">
                <div class="flex items-center justify-between gap-3">
                    <!-- Start Date & Time -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between border-b border-[#ece9e2] pb-1.5 relative">
                            <span class="text-[14px] text-[#1d2d35]" x-text="startDateLabel">Fri, Oct 30, 2026</span>
                            <label class="cursor-pointer text-[#8c8881] hover:text-[#1d2d35]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <input type="date"
                                       :value="startDateIso"
                                       @change="updateDateFromPicker('start', $event.target.value)"
                                       class="sr-only">
                            </label>
                        </div>
                        <div x-show="!allDay" class="pt-1.5 border-b border-[#ece9e2] pb-1">
                            <input type="text"
                                   x-model="startTime"
                                   style="background:transparent !important;border:0 !important;box-shadow:none !important;"
                                   class="w-full text-[14px] text-[#1d2d35] p-0 focus:outline-none focus:ring-0">
                        </div>
                    </div>

                    <!-- Center Arrow -->
                    <div class="text-[#bfbcb4] px-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6"/></svg>
                    </div>

                    <!-- End Date & Time -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between border-b border-[#ece9e2] pb-1.5 relative">
                            <span class="text-[14px] text-[#1d2d35]" x-text="endDateLabel">Fri, Oct 30, 2026</span>
                            <label class="cursor-pointer text-[#8c8881] hover:text-[#1d2d35]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <input type="date"
                                       :value="endDateIso"
                                       @change="updateDateFromPicker('end', $event.target.value)"
                                       class="sr-only">
                            </label>
                        </div>
                        <div x-show="!allDay" class="pt-1.5 border-b border-[#ece9e2] pb-1">
                            <input type="text"
                                   x-model="endTime"
                                   style="background:transparent !important;border:0 !important;box-shadow:none !important;"
                                   class="w-full text-[14px] text-[#1d2d35] p-0 focus:outline-none focus:ring-0">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Centered All Day Toggle Switch -->
            <div class="flex items-center justify-center gap-2 mb-4">
                <button type="button"
                        @click="allDay = !allDay"
                        :class="allDay ? 'bg-[#198744]' : 'bg-[#8c8881]'"
                        class="w-[38px] h-[20px] rounded-full p-0.5 transition-colors flex items-center cursor-pointer">
                    <span :class="allDay ? 'translate-x-[18px]' : 'translate-x-0'"
                          class="w-[16px] h-[16px] rounded-full bg-white shadow-xs transition-transform"></span>
                </button>
                <span class="text-[13.5px] text-[#1d2d35] cursor-pointer select-none" @click="allDay = !allDay">All day</span>
            </div>

            <!-- Attendees Row (Matches Shachish Sneh chip in media_1793346493394_e42454a0.jpg) -->
            <div class="flex items-center flex-wrap gap-2 mb-3.5 relative" @click.outside="attendeeDropdownOpen = false">
                <svg class="w-4 h-4 text-[#1b6ac9] flex-shrink-0 mr-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>

                <template x-for="att in attendees" :key="att.id">
                    <span class="inline-flex items-center gap-1.5 pl-1 pr-2.5 py-0.5 rounded-full border border-[#e5e2dc] bg-[#fbfaf8] text-[13px] text-[#1d2d35]">
                        <template x-if="att.avatar">
                            <img :src="att.avatar" alt="" class="w-[20px] h-[20px] rounded-full object-cover">
                        </template>
                        <template x-if="!att.avatar">
                            <span class="w-[20px] h-[20px] rounded-full text-white text-[9px] font-bold inline-flex items-center justify-center"
                                  :style="'background:' + att.bg"
                                  x-text="att.initials"></span>
                        </template>
                        <span x-text="att.name"></span>
                        <button type="button" @click="removeAttendee(att.id)" class="text-[#8c8881] hover:text-[#1d2d35] text-[12px] leading-none ml-0.5 cursor-pointer">×</button>
                    </span>
                </template>

                <button type="button"
                        @click="attendeeDropdownOpen = !attendeeDropdownOpen"
                        class="text-[12.5px] text-[#8c8881] hover:text-[#1d2d35] px-1.5 py-0.5 cursor-pointer">
                    + Add person
                </button>

                <div x-show="attendeeDropdownOpen"
                     x-cloak
                     class="absolute left-6 top-[calc(100%+4px)] w-[240px] max-h-[180px] overflow-y-auto rounded-[8px] bg-white border border-[#d8d4cc] shadow-lg p-1 z-30">
                    <template x-for="person in allPeople" :key="'ev-p-' + person.id">
                        <button type="button"
                                @click="addAttendee(person)"
                                class="w-full px-2.5 py-1.5 rounded-[5px] hover:bg-[#1b6ac9] hover:text-white flex items-center gap-2 text-left text-[13px] text-[#1d2d35] cursor-pointer">
                            <template x-if="person.avatar">
                                <img :src="person.avatar" alt="" class="w-[20px] h-[20px] rounded-full object-cover">
                            </template>
                            <template x-if="!person.avatar">
                                <span class="w-[20px] h-[20px] rounded-full text-white text-[9px] font-bold inline-flex items-center justify-center"
                                      :style="'background:' + person.bg"
                                      x-text="person.initials"></span>
                            </template>
                            <span x-text="person.name"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- 4 Pill Buttons: Link, Repeat, Notes, Circle event -->
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <button type="button"
                        @click="showLinkField = !showLinkField"
                        :class="showLinkField ? 'border-[#1b6ac9] bg-[#eff6ff]' : 'border-[#e5e2dc] bg-white hover:bg-[#fbfaf8]'"
                        class="px-3 py-1 rounded-[6px] border text-[13px] text-[#1d2d35] inline-flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#1b6ac9]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    <span>Link</span>
                </button>

                <button type="button"
                        @click="showRepeatField = !showRepeatField"
                        :class="showRepeatField ? 'border-[#1b6ac9] bg-[#eff6ff]' : 'border-[#e5e2dc] bg-white hover:bg-[#fbfaf8]'"
                        class="px-3 py-1 rounded-[6px] border text-[13px] text-[#1d2d35] inline-flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#1b6ac9]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Repeat</span>
                </button>

                <button type="button"
                        @click="showNotesField = !showNotesField"
                        :class="showNotesField ? 'border-[#1b6ac9] bg-[#eff6ff]' : 'border-[#e5e2dc] bg-white hover:bg-[#fbfaf8]'"
                        class="px-3 py-1 rounded-[6px] border text-[13px] text-[#1d2d35] inline-flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#1b6ac9]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span>Notes</span>
                </button>

                <button type="button"
                        @click="circleEvent = !circleEvent"
                        :class="circleEvent ? 'border-[#1b6ac9] bg-[#eff6ff]' : 'border-[#e5e2dc] bg-white hover:bg-[#fbfaf8]'"
                        class="px-3 py-1 rounded-[6px] border text-[13px] text-[#1d2d35] inline-flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#1b6ac9]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-dasharray="4 2"/></svg>
                    <span>Circle event</span>
                </button>
            </div>

            <!-- Optional Expanded Fields -->
            <div x-show="showLinkField" x-cloak class="mb-3">
                <input type="url" x-model="eventLink" placeholder="Paste a link (e.g. Zoom, Google Meet)..." class="w-full px-3 py-1.5 rounded-[6px] border border-[#d8d4cc] text-[13px] focus:outline-none focus:border-[#1b6ac9]">
            </div>
            <div x-show="showRepeatField" x-cloak class="mb-3">
                <select x-model="eventRepeat" class="w-full px-3 py-1.5 rounded-[6px] border border-[#d8d4cc] text-[13px] bg-white focus:outline-none focus:border-[#1b6ac9]">
                    <option>Does not repeat</option>
                    <option>Every day</option>
                    <option>Every week</option>
                    <option>Every month</option>
                </select>
            </div>
            <div x-show="showNotesField" x-cloak class="mb-3">
                <textarea x-model="eventNotes" rows="2" placeholder="Add notes or details about this event..." class="w-full px-3 py-1.5 rounded-[6px] border border-[#d8d4cc] text-[13px] focus:outline-none focus:border-[#1b6ac9]"></textarea>
            </div>

            <!-- Bottom Submit & Cancel Buttons -->
            <div class="flex items-center gap-2 pt-1">
                <button type="button"
                        @click="postEvent()"
                        class="px-4 py-2 rounded-[6px] bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-semibold text-[14px] transition-colors cursor-pointer">
                    Post this event
                </button>
                <button type="button"
                        @click="newEventOpen = false"
                        class="px-3.5 py-2 rounded-[6px] border border-[#e5e2dc] bg-white text-[#1b6ac9] hover:bg-[#fbfaf8] font-medium text-[14px] transition-colors cursor-pointer">
                    Cancel
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
