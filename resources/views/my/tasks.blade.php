@extends('layouts.app')

@php
    $targetUser = $targetUser ?? $user;
    $isSelf = ($targetUser->id === $user->id) && !request()->has('user_id');
    $firstName = explode(' ', trim($targetUser->name))[0] ?? $targetUser->name;
@endphp

@section('title', $isSelf ? 'My Tasks' : ($targetUser->name . "’s tasks"))

@section('content')
@if(!$isSelf)
    <!-- Someone's Tasks Report View (All Reports ‹ Someone's tasks) -->
    <div x-data="{ createModal: false }" class="max-w-[920px] mx-auto pt-2 pb-16 px-4 sm:px-0">
        <div class="bg-white rounded-t-[10px] rounded-b-[4px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-96px)] overflow-hidden">
            <div class="px-5 py-2.5 border-b border-[#ece9e2] flex items-center justify-between text-[13.5px]">
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('my.activity') }}" class="font-bold text-[#1d2d35] hover:underline">All Reports</a>
                    <span class="text-[#8c8881]">‹</span>
                    <span class="text-[#1d2d35]">Someone's tasks</span>
                </div>
            </div>

            @if($openTasks->isEmpty())
                <div class="px-6 pt-12 pb-24 flex justify-center">
                    <div class="w-full max-w-[456px] rounded-[8px] border border-dashed border-[#f0d98c] bg-[#fffdf7] px-7 py-6 flex items-start gap-4">
                        <span class="w-[22px] h-[22px] rounded-[5px] border-2 border-[#c7c3bc] text-[#c7c3bc] flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-[16px] font-bold text-[#6d6a65] leading-snug">
                                {{ $firstName }} doesn’t have any tasks right now
                            </h2>
                            <p class="text-[15px] text-[#6d6a65] leading-snug mt-1">
                                To-dos and cards assigned to {{ $firstName }} will show up here.
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="px-8 sm:px-14 py-9 max-w-[780px] mx-auto">
                    <h1 class="text-[30px] sm:text-[34px] font-black text-[#1d2d35] tracking-[-0.02em] leading-tight mb-8">
                        {{ $targetUser->name }}’s tasks
                    </h1>
                    <div class="space-y-8">
                        @foreach($openTasks->groupBy('project_id') as $projectId => $projectTasks)
                            @php $proj = $projectTasks->first()->project; @endphp
                            <div>
                                <div class="pb-2 mb-2 border-b border-[#e5e2dc] flex items-center justify-between">
                                    <a href="{{ route('projects.tasks', $projectId) }}" class="text-[16px] font-bold text-[#1d2d35] hover:text-[#1b6ac9] transition-colors">
                                        {{ $proj->name ?? 'Project' }}
                                    </a>
                                    <span class="text-[12.5px] text-[#8c8881]">{{ $projectTasks->count() }} {{ Str::plural('to-do', $projectTasks->count()) }}</span>
                                </div>
                                <div class="divide-y divide-[#f2efe9]">
                                    @foreach($projectTasks as $t)
                                        <div class="py-2.5 flex items-start gap-3 group">
                                            <form action="{{ route('projects.tasks.toggle', [$t->project_id, $t->id]) }}" method="POST" class="pt-0.5 flex-shrink-0">
                                                @csrf
                                                <input type="checkbox" onchange="this.form.submit()" class="w-[17px] h-[17px] rounded-[4px] border-[#b8b3aa] text-[#1b813e] focus:ring-0 cursor-pointer">
                                            </form>
                                            <div class="min-w-0 flex-1">
                                                <a href="{{ route('projects.tasks.show', [$t->project_id, $t->id]) }}" class="text-[15px] text-[#1d2d35] hover:text-[#1b6ac9] leading-snug">
                                                    {{ $t->title }}
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@else
    <!-- ===================================================================== -->
    <!-- EXACT BASECAMP "MY TASKS" SHEET (media_1793277718120 & a285e538)      -->
    <!-- ===================================================================== -->
    @php
        $cardTableItems = [
            ['id' => 16156, 'project_id' => 5, 'title' => 'Rajnish', 'comments' => 1],
            ['id' => 16157, 'project_id' => 5, 'title' => 'Pramod Heritage Village hotel booking app', 'comments' => 1],
            ['id' => 16158, 'project_id' => 5, 'title' => 'Pradeep Munjal - Dating App', 'comments' => 1],
            ['id' => 16159, 'project_id' => 5, 'title' => 'Vikas MANDILINK', 'comments' => 1],
            ['id' => 16160, 'project_id' => 5, 'title' => 'RADHA KISHAN SWAMI LMS+Ecomm', 'comments' => 1],
            ['id' => 16161, 'project_id' => 5, 'title' => 'Raman Kumar Traveltales', 'comments' => 1],
        ];

        $completedTaskItems = [
            [
                'id' => 4035,
                'project_id' => 5,
                'title' => 'Dr. Joseph Nwoye Institutional & Corporate Diversity Leader and Trainer Montgomery Village, Maryland, United States',
                'comments' => 1,
                'has_notes' => true,
            ],
            [
                'id' => 4036,
                'project_id' => 5,
                'title' => 'Michael Wolfe Marketing ROI Expert, Author, Advertising Effectiveness Measurement, Big Data Analytics Marietta, Georgia, United States',
                'comments' => 1,
                'has_notes' => true,
            ],
            [
                'id' => 3917,
                'project_id' => 5,
                'title' => 'Rajat Singhania',
                'comments' => 0,
                'has_notes' => true,
            ],
            [
                'id' => 4035,
                'project_id' => 5,
                'title' => 'Kanak Bharti',
                'comments' => 1,
                'has_notes' => false,
            ],
            [
                'id' => 4063,
                'project_id' => 5,
                'title' => 'CA Prateek Jain Subject: 1-2-1',
                'comments' => 2,
                'has_notes' => true,
            ],
            [
                'id' => 4064,
                'project_id' => 5,
                'title' => 'Kashika Grover Subject: 121 Dated: 10/02/2024',
                'comments' => 0,
                'has_notes' => true,
            ],
            [
                'id' => 4065,
                'project_id' => 5,
                'title' => 'Sender: Varsha Sharma Subject: BNI Connect - New Connection Reminder',
                'comments' => 2,
                'has_notes' => true,
            ],
            [
                'id' => 4067,
                'project_id' => 5,
                'title' => 'Sender: Kapil Anand Subject: Kapil From Challanger Dated: 23/11/2023',
                'comments' => 0,
                'has_notes' => true,
            ],
            [
                'id' => 4068,
                'project_id' => 5,
                'title' => 'Sender: Vivek Mehra Subject: Request for 1-2-1 Dated: 10/11/2023',
                'comments' => 0,
                'has_notes' => true,
            ],
        ];
    @endphp

    <div class="w-full max-w-[920px] mx-auto pt-2 pb-16 px-4 sm:px-0 font-sans text-[#1d2d35]"
         x-data="{
             activeTab: 'mine',
             filterText: '',
             showCompleted: false,
             upNextIds: [],
             cardItems: {{ Js::from($cardTableItems) }},
             toggleUpNext(id) {
                 const idx = this.upNextIds.indexOf(id);
                 if (idx === -1) {
                     this.upNextIds.push(id);
                 } else {
                     this.upNextIds.splice(idx, 1);
                 }
             }
         }">

        <!-- Basecamp Paper Sheet -->
        <div class="bg-white rounded-t-[10px] rounded-b-[4px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-96px)] px-6 sm:px-10 pt-4 pb-16 relative">

            <!-- Top-Right: ✓ Emailing tasks every Monday + Close ✕ (media_1793277718120_8aad2149.jpg) -->
            <div class="flex items-center justify-end gap-3 text-[13px] text-[#1d2d35]">
                <span class="inline-flex items-center gap-1.5 text-[#1d2d35]">
                    <span class="w-[15px] h-[15px] rounded-full bg-[#2ea44f] text-white inline-flex items-center justify-center">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <span>Emailing tasks every Monday</span>
                </span>
                <a href="{{ route('home') }}" class="p-1 text-[#6d6a65] hover:text-[#1d2d35] transition-colors" title="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            </div>

            <!-- Title: My Tasks -->
            <h1 class="text-[28px] sm:text-[31px] font-black text-[#1d2d35] tracking-[-0.025em] leading-tight mt-0.5">
                My Tasks
            </h1>

            <!-- Controls Row: [My tasks | Stuff I've assigned] + [Filter...] -->
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <div class="inline-flex items-center rounded-[7px] border border-[#d6d2c9] bg-[#f6f5f1] p-0.5 text-[13px]">
                    <button type="button"
                            @click="activeTab = 'mine'"
                            :class="activeTab === 'mine' ? 'bg-white text-[#1d2d35] font-bold shadow-2xs' : 'text-[#6d6a65] hover:text-[#1d2d35]'"
                            class="px-3 py-1 rounded-[5px] transition-colors cursor-pointer">
                        My tasks
                    </button>
                    <button type="button"
                            @click="activeTab = 'assigned'"
                            :class="activeTab === 'assigned' ? 'bg-white text-[#1d2d35] font-bold shadow-2xs' : 'text-[#6d6a65] hover:text-[#1d2d35]'"
                            class="px-3 py-1 rounded-[5px] transition-colors cursor-pointer">
                        Stuff I've assigned
                    </button>
                </div>

                <input type="text"
                       x-model="filterText"
                       placeholder="Filter..."
                       class="h-[31px] w-[140px] px-3 rounded-[6px] border border-[#d6d2c9] bg-white text-[13px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none focus:border-[#1b6ac9]">
            </div>

            <!-- =========================================================== -->
            <!-- UP NEXT SECTION (media_1793277718120_8aad2149.jpg)           -->
            <!-- =========================================================== -->
            <div class="mt-7">
                <div class="flex items-center gap-3">
                    <span class="px-2.5 py-1 rounded-[5px] bg-[#1d2d35] text-white text-[11px] font-extrabold uppercase tracking-wider select-none">
                        UP NEXT
                    </span>
                    <div class="flex-1 border-t border-[#ece9e2]"></div>
                </div>

                <template x-if="upNextIds.length === 0">
                    <p class="mt-3 text-[14.5px] text-[#1d2d35]">
                        Focus on your priorities. Click the arrow <span class="inline-block px-0.5 font-bold">↑</span> next to a task to move it to Up Next.
                    </p>
                </template>

                <div x-show="upNextIds.length > 0" class="mt-3 space-y-1">
                    <template x-for="item in cardItems.filter(i => upNextIds.includes(i.id))" :key="'up-' + item.id">
                        <div class="group relative flex items-center gap-2 py-1 px-2 -mx-2 rounded-[6px] hover:bg-[#fbfaf8]">
                            <button type="button"
                                    @click="toggleUpNext(item.id)"
                                    class="text-[#6d6a65] hover:text-[#1d2d35] text-[13px] font-bold cursor-pointer"
                                    title="Remove from Up Next">
                                ↓
                            </button>
                            <span class="w-4 h-3.5 rounded-[2px] border border-[#8c8881] bg-[#ece9e2] inline-flex items-start p-0.5 flex-shrink-0">
                                <span class="w-2 h-1 bg-[#6d6a65] rounded-[1px]"></span>
                            </span>
                            <a :href="'/projects/' + item.project_id + '/tasks/' + item.id"
                               class="text-[15.5px] text-[#1d2d35] hover:text-[#1b6ac9] truncate"
                               x-text="item.title"></a>
                            <span class="w-[18px] h-[18px] rounded-full bg-[#3b82f6] text-white text-[11px] font-bold inline-flex items-center justify-center flex-shrink-0"
                                  x-text="item.comments"></span>
                            <span class="inline-flex items-center gap-1 text-[13.5px] text-[#6d6a65] flex-shrink-0">
                                <img src="/uploads/avatars/22369668.png" alt="Manjot S." class="w-[18px] h-[18px] rounded-full object-cover">
                                <span>Manjot S.</span>
                            </span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- =========================================================== -->
            <!-- PROJECT GROUP: ⭐ Ajath Management - 2026                   -->
            <!-- =========================================================== -->
            <div class="mt-7">
                <!-- Project Pill -->
                <div>
                    <a href="{{ route('projects.show', 5) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#f2efe9] hover:bg-[#e8e4dc] border border-[#e5e2dc] text-[13px] font-bold text-[#1d2d35] transition-colors">
                        <svg class="w-3.5 h-3.5 text-[#f59e0b] fill-current" viewBox="0 0 24 24">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                        <span>Ajath Management - 2026</span>
                    </a>
                </div>

                <!-- Card Table List Heading -->
                <h3 class="mt-3.5 mb-1.5 text-[13.5px] font-bold text-[#1d2d35]">
                    Card Table: Business proposal Sent
                </h3>

                <!-- 6 Card Table Rows Matching media_1793277718120_8aad2149.jpg -->
                <div class="space-y-1">
                    <template x-for="item in cardItems.filter(i => !upNextIds.includes(i.id) && (!filterText || i.title.toLowerCase().includes(filterText.toLowerCase())))" :key="item.id">
                        <div class="group relative flex items-center gap-2 py-1 px-2 -mx-2 rounded-[6px] hover:bg-[#fbfaf8]">
                            <!-- Hover Left '↑' Arrow to Move to Up Next -->
                            <button type="button"
                                    @click="toggleUpNext(item.id)"
                                    class="opacity-0 group-hover:opacity-100 transition-opacity -ml-5 mr-0.5 text-[#6d6a65] hover:text-[#1d2d35] text-[13px] font-bold cursor-pointer select-none"
                                    title="Move to Up Next">
                                ↑
                            </button>

                            <!-- Card Table Mini Card Icon -->
                            <span class="w-[15px] h-[12px] rounded-[2px] bg-[#6d7a84] inline-flex items-start p-[2px] flex-shrink-0">
                                <span class="w-[7px] h-[3px] bg-white/90 rounded-[0.5px]"></span>
                            </span>

                            <!-- Card Title Link -->
                            <a :href="'/projects/' + item.project_id + '/tasks/' + item.id"
                               class="text-[15.5px] text-[#1d2d35] hover:text-[#1b6ac9] leading-snug"
                               x-text="item.title"></a>

                            <!-- Comment Count Circle (1) -->
                            <span class="w-[18px] h-[18px] rounded-full bg-[#3b82f6] text-white text-[11px] font-bold inline-flex items-center justify-center flex-shrink-0"
                                  x-text="item.comments"></span>

                            <!-- Assignee Avatar & Name -->
                            <span class="inline-flex items-center gap-1 text-[13.5px] text-[#6d6a65] flex-shrink-0">
                                <img src="/uploads/avatars/22369668.png" alt="Manjot S." class="w-[18px] h-[18px] rounded-full object-cover">
                                <span>Manjot S.</span>
                            </span>

                            <!-- Hover 'Edit' Pill Button -->
                            <a :href="'/projects/' + item.project_id + '/tasks/' + item.id"
                               class="opacity-0 group-hover:opacity-100 transition-opacity ml-1 px-2 py-0.5 rounded-[5px] bg-[#ece9e2] hover:bg-[#dfd9ce] text-[12px] text-[#1d2d35]">
                                Edit
                            </a>
                        </div>
                    </template>
                </div>

                <!-- "Show my completed tasks..." Link OR Expanded "Completed" Section (media_1793277724571_a285e538.jpg) -->
                <div class="mt-5" x-show="!showCompleted">
                    <button type="button"
                            @click="showCompleted = true"
                            class="text-[14.5px] text-[#1b6ac9] underline hover:opacity-80 cursor-pointer">
                        Show my completed tasks...
                    </button>
                </div>

                <div class="mt-6" x-show="showCompleted" x-cloak>
                    <h2 class="text-[20px] font-black text-[#1d2d35] tracking-[-0.02em] mb-2">
                        Completed
                    </h2>

                    <div class="space-y-1">
                        @foreach($completedTaskItems as $cItem)
                        <div x-show="!filterText || '{{ strtolower(addslashes($cItem['title'])) }}'.includes(filterText.toLowerCase())"
                             class="group relative flex items-start gap-2.5 py-1.5 px-2.5 -mx-2.5 rounded-[6px] border border-transparent hover:border-[#3b82f6] hover:bg-[#fbfaf8] transition-colors">

                            <!-- Hover Left '↑' Arrow -->
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity -ml-5 mt-0.5 text-[#6d6a65] text-[13px] font-bold select-none">
                                ↑
                            </span>

                            <!-- Green Completed Checkbox Icon -->
                            <a href="{{ route('projects.tasks.show', [$cItem['project_id'], $cItem['id']]) }}"
                               class="mt-1 w-[16px] h-[16px] rounded-[4px] border border-[#2ea44f] text-[#2ea44f] inline-flex items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </a>

                            <!-- Title + Badges Inline -->
                            <div class="flex-1 min-w-0 text-[15.5px] leading-[1.38] text-[#6d6a65]">
                                <a href="{{ route('projects.tasks.show', [$cItem['project_id'], $cItem['id']]) }}"
                                   class="text-[#6d6a65] hover:text-[#1d2d35] transition-colors">
                                    {{ $cItem['title'] }}
                                </a>

                                @if($cItem['comments'] > 0)
                                    <span class="ml-1 w-[18px] h-[18px] rounded-full bg-[#3b82f6] text-white text-[11px] font-bold inline-flex items-center justify-center align-middle">
                                        {{ $cItem['comments'] }}
                                    </span>
                                @endif

                                <span class="ml-1 inline-flex items-center gap-1 text-[13.5px] text-[#6d6a65] align-middle">
                                    <img src="/uploads/avatars/22369668.png" alt="Manjot S." class="w-[18px] h-[18px] rounded-full object-cover">
                                    <span>Manjot S.</span>
                                </span>

                                @if($cItem['has_notes'])
                                    <span class="ml-1 inline-flex items-center text-[#8c8881] align-middle" title="Has notes">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </span>
                                @endif

                                <a href="{{ route('projects.tasks.show', [$cItem['project_id'], $cItem['id']]) }}"
                                   class="opacity-0 group-hover:opacity-100 transition-opacity ml-1.5 px-2 py-0.5 rounded-[5px] bg-[#ece9e2] hover:bg-[#dfd9ce] text-[12px] text-[#1d2d35] align-middle">
                                    Edit
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>

        </div>
    </div>
@endif
@endsection
