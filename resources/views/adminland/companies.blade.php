@extends('layouts.app')

@section('title', 'Manage companies - Basecamp')

@section('content')
@php
    $initialCompanies = [
        [
            'id' => 1,
            'name' => 'Adstalk Media',
            'isClient' => false,
            'people' => [],
        ],
        [
            'id' => 2,
            'name' => 'Borgfy',
            'isClient' => false,
            'people' => [
                ['name' => 'Nirdesh Verma', 'initials' => 'NV', 'bg' => 'bg-[#0d9488]'],
            ],
        ],
        [
            'id' => 3,
            'name' => 'Brancosoft',
            'isClient' => false,
            'people' => [],
        ],
        [
            'id' => 4,
            'name' => 'Contractor',
            'isClient' => false,
            'people' => [
                ['name' => 'Rudra Banger', 'initials' => 'RB', 'bg' => 'bg-[#92400e]'],
            ],
        ],
        [
            'id' => 5,
            'name' => 'Harsac',
            'isClient' => true,
            'people' => [
                ['name' => 'Harsac Client', 'initials' => 'HC', 'bg' => 'bg-[#0284c7]'],
                ['name' => 'Project Coordinator', 'initials' => 'PC', 'bg' => 'bg-[#7c3aed]'],
            ],
        ],
        [
            'id' => 6,
            'name' => 'ITG',
            'isClient' => false,
            'people' => [
                ['name' => 'Nikhil Aneja', 'initials' => 'NA', 'bg' => 'bg-[#0284c7]'],
            ],
        ],
        [
            'id' => 7,
            'name' => 'manikarananalytics',
            'isClient' => true,
            'people' => [
                ['name' => 'Manikaran Lead', 'initials' => 'ML', 'bg' => 'bg-[#059669]'],
            ],
        ],
        [
            'id' => 8,
            'name' => 'Orahi',
            'isClient' => false,
            'people' => [
                ['name' => 'Srijita Roy', 'initials' => 'SR', 'bg' => 'bg-[#7c3aed]'],
                ['name' => 'Harshvardhan Singh', 'initials' => 'HK', 'bg' => 'bg-[#c026d3]'],
            ],
        ],
        [
            'id' => 9,
            'name' => 'Healthline',
            'isClient' => true,
            'people' => [
                ['name' => 'Dr. Muaad Alahmadi', 'initials' => 'DMP', 'bg' => 'bg-[#1e293b]'],
                ['name' => 'Doug Morneau', 'initials' => 'DMON', 'bg' => 'bg-[#ea580c]'],
            ],
        ],
        [
            'id' => 10,
            'name' => 'NDC Homes',
            'isClient' => true,
            'people' => [
                ['name' => 'Edward Smith', 'initials' => 'E', 'bg' => 'bg-[#f97316]'],
            ],
        ],
    ];
@endphp

