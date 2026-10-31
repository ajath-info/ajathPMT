@extends('layouts.app')

@section('title', 'Projects')

@section('content')
@php
    $directoryGroups = [];
    foreach ($groupedAllProjects as $letter => $items) {
        $rows = [];
        foreach ($items as $proj) {
            $rows[] = [
                'id' => $proj->id,
                'name' => $proj->name,
                'desc' => $proj->directory_desc ?? '',
                'url' => route('projects.show', $proj->id),
                'archived' => (bool) ($proj->is_archived_or_trashed ?? false),
                'archive_badge' => $proj->archive_badge ?? '',
            ];
        }
        $directoryGroups[] = [
            'letter' => $letter,
            'projects' => $rows,
        ];
    }
@endphp

<script>
window.BC_DIRECTORY_GROUPS = {!! json_encode($directoryGroups) !!};
window.bcAllProjectsDirectory = function () {
    return {
        search: {!! json_encode((string) request('q', '')) !!},
        showArchived: false,
        groups: window.BC_DIRECTORY_GROUPS || [],
        visibleProjects(group) {
            const q = this.search.trim().toLowerCase();
            return group.projects.filter((p) => {
                if (!this.showArchived && p.archived) return false;
                if (!q) return true;
                return p.name.toLowerCase().includes(q) || (p.desc && p.desc.toLowerCase().includes(q));
            });
        },
        filteredGroups() {
            return this.groups
                .map((g) => ({
                    letter: g.letter,
                    projects: this.visibleProjects(g)
                }))
                .filter((g) => g.projects.length > 0);
        }
    };
};
</script>

<style>
.bc-active-link {
    color: #1d2d35 !important;
}
.bc-archived-link {
    color: #7c7872 !important;
}
.bc-archive-badge {
    background-color: #ece9e2 !important;
    color: #6d6a65 !important;
}
.bc-toggle-knob {
    background-color: #ffffff !important;
}
html.bc-theme-dark .bc-active-link {
    color: #ffffff !important;
}
html.bc-theme-dark .bc-archived-link {
    color: #9aa4ae !important;
}
html.bc-theme-dark .bc-archive-badge {
    background-color: #333f48 !important;
    color: #b5bec6 !important;
}
</style>

<!-- Exact Basecamp /projects/directory "All Projects" Sheet Matching media_1793289703915_4da1ebfa.jpg, media_1793289719235_71f13801.jpg & media_1793289731650_22e5a0d4.jpg -->
<div class="w-full sm:w-[72vw] max-w-[1130px] mx-auto pb-20 font-sans text-[#1d2d35] relative"
     x-data="bcAllProjectsDirectory()">

    <!-- Floating Left '↑ Back to top' Pill (Matches media_1793289731650_22e5a0d4.jpg) -->
    <button type="button"
            @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="hidden lg:inline-flex fixed bottom-12 left-14 z-30 items-center gap-1 px-2.5 py-1 bg-[#f2f0ec] hover:bg-[#e6e3dd] text-[#1d2d35] text-[12px] font-normal rounded-[6px] shadow-[0_1px_2px_rgba(0,0,0,0.05)] transition-colors cursor-pointer">
        <span>↑</span>
        <span>Back to top</span>
    </button>

    <!-- Continuous White Paper Sheet -->
    <div class="bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-88px)] px-8 sm:px-10 pt-8 pb-16">

        <!-- Heading: "All Projects" -->
        <h1 class="text-[28px] sm:text-[32px] font-black text-[#1d2d35] tracking-[-0.025em] leading-tight">
            All Projects
        </h1>

        <!-- Filter & Toggle Bar: "Find a project..." + "[Off] Show Archived and Trashed" -->
        <div class="mt-2.5 pb-4 border-b border-[#ece9e2] flex flex-wrap items-center gap-3">
            <input type="text"
                   x-model="search"
                   placeholder="Find a project..."
                   autocomplete="off"
                   class="w-[205px] h-[30px] px-3 rounded-[6px] border border-[#d6d2c9] focus:border-[#1b6ac9] bg-white text-[13.5px] text-[#1d2d35] placeholder-[#6d6a65] focus:outline-none">

            <div class="flex items-center gap-2">
                <!-- Basecamp Binary Toggle Switch ([Off] / [On]) -->
                <button type="button"
                        @click="showArchived = !showArchived"
                        class="w-[46px] h-[26px] rounded-[5px] border p-[2px] flex items-center justify-between transition-colors cursor-pointer select-none"
                        :class="showArchived ? 'bg-[#2da562] border-[#2da562]' : 'bg-[#8c969e] border-[#7b858e]'">
                    <template x-if="!showArchived">
                        <div class="flex items-center justify-between w-full px-0.5">
                            <span class="bc-toggle-knob w-[10px] h-[18px] rounded-[2px] bg-white"></span>
                            <span class="text-[11px] font-bold text-white pr-0.5">Off</span>
                        </div>
                    </template>
                    <template x-if="showArchived">
                        <div class="flex items-center justify-between w-full px-0.5">
                            <span class="text-[11px] font-bold text-white pl-0.5">On</span>
                            <span class="bc-toggle-knob w-[10px] h-[18px] rounded-[2px] bg-white"></span>
                        </div>
                    </template>
                </button>

                <span class="text-[13.5px] text-[#1d2d35] cursor-pointer select-none"
                      @click="showArchived = !showArchived">
                    Show Archived and Trashed
                </span>
            </div>
        </div>

        <!-- Alphabetical Directory Rows (0-9, A, B, C, D, E, G, H, ...) -->
        <div class="divide-y divide-[#ece9e2]">
            <template x-for="group in filteredGroups()" :key="group.letter">
                <div class="py-3.5 flex items-start gap-5 sm:gap-7">
                    <!-- Left Letter Column (0-9, A, B, ...) -->
                    <div class="w-8 text-[13px] font-normal text-[#6d6a65] uppercase pt-0.5 select-none shrink-0 whitespace-nowrap"
                         x-text="group.letter"></div>

                    <!-- Right Projects Column -->
                    <div class="flex-1 min-w-0 space-y-2.5">
                        <template x-for="proj in group.projects" :key="proj.id">
                            <div class="text-[15px] leading-[1.35] truncate">
                                <a :href="proj.url"
                                   class="font-bold hover:underline"
                                   :class="proj.archived ? 'bc-archived-link' : 'bc-active-link'"
                                   x-text="proj.name"></a>
                                <template x-if="proj.desc">
                                    <span class="font-normal text-[#6d6a65]" x-text="' - ' + proj.desc"></span>
                                </template>
                                <template x-if="proj.archive_badge">
                                    <span class="bc-archive-badge inline-block ml-1.5 px-2 py-[1.5px] rounded-[4px] text-[11px] font-normal leading-tight align-middle"
                                          x-text="proj.archive_badge"></span>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <div x-show="filteredGroups().length === 0" x-cloak class="py-10 text-[14px] text-[#6d6a65]">
                No matching projects found.
            </div>
        </div>

    </div>
</div>
@endsection
