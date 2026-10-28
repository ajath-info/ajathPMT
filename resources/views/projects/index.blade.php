@extends('layouts.app')

@section('title', 'All Projects - Basecamp')

@section('content')
<div x-data="{ 
    search: '', 
    createModal: false,
    selectedColor: 'white',
    toggleStar(projId, el) {
        fetch('/projects/' + projId + '/toggle-pin', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            const icon = el.querySelector('.star-icon');
            if (data.is_pinned) {
                icon.textContent = '★';
                icon.classList.remove('text-slate-300');
                icon.classList.add('text-amber-400');
            } else {
                icon.textContent = '☆';
                icon.classList.remove('text-amber-400');
                icon.classList.add('text-slate-300');
            }
        })
        .catch(e => console.error(e));
    }
}" class="max-w-[1080px] mx-auto space-y-6 pb-20">

    <!-- Top Action Breadcrumb & Title Bar (Matching Basecamp screenshot) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1 transition-colors">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Home
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">All Projects</span>
            </div>
            
            <div class="flex items-baseline gap-3">
                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">All Projects</h1>
                <span class="text-xs font-bold text-slate-400">
                    ({{ $activeProjects->count() }} active{{ $archivedProjects->count() > 0 ? ', ' . $archivedProjects->count() . ' archived' : '' }})
                </span>
            </div>
        </div>

        <!-- + Make a new project button (Basecamp green button) -->
        @if(in_array($membership->role, ['OWNER', 'ADMIN', 'MEMBER']))
        <button @click="createModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#199849] hover:bg-[#15803d] text-white font-bold rounded-xl text-xs shadow-sm hover:shadow transition-all self-start sm:self-auto">
            <i data-lucide="plus" class="w-4 h-4 stroke-[3]"></i>
            <span>Make a new project</span>
        </button>
        @endif
    </div>

    <!-- Filter Tabs & Quick Search Filter -->
    <div class="bg-white rounded-2xl p-3 border border-slate-200/90 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <!-- Sub-navigation Tabs -->
        <div class="flex items-center gap-2 text-xs font-bold">
            <a href="{{ route('projects.index', ['filter' => 'active']) }}" 
               class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5 {{ $filter !== 'archived' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">
                <span class="w-2 h-2 rounded-full {{ $filter !== 'archived' ? 'bg-emerald-400' : 'bg-slate-400' }}"></span>
                <span>Active projects ({{ $activeProjects->count() }})</span>
            </a>

            <a href="{{ route('projects.index', ['filter' => 'archived']) }}" 
               class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5 {{ $filter === 'archived' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">
                <span class="w-2 h-2 rounded-full {{ $filter === 'archived' ? 'bg-amber-400' : 'bg-slate-400' }}"></span>
                <span>Archived projects ({{ $archivedProjects->count() }})</span>
            </a>
        </div>

        <!-- Live Instant Search Filter -->
        <div class="relative w-full sm:w-72">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
            <input type="text" 
                   x-model="search" 
                   placeholder="Filter projects by name..." 
                   class="w-full pl-9 pr-3.5 py-1.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-emerald-500 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all">
        </div>
    </div>

    <!-- Alphabet Quick-Jump Anchor Bar (Basecamp Style) -->
    @if(count($availableLetters) > 1)
    <div class="bg-white/80 backdrop-blur-xs rounded-xl px-4 py-2 border border-slate-200/70 shadow-2xs flex items-center gap-2 overflow-x-auto text-xs font-bold text-slate-500">
        <span class="text-[10px] uppercase text-slate-400 tracking-wider flex-shrink-0">Jump to:</span>
        @foreach($availableLetters as $l)
        <a href="#letter-{{ $l }}" class="w-6 h-6 rounded-md hover:bg-emerald-50 hover:text-emerald-700 flex items-center justify-center transition-colors flex-shrink-0">
            {{ $l }}
        </a>
        @endforeach
    </div>
    @endif

    <!-- Alphabetized Projects Listing Container -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs space-y-8">

        @php
            $avatarColors = [
                'SN' => 'bg-purple-600',
                'PK' => 'bg-teal-500',
                'DT' => 'bg-emerald-600',
                'RO' => 'bg-blue-600',
                'GK' => 'bg-fuchsia-600',
                'PI' => 'bg-pink-500',
                'E'  => 'bg-orange-500',
                'AI' => 'bg-emerald-700',
                'RS' => 'bg-rose-500',
                'RK' => 'bg-indigo-600',
                'AY' => 'bg-violet-600',
                'IY' => 'bg-cyan-600',
                'AJ' => 'bg-red-500',
                'R'  => 'bg-amber-600',
            ];
        @endphp

        @forelse($groupedProjects as $letter => $projectsInLetter)
        <div id="letter-{{ $letter }}" class="space-y-2 letter-section" x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())">
            
            <!-- Section Letter Header -->
            <div class="flex items-center gap-3 pt-2">
                <span class="text-xl sm:text-2xl font-black text-slate-800 w-8">{{ $letter }}</span>
                <div class="flex-1 h-px bg-slate-200"></div>
                <span class="text-[11px] font-bold text-slate-400">{{ count($projectsInLetter) }} {{ count($projectsInLetter) === 1 ? 'project' : 'projects' }}</span>
            </div>

            <!-- Projects in this Letter -->
            <div class="divide-y divide-slate-100">
                @foreach($projectsInLetter as $proj)
                <div class="py-3.5 px-2 hover:bg-slate-50/80 rounded-2xl transition-colors flex flex-col md:flex-row md:items-center justify-between gap-3 group"
                     x-show="!search || '{{ strtolower(addslashes($proj->name . ' ' . $proj->client_subtitle . ' ' . $proj->description)) }}'.includes(search.toLowerCase())">
                    
                    <!-- Left Column: Star + Title + Subtitle + Tool Links -->
                    <div class="flex items-start gap-3 min-w-0 flex-1">
                        <!-- Star / Pin Toggle Button -->
                        <button type="button" 
                                @click="toggleStar({{ $proj->id }}, $el)" 
                                class="mt-0.5 text-slate-300 hover:text-amber-400 transition-colors flex-shrink-0 cursor-pointer" 
                                title="Star this project">
                            <span class="star-icon text-lg leading-none {{ $proj->is_pinned ? 'text-amber-400' : 'text-slate-300 hover:text-amber-300' }}">
                                {{ $proj->is_pinned ? '★' : '☆' }}
                            </span>
                        </button>

                        <div class="space-y-1 min-w-0 flex-1">
                            <!-- Title & Badges -->
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('projects.show', $proj->id) }}" class="text-sm sm:text-base font-bold text-slate-900 hover:text-[#199849] hover:underline transition-colors">
                                    {{ $proj->name }}
                                </a>

                                @if($proj->badge_text)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-sky-100 text-sky-800 border border-sky-200">
                                    {{ $proj->badge_text }}
                                </span>
                                @endif

                                @if($proj->status === 'ARCHIVED')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                    Archived
                                </span>
                                @endif
                            </div>

                            <!-- Client Subtitle (Matching screenshot lines) -->
                            @if($proj->client_subtitle)
                            <p class="text-xs text-slate-500 font-medium leading-relaxed">
                                {{ $proj->client_subtitle }}
                            </p>
                            @elseif($proj->description)
                            <p class="text-xs text-slate-400 leading-relaxed truncate">
                                {{ $proj->description }}
                            </p>
                            @endif

                            <!-- Direct Tool Links (Identical to Basecamp dock navigation) -->
                            <div class="flex items-center gap-2 pt-1 text-[11px] font-semibold text-slate-400 flex-wrap">
                                <a href="{{ route('projects.discussions', $proj->id) }}" class="hover:text-amber-600 transition-colors flex items-center gap-1">
                                    <i data-lucide="message-square" class="w-3 h-3 text-amber-500"></i>
                                    <span>Message Board</span>
                                </a>
                                <span class="text-slate-300">•</span>
                                <a href="{{ route('projects.tasks', $proj->id) }}" class="hover:text-emerald-600 transition-colors flex items-center gap-1">
                                    <i data-lucide="check-square" class="w-3 h-3 text-emerald-500"></i>
                                    <span>To-dos</span>
                                </a>
                                <span class="text-slate-300">•</span>
                                <a href="{{ route('projects.docs', $proj->id) }}" class="hover:text-purple-600 transition-colors flex items-center gap-1">
                                    <i data-lucide="file-text" class="w-3 h-3 text-purple-500"></i>
                                    <span>Docs & Files</span>
                                </a>
                                <span class="text-slate-300">•</span>
                                <a href="{{ route('projects.chat', $proj->id) }}" class="hover:text-rose-600 transition-colors flex items-center gap-1">
                                    <i data-lucide="flame" class="w-3 h-3 text-rose-500"></i>
                                    <span>Campfire</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Avatar Stack & Management Actions -->
                    <div class="flex items-center gap-3 self-end md:self-center pl-8 md:pl-0 flex-shrink-0">
                        <!-- Overlapping Member Initials Stack -->
                        <div class="flex items-center -space-x-1.5">
                            @foreach($proj->members->take(5) as $m)
                            @php
                                $words = explode(' ', trim($m->name));
                                $inits = count($words) >= 2 
                                    ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                                    : strtoupper(substr($m->name, 0, 2));
                                if (preg_match('/\(([A-Z]{1,2})\)/', $m->name, $matches)) {
                                    $inits = $matches[1];
                                }
                                $bgCol = $avatarColors[$inits] ?? 'bg-slate-600';
                            @endphp
                            <span class="w-6 h-6 rounded-full {{ $bgCol }} border-2 border-white text-[9px] font-extrabold flex items-center justify-center text-white shadow-2xs" title="{{ $m->name }}">
                                {{ $inits }}
                            </span>
                            @endforeach

                            @if($proj->members->count() > 5)
                            <span class="w-6 h-6 rounded-full bg-slate-200 border-2 border-white text-[9px] font-bold flex items-center justify-center text-slate-600 shadow-2xs">
                                +{{ $proj->members->count() - 5 }}
                            </span>
                            @endif
                        </div>

                        <!-- Hover Actions Menu (Archive, Restore, Trash) -->
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            @if($proj->status === 'ACTIVE')
                            <form action="{{ route('projects.archive', $proj->id) }}" method="POST" class="inline" title="Archive this project">
                                @csrf
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-amber-600 rounded-lg hover:bg-slate-200/60 transition-colors">
                                    <i data-lucide="archive" class="w-4 h-4"></i>
                                </button>
                            </form>
                            @else
                            <form action="{{ route('projects.unarchive', $proj->id) }}" method="POST" class="inline" title="Restore project to active">
                                @csrf
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-slate-200/60 transition-colors">
                                    <i data-lucide="archive-restore" class="w-4 h-4"></i>
                                </button>
                            </form>
                            @endif

                            @if(in_array($membership->role, ['OWNER', 'ADMIN']))
                            <form action="{{ route('projects.destroy', $proj->id) }}" method="POST" class="inline" onsubmit="return confirm('Move this project to trash?');" title="Move to trash">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-200/60 transition-colors">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                </div>
                @endforeach
            </div>

        </div>
        @empty
        <div class="py-16 text-center text-slate-400 space-y-3">
            <i data-lucide="folder-search" class="w-12 h-12 mx-auto opacity-40"></i>
            <p class="text-sm font-bold">No {{ $filter === 'archived' ? 'archived' : 'active' }} projects found.</p>
            @if($filter === 'archived')
            <a href="{{ route('projects.index', ['filter' => 'active']) }}" class="inline-block px-4 py-2 bg-slate-900 text-white font-bold rounded-xl text-xs">
                View active projects
            </a>
            @endif
        </div>
        @endforelse

    </div>

    <!-- Bottom Return to Dashboard Link -->
    <div class="text-center pt-4">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Back to Dashboard</span>
        </a>
    </div>

</div>

<!-- Modal: + Make a New Project -->
<div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <div @click="createModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
        
        <div class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all max-w-lg w-full border border-slate-200 p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-[#199849] flex items-center justify-center text-white">
                        <i data-lucide="plus" class="w-3.5 h-3.5 stroke-[3]"></i>
                    </div>
                    <h3 class="text-base font-black text-slate-900">Make a New Project</h3>
                </div>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('projects.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Project Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Adhyatmah Bharat Version 3.0" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Client / Subtitle Label</label>
                    <input type="text" name="client_subtitle" placeholder="e.g. Client: Seller and Driver Apps" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Description (optional)</label>
                    <textarea name="description" rows="2" placeholder="What is this project about?" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <!-- Pastel Card Color Picker -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Project Card Color Theme</label>
                    <div class="grid grid-cols-4 sm:grid-cols-8 gap-2">
                        @php
                            $colors = [
                                'white'  => ['name' => 'White',  'bg' => 'bg-white border-slate-300'],
                                'yellow' => ['name' => 'Yellow', 'bg' => 'bg-[#fef9c3] border-amber-300'],
                                'amber'  => ['name' => 'Amber',  'bg' => 'bg-[#ffedd5] border-amber-400'],
                                'peach'  => ['name' => 'Peach',  'bg' => 'bg-[#ffe4e6] border-rose-300'],
                                'rose'   => ['name' => 'Rose',   'bg' => 'bg-[#fce7f3] border-pink-300'],
                                'purple' => ['name' => 'Purple', 'bg' => 'bg-[#f3e8ff] border-purple-300'],
                                'sky'    => ['name' => 'Sky',    'bg' => 'bg-[#e0f2fe] border-sky-300'],
                                'mint'   => ['name' => 'Mint',   'bg' => 'bg-[#ecfdf5] border-emerald-300'],
                            ];
                        @endphp
                        @foreach($colors as $k => $c)
                        <label class="cursor-pointer text-center">
                            <input type="radio" name="color_theme" value="{{ $k }}" x-model="selectedColor" class="sr-only">
                            <div class="w-8 h-8 mx-auto rounded-xl border-2 {{ $c['bg'] }} flex items-center justify-center transition-all shadow-2xs"
                                 :class="selectedColor === '{{ $k }}' ? 'ring-2 ring-emerald-500 ring-offset-2 scale-110' : 'hover:scale-105'">
                                <i x-show="selectedColor === '{{ $k }}'" data-lucide="check" class="w-4 h-4 text-slate-800"></i>
                            </div>
                            <span class="text-[9px] font-bold text-slate-500 mt-1 block">{{ $c['name'] }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="createModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-[#199849] hover:bg-[#15803d] text-white text-xs font-bold rounded-xl shadow-sm transition-all">
                        Create Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
