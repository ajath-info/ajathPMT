@extends('layouts.app')

@section('title', 'To-dos - ' . $project->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.show', $project->id) }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> {{ $project->name }}
                </a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2.5">
                <i data-lucide="check-square" class="w-6 h-6 text-emerald-600"></i>
                <span>To-dos</span>
            </h1>
        </div>
    </div>

    <!-- Add Task Card -->
    <div x-data="{ open: false }" class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Need to get something done?</h2>
            <button @click="open = !open" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span x-text="open ? 'Close' : 'Add a to-do'"></span>
            </button>
        </div>

        <form x-show="open" x-cloak action="{{ route('projects.tasks.store', $project->id) }}" method="POST" class="pt-4 border-t border-slate-100 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">To-do title *</label>
                <input type="text" name="title" required placeholder="Describe what needs to be done..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-semibold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Extra details (optional)</label>
                <textarea name="description" rows="2" placeholder="Add extra notes, links, or criteria..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Assign to</label>
                    <select name="assigned_to" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">Unassigned</option>
                        @foreach($project->members as $m)
                        <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->pivot->role }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Priority</label>
                    <select name="priority" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="MEDIUM">Medium</option>
                        <option value="LOW">Low</option>
                        <option value="HIGH">High</option>
                        <option value="URGENT">Urgent</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Due on</label>
                    <input type="date" name="due_date" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-colors">
                    Add this to-do
                </button>
            </div>
        </form>
    </div>

    <!-- Task List -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-sm space-y-4">
        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">All To-dos</h2>

        @if($tasks->isEmpty())
        <div class="text-center py-8 text-slate-400 space-y-2">
            <i data-lucide="check-circle" class="w-8 h-8 mx-auto opacity-50"></i>
            <p class="text-xs">No tasks on this list yet. Click "Add a to-do" above to start!</p>
        </div>
        @else
        <div class="divide-y divide-slate-100">
            @foreach($tasks as $task)
            <div class="py-3.5 flex items-start justify-between gap-4 group">
                <form action="{{ route('projects.tasks.toggle', [$project->id, $task->id]) }}" method="POST" class="flex items-start gap-3 flex-1 min-w-0">
                    @csrf
                    <button type="submit" class="mt-0.5 text-slate-400 hover:text-emerald-600 transition-colors">
                        <i data-lucide="{{ $task->status === 'DONE' ? 'check-circle-2' : 'circle' }}" class="w-5 h-5 {{ $task->status === 'DONE' ? 'text-emerald-600' : '' }}"></i>
                    </button>
                    <div class="min-w-0 flex-1 space-y-1">
                        <p class="text-sm font-semibold {{ $task->status === 'DONE' ? 'line-through text-slate-400' : 'text-slate-800' }}">
                            {{ $task->title }}
                        </p>
                        @if($task->description)
                        <p class="text-xs text-slate-500">{{ $task->description }}</p>
                        @endif
                        <div class="flex items-center gap-3 text-[11px] text-slate-400 pt-0.5">
                            @if($task->assignee)
                            <span class="flex items-center gap-1 text-slate-600 font-medium">
                                <i data-lucide="user" class="w-3 h-3 text-slate-400"></i> {{ $task->assignee->name }}
                            </span>
                            @endif
                            @if($task->due_date)
                            <span class="flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3"></i> Due {{ $task->due_date }}
                            </span>
                            @endif
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase
                                {{ $task->priority === 'URGENT' ? 'bg-rose-100 text-rose-700' : '' }}
                                {{ $task->priority === 'HIGH' ? 'bg-amber-100 text-amber-700' : '' }}
                                {{ $task->priority === 'MEDIUM' ? 'bg-slate-100 text-slate-700' : '' }}
                                {{ $task->priority === 'LOW' ? 'bg-slate-50 text-slate-500' : '' }}
                            ">{{ $task->priority }}</span>
                        </div>
                    </div>
                </form>

                <form action="{{ route('projects.tasks.delete', [$project->id, $task->id]) }}" method="POST" onsubmit="return confirm('Delete this task?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1.5 text-slate-300 hover:text-rose-500 opacity-0 group-hover:opacity-100 transition-all" title="Delete">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection

