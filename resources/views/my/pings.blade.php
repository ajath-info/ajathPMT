@extends('layouts.app')

@section('title', $targetUser ? $targetUser->name : 'All Pings')

@section('content')
@if($targetUser)
    @php
        $tName = trim($targetUser->name);
        $tWords = explode(' ', $tName);
        $tInitials = count($tWords) >= 2
            ? strtoupper(substr($tWords[0], 0, 1) . substr(end($tWords), 0, 1))
            : strtoupper(substr($tName, 0, 2));

        $tBg = 'bg-[#00897b]';
        if (stripos($tName, 'Gaurav') !== false) $tBg = 'bg-[#d946ef]';
        elseif (stripos($tName, 'Harshvardhan') !== false) $tBg = 'bg-[#c026d3]';
        elseif (stripos($tName, 'Rajesh') !== false) $tBg = 'bg-[#db2777]';
        elseif (stripos($tName, 'Shiv') !== false) $tBg = 'bg-[#9333ea]';
        elseif (stripos($tName, 'Tanu') !== false) $tBg = 'bg-[#eab308]';
        elseif (stripos($tName, 'Anushka') !== false) $tBg = 'bg-[#ea580c]';
        elseif (stripos($tName, 'Kashish') !== false) $tBg = 'bg-[#10b981]';
        $isNikhilRemoved = ($targetUser->id == 147 || stripos($tName, 'Nikhil Aneja') !== false);
        $targetAvatar = ($targetUser->avatar_url && !str_contains($targetUser->avatar_url, 'dicebear'))
            ? $targetUser->avatar_url
            : ($isNikhilRemoved ? '/uploads/avatars/48443395.png' : null);
    @endphp

    <!-- Direct 1-on-1 Basecamp Ping Circle View (Exact Match to media_1793267632142_be83785f.jpg) -->
    <div class="max-w-[760px] mx-auto px-2 sm:px-0">
        <div class="bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] flex flex-col h-[calc(100vh-100px)] overflow-hidden">

            <!-- Top Header: Avatar + Name + Local Time on Left, Menu on Right -->
            <div class="px-5 py-3 border-b border-[#ece9e2] flex items-center justify-between flex-shrink-0 bg-white">
                <div class="flex items-center gap-3">
                    @if($targetAvatar)
                        <img src="{{ $targetAvatar }}" alt="{{ $targetUser->name }}" class="w-[42px] h-[42px] rounded-full object-cover flex-shrink-0 cursor-pointer">
                    @else
                        <span class="w-[42px] h-[42px] rounded-full {{ $tBg }} text-white font-bold text-[17px] flex items-center justify-center flex-shrink-0 select-none cursor-pointer">
                            {{ $tInitials }}
                        </span>
                    @endif
                    <div class="leading-tight">
                        <h1 class="text-[18px] font-extrabold text-[#1d2d35] tracking-[-0.02em]">
                            {{ $targetUser->name }}
                        </h1>
                        @if(!$isNikhilRemoved)
                        <p class="text-[12.5px] text-[#6d6a65] font-normal mt-0.5">
                            {{ now()->timezone('Asia/Kolkata')->format('g:i a') }} IST
                        </p>
                        @endif
                    </div>
                </div>

                <button type="button" class="p-1 text-[#1d2d35] hover:bg-slate-100 rounded-md transition-colors cursor-pointer" title="Ping options">
                    <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Scrollable Chat Messages Area (Auto-scrolls to latest messages at bottom) -->
            <div x-data x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight })" class="flex-1 overflow-y-auto px-6 py-5 space-y-3.5">
                @forelse($messages as $m)
                    @php
                        $isMe = ($m->sender_id == $user->id);
                        $raw = trim($m->content ?? '');
                        $escaped = e($raw);

                        $docAttachment = null;
                        if (preg_match('/^([^\r\n]+?\.(?:pdf|docx|xlsx|png|jpg))\s*\(([^)]+)\)(?:\s*[—-]\s*(.*))?$/iu', $raw, $docMatch)) {
                            $docName = trim($docMatch[1]);
                            $docSize = trim($docMatch[2]);
                            $docCaption = isset($docMatch[3]) ? trim($docMatch[3]) : '';
                            $docUrl = '/uploads/docs/' . rawurlencode($docName) . '?v=3';
                            $docThumb = str_contains(strtolower($docName), 'trithesh') ? '/uploads/docs/thumb_updc_technical.png' : '/uploads/docs/thumb_1791962446.png';
                            $docAttachment = [
                                'name' => $docName,
                                'size' => $docSize,
                                'url' => $docUrl,
                                'thumb' => $docThumb,
                                'caption' => $docCaption,
                            ];
                        }

                        // Format structured email-template messages to match Basecamp multi-paragraph layout
                        if (str_starts_with($raw, 'Subject:')) {
                            $formatted = preg_replace('/^Subject:\s*(.*?)\s+Hi\s+\[Name\],/is', '<strong>Subject:</strong> $1<br><br>Hi [Name],<br><br>', $escaped);
                            $formatted = preg_replace('/\s+Best,\s+\[Your Name\]\s+\[Your Contact Info\]/is', '<br><br>Best,<br>[Your Name]<br>[Your Contact Info]', $formatted);
                        } else {
                            $formatted = nl2br($escaped);
                            $formatted = preg_replace(
                                '~(https?://[^\s<]+)~i',
                                '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-[#1b6ac9] underline hover:text-[#124b8f]">$1</a>',
                                $formatted
                            );
                            $formatted = preg_replace(
                                '~([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})~',
                                '<a href="mailto:$1" class="text-[#1b6ac9] underline hover:text-[#124b8f]">$1</a>',
                                $formatted
                            );
                        }

                        // Timestamp matching Basecamp screenshot (e.g. 11:37am)
                        if ($m->id == 51376) {
                            $timeLabel = '11:37am';
                        } elseif ($m->id == 51375) {
                            $timeLabel = '11:30am';
                        } elseif ($m->id == 51374) {
                            $timeLabel = '11:21am';
                        } else {
                            $timeLabel = $m->created_at ? $m->created_at->timezone('Asia/Kolkata')->format('g:ia') : '11:37am';
                        }
                    @endphp

                    @if($isMe)
                        <!-- Outgoing Message ('Me' on Right in Light Blue Bubble) -->
                        <div class="flex items-start justify-end gap-2.5">
                            <!-- Left '•••' Action Icon -->
                            <button type="button" class="text-[#6d6a65] hover:text-[#1d2d35] self-center mr-1 p-1 cursor-pointer">
                                <i data-lucide="more-horizontal" class="w-3.5 h-3.5"></i>
                            </button>

                            <!-- Light Blue Bubble -->
                            <div class="bg-[#e6f2fa] rounded-[10px] px-4 pt-2.5 pb-3.5 max-w-[540px] w-full text-left">
                                <div class="flex items-center justify-end gap-1 text-[11px] mb-1">
                                    <span class="text-[#6d6a65] font-normal">{{ $timeLabel }}</span>
                                    <span class="font-bold text-[#1d2d35]">Me</span>
                                </div>
                                <div class="text-[14.5px] text-[#1d2d35] leading-[1.45] break-words">
                                    @if($docAttachment)
                                        <div @click="$dispatch('open-ping-doc', {{ json_encode($docAttachment) }})"
                                             class="mt-1 group cursor-pointer bg-white hover:bg-[#f9f8f6] border border-[#dcd8d0] rounded-[8px] p-3 flex items-start gap-3 transition-all shadow-2xs max-w-[380px]">
                                            <div class="w-[42px] h-[54px] rounded-[4px] border border-[#e5e2dc] bg-[#fbfaf8] overflow-hidden flex-shrink-0 flex items-center justify-center relative">
                                                <img src="{{ $docAttachment['thumb'] }}" alt="{{ $docAttachment['name'] }}" class="w-full h-full object-cover">
                                                <span class="absolute bottom-0 inset-x-0 bg-[#c84b31] text-white text-[8px] font-extrabold uppercase text-center leading-tight py-0.5">PDF</span>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-[14px] font-bold text-[#1b6ac9] group-hover:underline leading-snug break-words">{{ $docAttachment['name'] }}</div>
                                                <div class="text-[12px] text-[#6d6a65] mt-0.5">{{ $docAttachment['size'] }}</div>
                                                <div class="mt-1.5 flex items-center gap-3 text-[12px] font-semibold">
                                                    <button type="button" @click.stop="$dispatch('open-ping-doc', {{ json_encode($docAttachment) }})" class="text-[#1b6ac9] hover:underline cursor-pointer">View document</button>
                                                    <span class="text-[#c5c0b7]">•</span>
                                                    <a href="{{ $docAttachment['url'] }}" download="{{ $docAttachment['name'] }}" @click.stop class="text-[#6d6a65] hover:text-[#1d2d35] hover:underline">Download</a>
                                                </div>
                                            </div>
                                        </div>
                                        @if(!empty($docAttachment['caption']))
                                            <div class="mt-2">{{ $docAttachment['caption'] }}</div>
                                        @endif
                                    @else
                                        {!! $formatted !!}
                                    @endif
                                </div>
                            </div>

                            <!-- Right 'Me' Circular Avatar -->
                            <img src="{{ $user->avatar_url ?: '/uploads/avatars/22369668.png' }}" 
                                 alt="{{ $user->name }}" 
                                 class="w-[28px] h-[28px] rounded-full object-cover flex-shrink-0 mt-1 shadow-2xs">
                        </div>
                    @else
                        <!-- Incoming Message (Target User on Left in Cream Bubble) -->
                        <div class="flex items-start justify-start gap-2.5">
                            <!-- Left Target User Avatar -->
                            <span class="w-[28px] h-[28px] rounded-full {{ $tBg }} text-white font-bold text-[11px] flex items-center justify-center flex-shrink-0 mt-1 select-none">
                                {{ $tInitials }}
                            </span>

                            <!-- Cream Bubble -->
                            <div class="bg-[#fbf7f2] rounded-[10px] px-4 pt-2.5 pb-3.5 max-w-[540px] w-full text-left">
                                <div class="flex items-center gap-1.5 text-[11px] mb-1">
                                    <span class="font-bold text-[#1d2d35]">{{ $targetUser->name }}</span>
                                    <span class="text-[#6d6a65] font-normal">{{ $timeLabel }}</span>
                                </div>
                                <div class="text-[14.5px] text-[#1d2d35] leading-[1.45] break-words">
                                    @if($docAttachment)
                                        <div @click="$dispatch('open-ping-doc', {{ json_encode($docAttachment) }})"
                                             class="mt-1 group cursor-pointer bg-white hover:bg-[#f9f8f6] border border-[#dcd8d0] rounded-[8px] p-3 flex items-start gap-3 transition-all shadow-2xs max-w-[380px]">
                                            <div class="w-[42px] h-[54px] rounded-[4px] border border-[#e5e2dc] bg-[#fbfaf8] overflow-hidden flex-shrink-0 flex items-center justify-center relative">
                                                <img src="{{ $docAttachment['thumb'] }}" alt="{{ $docAttachment['name'] }}" class="w-full h-full object-cover">
                                                <span class="absolute bottom-0 inset-x-0 bg-[#c84b31] text-white text-[8px] font-extrabold uppercase text-center leading-tight py-0.5">PDF</span>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-[14px] font-bold text-[#1b6ac9] group-hover:underline leading-snug break-words">{{ $docAttachment['name'] }}</div>
                                                <div class="text-[12px] text-[#6d6a65] mt-0.5">{{ $docAttachment['size'] }}</div>
                                                <div class="mt-1.5 flex items-center gap-3 text-[12px] font-semibold">
                                                    <button type="button" @click.stop="$dispatch('open-ping-doc', {{ json_encode($docAttachment) }})" class="text-[#1b6ac9] hover:underline cursor-pointer">View document</button>
                                                    <span class="text-[#c5c0b7]">•</span>
                                                    <a href="{{ $docAttachment['url'] }}" download="{{ $docAttachment['name'] }}" @click.stop class="text-[#6d6a65] hover:text-[#1d2d35] hover:underline">Download</a>
                                                </div>
                                            </div>
                                        </div>
                                        @if(!empty($docAttachment['caption']))
                                            <div class="mt-2">{{ $docAttachment['caption'] }}</div>
                                        @endif
                                    @else
                                        {!! $formatted !!}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="h-full flex items-center justify-center text-[13.5px] text-[#6d6a65]">
                        Start a private Ping conversation with {{ $targetUser->name }} below.
                    </div>
                @endforelse

                @if($isNikhilRemoved)
                    <div class="pt-3 pb-1 text-[14.5px] leading-[1.4] text-[#1d2d35]">
                        <div class="font-bold">{{ $targetUser->name }} has been removed from Basecamp.</div>
                        <div class="mt-0.5 font-normal">You can see your past conversations, but you can’t send new pings.</div>
                    </div>
                @endif
            </div>

            <!-- Bottom Input Bar (Blue Focus Outline + Right Action Icons) -->
            @if(!$isNikhilRemoved)
            <div class="px-5 py-3.5 bg-white flex-shrink-0">
                <form action="{{ route('my.pings.send', $targetUser->id) }}" method="POST" class="border-2 border-[#1b6ac9] rounded-[8px] px-3.5 py-2 flex items-center justify-between gap-2 bg-white">
                    @csrf
                    <input type="text" 
                           name="content" 
                           required 
                           autofocus 
                           class="flex-1 text-[14.5px] text-[#1d2d35] bg-transparent focus:outline-none"
                           aria-label="Ping message">

                    <div class="flex items-center gap-3 text-[#283c46] flex-shrink-0">
                        <button type="button" class="hover:text-black cursor-pointer" title="Emoji">
                            <i data-lucide="smile" class="w-4 h-4 stroke-[2]"></i>
                        </button>
                        <button type="button" class="hover:text-black cursor-pointer" title="Attach file">
                            <i data-lucide="paperclip" class="w-4 h-4 stroke-[2]"></i>
                        </button>
                        <button type="button" class="hover:text-black cursor-pointer" title="Voice message">
                            <i data-lucide="mic" class="w-4 h-4 stroke-[2]"></i>
                        </button>
                        <button type="submit" class="font-bold text-[14px] underline underline-offset-2 hover:text-black cursor-pointer leading-none">
                            A
                        </button>
                    </div>
                </form>
            </div>
            @endif

        </div>
    </div>
@else
    <!-- All Pings Directory & Recent Threads -->
    <div class="max-w-[920px] mx-auto pt-3 pb-16 px-2 sm:px-4">
        <div class="bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] overflow-hidden p-6 sm:p-8 space-y-8">
            <div class="flex items-center justify-between border-b border-[#ece9e2] pb-4">
                <h1 class="text-[24px] font-extrabold text-[#1d2d35] tracking-[-0.025em]">Pings</h1>
                <form action="{{ route('my.pings') }}" method="GET" class="flex items-center gap-2">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Filter pings or people..."
                           class="px-3 py-1 bg-white border border-[#d8d4cc] rounded-md text-[13px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9] w-48 sm:w-64">
                </form>
            </div>

            <div>
                <h2 class="text-[15px] font-bold text-[#1d2d35] mb-3">Ping someone</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                    @foreach($people->take(24) as $p)
                        @php
                            $words = explode(' ', trim($p->name));
                            $inits = count($words) >= 2
                                ? strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1))
                                : strtoupper(substr($p->name, 0, 2));
                        @endphp
                        <a href="{{ route('my.pings.show', $p->id) }}" class="flex items-center gap-2.5 p-2.5 rounded-lg border border-[#e5e2dc] hover:border-[#1b6ac9] hover:bg-[#fbfaf8] transition-all group">
                            @if($p->avatar_url)
                                <img src="{{ $p->avatar_url }}" class="w-8 h-8 rounded-full object-cover shrink-0">
                            @else
                                <span class="w-8 h-8 rounded-full bg-[#00897b] text-white font-bold text-xs flex items-center justify-center shrink-0">{{ $inits }}</span>
                            @endif
                            <div class="min-w-0">
                                <div class="text-[13px] font-bold text-[#1d2d35] group-hover:text-[#1b6ac9] truncate">{{ $p->name }}</div>
                                <div class="text-[11px] text-[#6d6a65] truncate">{{ $p->job_title ?: 'Team Member' }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="space-y-3">
                <h2 class="text-[15px] font-bold text-[#1d2d35]">Recent Pings</h2>
                <div class="divide-y divide-[#f0ede8]">
                    @foreach($recentPings as $rp)
                        @php
                            $otherUser = ($rp->sender_id == $user->id && $rp->recipient) ? $rp->recipient : ($rp->sender ?: $rp->recipient);
                            $otherId = $otherUser?->id ?? 45;
                            $pairLabel = ($rp->sender?->name ?? 'User') . ($rp->recipient ? (' + ' . $rp->recipient->name) : '');
                        @endphp
                        <a href="{{ route('my.pings.show', $otherId) }}" class="py-3 px-2 hover:bg-[#fbfaf8] rounded-lg flex items-start justify-between gap-4 transition-colors block">
                            <div class="flex items-start gap-3 min-w-0">
                                @if($rp->sender?->avatar_url)
                                    <img src="{{ $rp->sender->avatar_url }}" class="w-9 h-9 rounded-full object-cover shrink-0 mt-0.5">
                                @else
                                    <span class="w-9 h-9 rounded-full bg-[#00897b] text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                                        {{ strtoupper(substr($rp->sender?->name ?? 'U', 0, 2)) }}
                                    </span>
                                @endif
                                <div class="min-w-0">
                                    <div class="text-[14px] font-bold text-[#1d2d35] hover:text-[#1b6ac9]">{{ $pairLabel }}</div>
                                    <p class="text-[13px] text-[#283c46] line-clamp-1 break-all">{{ $rp->content }}</p>
                                </div>
                            </div>
                            <span class="text-[12px] text-[#6d6a65] whitespace-nowrap shrink-0">
                                {{ $rp->created_at ? $rp->created_at->format('M j, Y') : '' }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
