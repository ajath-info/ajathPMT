@extends('layouts.app')

@section('title', 'Everyone on the account - Basecamp')

@section('content')
<div class="max-w-[780px] mx-auto pb-20 font-sans text-[#1d2d35]"
     x-data="{
         personSearch: '',
         activeMenuId: null,
         oooActive: localStorage.getItem('bc_ooo_active') !== '0'
     }">

    <!-- Basecamp Sheet Container (Exact Match to media_1793293094098_31e9386f.jpg) -->
    <div class="bc-sheet bg-white rounded-t-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-84px)] overflow-hidden">

        <!-- Top Breadcrumb Bar: Adminland -->
        <div class="px-5 py-2.5 border-b border-[#ece9e2] flex items-center text-[13.5px] bg-white">
            <a href="{{ route('adminland.index') }}" class="font-bold text-[#1d2d35] hover:underline">
                Adminland
            </a>
        </div>

        <div class="px-8 sm:px-14 pt-8 pb-16">
            <!-- Heading: Everyone on the account -->
            <h1 class="text-[28px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-[1.15]">
                Everyone on the account
            </h1>

            <!-- + Invite someone new Button -->
            <div class="mt-2.5">
                <a href="{{ route('enrollments.create') }}"
                   class="inline-flex items-center gap-1 px-4 py-1.5 rounded-full bg-[#60a5fa] hover:bg-[#3b82f6] text-[#0f172a] font-semibold text-[13.5px] transition-colors">
                    <span>+ Invite someone new</span>
                </a>
            </div>

            <!-- Search Input -->
            <div class="mt-6">
                <input type="text"
                       x-model="personSearch"
                       autofocus
                       placeholder="Search for a person..."
                       class="w-full h-[38px] px-3.5 rounded-[6px] border border-[#3b82f6] bg-white text-[14.5px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none">
            </div>

            <!-- Company Heading -->
            <h2 class="mt-5 text-[15px] font-bold text-[#1d2d35]">
                {{ $org->name ?? 'Ajath Infotech Pvt Ltd' }}
            </h2>

            @php
                $coreDirectory = [
                    [
                        'id' => 5,
                        'name' => 'Ajath Infotech',
                        'title' => null,
                        'badge' => 'Google Sign-In✓',
                        'invitedOn' => null,
                        'email' => 'ajathinfotech@gmail.com',
                        'initials' => 'AI',
                        'bg' => 'bg-[#00897b]',
                        'avatar' => null,
                        'isSelf' => false,
                    ],
                    [
                        'id' => 28,
                        'name' => 'Gaurav Khulbey',
                        'title' => 'Cybersecurity Expert',
                        'badge' => null,
                        'invitedOn' => null,
                        'email' => 'gaurav@ajath.com',
                        'initials' => 'GK',
                        'bg' => 'bg-[#d946ef]',
                        'avatar' => null,
                        'isSelf' => false,
                    ],
                    [
                        'id' => 1,
                        'name' => 'Manjot Singh',
                        'title' => 'Global VP & Partner',
                        'badge' => null,
                        'invitedOn' => null,
                        'email' => 'Manjot@ajath.com',
                        'initials' => 'M',
                        'bg' => 'bg-[#1d4ed8]',
                        'avatar' => '/uploads/avatars/22369668.png',
                        'isSelf' => true,
                    ],
                    [
                        'id' => 45,
                        'name' => 'Pawandeep Kaur',
                        'title' => 'Manager',
                        'badge' => 'Google Sign-In✓',
                        'invitedOn' => null,
                        'email' => 'pawandeep.kaur.khalsa@gmail.com',
                        'initials' => 'PK',
                        'bg' => 'bg-[#00897b]',
                        'avatar' => null,
                        'isSelf' => false,
                    ],
                    [
                        'id' => 150,
                        'name' => 'Prashant',
                        'title' => 'Analyst',
                        'badge' => null,
                        'invitedOn' => null,
                        'email' => 'prashant@ajath.com',
                        'initials' => 'P',
                        'bg' => 'bg-[#ea580c]',
                        'avatar' => '/uploads/avatars/47630095.png',
                        'isSelf' => false,
                    ],
                    [
                        'id' => 46,
                        'name' => 'Rajesh Kumar',
                        'title' => 'Project Lead',
                        'badge' => '2FA✓',
                        'invitedOn' => null,
                        'email' => 'rajesh@ajath.com',
                        'initials' => 'RK',
                        'bg' => 'bg-[#9333ea]',
                        'avatar' => null,
                        'isSelf' => false,
                    ],
                    [
                        'id' => 60,
                        'name' => 'Shachish Sneh',
                        'title' => 'Solution Architect & Technology Master',
                        'badge' => 'Google Sign-In✓',
                        'invitedOn' => null,
                        'email' => 'shachish@ajath.com',
                        'initials' => 'SS',
                        'bg' => 'bg-[#4f46e5]',
                        'avatar' => '/uploads/avatars/25036340.png',
                        'isSelf' => false,
                    ],
                    [
                        'id' => 62,
                        'name' => 'Shiv Narayan',
                        'title' => 'Developer',
                        'badge' => null,
                        'invitedOn' => null,
                        'email' => 'shiv@ajath.com',
                        'initials' => 'SN',
                        'bg' => 'bg-[#9333ea]',
                        'avatar' => null,
                        'isSelf' => false,
                    ],
                ];

                $seenEmails = array_map(fn($r) => strtolower($r['email']), $coreDirectory);
                $coreDefaultNames = array_map(fn($r) => strtolower($r['name']), $coreDirectory);

                foreach ($employees as $emp) {
                    $u = $emp->user;
                    if (!$u) continue;
                    $emailLower = strtolower($u->email ?? '');
                    if (in_array($emailLower, $seenEmails, true)) {
                        continue;
                    }
                    // Skip legacy seeded duplicates that have @ajath.com email and same name as core directory
                    if (str_ends_with($emailLower, '@ajath.com') && in_array(strtolower($u->name ?? ''), $coreDefaultNames, true)) {
                        continue;
                    }
                    $seenEmails[] = $emailLower;
                    $parts = preg_split('/\s+/', trim($u->name));
                    $in = strtoupper(substr($parts[0] ?? 'U', 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                    $invitedDate = ($emp->joined_at ?: ($u->created_at ?: now()))->format('j M, Y');
                    $hasJoined = !empty($u->email_verified_at) || ($emp->joined_at && $u->updated_at && $u->updated_at->gt($emp->joined_at));
                    $coreDirectory[] = [
                        'id' => $u->id,
                        'name' => $u->name,
                        'title' => str_replace(' at Ajath Infotech Pvt Ltd', '', $u->job_title ?? 'Product Owner'),
                        'badge' => null,
                        'invitedOn' => $hasJoined ? null : ('Invited on ' . $invitedDate),
                        'email' => $u->email,
                        'initials' => $in ?: 'TM',
                        'bg' => 'bg-[#525659]',
                        'avatar' => ($u->avatar_url && !str_contains($u->avatar_url, 'dicebear')) ? $u->avatar_url : null,
                        'isSelf' => false,
                    ];
                }

                usort($coreDirectory, function ($a, $b) {
                    $cmp = strcasecmp($a['name'], $b['name']);
                    if ($cmp !== 0) return $cmp;
                    return ($a['isSelf'] ? -1 : 1);
                });
            @endphp

            <!-- People Roster List Matching media_1793293094098_31e9386f.jpg -->
            <div class="mt-2.5 space-y-3.5">
                @foreach($coreDirectory as $person)
                    <div x-show="!personSearch || '{{ strtolower(addslashes($person['name'] . ' ' . ($person['title'] ?? '') . ' ' . $person['email'])) }}'.includes(personSearch.toLowerCase())"
                         class="flex items-center justify-between gap-3 py-1.5 relative">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- 42px Circular Avatar / Initials (with OUT badge on Manjot Singh) -->
                            <div class="relative w-[42px] h-[42px] rounded-full overflow-hidden flex-shrink-0">
                                @if(!empty($person['avatar']))
                                    <img src="{{ $person['avatar'] }}"
                                         alt="{{ $person['name'] }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <span class="w-full h-full {{ $person['bg'] }} text-white font-bold text-[15px] flex items-center justify-center select-none">
                                        {{ $person['initials'] }}
                                    </span>
                                @endif
                                @if(!empty($person['isSelf']))
                                    <div x-show="oooActive"
                                         class="absolute bottom-0 inset-x-0 h-[38%] bg-[#facc15] flex items-center justify-center">
                                        <span class="text-[8.5px] font-black text-[#1d1c1b] leading-none tracking-tight">OUT</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Name, Title, Security Badge & Email + Invited Badge -->
                            <div class="min-w-0 leading-snug">
                                <div class="text-[15px] text-[#1d2d35] truncate">
                                    <strong class="font-bold">{{ $person['name'] }}</strong>@if(!empty($person['title']))<span>, {{ $person['title'] }}</span>@endif
                                    @if(!empty($person['badge']))
                                        <span class="text-[#6d6a65] mx-0.5">&middot;</span>
                                        <span class="text-[#198744] font-normal">{{ $person['badge'] }}</span>
                                    @endif
                                </div>
                                <div class="text-[13.5px] text-[#6d6a65] flex items-center flex-wrap gap-1 mt-0.5">
                                    <span class="truncate">{{ $person['email'] }}</span>
                                    @if(!empty($person['invitedOn']))
                                        <span>&bull;</span>
                                        <span class="px-2 py-0.5 rounded-full bg-[#6b5335] text-[#f5ead8] text-[11px] font-medium leading-tight">
                                            {{ $person['invitedOn'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right ••• Action Menu -->
                        <div class="relative flex-shrink-0">
                            <button type="button"
                                    @click="activeMenuId = (activeMenuId === {{ $person['id'] }} ? null : {{ $person['id'] }})"
                                    class="px-2 py-1 text-[#6d6a65] hover:text-[#1d2d35] font-bold tracking-widest text-[13px] cursor-pointer">
                                •••
                            </button>

                            <div x-show="activeMenuId === {{ $person['id'] }}"
                                 x-cloak
                                 @click.outside="activeMenuId = null"
                                 class="absolute right-0 top-7 z-30 w-48 bg-white rounded-[8px] border border-[#e5e2dc] shadow-lg py-1 text-[13px] text-[#1d2d35]">
                                <a href="{{ route('enrollments.accesses.create', ['user_id' => $person['id']]) }}" class="block px-3.5 py-1.5 hover:bg-[#f6f4f0]">
                                    Manage project access
                                </a>
                                <a href="{{ route('my.activity', ['user_id' => $person['id']]) }}" class="block px-3.5 py-1.5 hover:bg-[#f6f4f0]">
                                    View activity
                                </a>
                                <a href="{{ route('my.tasks', ['user_id' => $person['id']]) }}" class="block px-3.5 py-1.5 hover:bg-[#f6f4f0]">
                                    View tasks
                                </a>
                                <a href="{{ route('my.pings.show', $person['id']) }}" class="block px-3.5 py-1.5 hover:bg-[#f6f4f0]">
                                    Ping {{ explode(' ', $person['name'])[0] }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

    </div>

</div>
@endsection