<div class="max-w-[780px] mx-auto pb-20 font-sans text-[#1d2d35]"
     x-data="{
         companies: @js($initialCompanies),
         expandedIds: {},
         editingCompany: null,
         renameValue: '',
         togglePeople(id) {
             this.expandedIds[id] = !this.expandedIds[id];
         },
         openEditModal(comp) {
             this.editingCompany = comp;
             this.renameValue = comp.name;
             this.$nextTick(() => {
                 if (this.$refs.renameInput) {
                     this.$refs.renameInput.focus();
                     this.$refs.renameInput.select();
                 }
             });
         },
         saveRename() {
             const val = (this.renameValue || '').trim();
             if (!val || !this.editingCompany) return;
             const target = this.companies.find(c => c.id === this.editingCompany.id);
             if (target) {
                 target.name = val;
             }
             this.editingCompany = null;
         },
         deleteCompany(comp) {
             if (!window.confirm('Are you sure you want to delete ' + comp.name + '? Deleting a company won\'t delete its people or their access to projects.')) {
                 return;
             }
             this.companies = this.companies.filter(c => c.id !== comp.id);
         }
     }">

    <!-- Basecamp Sheet Container (Exact Match to media_1793278375940_0eb01579.jpg) -->
    <div class="bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-84px)] overflow-hidden">

        <!-- Top Breadcrumb Bar: Adminland -->
        <div class="px-5 py-2.5 border-b border-[#ece9e2] flex items-center text-[13.5px] bg-white">
            <a href="{{ route('adminland.index') }}" class="font-bold text-[#1d2d35] underline decoration-[#8c8881] hover:text-black">
                Adminland
            </a>
        </div>

        <div class="px-8 sm:px-14 pt-8 pb-16">
            <!-- Heading: Manage companies -->
            <h1 class="text-[28px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-[1.15]">
                Manage companies
            </h1>

            <!-- Centered Subtitle -->
            <p class="mt-6 text-[14.5px] text-[#1d2d35] text-center">
                Deleting a company won’t delete its people or their access to projects.
            </p>

            <!-- Companies List -->
            <div class="mt-5 divide-y divide-[#ece9e2]">
                <template x-for="comp in companies" :key="comp.id">
                    <div class="py-3.5">
                        <div class="flex items-center justify-between gap-4">
                            <!-- Left: Company Name + Client Badge + Person Count -->
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-[18px] font-extrabold text-[#1d2d35] leading-snug" x-text="comp.name"></span>
                                    <template x-if="comp.isClient">
                                        <span class="px-2 py-0.5 rounded-[4px] bg-[#9c7338] text-white text-[12px] font-bold leading-none select-none">
                                            Client
                                        </span>
                                    </template>
                                </div>

                                <template x-if="comp.people && comp.people.length > 0">
                                    <div class="text-[13px] text-[#1d2d35] mt-0.5">
                                        <span x-text="comp.people.length === 1 ? '1 person' : (comp.people.length + ' people')"></span>
                                        <span>(</span><button type="button"
                                                              @click="togglePeople(comp.id)"
                                                              class="underline hover:opacity-80 cursor-pointer"
                                                              x-text="expandedIds[comp.id] ? 'hide them' : 'show them'"></button><span>)</span>
                                    </div>
                                </template>
                            </div>

                            <!-- Right: Trash Icon + Edit Button -->
                            <div class="flex items-center gap-2.5 flex-shrink-0">
                                <button type="button"
                                        @click="deleteCompany(comp)"
                                        class="p-1.5 text-[#8c8881] hover:text-[#c84b31] transition-colors cursor-pointer"
                                        title="Delete company">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                    </svg>
                                </button>

                                <button type="button"
                                        @click="openEditModal(comp)"
                                        class="px-3 py-1 rounded-[6px] border border-[#d6d2c9] bg-white hover:bg-[#f2f0ec] text-[13px] text-[#1d2d35] transition-colors cursor-pointer">
                                    Edit
                                </button>
                            </div>
                        </div>

                        <!-- Expanded People List -->
                        <div x-show="expandedIds[comp.id]" x-cloak class="mt-2.5 pl-1 flex flex-wrap items-center gap-3 text-[13.5px] text-[#1d2d35]">
                            <template x-for="(p, pIdx) in comp.people" :key="pIdx">
                                <div class="inline-flex items-center gap-2">
                                    <span class="w-[26px] h-[26px] rounded-full text-white font-bold text-[10.5px] flex items-center justify-center select-none"
                                          :class="p.bg"
                                          x-text="p.initials"></span>
                                    <span x-text="p.name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

        </div>

    </div>

    <!-- ======================================================================= -->
    <!-- EDIT COMPANY MODAL (Exact Match to media_1793278381594_03483af9.jpg)    -->
    <!-- ======================================================================= -->
    <div x-show="editingCompany"
         x-cloak
         @keydown.escape.window="editingCompany = null"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         role="dialog"
         aria-modal="true">
        <!-- Dimmed Backdrop -->
        <div class="fixed inset-0 bg-black/45 transition-opacity" @click="editingCompany = null"></div>

        <!-- Centered Modal Box -->
        <div class="relative z-10 w-[500px] max-w-[95vw] rounded-[10px] bg-white border border-[#e5e2dc] shadow-2xl px-7 py-6 text-[#1d2d35]">
            <form @submit.prevent="saveRename()">
                <h2 class="text-[24px] font-extrabold text-[#1d2d35] tracking-[-0.02em] leading-tight"
                    x-text="editingCompany ? ('Edit ' + editingCompany.name) : ''">
                </h2>

                <div class="mt-4">
                    <label class="block text-[14px] font-bold text-[#1d2d35] mb-1.5"
                           x-text="editingCompany ? ('Rename ' + editingCompany.name + ' to...') : ''">
                    </label>
                    <input type="text"
                           x-ref="renameInput"
                           x-model="renameValue"
                           required
                           class="w-full h-[40px] px-3.5 rounded-[6px] border-[1.5px] border-[#1b6ac9] bg-white text-[14.5px] text-[#1d2d35] focus:outline-none">
                </div>

                <div class="mt-5 flex items-center gap-2">
                    <button type="submit"
                            class="px-4 py-2 rounded-[6px] bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-semibold text-[14px] transition-colors cursor-pointer">
                        Save
                    </button>
                    <button type="button"
                            @click="editingCompany = null"
                            class="px-4 py-2 rounded-[6px] border border-[#d6d2c9] bg-white hover:bg-[#f6f4f0] text-[#1d2d35] font-normal text-[14px] transition-colors cursor-pointer">
                        Never mind
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

