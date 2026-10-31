<!DOCTYPE html>
<html lang="en" class="h-full bg-[#faf8f5]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Log in to Basecamp')</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script type="module" src="/js/firebase-init.js?v=20261030_5"></script>
    <style>
        [x-cloak] { display: none !important; }
        body {
            background-color: #faf8f5;
            color: #1d1c1b;
            font-family: -apple-system, system-ui, BlinkMacSystemFont, Aptos, Roboto, "Segoe UI", Helvetica, Arial, sans-serif;
            font-size: 18px;
            -webkit-font-smoothing: antialiased;
        }
        .text-xs { font-size: 14px !important; }
        .text-sm { font-size: 16px !important; }
        .text-base { font-size: 18px !important; }
        .text-\[11px\] { font-size: 13px !important; }
        .text-\[12px\] { font-size: 14px !important; }
        .text-\[12\.5px\] { font-size: 14.5px !important; }
        .text-\[13px\] { font-size: 15px !important; }
        .text-\[13\.5px\] { font-size: 15.5px !important; }
        .text-\[14px\] { font-size: 16px !important; }
        .text-\[15px\] { font-size: 17px !important; }
        .text-\[16px\] { font-size: 18px !important; }
        .text-\[17px\] { font-size: 19px !important; }
        .text-\[18px\] { font-size: 20px !important; }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-[356px] flex flex-col items-center">
        {{-- Top Basecamp Green Mountain Logo (Exact Match to media_1793282319134_7301cdd4.jpg) --}}
        <div class="mb-3">
            <svg class="w-[62px] h-[54px] drop-shadow-xs" viewBox="0 0 64 56" fill="none">
                <path d="M32 3C16.5 3 4 21.5 4 38C4 47.5 14.5 53 32 53C49.5 53 60 47.5 60 38C60 21.5 47.5 3 32 3Z" fill="#e5e7eb" stroke="#d1d5db" stroke-width="1.5"/>
                <path d="M32 7C18.5 7 8 23 8 37.5C8 45.5 17 50 32 50C47 50 56 45.5 56 37.5C56 23 45.5 7 32 7Z" fill="url(#bcGreenGrad)"/>
                <path d="M12 33C17 24 21 21 24.5 21C27.5 21 29.5 25.5 32.5 25.5C35.5 25.5 38 17 42 17C46 17 50 24 53 33C50 27 46 21 42 21C38.5 21 36 29 32.5 29C29 29 27 24.5 24.5 24.5C21.5 24.5 17.5 28 12 33Z" fill="white"/>
                <defs>
                    <linearGradient id="bcGreenGrad" x1="32" y1="7" x2="32" y2="50" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#4ade80"/>
                        <stop offset="100%" stop-color="#16a34a"/>
                    </linearGradient>
                </defs>
            </svg>
        </div>

        @if(session('success'))
        <div class="w-full mb-3 bg-[#eef8f1] border border-[#bfe3c8] text-[#1f883d] text-[12.5px] font-semibold px-4 py-2.5 rounded-[8px] text-center">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="w-full mb-3 bg-[#fef2f2] border border-[#fecaca] text-[#dc2626] text-[12.5px] font-semibold px-4 py-2.5 rounded-[8px] text-center">
            {{ session('error') }}
        </div>
        @endif

        @yield('content')
    </div>
</body>
</html>
