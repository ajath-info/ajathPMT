@extends('layouts.app')

@section('title', 'Manage groups - Basecamp')

@section('content')
@php
    $defaultGroups = [
        [
            'id' => 1,
            'name' => 'Delivery Managers',
            'members' => [
                [
                    'id' => 60,
                    'name' => 'Shachish',
                    'fullName' => 'Shachish Sneh',
                    'initials' => 'SS',
                    'bg' => 'bg-[#4f46e5]',
                    'avatar' => '/uploads/avatars/25036340.png',
                    'company' => 'Ajath Infotech Pvt Ltd',
                ],
            ],
        ],
    ];

    $allPeoplePool = [
        ['id' => 60, 'name' => 'Shachish', 'fullName' => 'Shachish Sneh', 'initials' => 'SS', 'bg' => 'bg-[#4f46e5]', 'avatar' => '/uploads/avatars/25036340.png', 'company' => 'Ajath Infotech Pvt Ltd'],
        ['id' => 1, 'name' => 'Manjot', 'fullName' => 'Manjot Singh', 'initials' => 'M', 'bg' => 'bg-[#1d4ed8]', 'avatar' => '/uploads/avatars/22369668.png', 'company' => 'Ajath Infotech Pvt Ltd'],
        ['id' => 45, 'name' => 'Pawandeep', 'fullName' => 'Pawandeep Kaur', 'initials' => 'PK', 'bg' => 'bg-[#00897b]', 'avatar' => null, 'company' => 'Ajath Infotech Pvt Ltd'],
        ['id' => 28, 'name' => 'Gaurav', 'fullName' => 'Gaurav Khulbey', 'initials' => 'GK', 'bg' => 'bg-[#d946ef]', 'avatar' => null, 'company' => 'Ajath Infotech Pvt Ltd'],
        ['id' => 46, 'name' => 'Rajesh', 'fullName' => 'Rajesh Kumar', 'initials' => 'RK', 'bg' => 'bg-[#9333ea]', 'avatar' => null, 'company' => 'Ajath Infotech Pvt Ltd'],
        ['id' => 62, 'name' => 'Shiv', 'fullName' => 'Shiv Narayan', 'initials' => 'SN', 'bg' => 'bg-[#9333ea]', 'avatar' => null, 'company' => 'Ajath Infotech Pvt Ltd'],
        ['id' => 150, 'name' => 'Prashant', 'fullName' => 'Prashant', 'initials' => 'P', 'bg' => 'bg-[#ea580c]', 'avatar' => '/uploads/avatars/48060513.png', 'company' => 'Brancosoft'],
        ['id' => 66, 'name' => 'Srijita', 'fullName' => 'Srijita Roy', 'initials' => 'SR', 'bg' => 'bg-[#7c3aed]', 'avatar' => null, 'company' => 'Orahi'],
    ];

    $companyOptions = [
        'Adstalk Media',
        'Ajath Infotech Pvt Ltd',
        'Borgfy',
        'Brancosoft',
        'Contractor',
        'ITG',
        'Orahi',
    ];
@endphp

