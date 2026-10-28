@extends('layouts.app')

@section('title', 'Help & Support - Ajath Workspace')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
                <i data-lucide="help-circle" class="w-6 h-6 text-blue-600"></i>
                <span>Help & Support</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Guide, keyboard shortcuts, and workspace assistance</p>
        </div>
        <a href="{{ route('home') }}" class="px-3.5 py-1.5 bg-white border border-slate-300 rounded-full text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
            Back to Home
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Shortcuts Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-2xs space-y-4">
            <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                <i data-lucide="command" class="w-4 h-4 text-emerald-600"></i>
                <span>Keyboard Shortcuts</span>
            </h2>
            <div class="space-y-2.5 text-xs">
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-600">Jump / Search anywhere</span>
                    <kbd class="px-2 py-0.5 bg-slate-100 font-mono text-[11px] rounded border border-slate-300 font-bold">Shift + J</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-600">Close modal or drawer</span>
                    <kbd class="px-2 py-0.5 bg-slate-100 font-mono text-[11px] rounded border border-slate-300 font-bold">Esc</kbd>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-600">Quick slash search</span>
                    <kbd class="px-2 py-0.5 bg-slate-100 font-mono text-[11px] rounded border border-slate-300 font-bold">/</kbd>
                </div>
            </div>
        </div>

        <!-- Contact Support -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-2xs space-y-4">
            <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                <i data-lucide="life-buoy" class="w-4 h-4 text-indigo-600"></i>
                <span>Ajath Infotech Workspace Support</span>
            </h2>
            <p class="text-xs text-slate-600 leading-relaxed">
                Need help migrating projects, inviting client partners, or managing project roles? Our operations team is available 24/7.
            </p>
            <div class="space-y-1.5 text-xs font-semibold text-slate-700">
                <p>📧 Email: <a href="mailto:support@ajath.com" class="text-blue-600 hover:underline">support@ajath.com</a></p>
                <p>🌐 Web: <a href="https://ajath.com" target="_blank" class="text-blue-600 hover:underline">ajath.com</a></p>
            </div>
        </div>
    </div>
</div>
@endsection

