@extends('layouts.app')

@section('title', 'Add/Remove Administrators - Basecamp')

@section('content')
@php
    $initialAdmins = [
        [
            'id' => 1,
            'name' => 'Manjot Singh',
            'shortName' => 'Manjot S.',
            'title' => 'Global VP & Partner',
            'isOwner' => true,
            'initials' => 'M',
            'bg' => 'bg-[#1d4ed8]',
            'avatar' => '/uploads/avatars/22369668.png',
        ],
        [
            'id' => 45,
            'name' => 'Pawandeep Kaur',
            'shortName' => 'Pawandeep K.',
            'title' => 'Manager',
            'isOwner' => false,
            'initials' => 'PK',
            'bg' => 'bg-[#00897b]',
            'avatar' => null,
        ],
        [
            'id' => 60,
            'name' => 'Shachish Sneh',
            'shortName' => 'Shachish S.',
            'title' => 'Solution Architect & Technology Master',
            'isOwner' => false,
            'initials' => 'SS',
            'bg' => 'bg-[#4f46e5]',
            'avatar' => '/uploads/avatars/25036340.png',
        ],
    ];

    $candidatePool = [
        [
            'id' => 62,
            'name' => 'Shiv Narayan',
            'shortName' => 'Shiv N.',
            'title' => 'Developer',
            'isOwner' => false,
            'initials' => 'SN',
            'bg' => 'bg-[#9333ea]',
            'avatar' => null,
        ],
        [
            'id' => 28,
            'name' => 'Gaurav Khulbey',
            'shortName' => 'Gaurav K.',
            'title' => 'Cybersecurity Expert',
            'isOwner' => false,
            'initials' => 'GK',
            'bg' => 'bg-[#d946ef]',
            'avatar' => null,
        ],
        [
            'id' => 46,
            'name' => 'Rajesh Kumar',
            'shortName' => 'Rajesh K.',
            'title' => 'Project Lead',
            'isOwner' => false,
            'initials' => 'RK',
            'bg' => 'bg-[#9333ea]',
            'avatar' => null,
        ],
        [
            'id' => 150,
            'name' => 'Prashant',
            'shortName' => 'Prashant',
            'title' => 'Analyst',
            'isOwner' => false,
            'initials' => 'P',
            'bg' => 'bg-[#ea580c]',
            'avatar' => '/uploads/avatars/48060513.png',
        ],
        [
            'id' => 32,
            'name' => 'Harshvardhan Singh',
            'shortName' => 'Harshvardhan S.',
            'title' => 'AI Consultant',
            'isOwner' => false,
            'initials' => 'HK',
            'bg' => 'bg-[#c026d3]',
            'avatar' => null,
        ],
        [
            'id' => 147,
            'name' => 'Nikhil Aneja',
            'shortName' => 'Nikhil A.',
            'title' => 'Developer',
            'isOwner' => false,
            'initials' => 'NA',
            'bg' => 'bg-[#0284c7]',
            'avatar' => '/uploads/avatars/48443395.png',
        ],
    ];
@endphp

