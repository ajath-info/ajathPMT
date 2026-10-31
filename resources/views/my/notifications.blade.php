@extends('layouts.app')

@section('title', 'Notification settings and Work Can Wait')

@section('content')
<div class="max-w-[780px] mx-auto px-4 sm:px-6 pb-20 pt-2" x-data="{
    notificationsOn: true,
    browserEnabled: false,
    whatChoice: 'everything',
    remindEvents: true,
    remindTasks: true,
    sendEmail: true,
    popupComputer: true,
    showUnreadCount: false,
    myDevicesChecked: true,
    whenChoice: 'always',
    savedToast: false,
    enableBrowserNotifications() {
        if ('Notification' in window) {
            Notification.requestPermission().then(() => { this.browserEnabled = true; });
        } else {
            this.browserEnabled = true;
        }
    }
}">
    <div class="bc-sheet overflow-hidden min-h-[680px] px-8 sm:px-14 pt-9 pb-16">
        <h1 class="text-[28px] sm:text-[32px] font-black text-[#1d1c1b] tracking-tight leading-tight mb-5">
            Notification settings and Work Can Wait
        </h1>

        {{-- Top Status Banner Box --}}
        <div class="rounded-[8px] border border-[#e5e2dc] bg-[#f7f5f2]/90 px-6 py-5 text-center mb-8">
            <div class="flex items-center justify-center gap-2 text-[16px] font-bold text-[#1d1c1b] mb-1">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                </svg>
                <span x-text="notificationsOn ? 'Notifications are on' : 'Notifications are paused'"></span>
            </div>
            <p class="text-[13.5px] text-[#1d1c1b] mb-3.5"
               x-text="notificationsOn ? 'You’re set to receive email, browser, and mobile notifications for everything all the time.' : 'Notifications are currently turned off across your devices.'"></p>

            <div class="flex items-center justify-center gap-3.5 text-[12.5px]">
                <button type="button" @click="enableBrowserNotifications()"
                        class="px-3 py-1 rounded-[5px] border border-[#d4d0c8] bg-white hover:bg-[#eff6ff] text-[#2563eb] font-medium transition-colors cursor-pointer"
                        x-text="browserEnabled ? 'Browser notifications enabled' : 'Enable notifications in Chrome'"></button>
                <button type="button" @click="notificationsOn = !notificationsOn"
                        class="underline text-[#5c5955] hover:text-[#1d1c1b] cursor-pointer"
                        x-text="notificationsOn ? 'Turn notifications off' : 'Turn notifications back on'"></button>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- SECTION 1: WHAT?                                                  --}}
        {{-- ================================================================= --}}
        <div class="mb-9">
            <div class="flex items-center gap-3 mb-4">
                <h2 class="text-[18px] font-black text-[#1d1c1b] shrink-0">What?</h2>
                <div class="h-px bg-[#e5e2dc] flex-1"></div>
            </div>

            <div class="space-y-4">
                {{-- Radio 1: Notify me about everything --}}
                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input type="radio" name="what_choice" value="everything" x-model="whatChoice"
                           class="mt-1 w-4 h-4 text-[#1d1c1b] focus:ring-0 cursor-pointer">
                    <div>
                        <div class="text-[14.5px] font-medium text-[#1d1c1b]">Notify me about everything</div>
                        <p class="text-[12.5px] text-[#5c5955] leading-relaxed">
                            This includes new messages and comments, to-dos assigned to you, when someone @mentions you, and Chats and Pings you&rsquo;re part of.
                        </p>
                    </div>
                </label>

                {{-- Radio 2: Only notify me when someone sends me a Ping or @mentions me --}}
                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input type="radio" name="what_choice" value="pings_mentions" x-model="whatChoice"
                           class="mt-1 w-4 h-4 text-[#1d1c1b] focus:ring-0 cursor-pointer">
                    <div>
                        <div class="text-[14.5px] font-medium text-[#1d1c1b]">Only notify me when someone sends me a Ping or @mentions me</div>
                        <p class="text-[12.5px] text-[#5c5955] leading-relaxed">
                            Basecamp will only send you a notification when someone sends you a Ping or @mentions you. You can check the sidebar anytime to see what else is new for you.
                        </p>
                    </div>
                </label>
            </div>

            {{-- Reminders Sub-section --}}
            <div class="mt-6">
                <h3 class="text-[13.5px] font-bold text-[#1d1c1b] mb-3">Reminders</h3>

                <div class="space-y-4">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" x-model="remindEvents"
                               class="mt-1 w-4 h-4 rounded-[3px] accent-[#22c55e] cursor-pointer">
                        <div>
                            <div class="text-[14.5px] font-medium text-[#1d1c1b]">Notify me about upcoming events</div>
                            <p class="text-[12.5px] text-[#5c5955] leading-relaxed">
                                You&rsquo;ll receive notifications the day before an event and an hour before it starts. For all-day events, you&rsquo;ll be notified on the day of the event.
                            </p>
                        </div>
                    </label>

                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" x-model="remindTasks"
                               class="mt-1 w-4 h-4 rounded-[3px] accent-[#22c55e] cursor-pointer">
                        <div>
                            <div class="text-[14.5px] font-medium text-[#1d1c1b]">Notify me about upcoming tasks</div>
                            <p class="text-[12.5px] text-[#5c5955] leading-relaxed">
                                You&rsquo;ll receive notifications the day before a to-do or a card is due and each day after until it&rsquo;s completed.
                            </p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- SECTION 2: HOW?                                                   --}}
        {{-- ================================================================= --}}
        <div class="mb-9">
            <div class="flex items-center gap-3 mb-4">
                <h2 class="text-[18px] font-black text-[#1d1c1b] shrink-0">How?</h2>
                <div class="h-px bg-[#e5e2dc] flex-1"></div>
            </div>

            <div class="space-y-4">
                {{-- Checkbox 1: Send me email notifications --}}
                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input type="checkbox" x-model="sendEmail"
                           class="mt-1 w-4 h-4 rounded-[3px] accent-[#22c55e] cursor-pointer">
                    <div>
                        <div class="text-[14.5px] font-medium text-[#1d1c1b]">Send me email notifications</div>
                        <p class="text-[12.5px] text-[#5c5955] leading-relaxed">
                            Note: To prevent your inbox from overflowing, Basecamp will bundle Pings together if they happen within a few minutes of each other. You won&rsquo;t be emailed if you are actively participating in a Ping or Chat in the Basecamp app or on your computer.
                        </p>
                    </div>
                </label>

                {{-- Checkbox 2: Pop up notifications on my computer when Basecamp is open --}}
                <div>
                    <label class="flex items-start gap-2.5 cursor-pointer mb-3">
                        <input type="checkbox" x-model="popupComputer"
                               class="mt-1 w-4 h-4 rounded-[3px] accent-[#22c55e] cursor-pointer">
                        <span class="text-[14.5px] font-medium text-[#1d1c1b]">Pop up notifications on my computer when Basecamp is open</span>
                    </label>

                    {{-- Indented Sub-checkbox with "1 New for you" (On) vs "• New for you" (Off) preview pills --}}
                    <div class="pl-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <label class="flex items-start gap-2.5 cursor-pointer max-w-[390px]">
                            <input type="checkbox" x-model="showUnreadCount"
                                   class="mt-1 w-4 h-4 rounded-[3px] accent-[#22c55e] cursor-pointer">
                            <div>
                                <div class="text-[14px] font-medium text-[#1d1c1b]">Show the number of unread items</div>
                                <p class="text-[12.5px] text-[#5c5955] leading-relaxed">
                                    You&rsquo;ll see the unread count for both Pings and notifications when Basecamp is open in your browser.
                                </p>
                            </div>
                        </label>

                        {{-- Visual Comparison Pills: On vs Off --}}
                        <div class="flex items-center gap-3 shrink-0 pt-0.5">
                            <div class="flex flex-col items-center gap-1">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[6px] bg-[#fae8d4] text-[#1d2d35] text-[11.5px] font-semibold">
                                    <span class="w-4 h-4 rounded-full bg-[#e53935] text-white text-[10px] font-bold inline-flex items-center justify-center leading-none">1</span>
                                    <span>New for you</span>
                                </span>
                                <span class="text-[11.5px] text-[#5c5955]">On</span>
                            </div>

                            <div class="flex flex-col items-center gap-1">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[6px] bg-[#fae8d4] text-[#1d2d35] text-[11.5px] font-semibold">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#e53935] inline-block"></span>
                                    <span>New for you</span>
                                </span>
                                <span class="text-[11.5px] text-[#5c5955]">Off</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Checkbox 3: My devices --}}
                <div class="flex items-start gap-2.5 pt-1">
                    <input type="checkbox" checked disabled
                           class="mt-1 w-4 h-4 rounded-[3px] accent-[#22c55e] opacity-75">
                    <div>
                        <div class="text-[14.5px] font-medium text-[#1d1c1b]">
                            My devices: iPad Pro (12.9-inch), RMX1921, iPhone X, iPhone, iPhone, iPad
                        </div>
                        <p class="text-[12.5px] text-[#5c5955] leading-relaxed">
                            Use the settings on your device to turn notifications on or off. You won&rsquo;t get notifications on your phone or tablet while Basecamp is open on your computer in the frontmost window. You&rsquo;ll get notifications again on your device when you switch away to a different tab or window, close Basecamp or your computer goes to sleep. See
                            <a href="{{ route('my.settings', ['devices' => 1]) }}" class="underline text-[#2563eb] hover:opacity-80">My Devices</a> for details.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- SECTION 3: WHEN?                                                  --}}
        {{-- ================================================================= --}}
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-4">
                <h2 class="text-[18px] font-black text-[#1d1c1b] shrink-0">When?</h2>
                <div class="h-px bg-[#e5e2dc] flex-1"></div>
            </div>

            <div class="space-y-2.5">
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="radio" name="when_choice" value="always" x-model="whenChoice"
                           class="w-4 h-4 text-[#1d1c1b] focus:ring-0 cursor-pointer">
                    <span class="text-[14.5px] font-medium text-[#1d1c1b]">Always! 24/7/365 no matter what.</span>
                </label>

                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="radio" name="when_choice" value="work_hours" x-model="whenChoice"
                           class="w-4 h-4 text-[#1d1c1b] focus:ring-0 cursor-pointer">
                    <span class="text-[14.5px] font-medium text-[#1d1c1b]">Work Can Wait! Only during my work hours...</span>
                </label>
            </div>
        </div>

        {{-- Saved Confirmation Banner --}}
        <div x-show="savedToast" x-cloak class="mb-4 px-4 py-2.5 rounded-[6px] border border-[#bfe3c8] bg-[#eef8f1] text-[13px] font-semibold text-[#1f883d]">
            Your notification settings have been saved!
        </div>

        {{-- Save Button --}}
        <button type="button" @click="savedToast = true"
                class="px-4 py-2 rounded-[6px] bg-[#60a5fa] hover:bg-[#3b82f6] text-[#0f172a] text-[13.5px] font-semibold transition-colors shadow-xs cursor-pointer">
            Save my settings
        </button>
    </div>
</div>
@endsection

