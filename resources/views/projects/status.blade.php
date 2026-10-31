@extends('layouts.app')

@section('title', 'Archive or delete — ' . $project->name)

@section('content')
<div class="max-w-[760px] mx-auto px-4 sm:px-6 pb-20 pt-2">
    <div class="bg-white rounded-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] overflow-hidden">

        {{-- Top Breadcrumb Header Bar --}}
        <div class="px-6 py-2.5 border-b border-[#ece9e2] bg-[#fbfaf8] flex items-center justify-center text-[13.5px] font-bold">
            <a href="{{ route('projects.show', $project->id) }}" class="text-[#1b6ac9] hover:underline">{{ $project->name }}</a>
        </div>

        {{-- Body --}}
        <div class="px-8 sm:px-14 py-10 text-[#1d2d35]">
            <h1 class="text-[28px] sm:text-[32px] font-black text-[#1d2d35] tracking-tight leading-tight text-center mb-8">
                Archive or delete this project
            </h1>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Option 1: Archive / Unarchive --}}
                <div class="rounded-[10px] border border-[#e5e2dc] bg-[#fbfaf8] p-6 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-full bg-[#e5e2dc] flex items-center justify-center mb-4 text-[#1d2d35]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                        </div>
                        <h2 class="text-[18px] font-extrabold text-[#1d2d35] mb-2">
                            {{ $project->status === 'ARCHIVED' ? 'Unarchive this project' : 'Archive this project' }}
                        </h2>
                        <p class="text-[14px] text-[#5c5955] leading-relaxed mb-6">
                            @if($project->status === 'ARCHIVED')
                                This project is currently archived. Unarchiving it makes it active again so your team can post updates and add to-dos.
                            @else
                                Archiving freezes the project and takes it off everyone&rsquo;s Home screen. Nothing is lost &mdash; everything stays searchable and accessible in All Projects.
                            @endif
                        </p>
                    </div>

                    @if($project->status === 'ARCHIVED')
                        <form action="{{ route('projects.unarchive', $project->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-2.5 px-4 rounded-[6px] bg-[#2da562] hover:bg-[#248f52] text-white font-semibold text-[14px] transition-colors cursor-pointer">
                                Unarchive this project
                            </button>
                        </form>
                    @else
                        <form action="{{ route('projects.archive', ['id' => $project->id, 'redirect_home' => 1]) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-2.5 px-4 rounded-[6px] bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-semibold text-[14px] transition-colors cursor-pointer">
                                Archive this project
                            </button>
                        </form>
                    @endif
                </div>

                {{-- Option 2: Delete / Trash --}}
                <div class="rounded-[10px] border border-[#e5e2dc] bg-[#fbfaf8] p-6 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-full bg-[#fee2e2] flex items-center justify-center mb-4 text-[#dc2626]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <h2 class="text-[18px] font-extrabold text-[#1d2d35] mb-2">
                            Delete this project
                        </h2>
                        <p class="text-[14px] text-[#5c5955] leading-relaxed mb-6">
                            Put this project in the trash. It will be immediately removed from everyone&rsquo;s Home screen and project directory.
                        </p>
                    </div>

                    <form action="{{ route('projects.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Put this project in the trash?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-2.5 px-4 rounded-[6px] bg-[#dc2626] hover:bg-[#b91c1c] text-white font-semibold text-[14px] transition-colors cursor-pointer">
                            Put this project in the trash
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('projects.show', $project->id) }}" class="text-[14px] text-[#5c5955] hover:text-[#1d2d35] underline">
                    Never mind, go back to {{ $project->name }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
