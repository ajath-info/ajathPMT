@extends('layouts.app')

@section('title', 'Account & Settings - Ajath Workspace')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Breadcrumb & Title -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1 transition-colors">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Account & Settings</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1 flex items-center gap-2.5">
                <i data-lucide="settings" class="w-7 h-7 text-slate-700"></i>
                <span>Account & Settings</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Manage notifications, password security, working hours, and workspace preferences.</p>
        </div>

        <a href="{{ route('my.profile') }}" class="flex items-center gap-1.5 px-3.5 py-1.5 bg-white border border-slate-300 rounded-full text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
            <i data-lucide="user" class="w-3.5 h-3.5 text-slate-500"></i>
            <span>View Profile</span>
        </a>
    </div>

    <!-- 1. Notification & Ping Preferences (Basecamp signature feature) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs space-y-5">
        <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
            <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="bell" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-sm font-black text-slate-900">Pings & Notification Schedule</h3>
                <p class="text-xs text-slate-500">Decide when and how Basecamp reaches you with activity alerts and pings.</p>
            </div>
        </div>

        <form action="{{ route('my.settings.update') }}" method="POST" class="space-y-3">
            @csrf
            <div class="space-y-2">
                <label class="flex items-start gap-3 p-3.5 rounded-2xl border border-slate-200/80 hover:bg-slate-50 cursor-pointer transition-colors">
                    <input type="radio" name="notifications" value="always" {{ $notificationPref === 'always' ? 'checked' : '' }} class="mt-1 text-emerald-600">
                    <div>
                        <span class="text-xs font-bold text-slate-900 block">Always send me pings instantly</span>
                        <span class="text-[11px] text-slate-500">Send notifications 24/7 for campfire mentions, task assignments, and discussions.</span>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3.5 rounded-2xl border border-slate-200/80 hover:bg-slate-50 cursor-pointer transition-colors">
                    <input type="radio" name="notifications" value="work_hours" {{ $notificationPref === 'work_hours' ? 'checked' : '' }} class="mt-1 text-emerald-600">
                    <div>
                        <span class="text-xs font-bold text-slate-900 block">Only during work hours (Work Can Wait)</span>
                        <span class="text-[11px] text-slate-500">Only ping me Monday through Friday between 9:00 AM and 6:00 PM {{ $user->timezone }}.</span>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3.5 rounded-2xl border border-slate-200/80 hover:bg-slate-50 cursor-pointer transition-colors">
                    <input type="radio" name="notifications" value="paused" {{ $notificationPref === 'paused' ? 'checked' : '' }} class="mt-1 text-emerald-600">
                    <div>
                        <span class="text-xs font-bold text-slate-900 block">Pause all notifications (Do Not Disturb)</span>
                        <span class="text-[11px] text-slate-500">Mute all alert sounds and badges until you turn them back on.</span>
                    </div>
                </label>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-2xs transition-all">
                    Save Notification Settings
                </button>
            </div>
        </form>
    </div>

    <!-- 2. Password & Security -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs space-y-5">
        <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
            <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-sm font-black text-slate-900">Change Password</h3>
                <p class="text-xs text-slate-500">Keep your account secure with a strong password.</p>
            </div>
        </div>

        <form action="{{ route('my.settings.update') }}" method="POST" class="space-y-4 max-w-lg">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Current Password *</label>
                <input type="password" name="current_password" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">New Password *</label>
                    <input type="password" name="new_password" required minlength="6" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Confirm New Password *</label>
                    <input type="password" name="new_password_confirmation" required minlength="6" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow-2xs transition-all">
                    Update Password
                </button>
            </div>
        </form>
    </div>

    <!-- 3. Workspaces & Multi-tenancy -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs space-y-4">
        <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
            <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <i data-lucide="building-2" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-sm font-black text-slate-900">Your Workspaces</h3>
                <p class="text-xs text-slate-500">Switch between company workspaces you have access to.</p>
            </div>
        </div>

        <div class="divide-y divide-slate-100">
            @foreach($memberships as $m)
            <div class="py-3 flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold text-slate-900">{{ $m->organization->name }}</h4>
                    <p class="text-[11px] text-slate-400">Role: {{ $m->role }} • Joined {{ \Carbon\Carbon::parse($m->joined_at)->format('M Y') }}</p>
                </div>
                @if($m->organization_id == session('active_org_id'))
                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-bold text-xs rounded-full border border-emerald-200">
                    Active Workspace
                </span>
                @else
                <a href="{{ route('switch-org', $m->organization_id) }}" class="px-3 py-1 bg-white border border-slate-300 text-slate-700 font-bold text-xs rounded-xl hover:bg-slate-50">
                    Switch
                </a>
                @endif
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
