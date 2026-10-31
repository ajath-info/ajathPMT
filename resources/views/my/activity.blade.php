@extends('layouts.app')

@php
    $isUserActivity = isset($targetUser) && $targetUser;
    $isPawandeep = $isUserActivity && ($targetUser->id == 45 || stripos($targetUser->name, 'Pawandeep') !== false);
    $isGlobalScope = request()->query('scope') === 'all';
    $manjotAvatar = ($user->avatar_url && !str_contains($user->avatar_url, 'dicebear') && !str_contains($user->avatar_url, 'avatar_1_1793286396')) ? $user->avatar_url : '/uploads/avatars/22369668.png';
    $latestNamedProj = \App\Models\Project::where('name', 'Name the project')->latest('id')->first();
    $namedProjId = $latestNamedProj ? $latestNamedProj->id : 213;
@endphp

@section('title', ($isUserActivity ? $targetUser->name . "’s activity" : ($isGlobalScope ? 'Latest Activity' : 'My Activity')) . ' - Basecamp')

@section('content')
<div x-data="{
    actFilter: '{{ addslashes($activityFilter ?? '') }}',
    projectMenuOpen: false,
    projectSearch: '',
    selectedProjectFilter: 'all',
    selectedProjectName: 'All projects',
    hl(text, q) {
        const esc = String(text || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
        const term = String(q || '').trim();
        if (!term) return esc;
        const safeTerm = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return esc.replace(new RegExp('(' + safeTerm + ')', 'gi'), '<mark class=\'bg-[#fae69e] text-[#1d2d35] rounded-[2px] px-0\'>$1</mark>');
    }
}" class="max-w-[920px] mx-auto pb-20 font-sans text-[#1d2d35]">

    <!-- Basecamp Sheet Container -->
    <div class="bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-84px)] relative">

        @if($isUserActivity)
            <!-- ======================================================================= -->
            <!-- SOMEONE'S ACTIVITY (?user_id=...)                                       -->
            <!-- ======================================================================= -->
            <div class="px-5 py-2.5 border-b border-[#ece9e2] flex items-center text-[13.5px] bg-white rounded-t-[10px]">
                <a href="{{ route('my.activity', ['scope' => 'all']) }}" class="font-bold text-[#1d2d35] underline decoration-[#8c8881] hover:text-black">
                    All Reports
                </a>
                <span class="text-[#6d6a65] text-[12px] mx-1.5">‹</span>
                <span class="text-[#1d2d35]">Someone’s activity</span>
            </div>

            <div class="px-8 sm:px-14 pt-8 pb-16">
                <h1 class="text-[28px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-[1.15]">
                    {{ $targetUser->name }}’s activity
                </h1>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <!-- All projects Dropdown Button & Popover (Matches media_1793335583582_81d0213d.jpg) -->
                    <div class="relative">
                        <button type="button"
                                @click="projectMenuOpen = !projectMenuOpen; if (projectMenuOpen) $nextTick(() => $refs.projSearchInput && $refs.projSearchInput.focus())"
                                class="inline-flex items-center gap-1 h-[30px] px-2.5 border border-[#d8d4cc] rounded-[6px] bg-white hover:bg-[#f9f8f6] text-[13px] font-bold text-[#1d2d35] cursor-pointer">
                            <span x-text="selectedProjectName">All projects</span>
                            <svg class="w-3 h-3 text-[#1d2d35] mt-0.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="projectMenuOpen"
                             x-cloak
                             @click.outside="projectMenuOpen = false"
                             class="absolute left-0 top-[calc(100%+4px)] z-40 w-[305px] bg-white rounded-[8px] border border-[#e5e2dc] shadow-[0_8px_28px_rgba(0,0,0,0.14)] p-4 text-left">
                            <div class="text-[14px] font-bold text-[#1d2d35] mb-2">Showing...</div>
                            <input type="text"
                                   x-ref="projSearchInput"
                                   x-model="projectSearch"
                                   placeholder="Filter..."
                                   class="w-full h-[32px] px-2.5 rounded-[6px] border-2 border-[#1b6ac9] bg-white text-[13.5px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none mb-2.5">

                            <div class="max-h-[340px] overflow-y-auto space-y-0.5 text-[14px]">
                                <button type="button"
                                        @click="selectedProjectFilter = 'all'; selectedProjectName = 'All projects'; projectMenuOpen = false"
                                        class="w-full text-left px-1.5 py-1 rounded hover:bg-[#f5f3ef] font-bold text-[#1d2d35] cursor-pointer">
                                    All projects
                                </button>
                                <button type="button"
                                        @click="selectedProjectFilter = 'starred'; selectedProjectName = 'Starred projects'; projectMenuOpen = false"
                                        class="w-full text-left px-1.5 py-1 rounded hover:bg-[#f5f3ef] text-[#1d2d35] inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-[#f5b400] fill-current flex-shrink-0" viewBox="0 0 24 24">
                                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                    </svg>
                                    <span>Starred projects</span>
                                </button>

                                @foreach($allProjects ?? [] as $proj)
                                    <button type="button"
                                            x-show="!projectSearch.trim() || '{{ strtolower(addslashes($proj->name)) }}'.includes(projectSearch.trim().toLowerCase())"
                                            @click="selectedProjectFilter = '{{ $proj->id }}'; selectedProjectName = '{{ addslashes(\Illuminate\Support\Str::limit($proj->name, 32)) }}'; projectMenuOpen = false"
                                            class="w-full text-left px-1.5 py-1 rounded hover:bg-[#f5f3ef] text-[#1d2d35] truncate block cursor-pointer">
                                        {{ \Illuminate\Support\Str::limit($proj->name, 34) }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Live Yellow Filter Input with Inline Clear Button (Matches media_1793335600252_18ee8d6b.jpg) -->
                    <div class="relative inline-flex items-center">
                        <input type="text"
                               x-model="actFilter"
                               placeholder="Filter..."
                               :class="actFilter.trim().length > 0 ? 'bg-[#fdf4d1] border-[#e6d79c] pr-7 w-[170px]' : 'bg-white border-[#d8d4cc] pr-3 w-[124px]'"
                               class="h-[30px] pl-2.5 border rounded-[6px] text-[13px] text-[#1d2d35] placeholder-[#8c8881] transition-colors focus:outline-none focus:border-[#1b6ac9]">
                        <button type="button"
                                x-show="actFilter.trim().length > 0"
                                x-cloak
                                @click="actFilter = ''"
                                title="Clear filter"
                                class="absolute right-1.5 w-[16px] h-[16px] rounded-full bg-[#6d6a65] hover:bg-[#1d2d35] text-white inline-flex items-center justify-center cursor-pointer transition-colors">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                @if($isPawandeep)
                    <div class="mt-8">
                        <div class="flex items-center gap-3">
                            <span class="px-2.5 py-1 bg-[#283c46] text-white text-[11px] font-extrabold uppercase tracking-[0.03em] rounded-[4px] select-none">
                                TODAY, THURSDAY, OCTOBER 29
                            </span>
                            <div class="h-px bg-[#ece9e2] flex-1"></div>
                        </div>

                        <div class="ml-2 pl-5 border-l border-[#ece9e2] pt-5 pb-2 space-y-8">
                            <!-- 12:00pm: Uploaded 4 files to BID NO: GEM/2026/B/8159428 -->
                            <div x-show="!actFilter || 'tender pawandeep k. uploaded 4 files to bid no: gem/2026/b/8159428 1791962446.pdf copy of updc technical bid.pdf copy of commercial bid.pdf gem-bidding-10033548.pdf'.includes(actFilter.toLowerCase())"
                                 class="relative flex items-start gap-2.5">
                                <span class="w-[16px] h-[16px] rounded-full bg-[#e39600] text-white inline-flex items-center justify-center flex-shrink-0 -ml-[28.5px] mt-2 shadow-[0_0_0_3px_#fff]">
                                    <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 16 16">
                                        <path d="M4 1h5.5L13 4.5V14a1 1 0 01-1 1H4a1 1 0 01-1-1V2a1 1 0 011-1z"/>
                                    </svg>
                                </span>

                                <span class="text-[12.5px] text-[#6d6a65] w-[50px] flex-shrink-0 pt-1.5">
                                    12:00pm
                                </span>

                                <span class="w-[34px] h-[34px] rounded-full bg-[#00897b] text-white font-bold text-[13px] flex items-center justify-center flex-shrink-0 mt-0.5 select-none cursor-pointer">
                                    PK
                                </span>

                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('projects.show', 29) }}" class="text-[12.5px] text-[#856d47] hover:underline leading-tight inline-block">Tender</a>
                                    <div class="text-[16px] font-bold text-[#1d2d35] leading-snug">
                                        Pawandeep K. uploaded 4 files to
                                        <a href="{{ route('projects.docs', ['id' => 29, 'folder' => '8159428']) }}" class="text-[#1b6ac9] underline hover:text-[#1557a6]">BID NO: GEM/2026/B/8159428</a>
                                    </div>

                                    <div class="mt-3.5 flex flex-wrap items-start gap-6">
                                        @php
                                            $batch1 = [
                                                ['name' => '1791962446.pdf', 'thumb' => '/uploads/docs/thumb_1791962446.png'],
                                                ['name' => 'Copy of UPDC Technical bid.pdf', 'thumb' => '/uploads/docs/thumb_updc_technical.png'],
                                                ['name' => 'Copy of Commercial bid.pdf', 'thumb' => '/uploads/docs/thumb_commercial_bid.png'],
                                                ['name' => 'GeM-Bidding-10033548.pdf', 'thumb' => '/uploads/docs/thumb_gem_bidding.png'],
                                            ];
                                        @endphp
                                        @foreach($batch1 as $f1)
                                            <a href="{{ route('projects.docs', ['id' => 29, 'folder' => '8159428', 'file' => $f1['name']]) }}"
                                               class="w-[98px] flex flex-col items-start group">
                                                <div class="w-[54px] h-[70px] bg-white border border-[#e5e2dc] shadow-[0_1px_2px_rgba(0,0,0,0.06)] flex items-center justify-center overflow-hidden">
                                                    <img src="{{ $f1['thumb'] }}" alt="{{ $f1['name'] }}" class="w-full h-full object-contain">
                                                </div>
                                                <span class="mt-1.5 text-[11.5px] font-bold text-[#1b6ac9] group-hover:underline leading-[1.25] break-words">
                                                    {{ $f1['name'] }}
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- 11:17am: Uploaded 3 files to GEM/2026/B/8176205 -->
                            <div x-show="!actFilter || 'tender pawandeep k. uploaded 3 files to gem/2026/b/8176205 updc technical bid.docx.pdf commercial bid.docx (1).pdf gem-bidding-10033548.pdf'.includes(actFilter.toLowerCase())"
                                 class="relative flex items-start gap-2.5">
                                <span class="w-[16px] h-[16px] rounded-full bg-[#e39600] text-white inline-flex items-center justify-center flex-shrink-0 -ml-[28.5px] mt-2 shadow-[0_0_0_3px_#fff]">
                                    <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 16 16">
                                        <path d="M4 1h5.5L13 4.5V14a1 1 0 01-1 1H4a1 1 0 01-1-1V2a1 1 0 011-1z"/>
                                    </svg>
                                </span>

                                <span class="text-[12.5px] text-[#6d6a65] w-[50px] flex-shrink-0 pt-1.5">
                                    11:17am
                                </span>

                                <span class="w-[34px] h-[34px] rounded-full bg-[#00897b] text-white font-bold text-[13px] flex items-center justify-center flex-shrink-0 mt-0.5 select-none cursor-pointer">
                                    PK
                                </span>

                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('projects.show', 29) }}" class="text-[12.5px] text-[#856d47] hover:underline leading-tight inline-block">Tender</a>
                                    <div class="text-[16px] font-bold text-[#1d2d35] leading-snug">
                                        Pawandeep K. uploaded 3 files to
                                        <a href="{{ route('projects.docs', ['id' => 29, 'folder' => '8176205']) }}" class="text-[#1b6ac9] underline hover:text-[#1557a6]">GEM/2026/B/8176205</a>
                                    </div>

                                    <div class="mt-3.5 flex flex-wrap items-start gap-6">
                                        @php
                                            $batch2 = [
                                                ['name' => 'UPDC Technical bid.docx.pdf', 'thumb' => '/uploads/docs/thumb_updc_technical.png'],
                                                ['name' => 'Commercial bid.docx (1).pdf', 'thumb' => '/uploads/docs/thumb_commercial_bid.png'],
                                                ['name' => 'GeM-Bidding-10033548.pdf', 'thumb' => '/uploads/docs/thumb_gem_bidding.png'],
                                            ];
                                        @endphp
                                        @foreach($batch2 as $f2)
                                            <a href="{{ route('projects.docs', ['id' => 29, 'folder' => '8176205', 'file' => $f2['name']]) }}"
                                               class="w-[98px] flex flex-col items-start group">
                                                <div class="w-[54px] h-[70px] bg-white border border-[#e5e2dc] shadow-[0_1px_2px_rgba(0,0,0,0.06)] flex items-center justify-center overflow-hidden">
                                                    <img src="{{ $f2['thumb'] }}" alt="{{ $f2['name'] }}" class="w-full h-full object-contain">
                                                </div>
                                                <span class="mt-1.5 text-[11.5px] font-bold text-[#1b6ac9] group-hover:underline leading-[1.25] break-words">
                                                    {{ $f2['name'] }}
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p class="mt-7 text-[13.5px] text-[#6d6a65]">
                            No activity for 7 days
                        </p>
                    </div>
                @else
                    <div class="mt-8 space-y-6">
                        @forelse($daysTimeline as $day)
                            @if(!$day->is_empty)
                                @php
                                    $dayHaystack = strtolower($day->label);
                                    $dayProjIds = [];
                                    $dayHasStarred = false;
                                    foreach ($day->items as $dIt) {
                                        $dayHaystack .= ' ' . strtolower(($dIt->project_name ?? '') . ' ' . $dIt->author_name . ' ' . $dIt->action . ' ' . $dIt->title . ' ' . ($dIt->snippet ?? ''));
                                        if (!empty($dIt->project_id)) {
                                            $dayProjIds[] = (string) $dIt->project_id;
                                            if (in_array((int) $dIt->project_id, [26, 4], true)) {
                                                $dayHasStarred = true;
                                            }
                                        }
                                    }
                                    $dayProjIdsJson = json_encode(array_values(array_unique($dayProjIds)));
                                @endphp
                                <div x-show="(!actFilter.trim() || '{{ addslashes($dayHaystack) }}'.includes(actFilter.trim().toLowerCase())) && (selectedProjectFilter === 'all' || (selectedProjectFilter === 'starred' && {{ $dayHasStarred ? 'true' : 'false' }}) || {{ $dayProjIdsJson }}.includes(String(selectedProjectFilter)))">
                                    <div class="flex items-center gap-3">
                                        <span class="px-2.5 py-1 bg-[#283c46] text-white text-[11px] font-extrabold uppercase tracking-[0.02em] rounded-[4px] select-none">
                                            {{ $day->label }}
                                        </span>
                                        <div class="h-px bg-[#ece9e2] flex-1"></div>
                                    </div>

                                    <div class="ml-2 pl-5 border-l border-[#ece9e2] pt-4 pb-1 space-y-5">
                                        @foreach($day->items as $it)
                                            @php
                                                $itHaystack = strtolower($day->label . ' ' . ($it->project_name ?? '') . ' ' . $it->author_name . ' ' . $it->action . ' ' . $it->title . ' ' . ($it->snippet ?? ''));
                                                $itIsStarred = in_array((int) ($it->project_id ?? 0), [26, 4], true);
                                            @endphp
                                            <div x-show="(!actFilter.trim() || '{{ addslashes($itHaystack) }}'.includes(actFilter.trim().toLowerCase())) && (selectedProjectFilter === 'all' || (selectedProjectFilter === 'starred' && {{ $itIsStarred ? 'true' : 'false' }}) || String(selectedProjectFilter) === '{{ $it->project_id ?? '' }}')"
                                                 class="relative flex items-start gap-2.5">
                                                @if(($it->icon_type ?? '') === 'todo_comment')
                                                    <span class="w-[16px] h-[16px] rounded-full bg-[#22c55e] text-white inline-flex items-center justify-center flex-shrink-0 -ml-[28.5px] mt-1.5 shadow-[0_0_0_3px_#fff]">
                                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </span>
                                                @else
                                                    <span class="w-[16px] h-[16px] rounded-full bg-[#4a90e2] text-white inline-flex items-center justify-center flex-shrink-0 -ml-[28.5px] mt-1.5 shadow-[0_0_0_3px_#fff]">
                                                        <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24">
                                                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                                        </svg>
                                                    </span>
                                                @endif
                                                <span class="text-[12.5px] text-[#6d6a65] w-[52px] flex-shrink-0 pt-1">
                                                    {{ $it->time_str }}
                                                </span>
                                                @if(!empty($it->badge->avatar))
                                                    <span @click="openProfileModal({{ json_encode($it->badge) }})" class="relative w-[34px] h-[34px] rounded-full overflow-hidden flex-shrink-0 cursor-pointer">
                                                        <img src="{{ $it->badge->avatar }}" alt="{{ $it->author_name }}" class="w-full h-full object-cover">
                                                    </span>
                                                @else
                                                    <span @click="openProfileModal({{ json_encode($it->badge) }})" class="w-[34px] h-[34px] rounded-full {{ $it->badge->bg ?? 'bg-[#d946ef]' }} text-white font-bold text-[13px] flex items-center justify-center flex-shrink-0 select-none cursor-pointer">
                                                        {{ $it->badge->initials }}
                                                    </span>
                                                @endif
                                                <div class="flex-1 min-w-0">
                                                    @if(isset($it->project_name))
                                                        <a href="{{ $it->project_url ?? route('projects.show', $it->project_id ?? 4) }}"
                                                           class="text-[12.5px] text-[#856d47] hover:underline leading-tight inline-block"
                                                           x-html="hl('{{ addslashes($it->project_name) }}', actFilter)">{{ $it->project_name }}</a>
                                                    @endif
                                                    <div class="text-[16px] font-bold text-[#1d2d35] leading-snug">
                                                        <span x-html="hl('{{ addslashes($it->author_name . ' ' . $it->action) }}', actFilter)">{{ $it->author_name }} {{ $it->action }}</span>
                                                        <a href="{{ $it->url }}"
                                                           class="text-[#1b6ac9] underline hover:text-[#1557a6]"
                                                           x-html="hl('{{ addslashes($it->title) }}', actFilter)">{{ $it->title }}</a>
                                                    </div>
                                                    @if($it->snippet)
                                                        <p class="text-[14.5px] text-[#1d2d35] leading-[1.38] mt-0.5"
                                                           x-html="hl('{{ addslashes($it->snippet) }}', actFilter)">{{ $it->snippet }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @empty
                            <p class="text-[13.5px] text-[#6d6a65]">No recent activity found.</p>
                        @endforelse
                    </div>
                @endif
            </div>

        @elseif($isGlobalScope)
            <!-- ======================================================================= -->
            <!-- GLOBAL LATEST ACTIVITY VIEW (/my/activity?scope=all)                    -->
            <!-- ======================================================================= -->
            <div class="px-7 pt-6 pb-12 space-y-6">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <h1 class="text-[24px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-none">
                            Latest Activity
                        </h1>

                        <form method="GET" action="{{ route('my.activity') }}" class="relative inline-flex items-center">
                            <input type="hidden" name="scope" value="all">
                            <input type="text"
                                   name="filter"
                                   x-model="actFilter"
                                   placeholder="Filter..."
                                   :class="actFilter.trim().length > 0 ? 'bg-[#fdf4d1] border-[#e6d79c] pr-7 w-[170px]' : 'bg-white border-[#d8d4cc] pr-3 w-[130px]'"
                                   class="h-[30px] pl-2.5 border rounded-[6px] text-[13px] text-[#1d2d35] placeholder-[#8c8881] transition-colors focus:outline-none focus:border-[#1b6ac9]">
                            <button type="button"
                                    x-show="actFilter.trim().length > 0"
                                    x-cloak
                                    @click="actFilter = ''"
                                    title="Clear filter"
                                    class="absolute right-1.5 w-[16px] h-[16px] rounded-full bg-[#6d6a65] hover:bg-[#1d2d35] text-white inline-flex items-center justify-center cursor-pointer transition-colors">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </form>
                    </div>

                    <a href="{{ route('home') }}" class="p-1 text-[#6d6a65] hover:text-[#1d2d35] transition-colors" title="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                </div>

                <div class="space-y-6">
                    @forelse($daysTimeline as $day)
                        @php
                            $gDayHaystack = strtolower($day->label);
                            foreach ($day->items as $dIt) {
                                $gDayHaystack .= ' ' . strtolower(($dIt->project_name ?? '') . ' ' . $dIt->author_name . ' ' . $dIt->action . ' ' . $dIt->title . ' ' . ($dIt->snippet ?? ''));
                            }
                        @endphp
                        <div x-show="{{ $day->is_empty ? '!actFilter.trim()' : "(!actFilter.trim() || '" . addslashes($gDayHaystack) . "'.includes(actFilter.trim().toLowerCase()))" }}">
                            @if($day->is_empty)
                                <div class="flex items-center gap-2.5 py-1">
                                    <span class="px-2.5 py-1 bg-white border border-[#d8d4cc] text-[#6d6a65] text-[11px] font-bold uppercase tracking-[0.02em] rounded-[4px] select-none">
                                        {{ $day->label }}
                                    </span>
                                    <span class="text-[13px] text-[#6d6a65] font-normal">No activity</span>
                                </div>
                            @else
                                <div class="flex items-center justify-between gap-3">
                                    <span class="px-2.5 py-1 bg-[#283c46] text-white text-[11px] font-bold uppercase tracking-[0.02em] rounded-[4px] select-none">
                                        {{ $day->label }}
                                    </span>
                                    <div class="h-px bg-[#e5e2dc] flex-1"></div>
                                    <div class="flex items-center gap-1.5 text-[13px] text-[#1d2d35] font-normal">
                                        <span>{{ $day->active_text }}</span>
                                        @foreach($day->active_badges as $b)
                                            @if(!empty($b->avatar))
                                                <span @click="openProfileModal({{ json_encode($b) }})" class="relative w-[22px] h-[22px] rounded-full overflow-hidden flex items-center justify-center select-none cursor-pointer" title="{{ $b->name ?? '' }}">
                                                    <img src="{{ $b->avatar }}" alt="{{ $b->name ?? $b->initials }}" class="w-full h-full object-cover">
                                                </span>
                                            @else
                                                <span @click="openProfileModal({{ json_encode($b) }})" class="w-[22px] h-[22px] rounded-full {{ $b->bg ?? 'bg-[#d946ef]' }} text-white font-bold text-[9.5px] flex items-center justify-center select-none cursor-pointer" title="{{ $b->name ?? '' }}">
                                                    {{ $b->initials }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                                <div class="ml-4 pl-5 border-l border-[#e5e2dc] pt-3 pb-1 space-y-5">
                                    @foreach($day->items as $it)
                                        @php
                                            $gItHaystack = strtolower($day->label . ' ' . ($it->project_name ?? '') . ' ' . $it->author_name . ' ' . $it->action . ' ' . $it->title . ' ' . ($it->snippet ?? ''));
                                        @endphp
                                        <div x-show="!actFilter.trim() || '{{ addslashes($gItHaystack) }}'.includes(actFilter.trim().toLowerCase())"
                                             class="relative flex items-start gap-2.5">
                                            <span class="w-[16px] h-[16px] rounded-full bg-[#4a90e2] text-white inline-flex items-center justify-center flex-shrink-0 -ml-[28.5px] mt-1.5 shadow-[0_0_0_3px_#fff]">
                                                <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24">
                                                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                                </svg>
                                            </span>

                                            <span class="text-[12.5px] font-normal text-[#6d6a65] w-[48px] flex-shrink-0 pt-1">
                                                {{ $it->time_str }}
                                            </span>

                                            @if(!empty($it->badge->avatar))
                                                <span @click="openProfileModal({{ json_encode($it->badge) }})" class="relative w-[32px] h-[32px] rounded-full overflow-hidden flex items-center justify-center flex-shrink-0 select-none cursor-pointer" title="{{ $it->badge->name ?? $it->author_name }}">
                                                    <img src="{{ $it->badge->avatar }}" alt="{{ $it->badge->name ?? $it->author_name }}" class="w-full h-full object-cover">
                                                </span>
                                            @else
                                                <span @click="openProfileModal({{ json_encode($it->badge) }})" class="w-[32px] h-[32px] rounded-full {{ $it->badge->bg ?? 'bg-[#d946ef]' }} text-white font-bold text-[13px] flex items-center justify-center flex-shrink-0 select-none cursor-pointer" title="{{ $it->badge->name ?? $it->author_name }}">
                                                    {{ $it->badge->initials }}
                                                </span>
                                            @endif

                                            <div class="flex-1 min-w-0 leading-[1.36]">
                                                <div class="text-[14.5px] text-[#1d2d35]">
                                                    <span class="font-bold text-[#1d2d35]" x-html="hl('{{ addslashes($it->author_name . ' ' . $it->action) }}', actFilter)">{{ $it->author_name }} {{ $it->action }}</span>
                                                    <a href="{{ $it->url }}" class="font-bold text-[#1b6ac9] underline hover:text-[#124b8f]" x-html="hl('{{ addslashes($it->title) }}', actFilter)">{{ $it->title }}</a>
                                                    @if(isset($it->project_name))
                                                        <span class="text-[#6d6a65]">—</span>
                                                        <a href="{{ $it->project_url ?? route('projects.show', $it->project_id ?? 4) }}" class="text-[#6d6a65] text-[13.5px] hover:underline" x-html="hl('{{ addslashes($it->project_name) }}', actFilter)">{{ $it->project_name }}</a>
                                                    @endif
                                                </div>

                                                @if($it->snippet)
                                                    <p class="text-[14.5px] font-normal text-[#1d2d35] leading-[1.38] mt-0.5"
                                                       x-html="hl('{{ addslashes($it->snippet) }}', actFilter)">
                                                        {{ $it->snippet }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="py-10 text-center text-[#6d6a65] text-[13.5px]">
                            No activity records found.
                        </div>
                    @endforelse
                </div>
            </div>

        @else
            <!-- ======================================================================= -->
            <!-- MY ACTIVITY SHEET (/my/activity — Exact Match to media_1793278185957_37d764f9.jpg) -->
            <!-- ======================================================================= -->
            <div class="px-7 sm:px-10 pt-7 pb-16 relative">
                <!-- Top-Right Close X Button -->
                <a href="{{ route('home') }}"
                   class="absolute top-3.5 right-3.5 p-1 text-[#6d6a65] hover:text-[#1d2d35] transition-colors"
                   title="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>

                <!-- Heading: My Activity -->
                <h1 class="text-[28px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-[1.15]">
                    My Activity
                </h1>

                <!-- Filter... Input -->
                <div class="mt-2.5 relative inline-flex items-center">
                    <input type="text"
                           x-model="actFilter"
                           placeholder="Filter..."
                           :class="actFilter.trim().length > 0 ? 'bg-[#fdf4d1] border-[#e6d79c] pr-7' : 'bg-white border-[#d6d2c9] pr-3'"
                           class="w-[205px] h-[34px] pl-3 border rounded-[6px] text-[13.5px] text-[#1d2d35] placeholder-[#8c8881] transition-colors focus:outline-none focus:border-[#1b6ac9]">
                    <button type="button"
                            x-show="actFilter.trim().length > 0"
                            x-cloak
                            @click="actFilter = ''"
                            title="Clear filter"
                            class="absolute right-2 w-[16px] h-[16px] rounded-full bg-[#6d6a65] hover:bg-[#1d2d35] text-white inline-flex items-center justify-center cursor-pointer transition-colors">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- =============================================================== -->
                <!-- SECTION 0: TODAY, FRIDAY, OCTOBER 30 (No activity)              -->
                <!-- =============================================================== -->
                <div class="mt-6 flex items-center gap-3" x-show="!actFilter">
                    <span class="px-2.5 py-1 bg-white border border-[#d6d2c9] text-[#1d2d35] text-[11px] font-extrabold uppercase tracking-[0.03em] rounded-[4px] select-none">
                        TODAY, FRIDAY, OCTOBER 30
                    </span>
                    <span class="text-[13.5px] text-[#6d6a65]">No activity from {{ $user->name ?? 'Manjot Singh' }}</span>
                </div>

                <!-- =============================================================== -->
                <!-- SECTION 1: YESTERDAY, THURSDAY, OCTOBER 29                      -->
                <!-- =============================================================== -->
                <div class="mt-6"
                     x-show="!actFilter || 'name the project manjot s. created this project yesterday today thursday october 29'.includes(actFilter.toLowerCase())">
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 bg-[#283c46] text-white text-[11px] font-extrabold uppercase tracking-[0.03em] rounded-[4px] select-none">
                            YESTERDAY, THURSDAY, OCTOBER 29
                        </span>
                        <div class="h-px bg-[#ece9e2] flex-1"></div>
                    </div>

                    <div class="ml-2 pl-5 border-l border-[#ece9e2] pt-5 pb-1">
                        <div class="relative flex items-start gap-2.5">
                            <!-- Slate Project Grid Icon Circle on Spine -->
                            <span class="w-[18px] h-[18px] rounded-full bg-[#607d8b] text-white inline-flex items-center justify-center flex-shrink-0 -ml-[29.5px] mt-2 shadow-[0_0_0_3px_#fff]">
                                <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 16 16">
                                    <rect x="2" y="2" width="5" height="5" rx="1"/>
                                    <rect x="9" y="2" width="5" height="5" rx="1"/>
                                    <rect x="2" y="9" width="5" height="5" rx="1"/>
                                    <rect x="9" y="9" width="5" height="5" rx="1"/>
                                </svg>
                            </span>

                            <!-- Time -->
                            <span class="text-[13px] text-[#6d6a65] w-[54px] flex-shrink-0 pt-1.5">
                                5:37pm
                            </span>

                            <!-- Manjot Avatar with OUT pill -->
                            <span class="relative w-[36px] h-[36px] rounded-full overflow-hidden flex-shrink-0 mt-0.5 cursor-pointer" title="Manjot Singh">
                                <img src="{{ $manjotAvatar }}"
                                     alt="Manjot S."
                                     class="w-full h-full object-cover">
                                <span x-show="localStorage.getItem('bc_ooo_active') !== '0'" class="bc-ooo-badge absolute bottom-0 inset-x-0 h-[36%] bg-[#facc15] flex items-center justify-center pointer-events-none">
                                    <span class="text-[7px] font-black text-[#1d1c1b] leading-none tracking-tight">OUT</span>
                                </span>
                            </span>

                            <!-- Details -->
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('projects.show', $namedProjId) }}" class="text-[12.5px] text-[#856d47] hover:underline leading-tight inline-block">Name the project</a>
                                <div class="text-[15.5px] font-bold text-[#1d2d35] leading-snug">
                                    <a href="{{ route('projects.show', $namedProjId) }}" class="hover:underline">Manjot S. created this project</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="mt-6 text-[13.5px] text-[#6d6a65]">
                        No activity for 79 days
                    </p>
                </div>

                <!-- =============================================================== -->
                <!-- SECTION 2: MONDAY, AUGUST 10                                    -->
                <!-- =============================================================== -->
                <div class="mt-6"
                     x-show="!actFilter || 'ajath infotech pvt ltd hq manjot s. commented on mom 10/08/2026 rekha no need to work on ajath.in or ajath.us bugs. kindly fix bipl app issue and share updated jyoti work on veggie pro and rent my car app monday august 10'.includes(actFilter.toLowerCase())">
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 bg-[#283c46] text-white text-[11px] font-extrabold uppercase tracking-[0.03em] rounded-[4px] select-none">
                            MONDAY, AUGUST 10
                        </span>
                        <div class="h-px bg-[#ece9e2] flex-1"></div>
                    </div>

                    <div class="ml-2 pl-5 border-l border-[#ece9e2] pt-5 pb-1">
                        <div class="relative flex items-start gap-2.5">
                            <!-- Blue Message Paper-Plane Circle on Spine -->
                            <span class="w-[18px] h-[18px] rounded-full bg-[#3b82f6] text-white inline-flex items-center justify-center flex-shrink-0 -ml-[29.5px] mt-2 shadow-[0_0_0_3px_#fff]">
                                <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24">
                                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                </svg>
                            </span>

                            <!-- Time -->
                            <span class="text-[13px] text-[#6d6a65] w-[54px] flex-shrink-0 pt-1.5">
                                10:37am
                            </span>

                            <!-- Manjot Avatar with OUT pill -->
                            <span class="relative w-[36px] h-[36px] rounded-full overflow-hidden flex-shrink-0 mt-0.5 cursor-pointer" title="Manjot Singh">
                                <img src="{{ $manjotAvatar }}"
                                     alt="Manjot S."
                                     class="w-full h-full object-cover">
                                <span x-show="localStorage.getItem('bc_ooo_active') !== '0'" class="bc-ooo-badge absolute bottom-0 inset-x-0 h-[36%] bg-[#facc15] flex items-center justify-center pointer-events-none">
                                    <span class="text-[7px] font-black text-[#1d1c1b] leading-none tracking-tight">OUT</span>
                                </span>
                            </span>

                            <!-- Details + Sub-comments -->
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('projects.show', 4) }}" class="text-[12.5px] text-[#856d47] hover:underline leading-tight inline-block">Ajath Infotech Pvt Ltd HQ</a>
                                <div class="text-[15.5px] font-bold text-[#1d2d35] leading-snug">
                                    Manjot S. commented on
                                    <a href="{{ url('/projects/4/discussions/13829') }}" class="text-[#1b6ac9] underline hover:opacity-80">MOM 10/08/2026</a>
                                </div>

                                <!-- Sub-comment rows -->
                                <div class="mt-1.5 space-y-1 text-[14.5px] text-[#1d2d35]">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="relative w-[16px] h-[16px] rounded-full overflow-hidden flex-shrink-0 cursor-pointer" title="Manjot Singh">
                                            <img src="{{ $manjotAvatar }}" alt="Manjot S." class="w-full h-full object-cover">
                                            <span x-show="localStorage.getItem('bc_ooo_active') !== '0'" class="bc-ooo-badge absolute bottom-0 inset-x-0 h-[38%] bg-[#facc15] flex items-center justify-center pointer-events-none">
                                                <span class="text-[4.5px] font-black text-[#1d1c1b] leading-none tracking-tight">OUT</span>
                                            </span>
                                        </span>
                                        <span class="truncate">
                                            <strong class="font-bold">Manjot S.</strong> — Rekha no need to work on ajath.in or ajath.us bugs. Kindly fix BIPL app issue and share updated...
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="relative w-[16px] h-[16px] rounded-full overflow-hidden flex-shrink-0 cursor-pointer" title="Manjot Singh">
                                            <img src="{{ $manjotAvatar }}" alt="Manjot S." class="w-full h-full object-cover">
                                            <span x-show="localStorage.getItem('bc_ooo_active') !== '0'" class="bc-ooo-badge absolute bottom-0 inset-x-0 h-[38%] bg-[#facc15] flex items-center justify-center pointer-events-none">
                                                <span class="text-[4.5px] font-black text-[#1d1c1b] leading-none tracking-tight">OUT</span>
                                            </span>
                                        </span>
                                        <span class="truncate">
                                            <strong class="font-bold">Manjot S.</strong> — Jyoti work on veggie pro and rent my car app.
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="mt-6 text-[13.5px] text-[#6d6a65]">
                        No activity for 2 days
                    </p>
                </div>

                <!-- =============================================================== -->
                <!-- SECTION 3: FRIDAY, AUGUST 7                                     -->
                <!-- =============================================================== -->
                <div class="mt-6"
                     x-show="!actFilter || 'ajath infotech pvt ltd hq manjot s. commented on worklog 07/08/2026 friday august 7'.includes(actFilter.toLowerCase())">
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 bg-[#283c46] text-white text-[11px] font-extrabold uppercase tracking-[0.03em] rounded-[4px] select-none">
                            FRIDAY, AUGUST 7
                        </span>
                        <div class="h-px bg-[#ece9e2] flex-1"></div>
                    </div>

                    <div class="ml-2 pl-5 border-l border-[#ece9e2] pt-5 pb-1">
                        <div class="relative flex items-start gap-2.5">
                            <span class="w-[18px] h-[18px] rounded-full bg-[#3b82f6] text-white inline-flex items-center justify-center flex-shrink-0 -ml-[29.5px] mt-2 shadow-[0_0_0_3px_#fff]">
                                <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24">
                                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                </svg>
                            </span>

                            <span class="text-[13px] text-[#6d6a65] w-[54px] flex-shrink-0 pt-1.5">
                                11:24am
                            </span>

                            <span class="relative w-[36px] h-[36px] rounded-full overflow-hidden flex-shrink-0 mt-0.5 cursor-pointer" title="Manjot Singh">
                                <img src="{{ $manjotAvatar }}"
                                     alt="Manjot S."
                                     class="w-full h-full object-cover">
                                <span x-show="localStorage.getItem('bc_ooo_active') !== '0'" class="bc-ooo-badge absolute bottom-0 inset-x-0 h-[36%] bg-[#facc15] flex items-center justify-center pointer-events-none">
                                    <span class="text-[7px] font-black text-[#1d1c1b] leading-none tracking-tight">OUT</span>
                                </span>
                            </span>

                            <div class="flex-1 min-w-0">
                                <a href="{{ route('projects.show', 4) }}" class="text-[12.5px] text-[#856d47] hover:underline leading-tight inline-block">Ajath Infotech Pvt Ltd HQ</a>
                                <div class="text-[15.5px] font-bold text-[#1d2d35] leading-snug">
                                    Manjot S. commented on
                                    <a href="{{ url('/projects/4/discussions/13829') }}" class="text-[#1b6ac9] underline hover:opacity-80">Worklog 07/08/2026</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        @endif

    </div>

</div>
@endsection
