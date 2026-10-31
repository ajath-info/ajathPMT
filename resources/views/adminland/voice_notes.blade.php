@extends('layouts.app')

@section('title', 'Turn Voice Notes on/off')

@section('content')
<div class="max-w-[780px] mx-auto px-4 sm:px-6 pb-20 pt-2" x-data="{ enabled: true }">
    <div class="bc-sheet bg-white overflow-hidden min-h-[640px] text-[#1d2d35]">

        {{-- Top Breadcrumb Header Bar --}}
        <div class="px-6 py-3 border-b border-[#e5e2dc] bg-[#f7f5f2] flex items-center gap-1.5 text-[13px] font-medium text-[#5c5955]">
            <a href="{{ route('adminland.index') }}" class="hover:text-[#1d1c1b] hover:underline">Adminland</a>
        </div>

        {{-- Sheet Body --}}
        <div class="px-8 sm:px-14 pt-8 pb-16">
            <h1 class="text-[28px] sm:text-[32px] font-black text-[#1d1c1b] tracking-tight leading-tight mb-2">Turn Voice Notes on/off</h1>
            <p class="text-[15px] text-[#1d1c1b] leading-relaxed mb-3">
                Voice Notes let your team send voice recordings in Pings and Chats, and attach them to messages, comments, and documents.
            </p>
            <p class="text-[15px] text-[#1d1c1b] leading-relaxed mb-6">
                Note: Turning off Voice Notes doesn&rsquo;t remove existing recordings.
            </p>

            {{-- Toggle Box --}}
            <div class="rounded-[8px] border px-5 py-4 flex items-center gap-3.5 transition-colors"
                 :class="enabled ? 'border-[#a7d7b8] bg-[#eef8f1]' : 'border-[#e5e2dc] bg-[#f7f5f2]'">
                <span class="text-[14px] font-bold text-[#1d1c1b]">Enable Voice Notes</span>

                <button type="button" @click="enabled = !enabled"
                    class="relative inline-flex items-center h-6 w-12 rounded-[5px] p-0.5 transition-colors focus:outline-none"
                    :class="enabled ? 'bg-[#1f883d]' : 'bg-[#7c7872]'">
                    <span x-show="enabled" class="text-[10px] font-bold text-white pl-1.5 pr-1 select-none">On</span>
                    <span class="w-4 h-5 rounded-[3px] bg-white shadow transform transition-transform"
                          :class="enabled ? 'ml-auto' : 'mr-auto'"></span>
                    <span x-show="!enabled" x-cloak class="text-[10px] font-bold text-white pr-1.5 pl-1 select-none">Off</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

