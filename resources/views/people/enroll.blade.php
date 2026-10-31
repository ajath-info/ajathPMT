@extends('layouts.app')

@section('title', 'Invite people to the account')

@section('content')
@php
    $orgName = $org->name ?? 'Ajath Infotech Pvt Ltd';
    $defaultType = $initialInviteType ?? 'employee';
@endphp

<!-- Exact Basecamp /account/enrollments/new, /employees/new, /collaborators/new, /clients/new -->
<div class="w-full px-4 pt-5 pb-16 font-sans text-[#1d2d35]"
     x-data="{
         step: {{ $initialStep ?? 1 }},
         inviteType: '{{ $defaultType }}',
         showNote: false,
         noteText: '',
         people: [
             { id: 1, name: '', email: '', jobTitle: '', company: '{{ $defaultType === 'employee' ? addslashes($orgName) : '' }}', companyOpen: false, companyConfirmed: {{ $defaultType === 'employee' ? 'true' : 'false' }} }
         ],
         knownCompanies: ['Healthline', 'NDC Homes', 'Urban Racks', 'Stalkre', 'Client'],
         goToStep2() {
             this.step = 2;
             this.people.forEach(p => {
                 if (this.inviteType === 'employee') {
                     if (!p.company) p.company = '{{ addslashes($orgName) }}';
                     p.companyConfirmed = true;
                 } else {
                     if (p.company === '{{ addslashes($orgName) }}') p.company = '';
                     p.companyConfirmed = false;
                 }
             });
             const nextPath = this.inviteType === 'client'
                 ? '/account/enrollments/clients/new'
                 : (this.inviteType === 'collaborator' ? '/account/enrollments/collaborators/new' : '/account/enrollments/employees/new');
             history.pushState(null, '', nextPath);
             this.$nextTick(() => {
                 const firstInput = document.getElementById('person-name-0');
                 if (firstInput) firstInput.focus();
             });
         },
         addPerson() {
             this.people.push({
                 id: Date.now() + Math.random(),
                 name: '',
                 email: '',
                 jobTitle: '',
                 company: this.inviteType === 'employee' ? '{{ addslashes($orgName) }}' : '',
                 companyOpen: false,
                 companyConfirmed: this.inviteType === 'employee'
             });
         },
         removePerson(index) {
             if (this.people.length > 1) {
                 this.people.splice(index, 1);
             }
         }
     }">

    <!-- Centered Basecamp Sheet Card -->
    <div class="w-full max-w-[540px] mx-auto bg-white rounded-[8px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.06)] px-7 sm:px-9 py-8">

        <!-- =============================================================== -->
        <!-- STEP 1: "Who are you inviting?" (media_1793278262385_e080745d.jpg) -->
        <!-- =============================================================== -->
        <div x-show="step === 1">
            <h1 class="text-[26px] font-black text-[#1d2d35] tracking-[-0.02em] leading-tight">
                Who are you inviting?
            </h1>
            <p class="mt-1 text-[15px] text-[#1d2d35]">
                First you'll invite them to the account. Then you can add them to projects.
            </p>

            <div class="mt-6 space-y-5">
                <!-- Option 1: Someone who works at Ajath Infotech Pvt Ltd -->
                <label class="flex items-start gap-2.5 cursor-pointer" @click="inviteType = 'employee'">
                    <span class="bc-custom-radio mt-1 w-[16px] h-[16px] rounded-full border-[1.5px] flex items-center justify-center flex-shrink-0 transition-colors"
                          :class="inviteType === 'employee' ? 'is-selected bc-radio-selected border-[#1b6ac9] bg-[#1b6ac9]' : 'bc-radio-unselected border-[#8c969e] bg-transparent'">
                        <span x-show="inviteType === 'employee'" class="bc-custom-radio-dot bc-radio-dot w-[6px] h-[6px] rounded-full" style="background-color: #ffffff !important;"></span>
                    </span>
                    <div>
                        <div class="text-[15.5px] font-bold text-[#1d2d35] leading-snug">
                            Someone who works at {{ $orgName }}
                        </div>
                        <p class="text-[13.5px] text-[#6d6a65] leading-[1.42] mt-0.5">
                            Full-time, part-time, or a volunteer at {{ $orgName }}? They can create projects, add people to projects, and become administrators.
                        </p>
                    </div>
                </label>

                <!-- Option 2: An outside collaborator, partner, contractor, guest, etc. -->
                <label class="flex items-start gap-2.5 cursor-pointer" @click="inviteType = 'collaborator'">
                    <span class="bc-custom-radio mt-1 w-[16px] h-[16px] rounded-full border-[1.5px] flex items-center justify-center flex-shrink-0 transition-colors"
                          :class="inviteType === 'collaborator' ? 'is-selected bc-radio-selected border-[#1b6ac9] bg-[#1b6ac9]' : 'bc-radio-unselected border-[#8c969e] bg-transparent'">
                        <span x-show="inviteType === 'collaborator'" class="bc-custom-radio-dot bc-radio-dot w-[6px] h-[6px] rounded-full" style="background-color: #ffffff !important;"></span>
                    </span>
                    <div>
                        <div class="text-[15.5px] font-bold text-[#1d2d35] leading-snug">
                            An outside collaborator, partner, contractor, guest, etc.
                        </div>
                        <p class="text-[13.5px] text-[#6d6a65] leading-[1.42] mt-0.5">
                            They don't work at {{ $orgName }} but can collaborate on projects with you.
                        </p>
                    </div>
                </label>

                <!-- Option 3: A client you're doing work for -->
                <label class="flex items-start gap-2.5 cursor-pointer" @click="inviteType = 'client'">
                    <span class="bc-custom-radio mt-1 w-[16px] h-[16px] rounded-full border-[1.5px] flex items-center justify-center flex-shrink-0 transition-colors"
                          :class="inviteType === 'client' ? 'is-selected bc-radio-selected border-[#1b6ac9] bg-[#1b6ac9]' : 'bc-radio-unselected border-[#8c969e] bg-transparent'">
                        <span x-show="inviteType === 'client'" class="bc-custom-radio-dot bc-radio-dot w-[6px] h-[6px] rounded-full" style="background-color: #ffffff !important;"></span>
                    </span>
                    <div>
                        <div class="text-[15.5px] font-bold text-[#1d2d35] leading-snug">
                            A client you're doing work for
                        </div>
                        <p class="text-[13.5px] text-[#6d6a65] leading-[1.42] mt-0.5">
                            Clients can access projects you’ve created, but <strong class="font-bold text-[#1d2d35]">they can’t</strong> create their own, invite or add new people, or become admins. You can hide parts of projects from them so they can’t see work in progress.
                        </p>
                    </div>
                </label>
            </div>

            <div class="mt-7">
                <button type="button"
                        @click="goToStep2()"
                        class="bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-semibold text-[14px] px-4 py-2 rounded-[6px] transition cursor-pointer">
                    Next, enter their name...
                </button>
            </div>
        </div>

        <!-- =============================================================== -->
        <!-- STEP 2: Coworker / Collaborator / Client Setup                  -->
        <!-- (Matches media_1793277506670_d559c672.jpg & media_1793278277893_f267a3d4.jpg) -->
        <!-- =============================================================== -->
        <div x-show="step === 2" x-cloak>
            <form action="{{ route('enrollments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="invite_type" :value="inviteType">

                <!-- Dynamic Heading per Invite Type -->
                <h1 class="text-[26px] font-black text-[#1d2d35] tracking-[-0.02em] leading-tight"
                    x-text="inviteType === 'client' ? 'Set up your client\'s account' : (inviteType === 'collaborator' ? 'Set up your collaborator\'s account' : 'Set up your coworker\'s account')">
                </h1>

                <!-- Dynamic Subtitle per Invite Type -->
                <p class="mt-1.5 text-[15px] text-[#1d2d35] leading-[1.42]"
                   x-show="inviteType === 'employee'">
                    Full-time, part-time, or a volunteer at {{ $orgName }}? They can create projects, add people to projects, and become administrators.
                </p>
                <p class="mt-1.5 text-[15px] text-[#1d2d35] leading-[1.42]"
                   x-show="inviteType === 'collaborator'" x-cloak>
                    They don't work at {{ $orgName }} but can collaborate on projects with you.
                </p>
                <p class="mt-1.5 text-[15px] text-[#1d2d35] leading-[1.42]"
                   x-show="inviteType === 'client'" x-cloak>
                    Clients can access projects you’ve created, but they can’t create their own, invite or add new people, or become admins. You can hide parts of projects from them so they can’t see work in progress.
                </p>

                <!-- Repeatable 2x2 Person Blocks -->
                <div class="mt-6 space-y-5">
                    <template x-for="(person, idx) in people" :key="person.id">
                        <div class="relative grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <!-- Top-Right Circular Remove '✕' Button when multiple blocks exist -->
                            <button type="button"
                                    x-show="people.length > 1"
                                    @click="removePerson(idx)"
                                    class="absolute -top-2.5 -right-2.5 w-5 h-5 rounded-full bg-white border border-[#d6d2c9] hover:border-[#1d2d35] text-[#1d2d35] flex items-center justify-center text-[10px] font-bold shadow-2xs z-10 cursor-pointer"
                                    title="Remove person">
                                ✕
                            </button>

                            <!-- Full name -->
                            <div>
                                <input type="text"
                                       :id="'person-name-' + idx"
                                       :name="'people[' + idx + '][name]'"
                                       x-model="person.name"
                                       placeholder="Full name"
                                       required
                                       class="w-full h-[42px] px-3 bg-white border border-[#d6d2c9] focus:border-[1.5px] focus:border-[#1b6ac9] rounded-[6px] text-[14px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none">
                            </div>

                            <!-- Email address -->
                            <div>
                                <input type="email"
                                       :name="'people[' + idx + '][email]'"
                                       x-model="person.email"
                                       placeholder="Email address"
                                       required
                                       class="w-full h-[42px] px-3 bg-white border border-[#d6d2c9] focus:border-[1.5px] focus:border-[#1b6ac9] rounded-[6px] text-[14px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none">
                            </div>

                            <!-- Job title (optional) -->
                            <div>
                                <input type="text"
                                       :name="'people[' + idx + '][job_title]'"
                                       x-model="person.jobTitle"
                                       placeholder="Job title (optional)"
                                       class="w-full h-[42px] px-3 bg-white border border-[#d6d2c9] focus:border-[1.5px] focus:border-[#1b6ac9] rounded-[6px] text-[14px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none">
                            </div>

                            <!-- Company/organization: Floating Label OR Basecamp Interactive Combobox (media_1793278287851_79a309d7.jpg) -->
                            <div class="relative" @click.outside="person.companyOpen = false">
                                <template x-if="inviteType === 'employee' || person.companyConfirmed">
                                    <div @click="if (inviteType !== 'employee') { person.companyConfirmed = false; person.companyOpen = true; }"
                                         class="h-[42px] px-3 py-1 bg-white border border-[#d6d2c9] focus-within:border-[1.5px] focus-within:border-[#1b6ac9] rounded-[6px] flex flex-col justify-center relative cursor-text">
                                        <span class="text-[9.5px] text-[#6d6a65] leading-tight">Company/organization</span>
                                        <input type="text"
                                               :name="'people[' + idx + '][company]'"
                                               x-model="person.company"
                                               class="w-full text-[13.5px] text-[#1d2d35] bg-transparent border-0 p-0 pr-5 leading-tight focus:outline-none">
                                        <span x-show="inviteType !== 'employee'" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-[#6d6a65] text-[11px] leading-none">
                                            ⬍
                                        </span>
                                    </div>
                                </template>

                                <template x-if="inviteType !== 'employee' && !person.companyConfirmed">
                                    <div class="relative">
                                        <input type="text"
                                               :name="'people[' + idx + '][company]'"
                                               x-model="person.company"
                                               @focus="person.companyOpen = true"
                                               @input="person.companyOpen = true"
                                               @keydown.enter.prevent="if (person.company.trim()) { person.companyConfirmed = true; person.companyOpen = false; }"
                                               placeholder="Type company/org name..."
                                               autocomplete="off"
                                               class="w-full h-[42px] pl-3 pr-7 bg-white border border-[#d6d2c9] focus:border-[1.5px] focus:border-[#1b6ac9] rounded-[6px] text-[14px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none">
                                        <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-[#6d6a65] text-[11px] leading-none">
                                            ⬍
                                        </span>

                                        <!-- Basecamp Blue "Add <Company>..." Dropdown Pill (Exact Match to media_1793278287851_79a309d7.jpg) -->
                                        <div x-show="person.companyOpen && person.company.trim().length > 0"
                                             x-cloak
                                             class="absolute left-0 right-0 top-[44px] z-30 rounded-[6px] overflow-hidden shadow-md">
                                            <button type="button"
                                                    @click="if (!knownCompanies.includes(person.company.trim())) knownCompanies.push(person.company.trim()); person.companyConfirmed = true; person.companyOpen = false;"
                                                    class="w-full text-left px-3 py-1.5 bg-[#6ab0ff] hover:bg-[#5aa3f5] text-[#1d2d35] text-[13.5px] cursor-pointer">
                                                Add <strong class="font-extrabold" x-text="person.company.trim()"></strong>...
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- "+" Add Another Person Button -->
                <div class="mt-3">
                    <button type="button"
                            @click="addPerson()"
                            class="w-[34px] h-[34px] rounded-[6px] border border-[#d6d2c9] bg-white hover:bg-[#f6f4f0] text-[#1b6ac9] flex items-center justify-center text-[22px] font-medium leading-none cursor-pointer"
                            title="Add another person">
                        +
                    </button>
                </div>

                <!-- Personal Note Link / Rich Text Editor -->
                <div class="mt-5">
                    <button type="button"
                            x-show="!showNote"
                            @click="showNote = true; $nextTick(() => $refs.personalNoteInput && $refs.personalNoteInput.focus())"
                            class="text-[13.5px] text-[#6d6a65] underline hover:text-[#1d2d35] cursor-pointer">
                        Add a personal note to the invitation email
                    </button>

                    <div x-show="showNote" x-cloak>
                        <div class="text-[13.5px] font-bold text-[#1d2d35] mb-1.5">
                            Add a personal note to the invitation email
                        </div>

                        <div class="rounded-[6px] border border-[#d6d2c9] bg-white overflow-hidden">
                            <!-- Basecamp Trix Formatting Toolbar -->
                            <div class="px-3 py-2 border-b border-[#ece9e2] flex items-center justify-between text-[#1d2d35] text-[13.5px] select-none">
                                <div class="flex items-center gap-3.5">
                                    <button type="button" class="font-bold hover:opacity-70">B</button>
                                    <button type="button" class="italic font-serif hover:opacity-70">I</button>
                                    <button type="button" class="line-through hover:opacity-70">S</button>
                                    <button type="button" class="inline-flex items-center gap-0.5 text-[12px] font-bold hover:opacity-70">
                                        <span>TT</span>
                                        <span class="text-[9px]">▾</span>
                                    </button>
                                    <button type="button" class="inline-flex items-center gap-0.5 text-[12px] hover:opacity-70">
                                        <span>✎</span>
                                        <span class="text-[9px]">▾</span>
                                    </button>
                                    <button type="button" class="hover:opacity-70">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                    </button>
                                    <button type="button" class="font-serif font-bold text-[15px] leading-none hover:opacity-70">”</button>
                                    <button type="button" class="text-[12px] font-mono font-bold hover:opacity-70">&lt;&gt;</button>
                                    <button type="button" class="hover:opacity-70">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="currentColor"><circle cx="2.5" cy="3.5" r="1.2"/><rect x="5" y="2.8" width="9" height="1.4"/><circle cx="2.5" cy="8" r="1.2"/><rect x="5" y="7.3" width="9" height="1.4"/><circle cx="2.5" cy="12.5" r="1.2"/><rect x="5" y="11.8" width="9" height="1.4"/></svg>
                                    </button>
                                    <button type="button" class="hover:opacity-70">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="currentColor"><text x="1" y="5" font-size="4.5" font-weight="bold">1</text><rect x="5" y="2.8" width="9" height="1.4"/><text x="1" y="9.5" font-size="4.5" font-weight="bold">2</text><rect x="5" y="7.3" width="9" height="1.4"/><text x="1" y="14" font-size="4.5" font-weight="bold">3</text><rect x="5" y="11.8" width="9" height="1.4"/></svg>
                                    </button>
                                    <button type="button" class="hover:opacity-70">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 12h18M12 3v18"/></svg>
                                    </button>
                                    <button type="button" class="hover:opacity-70">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="currentColor"><rect x="2" y="3" width="12" height="1.3"/><rect x="4" y="7.3" width="8" height="1.3"/><rect x="2" y="11.6" width="12" height="1.3"/></svg>
                                    </button>
                                </div>

                                <div class="flex items-center gap-2 text-[#b5b1a8]">
                                    <span>↶</span>
                                    <span>↷</span>
                                </div>
                            </div>

                            <textarea name="custom_message"
                                      x-ref="personalNoteInput"
                                      x-model="noteText"
                                      placeholder="Type your note here..."
                                      class="w-full h-[145px] p-3 text-[14.5px] text-[#1d2d35] placeholder-[#8c8881] border-0 focus:outline-none resize-none"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6">
                    <button type="submit"
                            class="bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-semibold text-[14px] px-4 py-2 rounded-[6px] transition cursor-pointer">
                        Email invitation now...
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
