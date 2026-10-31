@extends('layouts.app')

@section('title', 'Email invites sent - ' . $project->name . ' - Basecamp')

@section('content')
<div x-data="{
        enrollees: {{ Js::from($enrollees) }},
        openMenuIndex: null,
        toggleMenu(idx) {
            this.openMenuIndex = (this.openMenuIndex === idx ? null : idx);
        },
        selectRole(idx, roleType) {
            this.enrollees[idx].role_type = roleType;
            this.openMenuIndex = null;
        }
    }"
    class="max-w-[760px] mx-auto pb-20 font-sans text-[#1d2d35]">

    <!-- Basecamp Sheet Container (Exact Match to media_1793435555961_3e33672d.jpg) -->
    <div class="bc-sheet bg-white rounded-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-140px)] overflow-visible">

        <!-- Top Breadcrumb Bar: Project Name -->
        <div class="px-6 py-2.5 border-b border-[#ece9e2] flex items-center text-[13.5px]">
            <a href="{{ route('projects.people', $project->id) }}" class="font-bold text-[#1d2d35] hover:underline">
                {{ $project->name }}
            </a>
        </div>

        <!-- Main Sheet Content -->
        <div class="px-8 sm:px-14 pt-9 pb-24">

            <!-- Heading: Email invites sent -->
            <h1 class="text-[28px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-[1.15] mb-3">
                Email invites sent
            </h1>

            <!-- Description Paragraph -->
            <p class="text-[16px] sm:text-[17px] text-[#1d2d35] leading-[1.45] max-w-[630px]">
                Since this person is new to your Basecamp account, we&rsquo;ve added them as an outside collaborator. If they work at {{ $orgName }}, you can switch them to an employee below:
            </p>

            <form method="POST" action="{{ route('projects.invitations.enrollees.update', $project->id) }}">
                @csrf

                <!-- Enrollees Boxed Card -->
                <div class="mt-6 mb-7 rounded-[8px] border border-[#e2dfd9] bg-white px-6 sm:px-8 py-7 space-y-5">
                    <template x-for="(person, idx) in enrollees" :key="person.email + '-' + idx">
                        <div class="flex items-center justify-between gap-3 relative">
                            <input type="hidden" :name="'enrollees[' + idx + '][id]'" :value="person.id">
                            <input type="hidden" :name="'enrollees[' + idx + '][email]'" :value="person.email">
                            <input type="hidden" :name="'enrollees[' + idx + '][name]'" :value="person.name">
                            <input type="hidden" :name="'enrollees[' + idx + '][role_type]'" :value="person.role_type">

                            <!-- Left: Email Address -->
                            <span class="text-[15px] font-bold text-[#1d2d35] flex-shrink-0" x-text="person.email"></span>

                            <!-- Middle: Dotted Connector Line -->
                            <div class="flex-1 border-b border-dotted border-[#8c969e] mx-2 translate-y-[1px]"></div>

                            <!-- Right: Role Selector Button + Floating Popover (Matches media_1793435555961_3e33672d.jpg) -->
                            <div class="relative flex-shrink-0" @click.outside="if (openMenuIndex === idx) openMenuIndex = null">
                                <button type="button"
                                        @click="toggleMenu(idx)"
                                        class="px-3.5 py-1.5 rounded-[6px] border border-[#d8d4cc] bg-white hover:bg-[#f9f8f6] text-[13.5px] text-[#1d2d35] inline-flex items-center gap-1.5 shadow-2xs cursor-pointer">
                                    <span x-text="person.role_type === 'employee' ? '{{ $orgName }}' : 'Outside collaborator'"></span>
                                    <svg class="w-3.5 h-3.5 text-[#6d6a65]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <!-- Floating Popover Menu -->
                                <div x-show="openMenuIndex === idx"
                                     x-cloak
                                     class="absolute right-0 bottom-[calc(100%+10px)] w-[300px] rounded-[8px] border border-[#e2dfd9] bg-white shadow-[0_8px_28px_rgba(0,0,0,0.16)] p-4 z-50 text-left select-none">

                                    <!-- Option 1: Outside collaborator -->
                                    <button type="button"
                                            @click="selectRole(idx, 'outside_collaborator')"
                                            class="w-full flex items-start gap-2.5 text-left rounded-[6px] p-1.5 hover:bg-[#f6f5f1] transition-colors cursor-pointer">
                                        <span class="w-4 pt-0.5 flex-shrink-0 text-[#1d2d35]">
                                            <svg x-show="person.role_type === 'outside_collaborator'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-[13px] font-bold text-[#1d2d35] leading-snug">
                                                An outside collaborator, partner, contractor, guest, etc.
                                            </div>
                                            <div class="text-[12.5px] text-[#6d6a65] leading-[1.38] mt-1">
                                                They don&rsquo;t work at {{ $orgName }} but can collaborate on projects with you.
                                            </div>
                                        </div>
                                    </button>

                                    <!-- Option 2: Someone who works at Ajath Infotech Pvt Ltd -->
                                    <button type="button"
                                            @click="selectRole(idx, 'employee')"
                                            class="w-full flex items-start gap-2.5 text-left rounded-[6px] p-1.5 mt-3 hover:bg-[#f6f5f1] transition-colors cursor-pointer">
                                        <span class="w-4 pt-0.5 flex-shrink-0 text-[#1d2d35]">
                                            <svg x-show="person.role_type === 'employee'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-[13px] font-bold text-[#1d2d35] leading-snug">
                                                Someone who works at {{ $orgName }}
                                            </div>
                                            <div class="text-[12.5px] text-[#6d6a65] leading-[1.38] mt-1">
                                                Full-time, part-time, or a volunteer at {{ $orgName }}? They can create projects, add people to projects, and become administrators.
                                            </div>
                                        </div>
                                    </button>

                                    <!-- Downward Pointer Arrow -->
                                    <div class=" -bottom-2 right-6 w-3.5 h-3.5 bg-white border-r border-b border-[#e2dfd9] rotate-45 absolute"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Action Buttons: Save changes | Skip for now -->
                <div class="flex items-center gap-2.5">
                    <button type="submit"
                            class="px-4 py-2 rounded-[6px] bg-[#198744] hover:bg-[#146c36] text-white font-semibold text-[14px] transition-colors cursor-pointer">
                        Save changes
                    </button>
                    <a href="{{ route('projects.people', $project->id) }}"
                       class="px-4 py-2 rounded-[6px] border border-[#b8c7d9] bg-white hover:bg-[#f6f5f1] text-[#1b6ac9] font-medium text-[14px] transition-colors">
                        Skip for now
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

