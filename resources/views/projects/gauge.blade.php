@extends('layouts.app')

@section('title', 'How far along are we? - ' . $project->name)

@section('content')
@php
    $latestNeedle = !empty($needles) ? $needles[0] : null;
    $initialPercent = $latestNeedle ? (int) $latestNeedle['percent'] : 0;
    $initialStatus = $latestNeedle ? $latestNeedle['status'] : 'on_track';
@endphp

<style>
.bc-needle-privacy-ribbon {
    writing-mode: vertical-rl;
    background-color: #dbeafe;
    color: #1e3a8a;
    border: 1px solid #bfdbfe;
}
html.bc-theme-dark .bc-needle-privacy-ribbon {
    background-color: #1e3a5f !important;
    color: #93c5fd !important;
    border-color: #2563eb !important;
}
</style>

<div class="w-full sm:w-[72vw] max-w-[1000px] mx-auto pt-2 pb-20 px-2 sm:px-4 font-sans text-[#1d2d35]"
     x-data="{
         movingNeedle: {{ $isNewNeedle ? 'true' : 'false' }},
         needlePercent: {{ $initialPercent }},
         projectStatus: '{{ $initialStatus }}',
         notifyChoice: 'team',
         privateToTeam: true,
         draggingNeedle: false,
         openMoveNeedle() {
             this.movingNeedle = true;
             window.history.replaceState({}, '', '{{ route('projects.gauge.new', $project->id) }}');
         },
         closeMoveNeedle() {
             this.movingNeedle = false;
             window.history.replaceState({}, '', '{{ route('projects.gauge', $project->id) }}');
         },
         updateGaugeFromEvent(e) {
             const svg = this.$refs.gaugeSvg;
             if (!svg) return;
             const rect = svg.getBoundingClientRect();
             const clientX = (e.touches && e.touches[0]) ? e.touches[0].clientX : e.clientX;
             const relX = Math.max(0, Math.min(1, (clientX - (rect.left + rect.width * 0.08)) / (rect.width * 0.84)));
             this.needlePercent = Math.round(relX * 100);
         }
     }">

    <!-- Main Basecamp Paper Card -->
    <div class="relative bg-white rounded-t-[10px] rounded-b-[4px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.05)] min-h-[calc(100vh-96px)] overflow-visible">

        <!-- Vertical Right Team Privacy Ribbon (Matches media_1793328743968_61467822.png & media_1793328748773_12479062.png) -->
        <div @click="privateToTeam = !privateToTeam"
             class="hidden lg:flex bc-needle-privacy-ribbon absolute top-2 -right-[25px] z-20 py-3.5 px-1 rounded-r-[6px] items-center gap-1.5 text-[11.5px] font-medium select-none shadow-2xs cursor-pointer">
            <svg class="w-3 h-3 rotate-90 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
            </svg>
            <span>
                <span x-text="privateToTeam ? 'This needle is private to our team.' : 'Clients on this project can see this needle.'">This needle is private to our team.</span>
                <u class="cursor-pointer">Change</u>
            </span>
        </div>

        <!-- Top Breadcrumb Sub-Bar -->
        <div class="px-5 py-2.5 border-b border-[#ece9e2] flex items-center gap-1.5 text-[13.5px] text-[#1d2d35]">
            <a href="{{ route('projects.show', $project->id) }}" class="font-bold text-[#1d2d35] hover:underline">
                {{ $project->name }}
            </a>
            <template x-if="movingNeedle">
                <span class="inline-flex items-center gap-1.5">
                    <span class="text-[#8c8881] text-xs">‹</span>
                    <button type="button" @click="closeMoveNeedle()" class="text-[#1d2d35] hover:underline cursor-pointer">
                        How far along are we?
                    </button>
                </span>
            </template>
        </div>

        <!-- =================================================================== -->
        <!-- VIEW 1: GAUGE OVERVIEW (/projects/{id}/gauge/needles)               -->
        <!-- Matches media_1793328743968_61467822.png                            -->
        <!-- =================================================================== -->
        <div x-show="!movingNeedle" class="px-8 sm:px-12 pt-8 pb-16">
            <h1 class="text-[28px] sm:text-[32px] font-black text-[#1d2d35] tracking-[-0.025em] leading-tight">
                How far along are we?
            </h1>

            <div class="mt-3">
                <button type="button"
                        @click="openMoveNeedle()"
                        class="px-4 py-1.5 rounded-[6px] bg-[#2573d8] hover:bg-[#1e5eb3] text-white font-semibold text-[13.5px] transition-colors cursor-pointer">
                    Move the needle
                </button>
            </div>

            @if(empty($needles))
                <!-- Empty State Dashed Yellow Box (Exact match to media_1793328743968_61467822.png) -->
                <div class="w-[465px] max-w-full mx-auto mt-12 rounded-[8px] border border-dashed border-[#eab308]/80 bg-[#fffef8] px-7 py-6 text-left">
                    <div class="text-[15px] font-bold text-[#4a4640]">
                        The needle hasn’t been moved yet
                    </div>
                    <div class="mt-1 text-[15px] text-[#6d6a65]">
                        Ready to show progress?
                        <button type="button" @click="openMoveNeedle()" class="underline text-[#4a4640] hover:text-[#1d2d35] cursor-pointer">
                            Move the needle
                        </button>
                    </div>
                </div>
            @else
                <!-- Saved Needle Updates Feed -->
                <div class="mt-10 space-y-6 max-w-[680px] mx-auto">
                    @foreach($needles as $nd)
                        <div class="rounded-[10px] border border-[#e5e2dc] bg-[#fbfaf8] p-6">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ $nd['avatar'] ?? '/uploads/avatars/22369668.png' }}" alt="{{ $nd['author'] }}" class="w-8 h-8 rounded-full object-cover">
                                    <div>
                                        <span class="font-bold text-[14.5px] text-[#1d2d35]">{{ $nd['author'] }}</span>
                                        <span class="text-[13px] text-[#6d6a65]">• {{ $nd['created_at'] }}</span>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12.5px] font-bold {{ ($nd['status'] ?? 'on_track') === 'some_risk' ? 'bg-amber-100 text-amber-800' : (($nd['status'] ?? 'on_track') === 'concerned' ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800') }}">
                                    <span class="w-2.5 h-2.5 rounded-full {{ ($nd['status'] ?? 'on_track') === 'some_risk' ? 'bg-[#eab308]' : (($nd['status'] ?? 'on_track') === 'concerned' ? 'bg-[#dc2626]' : 'bg-[#15803d]') }}"></span>
                                    <span>{{ ($nd['status'] ?? 'on_track') === 'some_risk' ? 'Some risk' : (($nd['status'] ?? 'on_track') === 'concerned' ? 'Concerned' : 'On track') }} ({{ $nd['percent'] }}%)</span>
                                </span>
                            </div>
                            @if(!empty($nd['notes']))
                                <div class="mt-3 text-[15px] text-[#1d2d35] leading-relaxed whitespace-pre-line">
                                    {{ $nd['notes'] }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- =================================================================== -->
        <!-- VIEW 2: MOVE THE NEEDLE (/projects/{id}/gauge/needles/new)          -->
        <!-- Matches media_1793328748773_12479062.png - media_1793328774075.png  -->
        <!-- =================================================================== -->
        <div x-show="movingNeedle" x-cloak class="px-6 sm:px-14 pt-9 pb-16">
            <form action="{{ route('projects.gauge.store', $project->id) }}" method="POST">
                @csrf
                <input type="hidden" name="percent" :value="needlePercent">
                <input type="hidden" name="status" :value="projectStatus">

                <h1 class="text-[30px] sm:text-[34px] font-black text-[#1d2d35] tracking-[-0.025em] text-center">
                    How far along are we?
                </h1>

                <!-- Interactive Curved Speedometer Arc Gauge (Matches media_1793328755277 & media_1793328760006) -->
                <div class="mt-6 flex justify-center select-none">
                    <div class="relative w-[360px] max-w-full cursor-pointer"
                         @mousedown="draggingNeedle = true; updateGaugeFromEvent($event)"
                         @mousemove.window="if (draggingNeedle) updateGaugeFromEvent($event)"
                         @mouseup.window="draggingNeedle = false"
                         @click="updateGaugeFromEvent($event)">
                        <svg x-ref="gaugeSvg" viewBox="0 0 380 110" class="w-full h-auto overflow-visible">
                            <!-- Outer Curved Gauge Frame -->
                            <path d="M 28 62 Q 190 12 352 62 L 340 94 Q 190 48 40 94 Z"
                                  fill="#ffffff"
                                  stroke="#cbd5e1"
                                  stroke-width="1.6"/>

                            <!-- Inset Curved Progress Bar Inside Arc (Matches media_1793328755277 & media_1793328760006) -->
                            <clipPath id="bcGaugeInsetBarClip">
                                <path d="M 33 70 Q 190 23 347 70 L 341 85 Q 190 40 39 85 Z"/>
                            </clipPath>
                            <g clip-path="url(#bcGaugeInsetBarClip)">
                                <rect x="28"
                                      y="10"
                                      :width="Math.max(0, (needlePercent / 100) * 324)"
                                      height="90"
                                      :fill="projectStatus === 'some_risk' ? '#f5d487' : (projectStatus === 'concerned' ? '#fca5a5' : '#283c46')"/>
                            </g>

                            <!-- 9 Inner Tick Dividers Along the Arc -->
                            @for($tick = 1; $tick <= 9; $tick++)
                                @php
                                    $t = $tick / 10.0;
                                    $u = 1 - $t;
                                    $topX = ($u * $u * 28) + (2 * $u * $t * 190) + ($t * $t * 352);
                                    $topY = ($u * $u * 62) + (2 * $u * $t * 12) + ($t * $t * 62);
                                    $botX = ($u * $u * 40) + (2 * $u * $t * 190) + ($t * $t * 340);
                                    $botY = ($u * $u * 94) + (2 * $u * $t * 48) + ($t * $t * 94);
                                    $midX = ($topX + $botX) / 2;
                                    $midY = ($topY + $botY) / 2;
                                @endphp
                                <line x1="{{ round($midX, 1) }}" y1="{{ round($midY, 1) }}"
                                      x2="{{ round($botX, 1) }}" y2="{{ round($botY, 1) }}"
                                      stroke="#cbd5e1" stroke-width="1.3"/>
                            @endfor

                            <!-- Movable Needle Pointer -->
                            <g :transform="(() => {
                                const t = Math.max(0, Math.min(1, needlePercent / 100));
                                const u = 1 - t;
                                const x = (u * u * 34) + (2 * u * t * 190) + (t * t * 346);
                                const y = (u * u * 78) + (2 * u * t * 30) + (t * t * 78);
                                const angle = -16 + (t * 32);
                                return 'translate(' + x + ',' + y + ') rotate(' + angle + ')';
                            })()">
                                <path d="M -7.5 -27 L 7.5 -27 C 9.5 -27 10.5 -25 9.5 -22 L 3.5 19 C 2.5 22 -2.5 22 -3.5 19 L -9.5 -22 C -10.5 -25 -9.5 -27 -7.5 -27 Z"
                                      :fill="projectStatus === 'some_risk' ? '#eab308' : (projectStatus === 'concerned' ? '#dc2626' : '#283c46')"/>
                                <!-- 3 White Grip Lines at Top of Needle -->
                                <line x1="-2.5" y1="-22" x2="-2.5" y2="-13" stroke="#ffffff" stroke-width="1.3" stroke-linecap="round"/>
                                <line x1="0" y1="-22" x2="0" y2="-13" stroke="#ffffff" stroke-width="1.3" stroke-linecap="round"/>
                                <line x1="2.5" y1="-22" x2="2.5" y2="-13" stroke="#ffffff" stroke-width="1.3" stroke-linecap="round"/>
                            </g>
                        </svg>
                    </div>
                </div>

                <!-- "How's the project going?" Status Selector (Matches media_1793328748773 & media_1793328760006) -->
                <div class="mt-4 text-center">
                    <div class="text-[14.5px] font-bold text-[#1d2d35]">
                        How’s the project going?
                    </div>

                    <div class="mt-2 inline-flex flex-wrap items-center justify-center gap-2">
                        <!-- On track -->
                        <button type="button"
                                @click="projectStatus = 'on_track'; if (needlePercent === 0) needlePercent = 28;"
                                :class="projectStatus === 'on_track' ? 'ring-2 ring-[#1b6ac9] bg-[#f8fafc]' : 'hover:bg-[#f6f4f0]'"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[6px] text-[15px] text-[#1d2d35] cursor-pointer transition-all">
                            <span class="w-[18px] h-[18px] rounded-full inline-flex items-center justify-center"
                                  :class="projectStatus === 'on_track' ? 'border-2 border-[#15803d] p-[2px]' : ''">
                                <span class="w-full h-full rounded-full bg-[#15803d] block"></span>
                            </span>
                            <span>On track</span>
                        </button>

                        <!-- Some risk -->
                        <button type="button"
                                @click="projectStatus = 'some_risk'; if (needlePercent === 0) needlePercent = 28;"
                                :class="projectStatus === 'some_risk' ? 'ring-2 ring-[#1b6ac9] bg-[#f8fafc]' : 'hover:bg-[#f6f4f0]'"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[6px] text-[15px] text-[#1d2d35] cursor-pointer transition-all">
                            <span class="w-[18px] h-[18px] rounded-full inline-flex items-center justify-center"
                                  :class="projectStatus === 'some_risk' ? 'border-2 border-[#eab308] p-[2px]' : ''">
                                <span class="w-full h-full rounded-full bg-[#eab308] block"></span>
                            </span>
                            <span>Some risk</span>
                        </button>

                        <!-- Concerned -->
                        <button type="button"
                                @click="projectStatus = 'concerned'; if (needlePercent === 0) needlePercent = 28;"
                                :class="projectStatus === 'concerned' ? 'ring-2 ring-[#1b6ac9] bg-[#f8fafc]' : 'hover:bg-[#f6f4f0]'"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[6px] text-[15px] text-[#1d2d35] cursor-pointer transition-all">
                            <span class="w-[18px] h-[18px] rounded-full inline-flex items-center justify-center"
                                  :class="projectStatus === 'concerned' ? 'border-2 border-[#dc2626] p-[2px]' : ''">
                                <span class="w-full h-full rounded-full bg-[#dc2626] block"></span>
                            </span>
                            <span>Concerned</span>
                        </button>
                    </div>
                </div>

                <!-- "Describe why you moved the needle:" Rich Text Editor (Matches media_1793328774075_b02b1ed0.png) -->
                <div class="mt-8 max-w-[780px] mx-auto">
                    <label class="block text-[14.5px] font-bold text-[#1d2d35] mb-1.5">
                        Describe why you moved the needle:
                    </label>

                    <div class="rounded-[8px] border border-[#d6d2c9] bg-white overflow-hidden focus-within:border-[#1b6ac9]">
                        <!-- Basecamp Crisp Monochrome SVG Toolbar (Matches media_1793328774075_b02b1ed0.png) -->
                        <div class="px-3.5 py-2 border-b border-[#ece9e2] flex flex-wrap items-center justify-between gap-2 text-[#1d2d35]">
                            <div class="flex flex-wrap items-center gap-3.5 text-[13.5px]">
                                <!-- Image -->
                                <button type="button" class="text-[#1d2d35] hover:opacity-70 cursor-pointer" title="Insert image">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                                        <circle cx="8.5" cy="10" r="1.5"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 19"/>
                                    </svg>
                                </button>
                                <!-- Paperclip -->
                                <button type="button" class="text-[#1d2d35] hover:opacity-70 cursor-pointer" title="Attach file">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                </button>
                                <span class="h-4 w-px bg-[#e5e2dc]"></span>
                                <!-- B I S Tt Highlight -->
                                <button type="button" class="font-extrabold hover:opacity-70 cursor-pointer">B</button>
                                <button type="button" class="italic font-serif hover:opacity-70 cursor-pointer">I</button>
                                <button type="button" class="line-through hover:opacity-70 cursor-pointer">S</button>
                                <button type="button" class="font-semibold text-[13px] hover:opacity-70 cursor-pointer">Tt ▾</button>
                                <button type="button" class="inline-flex items-center gap-0.5 text-[13px] hover:opacity-70 cursor-pointer" title="Highlight">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                    <span>▾</span>
                                </button>
                                <span class="h-4 w-px bg-[#e5e2dc]"></span>
                                <!-- Link Quote Code Lists Table Indent Mic -->
                                <button type="button" class="hover:opacity-70 cursor-pointer" title="Link">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                </button>
                                <button type="button" class="font-serif font-extrabold text-[15px] leading-none hover:opacity-70 cursor-pointer" title="Quote">”</button>
                                <button type="button" class="font-mono text-[12px] font-bold hover:opacity-70 cursor-pointer" title="Code">&lt;&gt;</button>
                                <button type="button" class="hover:opacity-70 cursor-pointer" title="Bulleted list">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
                                    </svg>
                                </button>
                                <button type="button" class="hover:opacity-70 cursor-pointer" title="Numbered list">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6h11M10 12h11M10 18h11M4 6h1v4M4 10h2M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/>
                                    </svg>
                                </button>
                                <button type="button" class="hover:opacity-70 cursor-pointer" title="Table">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                                        <line x1="3" y1="10" x2="21" y2="10"/>
                                        <line x1="3" y1="16" x2="21" y2="16"/>
                                        <line x1="9" y1="4" x2="9" y2="20"/>
                                        <line x1="15" y1="4" x2="15" y2="20"/>
                                    </svg>
                                </button>
                                <button type="button" class="hover:opacity-70 cursor-pointer" title="Indent">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M9 12h11M9 18h11"/>
                                    </svg>
                                </button>
                                <button type="button" class="hover:opacity-70 cursor-pointer" title="Record audio">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 10v2a7 7 0 01-14 0v-2M12 19v4M8 23h8"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="flex items-center gap-3 text-[#8c8881]">
                                <button type="button" class="hover:text-[#1d2d35] cursor-pointer" title="Undo">↶</button>
                                <button type="button" class="hover:text-[#1d2d35] cursor-pointer" title="Redo">↷</button>
                            </div>
                        </div>

                        <textarea name="notes"
                                  rows="6"
                                  placeholder="Add extra details about your changes..."
                                  style="border:0 !important;box-shadow:none !important;"
                                  class="w-full px-4 py-3 text-[15px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none focus:ring-0"></textarea>
                    </div>

                    <!-- "When I post this, notify..." (Matches media_1793328774075_b02b1ed0.png) -->
                    <div class="mt-6">
                        <div class="text-[14.5px] font-bold text-[#1d2d35] mb-2">
                            When I post this, notify...
                        </div>

                        <div class="space-y-2 text-[14.5px] text-[#1d2d35]">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="radio" name="notify_mode" value="team" x-model="notifyChoice" class="mt-1 w-4 h-4 text-[#1d2d35]">
                                <div>
                                    <div>Only the 7 people who asked to be notified about this project</div>
                                    <div class="mt-1.5 flex items-center gap-1">
                                        <span class="w-[24px] h-[24px] rounded-full bg-[#d946ef] text-white font-bold text-[10px] inline-flex items-center justify-center">GK</span>
                                        <span class="w-[24px] h-[24px] rounded-full bg-[#00897b] text-white font-bold text-[10px] inline-flex items-center justify-center">PK</span>
                                        <img src="/uploads/avatars/47630095.png" alt="Prashant" class="w-[24px] h-[24px] rounded-full object-cover">
                                        <span class="w-[24px] h-[24px] rounded-full bg-[#ec4899] text-white font-bold text-[10px] inline-flex items-center justify-center">RK</span>
                                        <span class="w-[24px] h-[24px] rounded-full bg-[#0284c7] text-white font-bold text-[10px] inline-flex items-center justify-center">RO</span>
                                        <img src="/uploads/avatars/25036340.png" alt="Shachish Sneh" class="w-[24px] h-[24px] rounded-full object-cover">
                                        <span class="w-[24px] h-[24px] rounded-full bg-[#9333ea] text-white font-bold text-[10px] inline-flex items-center justify-center">SN</span>
                                    </div>
                                </div>
                            </label>

                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input type="radio" name="notify_mode" value="custom" x-model="notifyChoice" class="w-4 h-4 text-[#1d2d35]">
                                <span>Everyone I select...</span>
                            </label>

                            <div x-show="notifyChoice === 'custom'" x-cloak class="pl-7 py-2 grid grid-cols-1 sm:grid-cols-2 gap-2 text-[13.5px]">
                                @foreach(['Gaurav Khulbey', 'Pawandeep Kaur', 'Prashant', 'Rajesh Kumar', 'Rock', 'Shachish Sneh', 'Shiv Narayan'] as $personName)
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" checked class="rounded border-[#d6d2c9] text-[#1b6ac9]">
                                        <span>{{ $personName }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input type="radio" name="notify_mode" value="none" x-model="notifyChoice" class="w-4 h-4 text-[#1d2d35]">
                                <span>No one</span>
                            </label>
                        </div>
                    </div>

                    <!-- Save / Never mind Buttons -->
                    <div class="mt-6 flex items-center gap-2.5">
                        <button type="submit"
                                class="px-4 py-2 rounded-[6px] bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-semibold text-[14px] transition-colors cursor-pointer">
                            Save
                        </button>
                        <button type="button"
                                @click="closeMoveNeedle()"
                                class="px-4 py-2 rounded-[6px] border border-[#93c5fd] bg-white hover:bg-[#f8fafc] text-[#1b6ac9] font-normal text-[14px] transition-colors cursor-pointer">
                            Never mind
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>

    <!-- Floating "↑ Back to top" Pill -->
    <button type="button"
            onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-12 left-5 z-30 px-3 py-1 rounded-full bg-[#f2f0ec] hover:bg-[#e6e3dd] border border-[#e5e2dc] shadow-2xs text-[12px] text-[#1d2d35] transition-colors cursor-pointer">
        ↑ Back to top
    </button>
</div>
@endsection
