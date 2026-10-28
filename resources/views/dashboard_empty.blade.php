@extends('layouts.app')

@section('title', 'Welcome to Ajath PMT')

@section('content')
<div class="max-w-xl mx-auto py-16 text-center space-y-6">
    <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-3xl flex items-center justify-center mx-auto shadow-sm">
        <i data-lucide="building" class="w-8 h-8"></i>
    </div>
    <div class="space-y-2">
        <h1 class="text-2xl font-black text-slate-900">You don't belong to any company yet</h1>
        <p class="text-sm text-slate-500">Ask your organization administrator or project lead to invite your email address, or create a new company workspace.</p>
    </div>
    <div class="pt-4 flex justify-center gap-3">
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors">
            Log out
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
    </div>
</div>
@endsection

