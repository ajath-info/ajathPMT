@extends('layouts.app')

@section('title', 'Basecamp')

@section('content')
<div x-data="{ 
    createModal: false, 
    inviteModal: false,
    folderModal: false,
    selectedColor: 'white',
    inviteRole: 'MEMBER',
    toggleStar(projId, el) {
        fetch('/projects/' + projId + '/toggle-pin', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(r => r.json()).then(data => {
            el.dataset.pinned = data.is_pinned;
        }).catch(() => {
            window.location.reload();
        });
    }
}" class="space-y-8">

    <!-- Top Hero Section: "Ai" Gradient Emblem & Action Bar -->
    <div class="flex flex-col items-center justify-center pt-2 pb-4 space-y-4">
        
        <!-- Ai Fluid Ribbon Monogram Logo (matching screenshot) -->
        <div class="relative w-28 h-20 flex items-center justify-center">
            <svg viewBox="0 0 160 120" class="w-full h-full drop-shadow-md">
                <defs>
                    <linearGradient id="aiGradient1" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#ec4899" />
                        <stop offset="50%" stop-color="#a855f7" />
                        <stop offset="100%" stop-color="#6366f1" />
                    </linearGradient>
                    <linearGradient id="aiGradient2" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#6366f1" />
                        <stop offset="100%" stop-color="#3b82f6" />
                    </linearGradient>
                </defs>
                <!-- Stylized "A" loop -->
                <path d="M 35,95 C 20,80 20,40 50,25 C 80,10 90,55 60,85 C 45,100 25,95 25,80 C 25,65 45,55 75,55" 
                      fill="none" stroke="url(#aiGradient1)" stroke-width="18" stroke-linecap="round" stroke-linejoin="round" />
                <!-- Stylized "i" stem & dot -->
                <path d="M 125,50 L 125,95" 
                      fill="none" stroke="url(#aiGradient2)" stroke-width="18" stroke-linecap="round" />
                <circle cx="125" cy="22" r="10" fill="#6366f1" />
            </svg>
        </div>

        <!-- Search / Jump keyboard prompt -->
        <div class="text-xs text-slate-500 font-medium flex items-center gap-1.5 cursor-pointer hover:text-slate-800 transition-colors" @click="searchModal = true">
            <span>Press</span>
            <kbd class="px-1.5 py-0.5 text-[10px] font-bold font-mono bg-white text-slate-700 border border-slate-300 rounded shadow-2xs">SHIFT</kbd>
            <kbd class="px-1.5 py-0.5 text-[10px] font-bold font-mono bg-white text-slate-700 border border-slate-300 rounded shadow-2xs">J</kbd>
            <span>anytime to search or jump</span>
        </div>

        <!-- Basecamp Action Pill Buttons Bar -->
        <div class="flex items-center gap-2 flex-wrap justify-center pt-1">
            @if(in_array($userRole, ['OWNER', 'ADMIN', 'MEMBER']))
            <button @click="createModal = true" class="flex items-center gap-1.5 px-3.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-full border border-slate-300 shadow-2xs transition-all">
                <i data-lucide="plus" class="w-3.5 h-3.5 text-blue-600"></i>
                <span>New project</span>
            </button>
            @endif

            <button @click="folderModal = true" class="flex items-center gap-1.5 px-3.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-full border border-slate-300 shadow-2xs transition-all">
                <i data-lucide="folder" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Add a folder</span>
            </button>

            @if(in_array($userRole, ['OWNER', 'ADMIN']))
            <button @click="inviteModal = true" class="flex items-center gap-1.5 px-3.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-full border border-slate-300 shadow-2xs transition-all">
                <i data-lucide="user-plus" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Invite people</span>
            </button>

            <a href="{{ route('people.index') }}" class="flex items-center gap-1.5 px-3.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-full border border-slate-300 shadow-2xs transition-all">
                <i data-lucide="key" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Adminland</span>
            </a>
            @endif

            <!-- Theme / Droplet Icon -->
            <button @click="createModal = true" class="p-1.5 bg-white hover:bg-slate-50 text-slate-500 hover:text-slate-800 rounded-full border border-slate-300 shadow-2xs transition-all" title="Customize">
                <i data-lucide="droplet" class="w-3.5 h-3.5"></i>
            </button>
        </div>

    </div>

    <!-- The 4-Column Basecamp Project Cards Grid -->
    <div class="max-w-[1360px] mx-auto">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            
            @forelse($projects as $proj)
            @php
                $color = $proj->color_theme ?? 'white';
                $cardStyles = [
                    'yellow' => 'bg-[#fefce8] border-amber-300 hover:border-amber-400',
                    'amber'  => 'bg-[#fffbeb] border-amber-200 border-t-4 border-t-orange-400 hover:border-orange-300',
                    'peach'  => 'bg-[#fff7ed] border-orange-200 border-t-4 border-t-orange-400 hover:border-orange-300',
                    'rose'   => 'bg-[#fdf2f8] border-pink-200 border-t-4 border-t-pink-400 hover:border-pink-300',
                    'purple' => 'bg-[#f5f3ff] border-purple-200 border-t-4 border-t-purple-400 hover:border-purple-300',
                    'sky'    => 'bg-[#f0f9ff] border-sky-300 hover:border-sky-400',
                    'mint'   => 'bg-[#f0fdf4] border-emerald-200 border-t-4 border-t-emerald-500 hover:border-emerald-300',
                    'slate'  => 'bg-[#f8fafc] border-slate-200 hover:border-slate-300',
                    'white'  => 'bg-white border-slate-200 hover:border-slate-300',
                ][$color] ?? 'bg-white border-slate-200 hover:border-slate-300';

                $avatarColors = [
                    'SN' => 'bg-purple-600',
                    'PK' => 'bg-teal-500',
                    'DT' => 'bg-emerald-600',
                    'RO' => 'bg-blue-600',
                    'GK' => 'bg-fuchsia-600',
                    'E'  => 'bg-orange-500',
                    'PI' => 'bg-pink-500',
                    'AI' => 'bg-emerald-700',
                    'RS' => 'bg-rose-500',
                    'RK' => 'bg-indigo-600',
                    'AY' => 'bg-violet-600',
                    'IY' => 'bg-cyan-600',
                ];
            @endphp

            <div class="relative rounded-2xl {{ $cardStyles }} p-4 h-[160px] flex flex-col justify-between transition-all basecamp-card-shadow group">
                
                <!-- Card Header: Title, Subtitle, Star -->
                <div>
                    <div class="flex items-start justify-between gap-2">
                        <a href="{{ route('projects.show', $proj->id) }}" class="font-bold text-sm text-slate-900 hover:text-emerald-700 transition-colors line-clamp-2 leading-snug flex-1">
                            {{ $proj->name }}
                        </a>

                        <!-- Star / Pin Toggle -->
                        <button type="button" 
                                @click="toggleStar({{ $proj->id }}, $el)" 
                                :data-pinned="{{ $proj->is_pinned ? 'true' : 'false' }}"
                                class="text-slate-300 hover:text-amber-400 flex-shrink-0 transition-colors pt-0.5" 
                                title="Star this project">
                            <i data-lucide="star" class="w-4 h-4" :class="$el.dataset.pinned === 'true' ? 'text-amber-400 fill-amber-400' : 'text-slate-300'"></i>
                        </button>
                    </div>

                    <!-- Client Subtitle -->
                    <p class="text-[11px] text-slate-500 mt-1 line-clamp-2 leading-tight">
                        {{ $proj->client_subtitle ?: ($proj->description ?: 'Active project') }}
                    </p>

                    <!-- Badge (e.g. All-access) -->
                    @if($proj->badge_text)
                    <span class="inline-block mt-1.5 px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-sky-100 text-sky-700">
                        {{ $proj->badge_text }}
                    </span>
                    @endif
                </div>

                <!-- Bottom Row: Overlapping Member Avatars & Progress Indicator -->
                <div class="flex items-center justify-between pt-2">
                    <div class="flex items-center -space-x-1.5">
                        @foreach($proj->members->take(6) as $m)
                        @php
                            // Extract 2-letter initials
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

                        @if($proj->members->count() > 6)
                        <span class="w-6 h-6 rounded-full bg-slate-200 border-2 border-white text-[9px] font-bold flex items-center justify-center text-slate-600 shadow-2xs">
                            +{{ $proj->members->count() - 6 }}
                        </span>
                        @endif
                    </div>

                    <!-- Little Progress Donut on card (matching screenshot) -->
                    @if(isset($proj->progress) && $proj->progress > 0)
                    <div class="w-4 h-4 rounded-full border-2 border-orange-300 border-t-orange-500 animate-spin-none" title="{{ $proj->progress }}% completed"></div>
                    @endif
                </div>

            </div>
            @empty
            <div class="col-span-full py-16 text-center text-slate-400 space-y-3">
                <i data-lucide="layers" class="w-12 h-12 mx-auto opacity-40"></i>
                <p class="text-sm font-semibold">No projects found in this workspace.</p>
                @if(in_array($userRole, ['OWNER', 'ADMIN']))
                <button @click="createModal = true" class="px-4 py-2 bg-emerald-600 text-white font-bold rounded-xl text-xs">
                    Create your first project
                </button>
                @endif
            </div>
            @endforelse

        </div>

        <!-- See all projects bottom pill -->
        <div class="text-center pt-8">
            <button class="px-4 py-1.5 bg-slate-200/80 hover:bg-slate-300 text-slate-700 font-semibold rounded-lg text-xs transition-colors">
                See all projects
            </button>
        </div>
    </div>

    <!-- Modal: + New Project -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <div @click="createModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
            
            <div class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all max-w-lg w-full border border-slate-200 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-black text-slate-900">Create a New Project</h3>
                    <button @click="createModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('projects.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Project Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Mobile Apps & Admin Console" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Client / Subtitle Label</label>
                        <input type="text" name="client_subtitle" placeholder="e.g. Client: Seller and Driver Apps" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Description (Optional)</label>
                        <textarea name="description" rows="2" placeholder="Brief scope of work..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <!-- Basecamp Pastel Color Picker -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Card Color Accent</label>
                        <div class="grid grid-cols-5 gap-2">
                            <label class="flex items-center justify-center p-2 rounded-xl bg-white border border-slate-300 cursor-pointer">
                                <input type="radio" name="color_theme" value="white" checked class="text-slate-800">
                                <span class="text-[10px] font-bold text-slate-600 ml-1">White</span>
                            </label>
                            <label class="flex items-center justify-center p-2 rounded-xl bg-[#fefce8] border border-amber-300 cursor-pointer">
                                <input type="radio" name="color_theme" value="yellow" class="text-amber-500">
                                <span class="text-[10px] font-bold text-amber-700 ml-1">Yellow</span>
                            </label>
                            <label class="flex items-center justify-center p-2 rounded-xl bg-[#fff7ed] border border-orange-300 cursor-pointer">
                                <input type="radio" name="color_theme" value="peach" class="text-orange-500">
                                <span class="text-[10px] font-bold text-orange-700 ml-1">Peach</span>
                            </label>
                            <label class="flex items-center justify-center p-2 rounded-xl bg-[#fdf2f8] border border-pink-300 cursor-pointer">
                                <input type="radio" name="color_theme" value="rose" class="text-pink-500">
                                <span class="text-[10px] font-bold text-pink-700 ml-1">Rose</span>
                            </label>
                            <label class="flex items-center justify-center p-2 rounded-xl bg-[#f5f3ff] border border-purple-300 cursor-pointer">
                                <input type="radio" name="color_theme" value="purple" class="text-purple-500">
                                <span class="text-[10px] font-bold text-purple-700 ml-1">Purple</span>
                            </label>
                            <label class="flex items-center justify-center p-2 rounded-xl bg-[#f0f9ff] border border-sky-300 cursor-pointer">
                                <input type="radio" name="color_theme" value="sky" class="text-sky-500">
                                <span class="text-[10px] font-bold text-sky-700 ml-1">Sky</span>
                            </label>
                            <label class="flex items-center justify-center p-2 rounded-xl bg-[#f0fdf4] border border-emerald-300 cursor-pointer">
                                <input type="radio" name="color_theme" value="mint" class="text-emerald-500">
                                <span class="text-[10px] font-bold text-emerald-700 ml-1">Mint</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="createModal = false" class="px-4 py-2 text-xs font-bold text-slate-600">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-colors">
                            Create project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Invite People / Client -->
    <div x-show="inviteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <div @click="inviteModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
            
            <div class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all max-w-lg w-full border border-slate-200 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-black text-slate-900">Add Team Member or Client</h3>
                    <button @click="inviteModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('people.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl">
                        <button type="button" @click="inviteRole = 'MEMBER'" :class="inviteRole !== 'CLIENT' ? 'bg-white shadow text-slate-900 font-bold' : 'text-slate-600 font-semibold'" class="py-2 rounded-lg text-xs transition-all">
                            Team Member
                        </button>
                        <button type="button" @click="inviteRole = 'CLIENT'" :class="inviteRole === 'CLIENT' ? 'bg-white shadow text-slate-900 font-bold' : 'text-slate-600 font-semibold'" class="py-2 rounded-lg text-xs transition-all">
                            Client Partner
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Full Name *</label>
                            <input type="text" name="name" required placeholder="e.g. Deepak Tiwari" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email (Login) *</label>
                            <input type="email" name="email" required placeholder="deepak@company.com" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Password *</label>
                            <input type="password" name="password" required placeholder="Initial password" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div x-show="inviteRole !== 'CLIENT'">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Role</label>
                            <select name="role" :disabled="inviteRole === 'CLIENT'" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                                <option value="MEMBER">Member (Assigned Projects)</option>
                                <option value="ADMIN">Admin (All Projects)</option>
                            </select>
                        </div>

                        <template x-if="inviteRole === 'CLIENT'">
                            <input type="hidden" name="role" value="CLIENT">
                        </template>

                        <div x-show="inviteRole === 'CLIENT'" class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Client Company *</label>
                            <input type="text" name="client_company" placeholder="e.g. NDC Homes" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                        </div>
                    </div>

                    <div class="space-y-1.5 pt-2 border-t border-slate-100">
                        <label class="block text-xs font-bold text-slate-700">Assign to Projects</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 max-h-36 overflow-y-auto p-2 bg-slate-50 rounded-xl border border-slate-200">
                            @foreach($projects as $p)
                            <label class="flex items-center gap-2 p-1.5 rounded-lg bg-white border border-slate-100 text-xs font-semibold cursor-pointer">
                                <input type="checkbox" name="project_ids[]" value="{{ $p->id }}" class="rounded text-emerald-600">
                                <span class="truncate">{{ $p->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="inviteModal = false" class="px-4 py-2 text-xs font-bold text-slate-600">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-colors">
                            Send Login Access
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Add a Folder -->
    <div x-show="folderModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <div @click="folderModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
            
            <div class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all max-w-sm w-full border border-slate-200 p-6 space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h3 class="text-sm font-black text-slate-900">Add a Project Folder</h3>
                    <button @click="folderModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <p class="text-xs text-slate-500">Group related client and internal projects into categorized folders.</p>
                <div>
                    <input type="text" placeholder="e.g. 2026 Client Deliverables" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button @click="folderModal = false" class="px-3 py-1.5 text-xs font-bold text-slate-600">Cancel</button>
                    <button @click="folderModal = false" class="px-4 py-1.5 bg-emerald-600 text-white font-bold rounded-xl text-xs">Save Folder</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
