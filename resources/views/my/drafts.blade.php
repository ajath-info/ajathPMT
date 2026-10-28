@extends('layouts.app')

@section('title', 'My Drafts - Ajath Workspace')

@section('content')
<div x-data="{ 
    filter: 'all',
    createModal: false 
}" class="max-w-4xl mx-auto space-y-6">

    <!-- Top Breadcrumb & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1 transition-colors">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">My Drafts</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1 flex items-center gap-2.5">
                <i data-lucide="file-edit" class="w-7 h-7 text-indigo-600"></i>
                <span>My Drafts</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Unpublished messages, to-do lists, and document drafts you started and haven't posted yet.</p>
        </div>

        <button @click="createModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-[#199849] hover:bg-[#15803d] text-white font-bold rounded-xl text-xs shadow-2xs transition-all self-start sm:self-auto">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>New Draft</span>
        </button>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 text-xs font-bold bg-white p-1.5 rounded-2xl border border-slate-200/90 shadow-2xs max-w-fit">
        <button @click="filter = 'all'" :class="filter === 'all' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100'" class="px-3.5 py-1.5 rounded-xl transition-all">
            All ({{ count($drafts) }})
        </button>
        <button @click="filter = 'Discussion'" :class="filter === 'Discussion' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100'" class="px-3.5 py-1.5 rounded-xl transition-all">
            Discussions
        </button>
        <button @click="filter = 'To-do List'" :class="filter === 'To-do List' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100'" class="px-3.5 py-1.5 rounded-xl transition-all">
            To-do Lists
        </button>
        <button @click="filter = 'Document'" :class="filter === 'Document' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100'" class="px-3.5 py-1.5 rounded-xl transition-all">
            Docs & Notes
        </button>
    </div>

    <!-- Drafts List (Basecamp Card Fidelity) -->
    <div class="space-y-3">
        @forelse($drafts as $d)
        <div x-show="filter === 'all' || filter === '{{ $d['type'] }}'" class="p-5 bg-white rounded-2xl border border-slate-200/90 hover:border-slate-300 shadow-2xs transition-all group flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1.5 min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider {{ $d['type'] === 'Discussion' ? 'bg-amber-100 text-amber-800' : ($d['type'] === 'To-do List' ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800') }}">
                        {{ $d['type'] }}
                    </span>
                    <span class="text-xs font-semibold text-slate-400">in {{ $d['project_name'] }}</span>
                </div>
                <h3 class="text-sm font-bold text-slate-900 group-hover:text-emerald-700 transition-colors">
                    {{ $d['title'] }}
                </h3>
                <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                    {{ $d['content'] }}
                </p>
                <p class="text-[10px] text-slate-400">
                    Last modified {{ $d['updated_at'] }}
                </p>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-center flex-shrink-0">
                @if(!empty($d['project_id']))
                <a href="{{ route('projects.show', $d['project_id']) }}" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold rounded-xl transition-colors">
                    Resume
                </a>
                @endif
                <form action="{{ route('my.drafts.delete', $d['id']) }}" method="POST" onsubmit="return confirm('Discard this draft?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors" title="Discard draft">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="py-16 text-center text-slate-400 bg-white rounded-3xl border border-slate-200/90 shadow-2xs space-y-3">
            <i data-lucide="file-check-2" class="w-12 h-12 mx-auto text-emerald-500 opacity-60"></i>
            <h3 class="text-sm font-black text-slate-800">You're all clear! No unfinished drafts.</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">Whenever you start writing a message or doc and navigate away, it will be safely kept here.</p>
        </div>
        @endforelse
    </div>

    <!-- Modal: New Draft -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <div @click="createModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
            
            <div class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all max-w-lg w-full border border-slate-200 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-black text-slate-900">Create a New Draft</h3>
                    <button @click="createModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('my.drafts.save') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Draft Type</label>
                        <select name="type" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="Discussion">Message / Discussion Post</option>
                            <option value="To-do List">To-do Checklist</option>
                            <option value="Document">Document / Note</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Destination Project</label>
                        <select name="project_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                            @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Draft Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Q4 Release Strategy..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Draft Content</label>
                        <textarea name="content" rows="4" placeholder="Start typing thoughts, items, or notes..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="createModal = false" class="px-4 py-2 text-xs font-bold text-slate-600">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-2xs transition-colors">
                            Save to My Drafts
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

