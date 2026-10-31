@extends('layouts.app')

@section('title', 'Merge people')

@section('content')
<div class="max-w-[780px] mx-auto px-4 sm:px-6 pb-20 pt-2" x-data="{
    step: 1,
    loserQuery: '',
    winnerQuery: '',
    loserDropdownOpen: false,
    winnerDropdownOpen: false
}">
    <div class="bc-sheet bg-white overflow-hidden min-h-[640px] text-[#1d2d35]">

        {{-- Top Breadcrumb Header Bar --}}
        <div class="px-6 py-3 border-b border-[#e5e2dc] bg-[#f7f5f2] flex items-center gap-1.5 text-[13px] font-medium text-[#5c5955]">
            <a href="{{ route('adminland.index') }}" class="hover:text-[#1d1c1b] hover:underline">Adminland</a>
        </div>

        {{-- Sheet Body --}}
        <div class="px-8 sm:px-14 pt-8 pb-16">
            <h1 class="text-[28px] sm:text-[32px] font-black text-[#1d1c1b] tracking-tight leading-tight mb-2">Merge people</h1>
            <p class="text-[15px] text-[#1d1c1b] leading-relaxed mb-5">
                Merge one person on your account into another. This is useful if someone left your company and someone else is taking over their work. Or if the same person accidentally ended up with two logins on your account.
            </p>

            <h2 class="text-[15px] font-bold text-[#1d1c1b] mb-1">How it works</h2>
            <p class="text-[15px] text-[#1d1c1b] leading-relaxed mb-6">
                You&rsquo;ll pick two people: a winner and a loser. We&rsquo;ll reassign everything from the loser (projects, to-dos, messages, comments, etc.) to the winner. And then we&rsquo;ll permanently remove the loser from the account.
            </p>

            {{-- Step 1 Box --}}
            <div x-show="step === 1" class="rounded-[8px] border border-[#e5e2dc] bg-[#f7f5f2]/80 p-5 mb-10">
                <h3 class="text-[15px] font-bold text-[#1d1c1b] mb-3">
                    Step 1: Choose the <span class="px-1.5 py-0.5 rounded bg-[#fef08a] text-[#1d1c1b] font-bold">loser</span> of the merge
                </h3>

                <div class="relative mb-2" @click.away="loserDropdownOpen = false">
                    <input type="text" x-model="loserQuery" @focus="loserDropdownOpen = true"
                        placeholder="Type a person’s name..."
                        class="w-full px-3.5 py-2 pr-8 rounded-[6px] border border-[#d4d0c8] bg-white text-[14px] text-[#1d1c1b] placeholder-[#7c7872] focus:outline-none focus:border-[#3b82f6]">
                    <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-[#7c7872]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                    </span>

                    <div x-show="loserDropdownOpen" x-cloak
                        class="absolute left-0 right-0 top-full mt-1 rounded-[6px] border border-[#d4d0c8] bg-white shadow-lg z-30 max-h-52 overflow-y-auto divide-y divide-[#e5e2dc]/60">
                        @foreach($people as $person)
                            <button type="button"
                                x-show="!loserQuery || '{{ strtolower(addslashes($person->name)) }}'.includes(loserQuery.toLowerCase())"
                                @click="loserQuery = '{{ addslashes($person->name) }}'; loserDropdownOpen = false"
                                class="w-full px-3.5 py-2 text-left text-[13px] text-[#1d1c1b] hover:bg-[#eff6ff] flex items-center justify-between">
                                <span class="font-semibold">{{ $person->name }}</span>
                                <span class="text-[11px] text-[#7c7872]">{{ $person->email }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <p class="text-[12px] text-[#7c7872] mb-4">
                    The loser will be merged into the winner and then the loser will be removed.
                </p>

                <button type="button" @click="if (loserQuery.trim()) step = 2"
                    class="px-4 py-1.5 rounded-[6px] bg-[#60a5fa] hover:bg-[#3b82f6] text-[#0f172a] text-[13px] font-semibold transition-colors shadow-xs">
                    Next step...
                </button>
            </div>

            {{-- Step 2 Box --}}
            <div x-show="step === 2" x-cloak class="rounded-[8px] border border-[#e5e2dc] bg-[#f7f5f2]/80 p-5 mb-10">
                <h3 class="text-[15px] font-bold text-[#1d1c1b] mb-3">
                    Step 2: Choose the <span class="px-1.5 py-0.5 rounded bg-[#bbf7d0] text-[#1d1c1b] font-bold">winner</span> of the merge (merging <span class="underline" x-text="loserQuery"></span>)
                </h3>

                <div class="relative mb-2" @click.away="winnerDropdownOpen = false">
                    <input type="text" x-model="winnerQuery" @focus="winnerDropdownOpen = true"
                        placeholder="Type a person’s name..."
                        class="w-full px-3.5 py-2 pr-8 rounded-[6px] border border-[#d4d0c8] bg-white text-[14px] text-[#1d1c1b] placeholder-[#7c7872] focus:outline-none focus:border-[#3b82f6]">
                    <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-[#7c7872]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                    </span>

                    <div x-show="winnerDropdownOpen" x-cloak
                        class="absolute left-0 right-0 top-full mt-1 rounded-[6px] border border-[#d4d0c8] bg-white shadow-lg z-30 max-h-52 overflow-y-auto divide-y divide-[#e5e2dc]/60">
                        @foreach($people as $person)
                            <button type="button"
                                x-show="!winnerQuery || '{{ strtolower(addslashes($person->name)) }}'.includes(winnerQuery.toLowerCase())"
                                @click="winnerQuery = '{{ addslashes($person->name) }}'; winnerDropdownOpen = false"
                                class="w-full px-3.5 py-2 text-left text-[13px] text-[#1d1c1b] hover:bg-[#eff6ff] flex items-center justify-between">
                                <span class="font-semibold">{{ $person->name }}</span>
                                <span class="text-[11px] text-[#7c7872]">{{ $person->email }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-2 mt-4">
                    <button type="button" @click="step = 1; loserQuery = ''; winnerQuery = ''"
                        class="px-4 py-1.5 rounded-[6px] bg-[#1f883d] hover:bg-[#1a7433] text-white text-[13px] font-semibold transition-colors shadow-xs">
                        Merge people
                    </button>
                    <button type="button" @click="step = 1"
                        class="px-3.5 py-1.5 rounded-[6px] border border-[#d4d0c8] bg-white text-[13px] font-medium text-[#5c5955] hover:text-[#1d1c1b]">
                        Back
                    </button>
                </div>
            </div>

            {{-- Recent merges --}}
            <div>
                <h3 class="text-[15px] font-bold text-[#1d1c1b] pb-2 border-b border-[#e5e2dc] mb-3">Recent merges</h3>
                <div class="py-2 flex items-center justify-between text-[14px]">
                    <div class="flex items-center gap-2 text-[#1d1c1b]">
                        <span class="font-bold">Himanshi Srivastava</span>
                        <span class="text-[#7c7872]">→</span>
                        <span class="w-6 h-6 rounded-full bg-[#8b3d88] text-white text-[10px] font-bold inline-flex items-center justify-center">HS</span>
                        <span class="font-bold">Himanshi Srivastava</span>
                    </div>
                    <span class="text-[13px] text-[#7c7872]">Finished</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