<div class="max-w-[780px] mx-auto pb-20 font-sans text-[#1d2d35]"
     x-data="{
         view: '{{ $initialView ?? 'list' }}',
         editingGroupId: null,
         groups: @js($defaultGroups),
         peoplePool: @js($allPeoplePool),
         companies: @js($companyOptions),
         groupName: '',
         selectedMembers: [],
         memberQuery: '',
         memberDropdownOpen: false,
         companyMenuOpen: false,
         hoveredCompany: 'Contractor',
         get filteredPeople() {
             const q = (this.memberQuery || '').trim().toLowerCase();
             const pickedIds = this.selectedMembers.map(m => m.id);
             return this.peoplePool.filter(p => !pickedIds.includes(p.id) && (!q || p.fullName.toLowerCase().includes(q) || p.name.toLowerCase().includes(q)));
         },
         openNewGroup() {
             this.editingGroupId = null;
             this.groupName = '';
             this.selectedMembers = [];
             this.memberQuery = '';
             this.companyMenuOpen = false;
             this.view = 'new';
             history.pushState(null, '', '{{ route('adminland.groups.create', [], false) }}');
             this.$nextTick(() => {
                 if (this.$refs.groupNameInput) this.$refs.groupNameInput.focus();
             });
         },
         openEditGroup(grp) {
             this.editingGroupId = grp.id;
             this.groupName = grp.name;
             this.selectedMembers = JSON.parse(JSON.stringify(grp.members || []));
             this.memberQuery = '';
             this.companyMenuOpen = false;
             this.view = 'new';
             this.$nextTick(() => {
                 if (this.$refs.groupNameInput) this.$refs.groupNameInput.focus();
             });
         },
         backToList() {
             this.view = 'list';
             this.companyMenuOpen = false;
             history.pushState(null, '', '{{ route('adminland.groups', [], false) }}');
         },
         addMember(p) {
             if (!this.selectedMembers.some(m => m.id === p.id)) {
                 this.selectedMembers.push(p);
             }
             this.memberQuery = '';
             this.memberDropdownOpen = false;
         },
         removeMember(id) {
             this.selectedMembers = this.selectedMembers.filter(m => m.id !== id);
         },
         pickCompany(comp) {
             this.peoplePool.forEach(p => {
                 if (p.company === comp || comp === 'Ajath Infotech Pvt Ltd') {
                     if (!this.selectedMembers.some(m => m.id === p.id)) {
                         this.selectedMembers.push(p);
                     }
                 }
             });
             if (this.selectedMembers.length === 0 && this.peoplePool.length > 0) {
                 this.selectedMembers.push(this.peoplePool[0]);
             }
             this.companyMenuOpen = false;
         },
         saveGroup() {
             const name = (this.groupName || '').trim();
             if (!name) {
                 if (this.$refs.groupNameInput) this.$refs.groupNameInput.focus();
                 return;
             }
             if (this.editingGroupId) {
                 const g = this.groups.find(x => x.id === this.editingGroupId);
                 if (g) {
                     g.name = name;
                     g.members = JSON.parse(JSON.stringify(this.selectedMembers));
                 }
             } else {
                 this.groups.push({
                     id: Date.now(),
                     name: name,
                     members: JSON.parse(JSON.stringify(this.selectedMembers))
                 });
             }
             this.backToList();
         }
     }">

    <!-- Basecamp Sheet Container -->
    <div class="bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-84px)] overflow-hidden">

        <!-- =================================================================== -->
        <!-- VIEW 1: MANAGE GROUPS (/account/user_groups — 6f4e3adb.jpg)         -->
        <!-- =================================================================== -->
        <div x-show="view === 'list'">
            <!-- Top Breadcrumb Bar: Adminland -->
            <div class="px-5 py-2.5 border-b border-[#ece9e2] flex items-center text-[13.5px] bg-white">
                <a href="{{ route('adminland.index') }}" class="font-bold text-[#1d2d35] underline decoration-[#8c8881] hover:text-black">
                    Adminland
                </a>
            </div>

            <div class="px-8 sm:px-14 pt-8 pb-16">
                <!-- Heading & Description -->
                <h1 class="text-[28px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-[1.15]">
                    Manage groups
                </h1>
                <p class="mt-2 text-[15.5px] text-[#1d2d35] leading-[1.45]">
                    Groups let you mention or assign multiple people at once. You can add a group to a project or event, assign them a to-do, or @mention them in Chat. When you choose the group, it automatically expands into its individual members.
                </p>

                <!-- + New group Button -->
                <div class="mt-4">
                    <button type="button"
                            @click="openNewGroup()"
                            class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-[6px] bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-semibold text-[13.5px] transition-colors cursor-pointer">
                        <span>+ New group</span>
                    </button>
                </div>

                <!-- Group Cards List (Delivery Managers) -->
                <div class="mt-7 space-y-4">
                    <template x-for="grp in groups" :key="grp.id">
                        <div class="rounded-[8px] border border-[#e5e2dc] bg-[#f7f5f2] px-5 py-4">
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="text-[18px] font-extrabold text-[#1d2d35]" x-text="grp.name"></h2>
                                <button type="button"
                                        @click="openEditGroup(grp)"
                                        class="px-3 py-1 rounded-[6px] border border-[#d6d2c9] bg-white hover:bg-[#f2f0ec] text-[13px] text-[#1d2d35] transition-colors cursor-pointer">
                                    Edit
                                </button>
                            </div>

                            <div class="mt-2.5 flex flex-wrap items-center gap-4 text-[14.5px] text-[#1d2d35]">
                                <template x-for="mem in grp.members" :key="mem.id">
                                    <div class="inline-flex items-center gap-2">
                                        <template x-if="mem.avatar">
                                            <img :src="mem.avatar" :alt="mem.name" class="w-[30px] h-[30px] rounded-full object-cover">
                                        </template>
                                        <template x-if="!mem.avatar">
                                            <span class="w-[30px] h-[30px] rounded-full text-white font-bold text-[11.5px] flex items-center justify-center select-none"
                                                  :class="mem.bg"
                                                  x-text="mem.initials"></span>
                                        </template>
                                        <span x-text="mem.name"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- VIEW 2: ADD A NEW GROUP (/account/user_groups/new — 22211afc..c4450228) -->
        <!-- =================================================================== -->
        <div x-show="view === 'new'" x-cloak>
            <!-- Top Breadcrumb Bar: Back to Manage groups -->
            <div class="px-5 py-2.5 border-b border-[#ece9e2] flex items-center text-[13.5px] bg-white">
                <a href="{{ route('adminland.groups') }}"
                   @click.prevent="backToList()"
                   class="font-bold text-[#1d2d35] underline decoration-[#8c8881] hover:text-black cursor-pointer">
                    Back to Manage groups
                </a>
            </div>

            <div class="px-8 sm:px-14 pt-8 pb-16">
                <h1 class="text-[28px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-[1.15]"
                    x-text="editingGroupId ? 'Edit group' : 'Add a new group'">
                </h1>

                <form @submit.prevent="saveGroup()" class="mt-6">
                    <!-- 1. Give the group a name -->
                    <div>
                        <label class="block text-[14.5px] font-bold text-[#1d2d35] mb-1.5">
                            Give the group a name
                        </label>
                        <input type="text"
                               x-ref="groupNameInput"
                               x-model="groupName"
                               placeholder="e.g. Designers"
                               required
                               class="w-full h-[40px] px-3.5 rounded-[6px] border border-[#1b6ac9] bg-white text-[14.5px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none">
                    </div>

                    <!-- 2. Who's in the group? -->
                    <div class="mt-5" @click.outside="memberDropdownOpen = false">
                        <label class="block text-[14.5px] font-bold text-[#1d2d35] mb-1.5">
                            Who’s in the group?
                        </label>

                        <div class="relative">
                            <div class="min-h-[42px] px-3 py-1.5 rounded-[6px] border border-[#d6d2c9] focus-within:border-[#1b6ac9] bg-white flex flex-wrap items-center gap-1.5">
                                <template x-for="mem in selectedMembers" :key="mem.id">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#eef5fc] border border-[#cde0f7] text-[13px] text-[#1d2d35]">
                                        <template x-if="mem.avatar">
                                            <img :src="mem.avatar" :alt="mem.name" class="w-[18px] h-[18px] rounded-full object-cover">
                                        </template>
                                        <template x-if="!mem.avatar">
                                            <span class="w-[18px] h-[18px] rounded-full text-white font-bold text-[9px] flex items-center justify-center"
                                                  :class="mem.bg"
                                                  x-text="mem.initials"></span>
                                        </template>
                                        <span x-text="mem.fullName"></span>
                                        <button type="button" @click="removeMember(mem.id)" class="text-[#6d6a65] hover:text-[#1d2d35] font-bold cursor-pointer">✕</button>
                                    </span>
                                </template>

                                <input type="text"
                                       x-model="memberQuery"
                                       @focus="memberDropdownOpen = true"
                                       @input="memberDropdownOpen = true"
                                       :placeholder="selectedMembers.length === 0 ? 'Type their names here...' : ''"
                                       class="flex-1 min-w-[160px] h-[28px] bg-transparent border-0 text-[14.5px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none">
                            </div>

                            <!-- Autocomplete Dropdown for People -->
                            <div x-show="memberDropdownOpen && memberQuery.trim().length > 0 && filteredPeople.length > 0"
                                 x-cloak
                                 class="absolute left-0 right-0 top-full mt-1 z-30 rounded-[6px] overflow-hidden shadow-md bg-white border border-[#d6d2c9]">
                                <template x-for="(p, idx) in filteredPeople" :key="p.id">
                                    <button type="button"
                                            @click="addMember(p)"
                                            :class="idx === 0 ? 'bg-[#6ab0ff] text-[#1d2d35]' : 'bg-white hover:bg-[#6ab0ff] text-[#1d2d35]'"
                                            class="w-full text-left px-3 py-1.5 flex items-center gap-2 text-[14px] cursor-pointer">
                                        <template x-if="p.avatar">
                                            <img :src="p.avatar" :alt="p.fullName" class="w-[22px] h-[22px] rounded-full object-cover">
                                        </template>
                                        <template x-if="!p.avatar">
                                            <span class="w-[22px] h-[22px] rounded-full text-white font-bold text-[10px] flex items-center justify-center"
                                                  :class="p.bg"
                                                  x-text="p.initials"></span>
                                        </template>
                                        <span x-text="p.fullName"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- 3. "Or pick people from a company..." Popover Menu (Exact Match to media_1793278351101_c4450228.jpg) -->
                    <div class="mt-3.5 relative inline-block" @click.outside="companyMenuOpen = false">
                        <button type="button"
                                @click="companyMenuOpen = !companyMenuOpen"
                                class="text-[14px] text-[#1b6ac9] underline hover:opacity-80 cursor-pointer">
                            Or pick people from a company...
                        </button>

                        <!-- Native-Style Dark Popover Menu Matching media_1793278351101_c4450228.jpg -->
                        <div x-show="companyMenuOpen"
                             x-cloak
                             class="absolute left-0 top-0 z-40 w-[236px] rounded-[8px] bg-[#262b30]/95 backdrop-blur-md border border-white/15 shadow-2xl py-1.5 text-[13.5px] text-white select-none">
                            <div class="px-2.5 py-1 text-white/55 flex items-center gap-1.5 text-[13px]">
                                <span>✓</span>
                                <span>Or pick people from a company...</span>
                            </div>

                            <template x-for="comp in companies" :key="comp">
                                <button type="button"
                                        @mouseenter="hoveredCompany = comp"
                                        @click="pickCompany(comp)"
                                        :class="hoveredCompany === comp ? 'bg-[#2b6cb0] text-white' : 'text-white/95 hover:bg-[#2b6cb0]'"
                                        class="w-full text-left pl-6 pr-3 py-1 rounded-[4px] transition-colors cursor-pointer"
                                        x-text="comp">
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- 4. Highlighted Explanation Banner -->
                    <div class="mt-5 leading-[1.55]">
                        <span class="bg-[#fef08a]/70 text-[#1d2d35] text-[13.5px] px-1 py-0.5 rounded-[2px]">
                            When you tag a group, it will expand into everyone who's currently in that group. If you add or remove someone, they won't be automatically added or removed on previous tasks or mentions.
                        </span>
                    </div>

                    <!-- 5. Add this group Button -->
                    <div class="mt-6">
                        <button type="submit"
                                class="px-4 py-2 rounded-[6px] bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-semibold text-[14px] transition-colors cursor-pointer"
                                x-text="editingGroupId ? 'Save changes' : 'Add this group'">
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection

