@extends('layouts.app')

@section('title', 'My Events')

@section('content')
@php
    $now = now()->timezone('Asia/Kolkata');
    $monthYear = $now->format('F Y');
    $monthShort = $now->format('M');
    $dayNum = $now->format('j');
    $dayName = strtoupper($now->format('D'));
@endphp

<div x-data="{ filterQuery: '' }" class="w-full sm:w-[72vw] max-w-[1130px] mx-auto pt-2">
    <div class="bg-white rounded-t-[10px] shadow-[0_1px_4px_rgba(0,0,0,0.07)] border border-[#e5e2dc] border-b-0 min-h-[calc(100vh-88px)] relative pt-8 pb-16">
        
        <!-- Top-Right Close Button (✕) -->
        <a href="{{ route('home') }}" 
           class="absolute top-3.5 right-3.5 p-1 text-[#1d2d35] hover:text-black transition-colors"
           title="Close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </a>

        <!-- Header & Filter Input -->
        <div class="px-8 sm:px-10">
            <h1 class="text-[34px] font-extrabold text-[#1d2d35] tracking-[-0.03em] leading-tight">
                My Events
            </h1>

            <div class="mt-2.5">
                <input type="text"
                       x-model="filterQuery"
                       placeholder="Filter..."
                       class="w-[248px] max-w-full h-[34px] px-3 bg-white border border-[#d6d3cd] rounded-[6px] text-[14.5px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none focus:border-[#1b6ac9] focus:ring-1 focus:ring-[#1b6ac9]">
            </div>

            <!-- Month Section Header -->
            <div class="mt-8">
                <h2 class="text-[22px] font-bold text-[#1d2d35] tracking-[-0.02em] pb-2 border-b border-[#ece9e2]">
                    {{ $monthYear }}
                </h2>
            </div>
        </div>

        <!-- Highlighted Today Row (Full-bleed pale cream #fdfae5 matching media_1793274663689_dc2d8c21.jpg) -->
        <div class="mt-2 px-8 sm:px-10 py-4 bg-[#fdfae5] flex items-center gap-6">
            <!-- Basecamp Calendar Day Icon + TODAY label -->
            <div class="flex flex-col items-center flex-shrink-0">
                <div class="w-[46px] rounded-[6px] border-[1.5px] border-[#1d2d35] bg-white overflow-hidden text-center shadow-2xs">
                    <div class="bg-[#1d2d35] text-white text-[11px] font-bold py-[1.5px] leading-tight">
                        {{ $monthShort }}
                    </div>
                    <div class="text-[18px] font-extrabold text-[#1d2d35] leading-none pt-1">
                        {{ $dayNum }}
                    </div>
                    <div class="text-[9px] font-bold text-[#1d2d35] uppercase tracking-wider pb-1 pt-0.5 leading-none">
                        {{ $dayName }}
                    </div>
                </div>
                <span class="text-[9.5px] font-bold text-[#1d2d35] uppercase tracking-wider mt-1 leading-none">
                    TODAY
                </span>
            </div>

            <!-- Empty State Text -->
            <div class="text-[15.5px] text-[#1d2d35] font-normal -mt-3">
                No events today
            </div>
        </div>

    </div>
</div>
@endsection
