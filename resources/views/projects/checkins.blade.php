@extends('layouts.app')

@section('title', 'Automatic Check-ins - ' . $project->name)

@section('content')
<div x-data="{
    mode: '{{ $isNewQuestion ? 'new' : (request()->filled('question_id') && $checkin ? 'question' : 'index') }}',
    newQuestion: '',
    freqType: 'daily',
    days: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
    allDays: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    timeMode: 'morning',
    customTime: '10:00am',
    selectedPeople: ['ajath', 'gaurav', 'manjot', 'pawandeep', 'prashant', 'rajesh', 'shachish', 'shiv'],
    allPeopleKeys: ['ajath', 'gaurav', 'manjot', 'pawandeep', 'prashant', 'rajesh', 'shachish', 'shiv'],
    toggleDay(d) {
        if (this.freqType === 'daily') {
            if (this.days.includes(d)) {
                if (this.days.length > 1) {
                    this.days = this.days.filter(x => x !== d);
                }
            } else {
                this.days.push(d);
            }
        } else {
            this.days = [d];
        }
    },
    selectAll() {
        this.selectedPeople = [...this.allPeopleKeys];
    },
    deselectAll() {
        this.selectedPeople = [];
    },
    togglePerson(k) {
        if (this.selectedPeople.includes(k)) {
            this.selectedPeople = this.selectedPeople.filter(x => x !== k);
        } else {
            this.selectedPeople.push(k);
        }
    },
    get computedTime() {
        if (this.timeMode === 'morning') return '9:00am';
        if (this.timeMode === 'evening') return '4:30pm';
        return this.customTime;
    }
}" class="max-w-[880px] mx-auto pb-20">

    <!-- Basecamp Sheet -->
    <div class="bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-96px)] overflow-hidden">

        <!-- Top Breadcrumb & Actions Bar -->
        <div class="px-5 py-2.5 border-b border-[#ece9e2] flex items-center justify-between text-[13.5px] bg-white">
            <div class="flex items-center gap-1.5 truncate">
                <a href="{{ route('projects.show', $project->id) }}" class="font-bold text-[#1d2d35] hover:underline">
                    {{ $project->name }}
                </a>
                <template x-if="mode !== 'index'">
                    <div class="flex items-center gap-1.5">
                        <span class="text-[#6d6a65] text-[12px]">‹</span>
                        <a href="{{ route('projects.checkins', $project->id) }}"
                           @click.prevent="mode = 'index'"
                           class="text-[#1d2d35] hover:underline cursor-pointer">
                            Automatic Check-ins
                        </a>
                    </div>
                </template>
            </div>

            <!-- Right: Bookmark & More Menu (Shown on Index & Question View) -->
            <div x-show="mode !== 'new'" class="flex items-center gap-3 text-[#1d2d35] flex-shrink-0">
                <button type="button" onclick="window.toggleBookmark && window.toggleBookmark(this)" class="inline-flex items-center gap-1 text-[13px] text-[#1d2d35] hover:underline cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#4a4640]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                    <span data-bookmark-label>Bookmark</span>
                </button>
                <button type="button" class="p-1 text-[#1d2d35] hover:bg-slate-100 rounded-md transition-colors cursor-pointer" title="More options">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                        <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- VIEW 1: AUTOMATIC CHECK-INS INDEX (Matches media_1793293476244_63b14609) -->
        <!-- ========================================================================= -->
        <div x-show="mode === 'index'" class="px-8 sm:px-14 pt-7 pb-16">
            <h1 class="text-[30px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.02em] leading-[1.15]">
                Automatic Check-ins
            </h1>

            <div class="mt-2.5">
                <a href="{{ route('projects.checkins.questions.new', $project->id) }}"
                   @click.prevent="mode = 'new'"
                   class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-[5px] bg-[#2573d8] hover:bg-[#1e5eb3] text-white font-semibold text-[13.5px] transition-colors cursor-pointer">
                    + New question
                </a>
            </div>

            @if($checkins->isNotEmpty())
                <!-- Existing Check-in Questions List -->
                <div class="mt-8 divide-y divide-[#ece9e2] border-t border-b border-[#ece9e2]">
                    @foreach($checkins as $q)
                        <div class="py-5 flex items-start justify-between gap-4">
                            <div>
                                <a href="{{ route('projects.checkins', ['id' => $project->id, 'question_id' => $q->id]) }}"
                                   class="text-[18px] font-bold text-[#1d2d35] hover:text-[#1b6ac9] hover:underline">
                                    {{ $q->question }}
                                </a>
                                <p class="text-[13.5px] text-[#6d6a65] mt-0.5">
                                    {{ $q->frequency }} {{ $q->time ? 'at ' . $q->time : '' }} ·Asking everyone on the project
                                </p>
                                @if($q->answers->isNotEmpty())
                                    <div class="mt-3 space-y-2">
                                        @foreach($q->answers->take(3) as $ans)
                                            <div class="text-[13.5px] text-[#1d2d35] flex items-start gap-2">
                                                <span class="font-bold">{{ $ans->user->name ?? 'Team Member' }}:</span>
                                                <span class="text-[#4a4640]">{{ $ans->answer }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('projects.checkins', ['id' => $project->id, 'question_id' => $q->id]) }}"
                               class="px-3.5 py-1.5 rounded-full border border-[#d8d4cc] text-[13px] font-medium text-[#1d2d35] hover:bg-[#fbfaf8] flex-shrink-0">
                                Answer / View ({{ $q->answers->count() }})
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Centered Dashed Prompt Box (Exact 1:1 Match to media_1793293476244_63b14609.jpg) -->
            <div class="mt-10 max-w-[440px] mx-auto border border-dashed border-[#c9b99a] rounded-[6px] px-6 py-5 flex items-start gap-3.5">
                <div class="w-[26px] h-[26px] rounded-full bg-[#6d6a65]/25 text-[#1d2d35] font-bold text-[15px] flex items-center justify-center flex-shrink-0 mt-0.5 select-none">
                    ?
                </div>
                <div class="text-[14.5px] leading-[1.45] text-[#1d2d35]">
                    <p>
                        Send questions to your team on a regular schedule to get fresh insights and stay connected. Great for question like:
                    </p>
                    <div class="mt-3.5 space-y-0.5">
                        <div>&ldquo;What did you work on today?&rdquo;</div>
                        <div>&ldquo;Who&rsquo;s done great work this week?&rdquo;</div>
                        <div>&ldquo;What music are you jamming on?&rdquo;</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- VIEW 2: NEW QUESTION FORM (Matches media_1793293482090 & 1793293487212)  -->
        <!-- ========================================================================= -->
        <div x-show="mode === 'new'" x-cloak class="px-8 sm:px-14 pt-8 pb-16">
            <form action="{{ route('projects.checkins.questions.store', $project->id) }}" method="POST">
                @csrf
                <input type="hidden" name="frequency_type" :value="freqType">
                <input type="hidden" name="days" :value="days.join(', ')">
                <input type="hidden" name="time_of_day" :value="computedTime">

                <!-- Type your question... -->
                <div>
                    <input type="text"
                           name="question"
                           x-model="newQuestion"
                           required
                           placeholder="Type your question..."
                           class="w-full text-[30px] sm:text-[34px] font-extrabold text-[#1d2d35] placeholder-[#6d6a65] bg-transparent border-0 focus:outline-none focus:ring-0 p-0 tracking-[-0.02em]">
                </div>

                <!-- 1. How often do you want to ask? -->
                <div class="mt-6">
                    <div class="text-[14.5px] font-bold text-[#1d2d35] mb-3">
                        How often do you want to ask?
                    </div>

                    <div class="space-y-2.5 text-[15px] text-[#1d2d35]">
                        <!-- Option 1: Daily on... -->
                        <div>
                            <label class="inline-flex items-center gap-2.5 cursor-pointer" @click="freqType = 'daily'; if (days.length <= 1) days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri']">
                                <input type="radio" name="freq_radio" value="daily" x-model="freqType" class="w-4 h-4 accent-[#1d2d35]">
                                <span>Daily on...</span>
                            </label>

                            <!-- Day of Week Pill Selector -->
                            <div x-show="freqType === 'daily'" class="mt-2 ml-6 flex flex-wrap items-center gap-1.5">
                                <template x-for="d in allDays" :key="'daily-' + d">
                                    <button type="button"
                                            @click="toggleDay(d)"
                                            :class="days.includes(d) ? 'bg-[#2573d8] text-white border-[#2573d8]' : 'bg-transparent text-[#1d2d35] border-[#6d6a65]/50 hover:border-[#1d2d35]'"
                                            class="px-3.5 py-1.5 rounded-[5px] border text-[13.5px] font-medium transition-colors cursor-pointer min-w-[50px] text-center"
                                            x-text="d"></button>
                                </template>
                            </div>
                        </div>

                        <!-- Option 2: Once a week... -->
                        <div>
                            <label class="inline-flex items-center gap-2.5 cursor-pointer" @click="freqType = 'weekly'; days = ['Mon']">
                                <input type="radio" name="freq_radio" value="weekly" x-model="freqType" class="w-4 h-4 accent-[#1d2d35]">
                                <span>Once a week...</span>
                            </label>
                            <div x-show="freqType === 'weekly'" x-cloak class="mt-2 ml-6 flex flex-wrap items-center gap-1.5">
                                <template x-for="d in allDays" :key="'weekly-' + d">
                                    <button type="button"
                                            @click="toggleDay(d)"
                                            :class="days.includes(d) ? 'bg-[#2573d8] text-white border-[#2573d8]' : 'bg-transparent text-[#1d2d35] border-[#6d6a65]/50 hover:border-[#1d2d35]'"
                                            class="px-3.5 py-1.5 rounded-[5px] border text-[13.5px] font-medium transition-colors cursor-pointer min-w-[50px] text-center"
                                            x-text="d"></button>
                                </template>
                            </div>
                        </div>

                        <!-- Option 3: Every other week... -->
                        <div>
                            <label class="inline-flex items-center gap-2.5 cursor-pointer" @click="freqType = 'biweekly'; days = ['Mon']">
                                <input type="radio" name="freq_radio" value="biweekly" x-model="freqType" class="w-4 h-4 accent-[#1d2d35]">
                                <span>Every other week...</span>
                            </label>
                            <div x-show="freqType === 'biweekly'" x-cloak class="mt-2 ml-6 flex flex-wrap items-center gap-1.5">
                                <template x-for="d in allDays" :key="'biweekly-' + d">
                                    <button type="button"
                                            @click="toggleDay(d)"
                                            :class="days.includes(d) ? 'bg-[#2573d8] text-white border-[#2573d8]' : 'bg-transparent text-[#1d2d35] border-[#6d6a65]/50 hover:border-[#1d2d35]'"
                                            class="px-3.5 py-1.5 rounded-[5px] border text-[13.5px] font-medium transition-colors cursor-pointer min-w-[50px] text-center"
                                            x-text="d"></button>
                                </template>
                            </div>
                        </div>

                        <!-- Option 4: Once a month on the first... -->
                        <div>
                            <label class="inline-flex items-center gap-2.5 cursor-pointer" @click="freqType = 'monthly'; days = ['Mon']">
                                <input type="radio" name="freq_radio" value="monthly" x-model="freqType" class="w-4 h-4 accent-[#1d2d35]">
                                <span>Once a month on the first...</span>
                            </label>
                            <div x-show="freqType === 'monthly'" x-cloak class="mt-2 ml-6 flex flex-wrap items-center gap-1.5">
                                <template x-for="d in allDays" :key="'monthly-' + d">
                                    <button type="button"
                                            @click="toggleDay(d)"
                                            :class="days.includes(d) ? 'bg-[#2573d8] text-white border-[#2573d8]' : 'bg-transparent text-[#1d2d35] border-[#6d6a65]/50 hover:border-[#1d2d35]'"
                                            class="px-3.5 py-1.5 rounded-[5px] border text-[13.5px] font-medium transition-colors cursor-pointer min-w-[50px] text-center"
                                            x-text="d"></button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. At what time of day? -->
                <div class="mt-8">
                    <div class="text-[14.5px] font-bold text-[#1d2d35] mb-3">
                        At what time of day?
                    </div>

                    <div class="space-y-2.5 text-[15px] text-[#1d2d35]">
                        <div>
                            <label class="inline-flex items-center gap-2.5 cursor-pointer">
                                <input type="radio" name="time_radio" value="morning" x-model="timeMode" class="w-4 h-4 accent-[#1d2d35]">
                                <span>Beginning of the day (9:00am)</span>
                            </label>
                        </div>
                        <div>
                            <label class="inline-flex items-center gap-2.5 cursor-pointer">
                                <input type="radio" name="time_radio" value="evening" x-model="timeMode" class="w-4 h-4 accent-[#1d2d35]">
                                <span>End of the day (4:30pm)</span>
                            </label>
                        </div>
                        <div>
                            <label class="inline-flex items-center gap-2.5 cursor-pointer">
                                <input type="radio" name="time_radio" value="custom" x-model="timeMode" class="w-4 h-4 accent-[#1d2d35]">
                                <span>Let me pick a time...</span>
                            </label>
                            <div x-show="timeMode === 'custom'" x-cloak class="mt-2 ml-6">
                                <select x-model="customTime" class="px-3 py-1.5 rounded-[5px] border border-[#d8d4cc] bg-white text-[14px] text-[#1d2d35] focus:outline-none">
                                    <option value="8:00am">8:00am</option>
                                    <option value="9:00am">9:00am</option>
                                    <option value="10:00am">10:00am</option>
                                    <option value="11:00am">11:00am</option>
                                    <option value="12:00pm">12:00pm</option>
                                    <option value="2:00pm">2:00pm</option>
                                    <option value="4:30pm">4:30pm</option>
                                    <option value="5:00pm">5:00pm</option>
                                    <option value="6:00pm">6:00pm</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Who do you want to ask? (Matches media_1793293487212_407aec1e.jpg) -->
                <div class="mt-8">
                    <div class="flex items-center justify-between pb-2.5 border-b border-[#ece9e2]">
                        <span class="text-[14.5px] font-bold text-[#1d2d35]">Who do you want to ask?</span>
                        <div class="text-[12.5px]">
                            <button type="button" @click="selectAll()" class="text-[#1d6ce0] underline cursor-pointer">Select all</button>
                            <span class="text-[#6d6a65] mx-0.5">&bull;</span>
                            <button type="button" @click="deselectAll()" class="text-[#1d6ce0] underline cursor-pointer">Deselect all</button>
                        </div>
                    </div>

                    <div class="divide-y divide-[#ece9e2] border-b border-[#ece9e2]">
                        @foreach($checkinPeople as $person)
                            <div @click="togglePerson('{{ $person['key'] }}')"
                                 class="py-2.5 flex items-center justify-between cursor-pointer select-none hover:bg-[#fbfaf8] -mx-2 px-2 rounded transition-colors">
                                <div class="flex items-center gap-3">
                                    @if(!empty($person['avatar']))
                                        <div class="relative w-[32px] h-[32px] flex-shrink-0">
                                            <img src="{{ $person['avatar'] }}" alt="{{ $person['name'] }}" class="w-[32px] h-[32px] rounded-full object-cover">
                                            @if(!empty($person['out']))
                                                <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 px-1 py-[0.5px] rounded-[3px] bg-[#ffd54f] text-[#1d2d35] font-extrabold text-[7.5px] leading-tight uppercase tracking-wider">OUT</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="w-[32px] h-[32px] rounded-full text-white font-bold text-[12px] flex items-center justify-center flex-shrink-0"
                                              style="background-color: {{ $person['bg'] }}">
                                            {{ $person['initials'] }}
                                        </span>
                                    @endif
                                    <span class="text-[15px] text-[#1d2d35]">{{ $person['name'] }}</span>
                                </div>

                                <!-- Green Checked Box -->
                                <div class="w-[18px] h-[18px] rounded-[3px] flex items-center justify-center transition-colors flex-shrink-0"
                                     :class="selectedPeople.includes('{{ $person['key'] }}') ? 'bg-[#66bb6a] text-[#1d2d35]' : 'border border-[#6d6a65]/60 bg-transparent'">
                                    <svg x-show="selectedPeople.includes('{{ $person['key'] }}')" class="w-3.5 h-3.5 stroke-[2.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Submit Button: Start collecting answers -->
                <div class="mt-7">
                    <button type="submit"
                            class="px-4 py-2 rounded-[5px] bg-[#2573d8] hover:bg-[#1e5eb3] text-white font-medium text-[14.5px] transition-colors cursor-pointer">
                        Start collecting answers
                    </button>
                </div>
            </form>
        </div>

        <!-- ========================================================================= -->
        <!-- VIEW 3: SINGLE QUESTION & ANSWERS THREAD                                  -->
        <!-- ========================================================================= -->
        @if($checkin)
            <div x-show="mode === 'question'" x-cloak class="px-8 sm:px-14 pt-8 pb-16">
                <h1 class="text-[28px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.02em] leading-tight">
                    {{ $checkin->question }}
                </h1>
                <p class="mt-1 text-[14px] text-[#6d6a65]">
                    {{ $checkin->frequency }} {{ $checkin->time ? 'at ' . $checkin->time : '' }}
                </p>

                <form action="{{ route('projects.checkins.store', $project->id) }}" method="POST" class="mt-6 pt-6 border-t border-[#ece9e2] space-y-3">
                    @csrf
                    <input type="hidden" name="checkin_id" value="{{ $checkin->id }}">
                    <textarea name="answer" required rows="4" placeholder="Write your answer..." class="w-full px-4 py-3 bg-white border border-[#d8d4cc] rounded-[8px] text-[15px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none focus:border-[#1b6ac9]"></textarea>
                    <div>
                        <button type="submit" class="px-4 py-1.5 rounded-full bg-[#199849] hover:bg-[#147a3a] text-white font-bold text-[13.5px] cursor-pointer">
                            Post my answer
                        </button>
                    </div>
                </form>

                <div class="mt-8 pt-6 border-t border-[#ece9e2] space-y-5">
                    @forelse($answers as $ans)
                        <div class="flex items-start gap-3.5">
                            <img src="{{ $ans->user->avatar_url && !str_contains($ans->user->avatar_url, 'dicebear') ? $ans->user->avatar_url : '/uploads/avatars/22369668.png' }}"
                                 alt="{{ $ans->user->name }}"
                                 class="w-[38px] h-[38px] rounded-full object-cover flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-[15px] text-[#1d2d35]">{{ $ans->user->name }}</span>
                                    <span class="text-[13px] text-[#6d6a65]">{{ $ans->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="mt-1 text-[15px] text-[#1d2d35] whitespace-pre-line leading-relaxed">{{ $ans->answer }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-[14.5px] text-[#6d6a65]">No answers posted yet.</p>
                    @endforelse
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
