@extends('layouts.auth')

@section('title', 'Sign up for Basecamp')

@section('content')
<div class="w-full">
    <div class="bg-white rounded-[10px] border border-[#e5e5e5] shadow-[0_2px_12px_rgba(0,0,0,0.06)] px-6 py-7">
        <h1 class="text-[20px] font-bold text-[#1d2d35] text-center mb-5 tracking-[-0.01em]">Sign up for Basecamp</h1>

        @if ($errors->any())
        <div class="mb-4 bg-[#fef2f2] border border-[#fecaca] text-[#dc2626] text-[13px] rounded-[6px] px-3.5 py-2.5 text-center font-medium">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="company_name" class="block text-[14px] font-bold text-[#1d2d35] mb-1.5">Organization name</label>
                <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" required autofocus
                    class="w-full h-[42px] px-3 bg-[#eef3fe] border border-[#8ab4f8] rounded-[6px] text-[14px] text-[#1d2d35] focus:outline-none focus:ring-2 focus:ring-[#3b82f6]/40 focus:border-[#2563eb] transition-all"
                    placeholder="e.g. Ajath Infotech Pvt Ltd">
            </div>

            <div>
                <label for="name" class="block text-[14px] font-bold text-[#1d2d35] mb-1.5">Your name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    class="w-full h-[42px] px-3 bg-white border border-[#d4d4d4] rounded-[6px] text-[14px] text-[#1d2d35] focus:outline-none focus:ring-2 focus:ring-[#3b82f6]/40 focus:border-[#2563eb] transition-all"
                    placeholder="e.g. Manjot Singh">
            </div>

            <div>
                <label for="email" class="block text-[14px] font-bold text-[#1d2d35] mb-1.5">Work email address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                    class="w-full h-[42px] px-3 bg-white border border-[#d4d4d4] rounded-[6px] text-[14px] text-[#1d2d35] focus:outline-none focus:ring-2 focus:ring-[#3b82f6]/40 focus:border-[#2563eb] transition-all"
                    placeholder="name@company.com">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="password" class="block text-[14px] font-bold text-[#1d2d35] mb-1.5">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full h-[42px] px-3 bg-white border border-[#d4d4d4] rounded-[6px] text-[14px] text-[#1d2d35] focus:outline-none focus:ring-2 focus:ring-[#3b82f6]/40 focus:border-[#2563eb] transition-all"
                        placeholder="••••••••">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-[14px] font-bold text-[#1d2d35] mb-1.5">Confirm</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        class="w-full h-[42px] px-3 bg-white border border-[#d4d4d4] rounded-[6px] text-[14px] text-[#1d2d35] focus:outline-none focus:ring-2 focus:ring-[#3b82f6]/40 focus:border-[#2563eb] transition-all"
                        placeholder="••••••••">
                </div>
            </div>

            <button type="submit"
                class="w-full h-[42px] bg-white hover:bg-[#fafafa] border border-[#e0e0e0] rounded-[6px] text-[14px] font-bold text-[#1d2d35] shadow-[0_1px_2px_rgba(0,0,0,0.03)] transition-colors cursor-pointer">
                Create my account
            </button>
        </form>
    </div>

    <div class="mt-6 text-center">
        <p class="text-[13px] text-[#1d2d35]">
            Already have an account? <a href="{{ route('login') }}" class="text-[#1d6ce0] hover:text-[#1557b8] underline underline-offset-2">Log in</a>
        </p>
    </div>
</div>
@endsection
