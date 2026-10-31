<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basecamp Pricing — Trying is believing. Sign up free, today.</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #131b21;
            color: #e2e8f0;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-[#131b21] text-white antialiased selection:bg-[#1967d2] selection:text-white"
      x-data="{
          openFaq: null,
          videoModal: false,
          newsletterEmail: '',
          newsletterSubscribed: false,
          toggleFaq(idx) {
              this.openFaq = this.openFaq === idx ? null : idx;
          }
      }">

    {{-- Top Dark Header Bar with Centered Basecamp Logo --}}
    <header class="w-full bg-[#0e151a] border-b border-[#1c262e] py-5 flex items-center justify-center">
        <a href="{{ route('login') }}" class="inline-flex items-center justify-center hover:opacity-90 transition-opacity" title="Back to Basecamp Sign in">
            <svg class="w-[50px] h-[42px] drop-shadow-sm" viewBox="0 0 64 54" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M32 3C16.5 3 5 17.5 5 33.5C5 44.5 14.5 50 32 50C49.5 50 59 44.5 59 33.5C59 17.5 47.5 3 32 3Z" fill="url(#bcPricingSky)" stroke="#9ab3d8" stroke-width="2"/>
                <path d="M8 35.5C11.5 29 17.5 22 22.5 22C26.5 22 28.5 26.5 31 26.5C34 26.5 37.5 17.5 42.5 17.5C47.5 17.5 53.5 27.5 56.5 35.5C54 43.5 45.5 47.5 32 47.5C18.5 47.5 10 43.5 8 35.5Z" fill="url(#bcPricingGreen)"/>
                <path d="M8.5 35C12 28.5 17.5 22.5 22.5 22.5C26.5 22.5 28.5 27 31 27C34 27 37.5 18 42.5 18C47.5 18 53 27.5 56 35" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
                <defs>
                    <linearGradient id="bcPricingSky" x1="32" y1="3" x2="32" y2="32" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#c8dcfa"/>
                        <stop offset="1" stop-color="#eef5ff"/>
                    </linearGradient>
                    <linearGradient id="bcPricingGreen" x1="32" y1="17.5" x2="32" y2="47.5" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#4ade80"/>
                        <stop offset="0.55" stop-color="#22c55e"/>
                        <stop offset="1" stop-color="#15803d"/>
                    </linearGradient>
                </defs>
            </svg>
        </a>
    </header>

    <main class="max-w-[960px] mx-auto px-5 pt-14 pb-20">
        {{-- Hero Section (media_1793282336175_8fff0939.jpg) --}}
        <div class="text-center">
            <h1 class="text-[36px] sm:text-[46px] font-black text-white tracking-[-0.025em] leading-[1.12]">
                Trying is believing. Sign up free, today.
            </h1>
            <p class="mt-3 text-[17px] sm:text-[19.5px] text-[#cfd8dc]">
                <strong class="font-bold text-white">175,832 people are working in Basecamp right now.</strong> You&rsquo;re in good company.
            </p>
        </div>

        {{-- Two Primary Plan Cards --}}
        <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-6 max-w-[880px] mx-auto">
            {{-- Basecamp Personal --}}
            <div class="bg-[#1b252c] rounded-[4px] px-8 py-8 text-center border border-[#24313a] flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-6 h-5 shrink-0" viewBox="0 0 64 54" fill="none">
                            <path d="M32 3C16.5 3 5 17.5 5 33.5C5 44.5 14.5 50 32 50C49.5 50 59 44.5 59 33.5C59 17.5 47.5 3 32 3Z" fill="#dbeafe"/>
                            <path d="M8 35.5C11.5 29 17.5 22 22.5 22C26.5 22 28.5 26.5 31 26.5C34 26.5 37.5 17.5 42.5 17.5C47.5 17.5 53.5 27.5 56.5 35.5C54 43.5 45.5 47.5 32 47.5C18.5 47.5 10 43.5 8 35.5Z" fill="#22c55e"/>
                        </svg>
                        <h2 class="text-[22px] font-extrabold text-white tracking-tight">Basecamp Personal</h2>
                    </div>
                    <p class="mt-3 text-[14.5px] text-[#cfd8dc] leading-relaxed">
                        <strong class="font-bold text-white">One project, free forever.</strong> Invite up to 3 people.<br>
                        Limited, but plenty for personal use.
                    </p>
                </div>
                <div class="mt-5">
                    <a href="{{ route('register', ['plan' => 'personal']) }}"
                       class="block w-full py-3.5 px-6 rounded-[4px] bg-[#1967d2] hover:bg-[#1558b8] text-white font-bold text-[15px] transition-colors">
                        Get one project free
                    </a>
                    <p class="mt-3 text-[11.5px] text-[#8fa3b0]">
                        Need more projects? Upgrade to a business package anytime.
                    </p>
                </div>
            </div>

            {{-- Basecamp Business --}}
            <div class="bg-[#1b252c] rounded-[4px] px-8 py-8 text-center border border-[#24313a] flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-6 h-5 shrink-0" viewBox="0 0 64 54" fill="none">
                            <path d="M32 3C16.5 3 5 17.5 5 33.5C5 44.5 14.5 50 32 50C49.5 50 59 44.5 59 33.5C59 17.5 47.5 3 32 3Z" fill="#dbeafe"/>
                            <path d="M8 35.5C11.5 29 17.5 22 22.5 22C26.5 22 28.5 26.5 31 26.5C34 26.5 37.5 17.5 42.5 17.5C47.5 17.5 53.5 27.5 56.5 35.5C54 43.5 45.5 47.5 32 47.5C18.5 47.5 10 43.5 8 35.5Z" fill="#22c55e"/>
                        </svg>
                        <h2 class="text-[22px] font-extrabold text-white tracking-tight">Basecamp Business</h2>
                    </div>
                    <p class="mt-3 text-[14.5px] text-[#cfd8dc] leading-relaxed">
                        <strong class="font-bold text-white">Unlimited projects, unlimited people, every feature we offer.</strong> Try it all, risk free.
                    </p>
                </div>
                <div class="mt-5">
                    <a href="{{ route('register', ['plan' => 'business']) }}"
                       class="block w-full py-3.5 px-6 rounded-[4px] bg-[#1967d2] hover:bg-[#1558b8] text-white font-bold text-[15px] transition-colors">
                        Start a 30-day free trial. Unlimited use.
                    </a>
                    <p class="mt-3 text-[11.5px] text-[#8fa3b0]">
                        Pick a package when your trial ends. Pricing listed below.
                    </p>
                </div>
            </div>
        </div>

        {{-- Business packages & pricing Table Section (media_1793282340663_a119150a.jpg) --}}
        <div class="mt-12 max-w-[880px] mx-auto">
            <p class="text-[18px] sm:text-[19px] text-[#cfd8dc] text-center max-w-[700px] mx-auto leading-snug mb-7">
                <strong class="font-bold text-white">Business packages &amp; pricing:</strong> When your trial is up, you can choose whichever package fits you best, or just move on. Zero obligation, no risk.
            </p>

            <div class="border border-[#2b3842] bg-[#161f26] divide-y divide-[#2b3842] text-[13.5px]">
                {{-- Row 1: Freelancer --}}
                <div class="grid grid-cols-1 md:grid-cols-12">
                    <div class="md:col-span-2 p-4 sm:p-5 flex flex-col justify-center border-b md:border-b-0 md:border-r border-[#2b3842]">
                        <div class="font-bold text-white">Freelancer</div>
                        <div class="text-[#b0bec5]">$29<span class="text-[11.5px]">/mo</span></div>
                    </div>
                    <div class="md:col-span-3 p-4 sm:p-5 flex flex-col justify-center border-b md:border-b-0 md:border-r border-[#2b3842] leading-snug">
                        <div class="font-bold text-white">3 projects</div>
                        <div class="text-[#cfd8dc]">10 GB storage</div>
                        <div class="text-[#cfd8dc]">Includes up to 5 users</div>
                    </div>
                    <div class="md:col-span-7 p-4 sm:p-5 flex items-center text-[#cfd8dc] leading-relaxed">
                        Everything you need to run a handful of concurrent projects with a few people. Multiple tools per project, templates, and time tracking are not included in Freelancer.
                    </div>
                </div>

                {{-- Row 2: Pro --}}
                <div class="grid grid-cols-1 md:grid-cols-12">
                    <div class="md:col-span-2 p-4 sm:p-5 flex flex-col justify-center border-b md:border-b-0 md:border-r border-[#2b3842]">
                        <div class="font-bold text-white">Pro</div>
                        <div class="text-[#b0bec5]">$79<span class="text-[11.5px]">/mo</span></div>
                    </div>
                    <div class="md:col-span-3 p-4 sm:p-5 flex flex-col justify-center border-b md:border-b-0 md:border-r border-[#2b3842] leading-snug">
                        <div class="font-bold text-white">8 projects</div>
                        <div class="text-[#cfd8dc]">25 GB storage</div>
                        <div class="text-[#cfd8dc]">Includes up to 10 users</div>
                    </div>
                    <div class="md:col-span-7 p-4 sm:p-5 flex items-center text-[#cfd8dc] leading-relaxed">
                        <div>
                            <span class="underline decoration-[#64748b] underline-offset-2">Everything in Freelancer</span>, <strong class="font-bold text-white">plus more projects, 2x the people, 2.5x the storage.</strong> Also includes multiple tools per project <span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full border border-[#94a3b8] text-[9px] text-[#94a3b8] align-middle" title="Add extra Message Boards, To-do lists, or Card Tables inside any project">i</span>, and templates <span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full border border-[#94a3b8] text-[9px] text-[#94a3b8] align-middle" title="Save reusable project, to-do list, and card table templates">i</span>.
                        </div>
                    </div>
                </div>

                {{-- Row 3: Pro Plus --}}
                <div class="grid grid-cols-1 md:grid-cols-12">
                    <div class="md:col-span-2 p-4 sm:p-5 flex flex-col justify-center border-b md:border-b-0 md:border-r border-[#2b3842]">
                        <div class="font-bold text-white">Pro Plus</div>
                        <div class="text-[#b0bec5]">$99<span class="text-[11.5px]">/mo</span></div>
                    </div>
                    <div class="md:col-span-3 p-4 sm:p-5 flex flex-col justify-center border-b md:border-b-0 md:border-r border-[#2b3842] leading-snug">
                        <div class="font-bold text-white">12 projects</div>
                        <div class="text-[#cfd8dc]">100 GB storage</div>
                        <div class="text-[#cfd8dc]">Includes up to 15 users</div>
                    </div>
                    <div class="md:col-span-7 p-4 sm:p-5 flex items-center text-[#cfd8dc] leading-relaxed">
                        <div>
                            <span class="underline decoration-[#64748b] underline-offset-2">Everything in Pro</span>, <strong class="font-bold text-white">plus more projects, more people, 4x the storage.</strong> Also includes time tracking <span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full border border-[#94a3b8] text-[9px] text-[#94a3b8] align-middle" title="Built-in Timesheet add-on included">i</span>.
                        </div>
                    </div>
                </div>

                {{-- Row 4: Pro Unlimited --}}
                <div class="grid grid-cols-1 md:grid-cols-12">
                    <div class="md:col-span-2 p-4 sm:p-5 flex flex-col justify-center border-b md:border-b-0 md:border-r border-[#2b3842]">
                        <div class="font-bold text-white">Pro Unlimited</div>
                        <div class="text-[#b0bec5] text-[12.5px] leading-snug">$199<span class="text-[11px]">/mo, billed annually</span></div>
                    </div>
                    <div class="md:col-span-3 p-4 sm:p-5 flex flex-col justify-center border-b md:border-b-0 md:border-r border-[#2b3842] leading-snug">
                        <div class="font-bold text-white">UNLIMITED projects</div>
                        <div class="font-bold text-white">1000 GB (1 TB) storage</div>
                        <div class="font-bold text-white">UNLIMITED users</div>
                    </div>
                    <div class="md:col-span-7 p-4 sm:p-5 flex items-center text-[#cfd8dc] leading-relaxed">
                        <div>
                            <span class="underline decoration-[#64748b] underline-offset-2">Everything in Pro Plus</span>, <strong class="font-bold text-white">plus UNLIMITED projects, UNLIMITED people, 10x MORE storage.</strong> Also includes enhanced admin controls <span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full border border-[#94a3b8] text-[9px] text-[#94a3b8] align-middle" title="Granular permissions & Admin Pro Pack included">i</span>, priority 24/7/365 support <span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full border border-[#94a3b8] text-[9px] text-[#94a3b8] align-middle" title="Fast-track support response">i</span>, personal onboarding <span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full border border-[#94a3b8] text-[9px] text-[#94a3b8] align-middle" title="1-on-1 onboarding session with our team">i</span>, and an annual billing option by check or wire <span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full border border-[#94a3b8] text-[9px] text-[#94a3b8] align-middle" title="Pay annually via invoice, check, or wire transfer">i</span>.
                        </div>
                    </div>
                </div>
            </div>

            <p class="mt-6 text-center text-[14.5px] text-[#cfd8dc]">
                Want a live walkthrough? <a href="{{ route('register') }}" class="text-[#5c9ce6] hover:text-[#7bb2f0] underline font-bold">Book a personal demo</a> with our team. Pre-sales question? <a href="mailto:support@basecamp.com" class="text-[#5c9ce6] hover:text-[#7bb2f0] underline font-bold">Email us</a>.
            </p>
        </div>

        {{-- Testimonial & Customer Video Row (media_1793282344941_21aec430.jpg) --}}
        <div class="mt-12 max-w-[880px] mx-auto grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Tobi Lutke Quote --}}
            <div class="border border-[#2b3842] bg-[#161f26] rounded-[4px] p-7 flex flex-col justify-between">
                <p class="text-[15.5px] italic text-[#e2e8f0] leading-relaxed">
                    &ldquo;Basecamp is beautiful software that has resisted every wrong trend and stayed true to the things that mattered most. Highly recommended.&rdquo;
                </p>
                <div class="mt-5 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#263238] border border-[#37474f] overflow-hidden flex items-center justify-center text-xs font-bold text-white shrink-0">
                        TL
                    </div>
                    <div class="text-[12.5px] leading-tight">
                        <div class="font-bold text-white">Tobi Lutke</div>
                        <div class="text-[#90a4ae]">Shopify Co-Founder &amp; CEO</div>
                    </div>
                </div>
            </div>

            {{-- Video Card --}}
            <div @click="videoModal = true" class="border border-[#2b3842] bg-[#1b252c] hover:border-[#3e505e] rounded-[4px] overflow-hidden grid grid-cols-12 cursor-pointer group transition-colors">
                <div class="col-span-6 relative min-h-[160px] bg-cover bg-center flex items-center justify-center"
                     style="background-image: linear-gradient(rgba(19,27,33,0.25), rgba(19,27,33,0.45)), url('https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?auto=format&fit=crop&w=600&q=80');">
                    <div class="w-12 h-12 rounded-full bg-white shadow-lg flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5 text-[#131b21] ml-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                        </svg>
                    </div>
                </div>
                <div class="col-span-6 p-5 flex items-center justify-center text-center">
                    <span class="text-[14.5px] font-bold text-white leading-snug">
                        Take a minute to meet some of our customers
                    </span>
                </div>
            </div>
        </div>

        {{-- Wavy Divider with Basecamp Mountain Icon --}}
        <div class="my-16 flex items-center justify-center gap-3 text-[#37474f]">
            <svg class="w-32 h-3" viewBox="0 0 120 10" fill="none">
                <path d="M0 5 Q 5 0, 10 5 T 20 5 T 30 5 T 40 5 T 50 5 T 60 5 T 70 5 T 80 5 T 90 5 T 100 5 T 110 5 T 120 5" stroke="currentColor" stroke-width="1.5" fill="none"/>
            </svg>
            <svg class="w-5 h-4 text-[#4b5e69]" viewBox="0 0 64 54" fill="none">
                <path d="M32 5C17 5 6 18 6 33C6 43 15 48 32 48C49 48 58 43 58 33C58 18 47 5 32 5Z" stroke="currentColor" stroke-width="4"/>
                <path d="M9 35C13 28 18 22 23 22C27 22 29 27 31 27C34 27 38 18 43 18C48 18 53 28 56 35" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
            </svg>
            <svg class="w-32 h-3" viewBox="0 0 120 10" fill="none">
                <path d="M0 5 Q 5 0, 10 5 T 20 5 T 30 5 T 40 5 T 50 5 T 60 5 T 70 5 T 80 5 T 90 5 T 100 5 T 110 5 T 120 5" stroke="currentColor" stroke-width="1.5" fill="none"/>
            </svg>
        </div>

        {{-- Section: The same core features are included with every package (media_1793282348576_e1b20ca0.jpg) --}}
        <div class="max-w-[660px] mx-auto">
            <h2 class="text-[30px] sm:text-[36px] font-black text-white text-center tracking-[-0.02em] leading-[1.15]">
                The same core features are included with every package
            </h2>

            <div class="mt-8 bg-[#1b252c] rounded-[4px] p-7 sm:p-9 space-y-3.5 text-[15px] text-[#cfd8dc]">
                @php
                    $coreFeatures = [
                        ['bold' => 'Message Boards', 'rest' => 'for internal announcements', 'link' => false],
                        ['bold' => 'To-dos', 'rest' => 'for tracking work and accountability', 'link' => false],
                        ['bold' => 'Card Tables', 'rest' => 'is our take on Kanban for process tracking', 'link' => false],
                        ['bold' => 'Campfires & Pings', 'rest' => 'for group chat and direct messaging', 'link' => false],
                        ['bold' => 'Scheduling', 'rest' => 'for deadlines, milestones, and events', 'link' => false],
                        ['bold' => 'Docs & Files', 'rest' => 'for storing assets', 'link' => false],
                        ['bold' => 'Reports & Automatic Check-ins', 'rest' => 'for staying up on things', 'link' => false],
                        ['bold' => 'Connect your AI agents', 'rest' => ', Claude, ChatGPT, Muse, Grok Bot, and others', 'link' => true],
                        ['bold' => 'Rock-solid reliability', 'rest' => 'and a record you can trust', 'link' => true],
                    ];
                @endphp
                @foreach($coreFeatures as $feat)
                <div class="flex items-start gap-3">
                    <span class="w-4 h-4 rounded-full bg-[#22c55e] text-[#0e151a] inline-flex items-center justify-center shrink-0 mt-1">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <div>
                        @if($feat['link'])
                            <a href="{{ route('register') }}" class="font-bold text-[#5c9ce6] underline hover:text-[#7bb2f0]">{{ $feat['bold'] }}</a><span>{{ $feat['rest'] }}</span>
                        @else
                            <strong class="font-bold text-white">{{ $feat['bold'] }}</strong> <span>{{ $feat['rest'] }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Wavy Divider --}}
        <div class="my-16 flex items-center justify-center gap-3 text-[#37474f]">
            <svg class="w-32 h-3" viewBox="0 0 120 10" fill="none">
                <path d="M0 5 Q 5 0, 10 5 T 20 5 T 30 5 T 40 5 T 50 5 T 60 5 T 70 5 T 80 5 T 90 5 T 100 5 T 110 5 T 120 5" stroke="currentColor" stroke-width="1.5" fill="none"/>
            </svg>
            <svg class="w-5 h-4 text-[#4b5e69]" viewBox="0 0 64 54" fill="none">
                <path d="M32 5C17 5 6 18 6 33C6 43 15 48 32 48C49 48 58 43 58 33C58 18 47 5 32 5Z" stroke="currentColor" stroke-width="4"/>
                <path d="M9 35C13 28 18 22 23 22C27 22 29 27 31 27C34 27 38 18 43 18C48 18 53 28 56 35" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
            </svg>
            <svg class="w-32 h-3" viewBox="0 0 120 10" fill="none">
                <path d="M0 5 Q 5 0, 10 5 T 20 5 T 30 5 T 40 5 T 50 5 T 60 5 T 70 5 T 80 5 T 90 5 T 100 5 T 110 5 T 120 5" stroke="currentColor" stroke-width="1.5" fill="none"/>
            </svg>
        </div>

        {{-- Section: A stable, well-run company is part of the deal, too (media_1793282355592_22af6a25.jpg) --}}
        <div class="max-w-[660px] mx-auto">
            <h2 class="text-[30px] sm:text-[36px] font-black text-white text-center tracking-[-0.02em] leading-[1.15]">
                A stable, well-run company is part of the deal, too
            </h2>

            <div class="mt-8 bg-[#1b252c] rounded-[4px] p-7 sm:p-9 text-[15px] text-[#cfd8dc]">
                <p class="leading-relaxed mb-5">
                    We&rsquo;ve been running an upstanding, trustworthy, sustainable business for nearly three decades. <strong class="font-bold text-white">You can&rsquo;t build reliable software unless you build a reliable company.</strong>
                </p>

                @php
                    $companyPoints = [
                        '27 profitable, financially responsible years in business',
                        'Zero debt, privately held, and built to stay, not exit',
                        '99.99% historical uptime, with full transparency',
                        'Gold standard, famously great customer service',
                        'Extensive learning library free for all customers',
                        'Direct access to our CEO via email',
                    ];
                @endphp
                <div class="space-y-3">
                    @foreach($companyPoints as $pt)
                    <div class="flex items-start gap-3">
                        <span class="w-4 h-4 rounded-full bg-[#22c55e] text-[#0e151a] inline-flex items-center justify-center shrink-0 mt-1">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        <span>{{ $pt }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Three Editorial Paragraphs below card (media_1793282359516_7b2e1685.jpg) --}}
            <div class="mt-12 max-w-[580px] mx-auto space-y-8 text-left">
                <div>
                    <h3 class="text-[19px] font-extrabold text-white tracking-tight">Risk-free, cancel anytime, no long-term lock-in</h3>
                    <p class="mt-1.5 text-[15px] text-[#cfd8dc] leading-relaxed">
                        With Basecamp, cancellation is entirely self-serve, no questions asked, no retention specialists trying to talk you out of it. Cancel any time, no long-term contracts to lock you in. Simple, straightforward, and fair, just as it should be.
                    </p>
                </div>

                <div>
                    <h3 class="text-[19px] font-extrabold text-white tracking-tight">A big boost for bootstrappers &amp; small businesses</h3>
                    <p class="mt-1.5 text-[15px] text-[#cfd8dc] leading-relaxed">
                        We started our business without outside funding, and you probably did too. We know your money&rsquo;s precious, and we want to help. That&rsquo;s why Basecamp is intentionally affordable for start-ups, freelancers, and small businesses just getting started.
                    </p>
                </div>

                <div>
                    <h3 class="text-[19px] font-extrabold text-white tracking-tight">Basecamp eliminates the Hassle Tax</h3>
                    <p class="mt-1.5 text-[15px] text-[#cfd8dc] leading-relaxed">
                        There&rsquo;s no more paying separate bills, onboarding people in multiple places, training people how to use different tools, managing expenses across vendors, etc... the benefits of Basecamp go beyond price.
                    </p>
                </div>
            </div>
        </div>

        {{-- Wavy Divider --}}
        <div class="my-16 flex items-center justify-center gap-3 text-[#37474f]">
            <svg class="w-32 h-3" viewBox="0 0 120 10" fill="none">
                <path d="M0 5 Q 5 0, 10 5 T 20 5 T 30 5 T 40 5 T 50 5 T 60 5 T 70 5 T 80 5 T 90 5 T 100 5 T 110 5 T 120 5" stroke="currentColor" stroke-width="1.5" fill="none"/>
            </svg>
            <svg class="w-5 h-4 text-[#4b5e69]" viewBox="0 0 64 54" fill="none">
                <path d="M32 5C17 5 6 18 6 33C6 43 15 48 32 48C49 48 58 43 58 33C58 18 47 5 32 5Z" stroke="currentColor" stroke-width="4"/>
                <path d="M9 35C13 28 18 22 23 22C27 22 29 27 31 27C34 27 38 18 43 18C48 18 53 28 56 35" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
            </svg>
            <svg class="w-32 h-3" viewBox="0 0 120 10" fill="none">
                <path d="M0 5 Q 5 0, 10 5 T 20 5 T 30 5 T 40 5 T 50 5 T 60 5 T 70 5 T 80 5 T 90 5 T 100 5 T 110 5 T 120 5" stroke="currentColor" stroke-width="1.5" fill="none"/>
            </svg>
        </div>

        {{-- Section: I have pricing questions... (media_1793282363849_c28b4fb2.jpg) --}}
        <div class="max-w-[660px] mx-auto">
            <h2 class="text-[30px] sm:text-[36px] font-black text-white text-center tracking-[-0.02em]">
                I have pricing questions...
            </h2>

            @php
                $faqs = [
                    [
                        'q' => 'Can we upgrade, downgrade, switch packages, or cancel whenever we want?',
                        'a' => 'Yes! You can upgrade, downgrade, or cancel your Basecamp account at any time right from Adminland with a couple of clicks — no phone calls or retention hurdles required.',
                    ],
                    [
                        'q' => 'Do you offer non-profit or educational discounts?',
                        'a' => 'Yes! Verified non-profits receive a 10% discount, and teachers and students using Basecamp for classroom work can apply for a free educational account.',
                    ],
                    [
                        'q' => 'Could we really add 500 users and run unlimited projects and still just pay $329/month total on Pro Unlimited?',
                        'a' => 'Yes! On Pro Unlimited, there are no per-seat charges. Whether you have 25 employees, 100 employees, or 500 employees, your flat price stays the same.',
                    ],
                    [
                        'q' => 'Are clients or contractors or guests included in the user count?',
                        'a' => 'On Pro Unlimited, you have unlimited users for everyone. On Freelancer, Pro, and Pro Plus, you can invite external clients and guests to projects without counting against your internal team seats.',
                        'highlight' => true,
                    ],
                    [
                        'q' => 'Do I need a credit card to try Basecamp?',
                        'a' => 'No credit card is required to sign up for Basecamp Personal or to start your 30-day free trial of Basecamp Business.',
                    ],
                    [
                        'q' => 'Will I automatically be charged when my free trial is up?',
                        'a' => 'Never! Since we don’t ask for a credit card upfront, you will only be billed if you explicitly choose a package and enter payment details.',
                    ],
                    [
                        'q' => 'Can we buy more storage if we need it? How much is it?',
                        'a' => 'Yes, additional storage upgrades are available anytime from Adminland if your team outgrows the storage included in your package.',
                    ],
                    [
                        'q' => 'On the free one project package, can I delete the project and start another one?',
                        'a' => 'Yes! Basecamp Personal lets you have one active project at a time. If you finish or delete that project, you can create another one for free.',
                    ],
                ];
            @endphp

            <div class="mt-8 bg-[#1b252c] rounded-[4px] px-7 py-3 divide-y divide-[#2b3842]">
                @foreach($faqs as $idx => $faq)
                <div class="py-4">
                    <button type="button" @click="toggleFaq({{ $idx }})"
                            class="w-full flex items-start gap-2.5 text-left text-[15px] font-bold transition-colors cursor-pointer {{ !empty($faq['highlight']) ? 'text-[#3b82f6] hover:text-[#60a5fa]' : 'text-white hover:text-[#5c9ce6]' }}">
                        <svg class="w-4 h-4 mt-1 shrink-0 transition-transform duration-150"
                             :class="openFaq === {{ $idx }} ? 'rotate-90' : ''"
                             fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span>{{ $faq['q'] }}</span>
                    </button>
                    <div x-show="openFaq === {{ $idx }}" x-cloak class="mt-2.5 pl-6 text-[14px] text-[#cfd8dc] leading-relaxed">
                        {{ $faq['a'] }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Footer Section: Have a stellar day! (media_1793282371743_43e16492.jpg) --}}
        <footer class="mt-20 pt-6 text-center">
            <h2 class="text-[30px] sm:text-[34px] font-black text-white tracking-[-0.02em]">
                Have a stellar day!
            </h2>
            <p class="mt-2.5 text-[15.5px] text-[#cfd8dc] max-w-[580px] mx-auto leading-relaxed">
                <strong class="font-bold text-white">Join more than 200,000 people who get our email newsletter.</strong> We&rsquo;ll share product updates, thoughts, new releases, and other tidbits we think you&rsquo;ll find interesting.
            </p>

            {{-- Newsletter Input + Subscribe --}}
            <form @submit.prevent="if(newsletterEmail.trim()) newsletterSubscribed = true" class="mt-6 max-w-[440px] mx-auto flex items-center">
                <input type="email" x-model="newsletterEmail" placeholder="julie.young@hey.com" required
                       class="flex-1 h-[42px] px-3.5 bg-[#1b252c] border border-[#2b3842] rounded-l-[4px] text-[14px] text-white placeholder-[#607d8b] focus:outline-none focus:border-[#1967d2]">
                <button type="submit"
                        class="h-[42px] px-5 bg-[#1967d2] hover:bg-[#1558b8] text-white font-bold text-[14px] rounded-r-[4px] transition-colors cursor-pointer">
                    Subscribe
                </button>
            </form>
            <p x-show="newsletterSubscribed" x-cloak class="mt-2 text-[12.5px] text-[#4ade80]">
                Thanks for subscribing! We&rsquo;ve added <span x-text="newsletterEmail"></span> to our newsletter.
            </p>

            {{-- Directory Link Pills (6 exact rows matching media_1793282371743_43e16492.jpg) --}}
            @php
                $pillRows = [
                    [
                        ['label' => 'Basecamp 5 is here', 'new' => true],
                        ['label' => 'Bring your AI agents to Basecamp', 'new' => true],
                        ['label' => 'Where we came from'],
                        ['label' => 'Features and benefits'],
                        ['label' => 'Reliable to the core'],
                        ['label' => 'Friendly folks, standing by'],
                    ],
                    [
                        ['label' => "The people's path to Basecamp"],
                        ['label' => 'Apps for every platform'],
                        ['label' => 'Integrations'],
                        ['label' => 'What changed for the better?'],
                        ['label' => 'Learn Basecamp'],
                        ['label' => "With Basecamp, it's a Yes!"],
                    ],
                    [
                        ['label' => 'Come small, come all'],
                        ['label' => 'We stand with the underdogs'],
                        ['label' => 'Leaving the Cloud'],
                        ['label' => 'How we make decisions'],
                        ['label' => 'Group chat problems'],
                        ['label' => 'Why we choose profit'],
                    ],
                    [
                        ['label' => 'Seven shipping principles'],
                        ['label' => 'How we communicate'],
                        ['label' => 'Night & day'],
                        ['label' => 'Bootstrapped, profitable, & proud'],
                        ['label' => 'Basecamp community'],
                        ['label' => 'Free live classes'],
                    ],
                    [
                        ['label' => "Books we've written"],
                        ['label' => 'Kill overkill'],
                        ['label' => 'Breakfast with Basecamp'],
                        ['label' => 'Basecamp help'],
                        ['label' => 'Discounts'],
                        ['label' => 'Basecamp accessibility'],
                        ['label' => 'Keyboard shortcuts'],
                    ],
                    [
                        ['label' => 'Can we stay in touch?'],
                        ['label' => 'See where projects really stand'],
                        ['label' => 'Getting Real'],
                        ['label' => 'Shape Up'],
                        ['label' => 'Manager Playbook'],
                        ['label' => 'Employee Handbook'],
                    ],
                ];
            @endphp
            <div class="mt-10 max-w-[940px] mx-auto space-y-2.5 text-[12.5px]">
                @foreach($pillRows as $row)
                <div class="flex flex-wrap items-center justify-center gap-2">
                    @foreach($row as $item)
                    <a href="{{ route('login') }}" class="relative px-3 py-1.5 rounded-[3px] bg-[#152331] hover:bg-[#1c2f42] text-[#6ab0f3] font-medium transition-colors">
                        @if(!empty($item['new']))
                        <span class="absolute -top-2.5 -left-2 bg-[#16a34a] text-white text-[9px] font-black px-1.5 py-0.2 rounded-[2px] -rotate-6 tracking-wider">NEW!</span>
                        @endif
                        {{ $item['label'] }}
                    </a>
                    @endforeach
                </div>
                @endforeach
            </div>

            {{-- Bottom 37signals Attribution & Links --}}
            <div class="mt-10 space-y-3 text-[13px] text-[#cfd8dc]">
                <p class="flex flex-wrap items-center justify-center gap-1.5">
                    <span class="inline-flex items-center gap-1 font-bold text-white">
                        <svg class="w-4 h-3.5" viewBox="0 0 64 54" fill="none">
                            <path d="M32 5C17 5 6 18 6 33C6 43 15 48 32 48C49 48 58 43 58 33C58 18 47 5 32 5Z" stroke="currentColor" stroke-width="5"/>
                            <path d="M9 35C13 28 18 22 23 22C27 22 29 27 31 27C34 27 38 18 43 18C48 18 53 28 56 35" stroke="currentColor" stroke-width="5" stroke-linecap="round"/>
                        </svg>
                        Basecamp&trade;
                    </span>
                    <span>is designed, built, and backed by</span>
                    <span class="inline-flex items-center gap-1 font-bold text-white">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="currentColor">
                            <circle cx="4" cy="4" r="2.2"/><circle cx="11.5" cy="5.5" r="1.8"/><circle cx="5.5" cy="11.5" r="2.5"/><circle cx="11.5" cy="11.5" r="1.4"/>
                        </svg>
                        37signals&trade;,
                    </span>
                    <span>the people behind</span>
                    <span class="inline-flex items-center gap-1 font-bold text-white">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 3v7m3-6v6m-6-5v5m9-2v4a6 6 0 01-12 0V9"/>
                        </svg>
                        HEY&trade;
                    </span>
                    <span>and</span>
                    <span class="inline-flex items-center gap-1 font-bold text-white">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="currentColor">
                            <rect x="1" y="2" width="2" height="12" rx="1"/><rect x="5" y="4" width="2" height="10" rx="1"/><rect x="9" y="1" width="2" height="13" rx="1"/><rect x="13" y="3" width="2" height="11" rx="1"/>
                        </svg>
                        Fizzy&trade;
                    </span>
                </p>

                <div class="flex flex-wrap items-center justify-center gap-3 text-[12px] text-[#90a4ae]">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#22c55e]"></span>
                        <a href="{{ route('login') }}" class="hover:text-white">Status</a>
                    </span>
                    <a href="{{ route('login') }}" class="hover:text-white">Support</a>
                    <a href="{{ route('login') }}" class="hover:text-white">Policies</a>
                    <a href="{{ route('login') }}" class="hover:text-white">Privacy</a>
                    <a href="{{ route('login') }}" class="hover:text-white">Security</a>
                    <a href="{{ route('login') }}" class="hover:text-white">Reliability</a>
                    <a href="{{ route('login') }}" class="hover:text-white">Sign in</a>
                    <span>&copy; 37signals LLC. All rights reserved.</span>
                </div>
            </div>
        </footer>
    </main>

    {{-- Customer Video Modal --}}
    <div x-show="videoModal" x-cloak @keydown.escape.window="videoModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4">
        <div @click.outside="videoModal = false" class="bg-[#1b252c] border border-[#2b3842] rounded-[8px] max-w-[640px] w-full p-6 text-center">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[16px] font-bold text-white">Meet some of our customers</h3>
                <button @click="videoModal = false" class="text-[#90a4ae] hover:text-white text-xl leading-none">&times;</button>
            </div>
            <div class="aspect-video bg-[#0e151a] rounded-[4px] flex flex-col items-center justify-center p-6 border border-[#2b3842]">
                <p class="text-[15px] text-[#cfd8dc] max-w-md leading-relaxed">
                    &ldquo;Basecamp keeps all of our projects, clients, and internal communications in one calm, organized place.&rdquo;
                </p>
                <a href="{{ route('register') }}" class="mt-5 px-5 py-2.5 bg-[#1967d2] hover:bg-[#1558b8] text-white font-bold text-[13.5px] rounded-[4px]">
                    Start your free trial today
                </a>
            </div>
        </div>
    </div>
</body>
</html>

