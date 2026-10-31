@extends('layouts.app')

@section('title', 'Invitation sent to ' . $invitedName . ' - Basecamp')

@section('content')
<div class="max-w-[590px] mx-auto px-4 sm:px-0 pt-2 pb-20 font-sans text-[#1d2d35]">
    <!-- Compact Basecamp Sheet (Exact Match to media_1793293063846_3a361d59.jpg) -->
    <div class="bc-sheet bg-white rounded-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] px-8 sm:px-12 py-10 text-center">

        <!-- Heading with Green Circle-Check Icon -->
        <h1 class="text-[24px] sm:text-[27px] font-extrabold text-[#1d2d35] tracking-[-0.02em] leading-snug inline-flex items-center justify-center gap-2.5 flex-wrap">
            <span class="w-[26px] h-[26px] rounded-full border-[2px] border-[#4ade80] text-[#4ade80] inline-flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </span>
            <span>Invitation sent to {{ $invitedName }}</span>
        </h1>

        <!-- Subtitle -->
        <p class="mt-2.5 text-[14.5px] text-[#1d2d35] leading-relaxed max-w-[490px] mx-auto">
            They&rsquo;ll get an email with instructions on how to sign in and join your account. Add them to existing projects now or do that later.
        </p>

        <!-- Action Buttons Row -->
        <div class="mt-6 flex flex-wrap items-center justify-center gap-2.5">
            <a href="{{ route('enrollments.accesses.create', ['identifier' => $identifier, 'user_id' => $invitedUserId]) }}"
               class="px-4 py-2 rounded-[6px] bg-[#60a5fa] hover:bg-[#3b82f6] text-[#0f172a] font-semibold text-[13.5px] transition-colors">
                Set up which projects they can see
            </a>
            <a href="{{ route('people.index') }}"
               class="px-4 py-2 rounded-[6px] border border-[#3b4954] text-[#60a5fa] hover:bg-[#f2f0ec] font-normal text-[13.5px] transition-colors">
                Not now, I&rsquo;ll do it later
            </a>
        </div>

    </div>
</div>
@endsection
