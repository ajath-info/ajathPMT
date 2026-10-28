@extends('layouts.app')

@section('title', $project->name)

@section('content')
<div class="space-y-8">

    <!-- Project Header -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 space-y-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1">
                        <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
                    </a>
                    <span class="text-slate-300">/</span>
                    <a href="{{ route('projects.index') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1">
                        All Projects
                    </a>
                    <span class="text-slate-300">/</span>
                    <span class="text-xs font-bold {{ $project->status === 'ACTIVE' ? 'text-emerald-600' : 'text-amber-600' }} uppercase tracking-wider">{{ $project->status }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                    <span>{{ $project->name }}</span>
                    @if($project->members->contains(fn($m) => ($m->pivot?->role ?? '') === 'PROJECT_CLIENT'))
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Client Accessible
                    </span>
                    @endif
                </h1>
                <p class="text-sm text-slate-500 max-w-3xl leading-relaxed">
                    {{ $project->description ?: 'No project description added yet.' }}
                </p>
            </div>

            <!-- Team Avatars Stack -->
            <div class="flex items-center gap-3 bg-slate-50 p-2.5 rounded-2xl border border-slate-100 self-stretch sm:self-auto justify-between sm:justify-start">
                <div class="flex items-center -space-x-2">
                    @foreach($project->members as $m)
                    <img src="{{ $m->avatar_url ?: 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($m->name) }}" class="w-8 h-8 rounded-full border-2 border-white object-cover shadow-sm" title="{{ $m->name }} ({{ $m->pivot?->role ?? 'MEMBER' }})">
                    @endforeach
                </div>
                <span class="text-xs font-bold text-slate-700 pr-2">{{ $project->members->count() }} on project</span>
            </div>
        </div>

        <!-- Progress bar -->
        <div class="space-y-1.5 pt-2">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
                <span>Milestone Progress</span>
                <span class="text-slate-900 font-bold">{{ $doneTasks }} of {{ $totalTasks }} to-dos completed ({{ $progress }}%)</span>
            </div>
            <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-amber-500 to-emerald-500 rounded-full transition-all" style="width: {{ $progress }}%"></div>
            </div>
        </div>
    </div>

    <!-- The Basecamp 6 Tools Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Tool 1: Message Board (Discussions) -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between overflow-hidden">
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i data-lucide="message-square" class="w-5 h-5"></i>
                        </div>
                        <h2 class="text-base font-bold text-slate-900">Message Board</h2>
                    </div>
                    <a href="{{ route('projects.discussions', $project->id) }}" class="text-xs font-bold text-amber-600 hover:underline">Open</a>
                </div>
                <p class="text-xs text-slate-500">Post announcements, pitch ideas, and discuss updates with your team.</p>

                <div class="space-y-2.5 pt-2">
                    @forelse($discussions as $disc)
                    <a href="{{ route('projects.discussions.show', [$project->id, $disc->id]) }}" class="block p-3 rounded-2xl bg-slate-50 hover:bg-amber-50/50 transition-colors border border-slate-100">
                        <span class="text-[10px] font-bold uppercase text-amber-600">{{ $disc->category }}</span>
                        <p class="text-xs font-bold text-slate-800 truncate">{{ $disc->title }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">by {{ $disc->author->name }}</p>
                    </a>
                    @empty
                    <p class="text-xs text-slate-400 text-center py-4">No discussions posted yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="p-4 bg-slate-50/60 border-t border-slate-100 text-center">
                <a href="{{ route('projects.discussions', $project->id) }}" class="text-xs font-bold text-slate-700 hover:text-amber-600 flex items-center justify-center gap-1">
                    <span>View all messages</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- Tool 2: To-dos & Tasks -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between overflow-hidden">
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="check-square" class="w-5 h-5"></i>
                        </div>
                        <h2 class="text-base font-bold text-slate-900">To-dos</h2>
                    </div>
                    <a href="{{ route('projects.tasks', $project->id) }}" class="text-xs font-bold text-emerald-600 hover:underline">Open</a>
                </div>
                <p class="text-xs text-slate-500">Make lists of work that needs to get done and assign team members.</p>

                <div class="space-y-2.5 pt-2">
                    @forelse($tasks as $t)
                    <form action="{{ route('projects.tasks.toggle', [$project->id, $t->id]) }}" method="POST" class="flex items-start gap-2.5 p-3 rounded-2xl bg-slate-50 border border-slate-100 group">
                        @csrf
                        <button type="submit" class="mt-0.5 text-slate-400 hover:text-emerald-600 transition-colors">
                            <i data-lucide="{{ $t->status === 'DONE' ? 'check-circle-2' : 'circle' }}" class="w-4 h-4 {{ $t->status === 'DONE' ? 'text-emerald-600' : '' }}"></i>
                        </button>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold {{ $t->status === 'DONE' ? 'line-through text-slate-400' : 'text-slate-800' }} truncate">{{ $t->title }}</p>
                            @if($t->assignee)
                            <p class="text-[10px] text-slate-400">Assigned: {{ $t->assignee->name }}</p>
                            @endif
                        </div>
                    </form>
                    @empty
                    <p class="text-xs text-slate-400 text-center py-4">No tasks in this project yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="p-4 bg-slate-50/60 border-t border-slate-100 text-center">
                <a href="{{ route('projects.tasks', $project->id) }}" class="text-xs font-bold text-slate-700 hover:text-emerald-600 flex items-center justify-center gap-1">
                    <span>Manage all {{ $totalTasks }} to-dos</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- Tool 3: Docs & Files -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between overflow-hidden">
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                        </div>
                        <h2 class="text-base font-bold text-slate-900">Docs & Files</h2>
                    </div>
                    <a href="{{ route('projects.docs', $project->id) }}" class="text-xs font-bold text-purple-600 hover:underline">Open</a>
                </div>
                <p class="text-xs text-slate-500">Share scopes, specifications, notes, roadmaps, and project assets.</p>

                <div class="space-y-2.5 pt-2">
                    @forelse($docs as $doc)
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <p class="text-xs font-bold text-slate-800 truncate">{{ $doc->title }}</p>
                        <p class="text-[10px] text-slate-400">by {{ $doc->author->name }}</p>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 text-center py-4">No documents created yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="p-4 bg-slate-50/60 border-t border-slate-100 text-center">
                <a href="{{ route('projects.docs', $project->id) }}" class="text-xs font-bold text-slate-700 hover:text-purple-600 flex items-center justify-center gap-1">
                    <span>View all documents</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- Tool 4: Campfire Chat -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between overflow-hidden">
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                            <i data-lucide="flame" class="w-5 h-5"></i>
                        </div>
                        <h2 class="text-base font-bold text-slate-900">Campfire</h2>
                    </div>
                    <a href="{{ route('projects.chat', $project->id) }}" class="text-xs font-bold text-rose-600 hover:underline">Open</a>
                </div>
                <p class="text-xs text-slate-500">Fast, informal live chat with everyone in this project room.</p>

                <div class="space-y-2.5 pt-2 max-h-48 overflow-y-auto">
                    @forelse($messages as $msg)
                    <div class="p-2.5 rounded-2xl bg-slate-50 border border-slate-100 space-y-0.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-800">{{ $msg->sender->name }}</span>
                            <span class="text-[9px] text-slate-400">{{ $msg->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-slate-600">{{ $msg->content }}</p>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 text-center py-4">No messages yet. Jump in!</p>
                    @endforelse
                </div>
            </div>

            <div class="p-4 bg-slate-50/60 border-t border-slate-100 text-center">
                <a href="{{ route('projects.chat', $project->id) }}" class="text-xs font-bold text-slate-700 hover:text-rose-600 flex items-center justify-center gap-1">
                    <span>Open Campfire Chat</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- Tool 5: Schedule -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between overflow-hidden">
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center">
                            <i data-lucide="calendar" class="w-5 h-5"></i>
                        </div>
                        <h2 class="text-base font-bold text-slate-900">Schedule</h2>
                    </div>
                    <a href="{{ route('projects.schedule', $project->id) }}" class="text-xs font-bold text-sky-600 hover:underline">Open</a>
                </div>
                <p class="text-xs text-slate-500">Every due date and milestone in this project is collected on the schedule.</p>

                <div class="space-y-2 pt-2">
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] font-bold text-sky-600">TARGET DELIVERY</span>
                        <p class="text-xs font-bold text-slate-800">Sprint Milestones & Deadlines</p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-slate-50/60 border-t border-slate-100 text-center">
                <a href="{{ route('projects.schedule', $project->id) }}" class="text-xs font-bold text-slate-700 hover:text-sky-600 flex items-center justify-center gap-1">
                    <span>View project schedule</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- Tool 6: Automatic Check-ins -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between overflow-hidden">
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center">
                            <i data-lucide="repeat" class="w-5 h-5"></i>
                        </div>
                        <h2 class="text-base font-bold text-slate-900">Automatic Check-ins</h2>
                    </div>
                    <a href="{{ route('projects.checkins', $project->id) }}" class="text-xs font-bold text-teal-600 hover:underline">Open</a>
                </div>
                <p class="text-xs text-slate-500">Ask the team automatic recurring questions so everyone stays in sync.</p>

                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-[10px] font-bold text-teal-600 uppercase">Daily Standup</span>
                    <p class="text-xs font-bold text-slate-800">What did you work on today?</p>
                </div>
            </div>

            <div class="p-4 bg-slate-50/60 border-t border-slate-100 text-center">
                <a href="{{ route('projects.checkins', $project->id) }}" class="text-xs font-bold text-slate-700 hover:text-teal-600 flex items-center justify-center gap-1">
                    <span>Open automatic check-ins</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

    </div>

</div>
@endsection

