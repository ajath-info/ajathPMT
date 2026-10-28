@extends('layouts.app')

@section('title', 'My Tasks - Ajath Workspace')

@section('content')
<div x-data="{ 
    filter: 'all',
    createModal: false 
}" class="max-w-4xl mx-auto space-y-6">

    <!-- Top Breadcrumb & Title Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1 transition-colors">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">My Tasks</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1 flex items-center gap-2.5">
                <i data-lucide="check-square" class="w-7 h-7 text-emerald-600"></i>
                <span>My Tasks</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">To-dos assigned to you across projects in {{ session('active_org_name', 'Ajath Workspace') }}</p>
        </div>

        <button @click="createModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-[#199849] hover:bg-[#15803d] text-white font-bold rounded-xl text-xs shadow-2xs transition-all self-start sm:self-auto">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add a to-do</span>
        </button>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 text-xs font-bold bg-white p-1.5 rounded-2xl border border-slate-200/90 shadow-2xs max-w-fit">
        <button @click="filter = 'all'" :class="filter === 'all' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100'" class="px-3.5 py-1.5 rounded-xl transition-all">
            Open ({{ $openTasks->count() }})
        </button>
        <button @click="filter = 'urgent'" :class="filter === 'urgent' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100'" class="px-3.5 py-1.5 rounded-xl transition-all">
            Urgent / High
        </button>
        <button @click="filter = 'dated'" :class="filter === 'dated' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100'" class="px-3.5 py-1.5 rounded-xl transition-all">
            Has Due Date
        </button>
        <button @click="filter = 'done'" :class="filter === 'done' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100'" class="px-3.5 py-1.5 rounded-xl transition-all">
            Completed ({{ $completedTasks->count() }})
        </button>
    </div>

    <!-- Open Tasks Card -->
    <div x-show="filter !== 'done'" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h2 class="text-sm font-black text-slate-800 flex items-center gap-2">
                <span>To-dos to complete</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">{{ $openTasks->count() }}</span>
            </h2>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($openTasks as $t)
            <div x-show="filter === 'all' || (filter === 'urgent' && ('{{ $t->priority }}' === 'URGENT' || '{{ $t->priority }}' === 'HIGH')) || (filter === 'dated' && '{{ $t->due_date }}' !== '')" 
                 class="py-3.5 flex items-start justify-between gap-3 group">
                <div class="flex items-start gap-3">
                    <form action="{{ route('projects.tasks.toggle', [$t->project_id, $t->id]) }}" method="POST" class="pt-0.5">
                        @csrf
                        <input type="checkbox" onchange="this.form.submit()" class="w-4 h-4 rounded text-emerald-600 border-slate-300 cursor-pointer">
                    </form>
                    <div>
                        <a href="{{ route('projects.tasks', $t->project_id) }}" class="text-xs font-bold text-slate-800 hover:text-emerald-700 transition-colors">
                            {{ $t->title }}
                        </a>
                        <div class="flex items-center gap-2 text-[11px] text-slate-400 mt-0.5">
                            <span class="font-medium text-slate-600">{{ $t->project->name ?? 'Project' }}</span>
                            @if($t->due_date)
                            <span>•</span>
                            <span class="text-amber-600 font-semibold flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                Due {{ \Carbon\Carbon::parse($t->due_date)->format('M d, Y') }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $t->priority === 'URGENT' ? 'bg-rose-100 text-rose-700' : ($t->priority === 'HIGH' ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-600') }}">
                    {{ $t->priority }}
                </span>
            </div>
            @empty
            <div class="py-12 text-center text-slate-400 space-y-2">
                <i data-lucide="check-circle-2" class="w-10 h-10 mx-auto text-emerald-500 opacity-60"></i>
                <p class="text-xs font-semibold">You're all caught up! No open tasks assigned to you.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Completed Tasks Card -->
    <div x-show="filter === 'all' || filter === 'done'" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h2 class="text-sm font-black text-slate-400 flex items-center gap-2">
                <i data-lucide="check-check" class="w-4 h-4 text-emerald-600"></i>
                <span>Completed Tasks ({{ $completedTasks->count() }})</span>
            </h2>
        </div>

        <div class="divide-y divide-slate-100 opacity-80">
            @forelse($completedTasks as $t)
            <div class="py-2.5 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="check" class="w-4 h-4 text-emerald-600"></i>
                    <span class="line-through text-slate-400 font-medium">{{ $t->title }}</span>
                </div>
                <span class="text-[10px] text-slate-400 font-semibold">{{ $t->project->name ?? '' }}</span>
            </div>
            @empty
            <p class="text-xs text-slate-400 py-4 text-center">No completed tasks yet.</p>
            @endforelse
        </div>
    </div>

    <!-- Modal: Add a To-do -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <div @click="createModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
            
            <div class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all max-w-md w-full border border-slate-200 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-black text-slate-900">Add a New To-do</h3>
                    <button @click="createModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                @if(isset($projects) && $projects->isNotEmpty())
                <form action="{{ route('projects.tasks.store', $projects->first()->id) }}" method="POST" class="space-y-4" x-data="{ selectedProject: '{{ $projects->first()->id }}' }">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Target Project</label>
                        <select x-model="selectedProject" @change="$el.closest('form').action = '/projects/' + selectedProject + '/tasks'" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                            @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">To-do Description *</label>
                        <input type="text" name="title" required placeholder="What needs to get done?" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Priority</label>
                            <select name="priority" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                                <option value="NORMAL">Normal</option>
                                <option value="HIGH">High Priority</option>
                                <option value="URGENT">Urgent</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Due Date</label>
                            <input type="date" name="due_date" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                        </div>
                    </div>

                    <input type="hidden" name="assigned_to" value="{{ $user->id }}">

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="createModal = false" class="px-4 py-2 text-xs font-bold text-slate-600">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-2xs transition-colors">
                            Add To-do
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
