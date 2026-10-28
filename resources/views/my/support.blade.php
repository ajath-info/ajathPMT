@extends('layouts.app')

@section('title', 'Help & Support - Ajath Workspace')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Breadcrumb & Title Bar -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1 transition-colors">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Help & Support</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1 flex items-center gap-2.5">
                <i data-lucide="help-circle" class="w-7 h-7 text-blue-600"></i>
                <span>Help & Guides</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Shortcuts, guides, and customer care for Ajath Workspace</p>
        </div>

        <a href="{{ route('home') }}" class="px-3.5 py-1.5 bg-white border border-slate-300 rounded-full text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
            Back to Home
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Shortcuts Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs space-y-4">
            <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                <i data-lucide="command" class="w-4 h-4 text-emerald-600"></i>
                <span>Keyboard Shortcuts</span>
            </h2>
            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <span class="text-slate-700 font-semibold">Jump / Search anywhere</span>
                    <kbd class="px-2 py-0.5 bg-slate-100 font-mono text-[11px] rounded-md border border-slate-300 font-bold">Shift + J</kbd>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <span class="text-slate-700 font-semibold">Close popup, drawer, or modal</span>
                    <kbd class="px-2 py-0.5 bg-slate-100 font-mono text-[11px] rounded-md border border-slate-300 font-bold">Esc</kbd>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <span class="text-slate-700 font-semibold">Quick slash search</span>
                    <kbd class="px-2 py-0.5 bg-slate-100 font-mono text-[11px] rounded-md border border-slate-300 font-bold">/</kbd>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <span class="text-slate-700 font-semibold">Toggle fullscreen view</span>
                    <span class="text-slate-400 font-mono text-[11px]">Top right ⤢</span>
                </div>
            </div>
        </div>

        <!-- Contact Support -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs space-y-4">
            <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                <i data-lucide="life-buoy" class="w-4 h-4 text-indigo-600"></i>
                <span>Direct Support</span>
            </h2>
            <p class="text-xs text-slate-600 leading-relaxed">
                Need help migrating projects, inviting team members, or setting up client permissions? We're here to help.
            </p>
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2 text-xs font-semibold text-slate-700">
                <p>📧 Email: <a href="mailto:support@ajath.com" class="text-blue-600 hover:underline">support@ajath.com</a></p>
                <p>🌐 Website: <a href="https://ajath.com" target="_blank" class="text-blue-600 hover:underline">ajath.com</a></p>
                <p>⚡ Platform: <span class="text-slate-500 font-normal">Ajath Workspace v2.0 (Basecamp Edition)</span></p>
            </div>
        </div>

        <!-- Basecamp Guide & Architecture -->
        <div class="md:col-span-2 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs space-y-4">
            <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                <i data-lucide="book-open" class="w-4 h-4 text-amber-500"></i>
                <span>Basecamp Toolset Reference</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-100 space-y-1">
                    <h4 class="text-xs font-black text-amber-800">Message Board</h4>
                    <p class="text-[11px] text-amber-900/70">Pitch ideas, make company-wide announcements, and document major decisions asynchronously.</p>
                </div>
                <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-100 space-y-1">
                    <h4 class="text-xs font-black text-emerald-800">To-dos</h4>
                    <p class="text-[11px] text-emerald-900/70">Assign tasks, track due dates, set priority levels, and check off milestone deliverables.</p>
                </div>
                <div class="p-4 rounded-2xl bg-rose-50/50 border border-rose-100 space-y-1">
                    <h4 class="text-xs font-black text-rose-800">Campfire</h4>
                    <p class="text-[11px] text-rose-900/70">Casual real-time team chat room for quick questions, casual chatter, and rapid links.</p>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