<div class="max-w-[780px] mx-auto pb-24 font-sans text-[#1d2d35]"
     x-data="{
         admins: @js($initialAdmins),
         candidates: @js($candidatePool),
         searchQuery: '',
         dropdownOpen: false,
         selectedCandidate: null,
         get filteredCandidates() {
             const q = (this.searchQuery || '').trim().toLowerCase();
             const adminIds = this.admins.map(a => a.id);
             return this.candidates.filter(c => !adminIds.includes(c.id) && (!q || c.name.toLowerCase().includes(q)));
         },
         selectCandidate(c) {
             this.selectedCandidate = c;
             this.searchQuery = c.name;
             this.dropdownOpen = false;
         },
         grantPower() {
             let target = this.selectedCandidate;
             if (!target && this.searchQuery.trim()) {
                 target = this.filteredCandidates[0] || null;
             }
             if (!target) return;
             if (!this.admins.some(a => a.id === target.id)) {
                 this.admins.push(target);
             }
             const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
             fetch('{{ route('adminland.administrators.grant') }}', {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                     'Accept': 'application/json',
                     'X-CSRF-TOKEN': token || ''
                 },
                 body: JSON.stringify({ user_id: target.id })
             }).catch(() => {});
             this.selectedCandidate = null;
             this.searchQuery = '';
             this.dropdownOpen = false;
         },
         revokePower(admin) {
             if (admin.isOwner) return;
             if (!window.confirm('Are you sure you want to revoke ' + admin.shortName + '\'s admin powers?')) {
                 return;
             }
             this.admins = this.admins.filter(a => a.id !== admin.id);
             if (!this.candidates.some(c => c.id === admin.id)) {
                 this.candidates.unshift(admin);
             }
             const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
             fetch('/account/administrators/' + admin.id, {
                 method: 'DELETE',
                 headers: {
                     'Accept': 'application/json',
                     'X-CSRF-TOKEN': token || ''
                 }
             }).catch(() => {});
         }
     }">

    <!-- Basecamp Sheet Container (Exact Match to media_1793278307631_37b3f04a.jpg .. 6fce48f1.jpg) -->
    <div class="bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-84px)] overflow-hidden">

        <!-- Top Breadcrumb Bar: Adminland -->
        <div class="px-5 py-2.5 border-b border-[#ece9e2] flex items-center text-[13.5px] bg-white">
            <a href="{{ route('adminland.index') }}" class="font-bold text-[#1d2d35] underline decoration-[#8c8881] hover:text-black">
                Adminland
            </a>
        </div>

        <div class="px-8 sm:px-14 pt-8 pb-16">
            <!-- Heading & Subtitle -->
            <h1 class="text-[28px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-[1.15]">
                Add/Remove Administrators
            </h1>
            <p class="mt-1 text-[15.5px] text-[#1d2d35]">
                Add trusted people to help with administrative tasks on your Basecamp account.
            </p>

            <!-- Administrators can... Bulleted List -->
            <div class="mt-6">
                <h2 class="text-[15px] font-bold text-[#1d2d35] mb-1.5">
                    Administrators can...
                </h2>
                <ul class="list-disc pl-5 space-y-1 text-[15px] text-[#1d2d35]">
                    <li>Add or remove people from the account</li>
                    <li>Change what people can access</li>
                    <li>Move people between companies/organizations</li>
                    <li>Rename a company/organization</li>
                    <li>Add or remove other administrators</li>
                </ul>
            </div>

            <!-- Administrators on this account Section -->
            <div class="mt-8">
                <h2 class="text-[15px] font-bold text-[#1d2d35] pb-2 border-b border-[#e5e2dc]">
                    Administrators on this account
                </h2>

                <div class="divide-y divide-[#ece9e2]">
                    <template x-for="admin in admins" :key="admin.id">
                        <div class="py-3 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <template x-if="admin.avatar">
                                    <img :src="admin.avatar" :alt="admin.name" class="w-[40px] h-[40px] rounded-full object-cover flex-shrink-0">
                                </template>
                                <template x-if="!admin.avatar">
                                    <span class="w-[40px] h-[40px] rounded-full text-white font-bold text-[15px] flex items-center justify-center flex-shrink-0 select-none"
                                          :class="admin.bg"
                                          x-text="admin.initials"></span>
                                </template>

                                <div class="text-[15.5px] text-[#1d2d35] truncate">
                                    <strong class="font-bold" x-text="admin.name"></strong><span x-show="admin.title" x-text="', ' + admin.title"></span>
                                </div>
                            </div>

                            <!-- Right: Owner or Revoke Button -->
                            <div class="flex-shrink-0">
                                <template x-if="admin.isOwner">
                                    <span class="text-[14.5px] text-[#6d6a65]">Owner</span>
                                </template>
                                <template x-if="!admin.isOwner">
                                    <button type="button"
                                            @click="revokePower(admin)"
                                            class="px-3 py-1 rounded-[6px] border border-[#e6b8af] text-[#c84b31] hover:bg-[#fef2f0] text-[13px] font-normal transition-colors cursor-pointer">
                                        Revoke
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Who else should be an administrator? Combobox -->
            <div class="mt-7" @click.outside="dropdownOpen = false">
                <label class="block text-[15px] font-bold text-[#1d2d35] mb-2">
                    Who else should be an administrator?
                </label>

                <div class="relative">
                    <input type="text"
                           x-model="searchQuery"
                           @focus="dropdownOpen = true"
                           @input="dropdownOpen = true; selectedCandidate = null"
                           @keydown.enter.prevent="grantPower()"
                           placeholder="Type a person’s name..."
                           autocomplete="off"
                           class="w-full h-[42px] pl-3.5 pr-8 bg-white border border-[#d6d2c9] focus:border-[1.5px] focus:border-[#1b6ac9] rounded-[6px] text-[14.5px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none">
                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[#6d6a65] text-[11px] leading-none">
                        ⬍
                    </span>

                    <!-- Basecamp Blue Person Picker Dropdown (Exact Match to media_1793278314621_dd9a32a0.jpg) -->
                    <div x-show="dropdownOpen && searchQuery.trim().length > 0 && filteredCandidates.length > 0"
                         x-cloak
                         class="absolute left-0 right-0 top-[44px] z-30 rounded-[6px] overflow-hidden shadow-md bg-white border border-[#d6d2c9]">
                        <template x-for="(cand, cIdx) in filteredCandidates" :key="cand.id">
                            <button type="button"
                                    @click="selectCandidate(cand)"
                                    :class="cIdx === 0 ? 'bg-[#6ab0ff] text-[#1d2d35]' : 'bg-white hover:bg-[#6ab0ff] text-[#1d2d35]'"
                                    class="w-full text-left px-3 py-1.5 flex items-center gap-2 text-[14px] cursor-pointer transition-colors">
                                <template x-if="cand.avatar">
                                    <img :src="cand.avatar" :alt="cand.name" class="w-[22px] h-[22px] rounded-full object-cover flex-shrink-0">
                                </template>
                                <template x-if="!cand.avatar">
                                    <span class="w-[22px] h-[22px] rounded-full text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0 select-none"
                                          :class="cand.bg"
                                          x-text="cand.initials"></span>
                                </template>
                                <span x-text="cand.name"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="button"
                            @click="grantPower()"
                            class="px-4 py-2 rounded-[6px] bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-semibold text-[14px] transition-colors cursor-pointer">
                        Grant administrator powers
                    </button>
                </div>
            </div>

        </div>

    </div>

    <!-- Floating Bottom-Left Back to top Pill -->
    <button type="button"
            onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-12 left-4 z-30 inline-flex items-center gap-1 px-3 py-1.5 rounded-[6px] bg-white border border-[#e5e2dc] shadow-xs text-[12.5px] text-[#1d2d35] hover:bg-[#f6f4f0] transition-colors cursor-pointer">
        <span>↑</span>
        <span>Back to top</span>
    </button>

</div>
@endsection

