@extends('layouts.auth')

@section('title', 'Log in to Basecamp')

@section('content')
<div class="w-full" x-data="{
    step: {{ $errors->any() ? 2 : 1 }},
    email: '{{ old('email', '') }}',
    forgotNotice: false,
    proceedToPassword() {
        if (this.email.trim()) {
            this.step = 2;
            this.$nextTick(() => {
                const pwd = document.getElementById('bc-login-password');
                if (pwd) pwd.focus();
            });
        }
    }
}">
    {{-- White Login Card (Exact Match to media_1793282319134_7301cdd4.jpg) --}}
    <div class="w-full bg-white rounded-[12px] border border-[#e5e2dc] shadow-[0_2px_12px_rgba(0,0,0,0.06)] px-7 pt-6 pb-7">
        <h1 class="text-[17px] font-extrabold text-[#1d1c1b] text-center mb-4">
            Log in to Basecamp
        </h1>

        @if ($errors->any())
        <div class="mb-4 bg-[#fef2f2] border border-[#fecaca] text-[#dc2626] text-[12px] rounded-[6px] p-2.5 space-y-0.5">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        {{-- Sign in with Google Button (Firebase workspace-bb7f2) --}}
        <button type="button"
                onclick="if (window.bcSignInWithGoogle) { window.bcSignInWithGoogle({ csrfToken: '{{ csrf_token() }}', mode: 'login' }); } else { window.location.href = '{{ route('quick-login', 'owner') }}'; }"
                class="w-full h-[40px] rounded-[6px] border border-[#d4d0c8] bg-white hover:bg-[#f7f5f2] flex items-center justify-center gap-2.5 text-[13.5px] font-bold text-[#1d1c1b] transition-colors cursor-pointer">
            <svg class="w-4 h-4" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17z"/>
                <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.11-6.72-4.96H1.29v3.14C3.26 21.3 7.31 24 12 24z"/>
                <path fill="#FBBC05" d="M5.28 14.24c-.24-.72-.38-1.49-.38-2.24s.14-1.52.38-2.24V6.62H1.29C.47 8.24 0 10.06 0 12s.47 3.76 1.29 5.38l3.99-3.14z"/>
                <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.26 2.7 1.29 6.62l3.99 3.14c.95-2.85 3.6-4.96 6.72-4.96z"/>
            </svg>
            <span>Sign in with Google</span>
        </button>

        {{-- Centered Divider: Or, use my email address --}}
        <div class="my-5 flex items-center gap-2.5 text-[12px] text-[#7c7872]">
            <div class="h-px bg-[#e5e2dc] flex-1"></div>
            <span class="shrink-0">Or, use my email address</span>
            <div class="h-px bg-[#e5e2dc] flex-1"></div>
        </div>

        {{-- Login Form --}}
        <form action="{{ route('login') }}" method="POST">
            @csrf

            {{-- STEP 1: Email or username + Next --}}
            <div x-show="step === 1">
                <label for="email" class="block text-[12.5px] font-bold text-[#1d1c1b] mb-1.5">
                    Email or username
                </label>
                <input type="email" id="email" name="email" x-model="email" required autofocus
                       @keydown.enter.prevent="proceedToPassword()"
                       class="w-full h-[38px] px-3 rounded-[5px] border border-[#93c5fd] bg-[#e8f0fe] text-[13.5px] text-[#1d1c1b] focus:outline-none focus:border-[#2563eb] focus:ring-2 focus:ring-[#93c5fd]/50">

                <button type="button" @click="proceedToPassword()"
                        class="mt-3.5 w-full h-[40px] rounded-[6px] border border-[#d4d0c8] bg-white hover:bg-[#f7f5f2] text-[13.5px] font-bold text-[#1d1c1b] transition-colors cursor-pointer">
                    Next
                </button>
            </div>

            {{-- STEP 2: Password + Log in --}}
            <div x-show="step === 2" x-cloak class="space-y-3.5">
                <div class="flex items-center justify-between text-[12.5px] bg-[#f7f5f2] px-3 py-2 rounded-[5px] border border-[#e5e2dc]">
                    <span class="font-semibold text-[#1d1c1b] truncate" x-text="email"></span>
                    <button type="button" @click="step = 1" class="text-[#1d64c8] underline hover:opacity-80 ml-2 shrink-0 cursor-pointer">Change</button>
                </div>

                <div>
                    <label for="bc-login-password" class="block text-[12.5px] font-bold text-[#1d1c1b] mb-1.5">
                        Password
                    </label>
                    <input type="password" id="bc-login-password" name="password" required
                           class="w-full h-[38px] px-3 rounded-[5px] border border-[#d4d0c8] bg-white text-[13.5px] text-[#1d1c1b] focus:outline-none focus:border-[#2563eb]">
                </div>

                <button type="submit"
                        class="w-full h-[40px] rounded-[6px] bg-[#1f883d] hover:bg-[#1a7433] text-white text-[13.5px] font-bold transition-colors cursor-pointer">
                    Log in
                </button>
            </div>
        </form>
    </div>

    {{-- Bottom Links: Forgot your password? & New to Basecamp? Sign up for free --}}
    <div class="mt-5 text-center space-y-2.5 text-[12px]">
        <div>
            <a href="{{ route('password.request') }}" class="underline text-[#1d64c8] hover:opacity-80 cursor-pointer">
                Forgot your password?
            </a>
        </div>
        <div class="text-[#1d1c1b]">
            New to Basecamp?
            <a href="{{ route('pricing') }}" class="underline text-[#1d64c8] hover:opacity-80">Sign up for free</a>
        </div>
    </div>
</div>
@endsection
