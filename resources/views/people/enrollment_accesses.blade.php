@extends('layouts.app')

@section('title', 'Decide what ' . $invitedName . ' can access - Basecamp')

@section('content')
<div x-data="{
        checkedIds: {{ Js::from($initialCheckedProjectIds) }},
        allIds: {{ Js::from(collect($projectRows)->pluck('id')->values()) }},
        toggleProject(id) {
            const idx = this.checkedIds.indexOf(id);
            if (idx === -1) {
                this.checkedIds.push(id);
            } else {
                this.checkedIds.splice(idx, 1);
            }
        },
        checkAll() {
            this.checkedIds = this.allIds.slice();
        },
        checkNone() {
            this.checkedIds = [];
        }
    }"
    class="max-w-[820px] mx-auto pb-20 font-sans text-[#1d2d35] relative">

    <!-- Floating Bottom-Left "↑ Back to top" Pill (Matches media_1793293083868_3f28ac8c.jpg) -->
    <button type="button"
            @click="window.scrollTo({top: 0, behavior: 'smooth'})"
            class="hidden lg:inline-flex fixed bottom-11 left-8 z-30 items-center gap-1 px-2.5 py-1 bg-[#f2f0ec] hover:bg-[#e6e3dd] text-[#1d2d35] text-[12px] font-normal rounded-[6px] shadow-[0_1px_2px_rgba(0,0,0,0.05)] transition-colors cursor-pointer">
        <span>&uarr;</span>
        <span>Back to top</span>
    </button>

    <!-- Basecamp Sheet Container (Exact Match to media_1793293075361_d77d4b45.jpg & media_1793293083868_3f28ac8c.jpg) -->
    <div class="bc-sheet bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-84px)] overflow-visible">

        <!-- Top Breadcrumb Bar: Back to Everyone on the account -->
        <div class="px-6 py-2.5 border-b border-[#ece9e2] flex items-center text-[13.5px]">
            <a href="{{ route('people.index') }}" class="font-bold text-[#1d2d35] hover:underline">
                Back to Everyone on the account
            </a>
        </div>

        <form action="{{ route('enrollments.accesses.store') }}" method="POST" class="px-8 sm:px-12 pt-8 pb-10">
            @csrf
            <input type="hidden" name="user_id" value="{{ $invitedUserId }}">
            <input type="hidden" name="identifier" value="{{ $identifier }}">
            <template x-for="pid in checkedIds" :key="'in-' + pid">
                <input type="hidden" name="project_ids[]" :value="pid">
            </template>

            <!-- Heading & Subtitle -->
            <h1 class="text-[28px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-[1.15]">
                What can {{ $invitedName }} access?
            </h1>
            <p class="text-[15.5px] text-[#1d2d35] mt-1">
                Check off the projects they should be able to access.
            </p>

            <!-- Grant access / Check all • Check none Header Row -->
            <div class="mt-4 pb-2.5 border-b border-[#ece9e2] flex items-end justify-end">
                <div class="w-[150px] text-center leading-tight">
                    <div class="text-[12px] font-bold text-[#1d2d35]">Grant access</div>
                    <div class="text-[12px] mt-0.5">
                        <button type="button"
                                @click="checkAll()"
                                class="text-[#60a5fa] underline hover:opacity-80 cursor-pointer">Check all</button>
                        <span class="text-[#6d6a65] mx-0.5">&bull;</span>
                        <button type="button"
                                @click="checkNone()"
                                class="text-[#60a5fa] underline hover:opacity-80 cursor-pointer">Check none</button>
                    </div>
                </div>
            </div>

            <!-- Projects List -->
            <div class="divide-y divide-[#ece9e2]">
                @foreach($projectRows as $proj)
                    <div @click="toggleProject({{ $proj['id'] }})"
                         class="py-3 flex items-center justify-between gap-4 cursor-pointer select-none">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center flex-wrap gap-1.5">
                                <span class="text-[15px] font-bold text-[#1d2d35]">{{ $proj['name'] }}</span>
                                @if(!empty($proj['isClientProject']))
                                    <span class="px-2 py-0.5 rounded-[4px] bg-[#7c6238] text-[#f5ead8] text-[11px] font-bold leading-none">
                                        Client project
                                    </span>
                                @endif
                            </div>
                            <div class="text-[12px] text-[#6d6a65] mt-0.5">
                                {{ $proj['subtitle'] }}
                            </div>
                        </div>

                        <!-- Right Checkbox Column (Centered under Grant access) -->
                        <div class="w-[150px] flex items-center justify-center flex-shrink-0">
                            <span class="w-[18px] h-[18px] rounded-[4px] flex items-center justify-center transition-colors border"
                                  :class="checkedIds.includes({{ $proj['id'] }})
                                      ? 'bg-[#4ade80] border-[#4ade80] text-[#052e16]'
                                      : 'bg-transparent border-[#6d7a84]'">
                                <svg x-show="checkedIds.includes({{ $proj['id'] }})"
                                     class="w-3.5 h-3.5"
                                     fill="none"
                                     stroke="#052e16"
                                     stroke-width="3.2"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Sticky Bottom "What happens next?" Callout Box (Exact Match to media_1793293075361_d77d4b45.jpg) -->
            <div class="sticky bottom-11 z-20 mt-6 rounded-[10px] bg-[#f6f5f1] border border-[#ece9e2] px-6 py-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 shadow-[0_4px_16px_rgba(0,0,0,0.16)]">
                <div class="text-[13.5px] text-[#1d2d35] leading-relaxed">
                    <div class="font-bold text-[14.5px] mb-0.5">What happens next?</div>
                    <p>
                        We&rsquo;ll send {{ $firstName }} a single email listing all the projects you&rsquo;ve added them to. They will then be able to see everything in those projects, start posting, and interact with the rest of the team. If they haven&rsquo;t signed into Basecamp before, they&rsquo;ll get instructions on how to join.
                    </p>
                </div>

                <button type="submit"
                        class="px-4 py-2.5 rounded-[6px] bg-[#60a5fa] hover:bg-[#3b82f6] text-[#0f172a] font-semibold text-[13.5px] whitespace-nowrap transition-colors cursor-pointer flex-shrink-0">
                    Save changes for {{ $firstName }}
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
