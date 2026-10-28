@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">

    <!-- Company Welcome Banner -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
        <div class="space-y-2 max-w-2xl relative z-10">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    <i data-lucide="building" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span>{{ $currentOrg->name }}</span>
                </span>
                @if($memberships->count() > 1)
                <div x-data="{ open: false }" class="relative inline-block text-left">
                    <button @click="open = !open" class="text-xs text-slate-500 hover:text-slate-800 underline font-medium">Switch Company</button>
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 mt-2 w-56 rounded-2xl bg-white shadow-xl border border-slate-100 py-2 z-50">
                        @foreach($memberships as $m)
                            <a href="{{ route('switch-org', $m->organization_id) }}" class="block px-4 py-2 text-xs font-semibold {{ $m->organization_id == $currentOrg->id ? 'bg-amber-50 text-amber-800' : 'text-slate-700 hover:bg-slate-50' }}">
                                {{ $m->organization->name }} ({{ $m->role }})
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Welcome back, {{ $user->name }}!
            </h1>
            <p class="text-sm text-slate-500 leading-relaxed">
                {{ $currentOrg->description ?: 'Everything your company is working on in one unified, Basecamp-style space.' }}
            </p>
        </div>

        <div class="flex items-center gap-3 relative z-10 w-full sm:w-auto">
            @if(in_array($userRole, ['OWNER', 'ADMIN', 'MEMBER']))
            <a href="{{ route('projects.create') }}" class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-gradient-to-r from-amber-500 to-rose-600 hover:from-amber-600 hover:to-rose-700 text-white font-bold text-sm shadow-lg shadow-amber-500/20 transition-all">
                <i data-lucide="folder-plus" class="w-4 h-4"></i>
                <span>Make a new project</span>
            </a>
            @endif

            @if(in_array($userRole, ['OWNER', 'ADMIN']))
            <a href="{{ route('people.index') }}" class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold text-sm transition-colors">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Add People</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Main Grid: Projects & Sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols: Projects -->
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-extrabold text-slate-900">Projects & Workspaces</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                        {{ $projects->count() }}
                    </span>
                </div>
                <span class="text-xs text-slate-400">
                    {{ $userRole === 'CLIENT' ? 'Only projects you are invited to' : 'Your company workspaces' }}
                </span>
            </div>

            @if($projects->isEmpty())
            <div class="bg-white rounded-3xl p-12 text-center border border-dashed border-slate-300 space-y-4">
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mx-auto">
                    <i data-lucide="folder-kanban" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">No projects yet</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    {{ $userRole === 'CLIENT' ? 'You have not been assigned to any client projects yet.' : 'Create your first project to collaborate with your team, assign to-dos, and share docs.' }}
                </p>
                @if(in_array($userRole, ['OWNER', 'ADMIN', 'MEMBER']))
                <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs">
                    <i data-lucide="plus" class="w-4 h-4"></i> Create First Project
                </a>
                @endif
            </div>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @foreach($projects as $p)
                <a href="{{ route('projects.show', $p->id) }}" class="group bg-white rounded-3xl overflow-hidden border border-slate-200/90 shadow-sm hover:shadow-xl hover:border-amber-500/50 transition-all flex flex-col justify-between">
                    <!-- Cover -->
                    <div class="h-32 w-full relative overflow-hidden bg-slate-100">
                        <img src="{{ $p->cover_url ?: 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?w=800' }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent"></div>
                        <div class="absolute bottom-3 left-4 right-4 flex items-center justify-between">
                            <span class="text-xs font-bold text-white truncate drop-shadow-sm">{{ $p->name }}</span>
                            @if($p->is_client_project)
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-500 text-white shadow-sm">Client Project</span>
                            @endif
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                            {{ $p->description ?: 'No description provided.' }}
                        </p>

                        <!-- Progress Bar -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500">
                                <span>Progress</span>
                                <span class="text-slate-800 font-bold">{{ $p->progress }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-amber-500 to-emerald-500 rounded-full transition-all" style="width: {{ $p->progress }}%"></div>
                            </div>
                        </div>

                        <!-- Footer: Members & Tools preview -->
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                            <div class="flex items-center -space-x-2">
                                @foreach($p->members->take(4) as $m)
                                <img src="{{ $m->avatar_url ?: 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($m->name) }}" class="w-6 h-6 rounded-full border-2 border-white object-cover" title="{{ $m->name }} ({{ $m->pivot->role }})">
                                @endforeach
                                @if($p->members->count() > 4)
                                <div class="w-6 h-6 rounded-full bg-slate-200 border-2 border-white flex items-center justify-center text-[9px] font-bold text-slate-600">
                                    +{{ $p->members->count() - 4 }}
                                </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-3 text-xs text-slate-400">
                                <span class="flex items-center gap-1" title="To-dos">
                                    <i data-lucide="check-square" class="w-3.5 h-3.5"></i> {{ $p->tasks_count }}
                                </span>
                                <span class="flex items-center gap-1" title="Discussions">
                                    <i data-lucide="message-square" class="w-3.5 h-3.5"></i> {{ $p->discussions_count }}
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Right Col: Company Directory & My Tasks -->
        <div class="space-y-6">

            <!-- Company People Card -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-amber-500"></i>
                        <span>People in {{ $currentOrg->name }}</span>
                    </h3>
                    <a href="{{ route('people.index') }}" class="text-xs font-bold text-amber-600 hover:underline">View All</a>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                        <p class="text-[10px] font-bold uppercase text-slate-400">Employees</p>
                        <p class="text-2xl font-black text-slate-800 mt-0.5">{{ $totalMembers }}</p>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                        <p class="text-[10px] font-bold uppercase text-slate-400">Clients</p>
                        <p class="text-2xl font-black text-slate-800 mt-0.5">{{ $totalClients }}</p>
                    </div>
                </div>

                @if(in_array($userRole, ['OWNER', 'ADMIN']))
                <a href="{{ route('people.index') }}" class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition-colors">
                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                    <span>Enroll Employee / Client</span>
                </a>
                @endif
            </div>

            <!-- My Assigned To-dos -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500"></i>
                    <span>My Assigned To-dos</span>
                </h3>

                @if($myTasks->isEmpty())
                <p class="text-xs text-slate-400 py-3 text-center">You have no pending tasks. Great job!</p>
                @else
                <div class="space-y-2.5">
                    @foreach($myTasks as $task)
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-amber-600 truncate max-w-[140px]">{{ $task->project->name }}</span>
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 uppercase">{{ $task->priority }}</span>
                        </div>
                        <p class="text-xs font-semibold text-slate-800">{{ $task->title }}</p>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection

