<!DOCTYPE html>
<html lang="en" class="h-full bg-[#fbfaf8]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Join {{ $org->name ?? 'Ajath Infotech Pvt Ltd' }} on Basecamp</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script type="module" src="/js/firebase-init.js?v=20261030_5"></script>
</head>
<body class="min-h-full bg-[#fbfaf8] font-sans text-[#1d2d35] flex flex-col items-center justify-center p-6">

    <!-- Top Basecamp Brand Pill -->
    <div class="mb-6 flex items-center gap-2">
        <span class="w-7 h-7 rounded-full bg-[#3cb371] flex items-center justify-center shadow-2xs shrink-0 overflow-hidden">
            <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none">
                <path d="M3 19L10.5 7.5L14.5 13.5L17.5 9.5L21 19H3Z" fill="#1e7e48"/>
                <path d="M10.5 7.5L7.5 12L10 13L12 11L13.5 12L10.5 7.5Z" fill="#ffffff"/>
            </svg>
        </span>
        <span class="font-bold text-[21px] tracking-[-0.02em] text-[#1d2d35]">Basecamp</span>
    </div>

    <!-- Basecamp White Invitation Acceptance Sheet -->
    <div class="w-full max-w-[540px] bg-white rounded-[12px] border border-[#e5e2dc] shadow-[0_2px_12px_rgba(0,0,0,0.06)] px-8 sm:px-11 py-9">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#e8f5e9] text-[#198744] text-[12.5px] font-bold mb-3">
            <span>Invitation to {{ $org->name ?? 'Ajath Infotech Pvt Ltd' }}</span>
        </div>

        <h1 class="text-[26px] sm:text-[29px] font-black text-[#1d2d35] tracking-[-0.025em] leading-tight">
            Welcome to {{ $org->name ?? 'Ajath Infotech Pvt Ltd' }}!
        </h1>
        <p class="mt-2 text-[15px] text-[#4a4640] leading-relaxed">
            Confirm your details below and choose a password to join the workspace.
        </p>

        @if ($errors->any())
            <div class="mt-4 p-3.5 rounded-[8px] bg-red-50 border border-red-200 text-[13.5px] text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="mt-5">
            <button type="button"
                    onclick="if (window.bcSignInWithGoogle) { window.bcSignInWithGoogle({ csrfToken: '{{ csrf_token() }}', mode: 'join', invitedUserId: {{ $user->id }} }); }"
                    class="w-full h-[42px] rounded-[6px] border border-[#d4d0c8] bg-white hover:bg-[#f7f5f2] flex items-center justify-center gap-2.5 text-[14px] font-bold text-[#1d1c1b] transition-colors cursor-pointer">
                <svg class="w-4 h-4" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17z"/>
                    <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.11-6.72-4.96H1.29v3.14C3.26 21.3 7.31 24 12 24z"/>
                    <path fill="#FBBC05" d="M5.28 14.24c-.24-.72-.38-1.49-.38-2.24s.14-1.52.38-2.24V6.62H1.29C.47 8.24 0 10.06 0 12s.47 3.76 1.29 5.38l3.99-3.14z"/>
                    <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.26 2.7 1.29 6.62l3.99 3.14c.95-2.85 3.6-4.96 6.72-4.96z"/>
                </svg>
                <span>Join with Google</span>
            </button>

            <div class="my-4 flex items-center gap-2.5 text-[12px] text-[#7c7872]">
                <div class="h-px bg-[#e5e2dc] flex-1"></div>
                <span class="shrink-0">Or set up a password below</span>
                <div class="h-px bg-[#e5e2dc] flex-1"></div>
            </div>
        </div>

        <form action="{{ route('people.join.submit', $user->id) }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-[14px] font-bold text-[#1d2d35] mb-1">Your name</label>
                <input type="text"
                       name="name"
                       required
                       value="{{ old('name', $user->name) }}"
                       class="w-full h-[40px] px-3 bg-white border border-[#b8b4ac] rounded-[6px] text-[15px] text-[#1d2d35] focus:outline-none focus:border-[1.5px] focus:border-[#1b6ac9]">
            </div>

            <div>
                <label class="block text-[14px] font-bold text-[#1d2d35] mb-1">Email address</label>
                <input type="email"
                       disabled
                       value="{{ $user->email }}"
                       class="w-full h-[40px] px-3 bg-[#f6f5f1] border border-[#d8d4cc] rounded-[6px] text-[15px] text-[#6d6a65] cursor-not-allowed">
            </div>

            <div>
                <label class="block text-[14px] font-bold text-[#1d2d35] mb-1">Job title <span class="font-normal text-[#6d6a65]">(optional)</span></label>
                <input type="text"
                       name="job_title"
                       value="{{ old('job_title', $user->job_title) }}"
                       class="w-full h-[40px] px-3 bg-white border border-[#b8b4ac] rounded-[6px] text-[15px] text-[#1d2d35] focus:outline-none focus:border-[1.5px] focus:border-[#1b6ac9]">
            </div>

            <div>
                <label class="block text-[14px] font-bold text-[#1d2d35] mb-1">Choose a password</label>
                <input type="password"
                       name="password"
                       required
                       minlength="6"
                       placeholder="At least 6 characters"
                       autofocus
                       class="w-full h-[40px] px-3 bg-white border border-[#b8b4ac] rounded-[6px] text-[15px] text-[#1d2d35] focus:outline-none focus:border-[1.5px] focus:border-[#1b6ac9]">
            </div>

            <div class="pt-3">
                <button type="submit"
                        class="w-full py-2.5 px-5 rounded-full bg-[#198744] hover:bg-[#146c36] text-white font-bold text-[15px] transition-colors cursor-pointer">
                    Accept invitation &amp; Join {{ $org->name ?? 'Ajath Infotech Pvt Ltd' }} &rarr;
                </button>
            </div>
        </form>
    </div>

</body>
</html>

