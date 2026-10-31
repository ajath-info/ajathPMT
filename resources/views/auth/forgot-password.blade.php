@extends('layouts.auth')

@section('title', 'Forgot your password?')

@section('content')
<div class="w-full">
    {{-- White Forgot Password Card (Exact Match to media_1793282328333_da184f9a.jpg) --}}
    <div class="w-full bg-white rounded-[12px] border border-[#e5e2dc] shadow-[0_2px_12px_rgba(0,0,0,0.06)] px-7 pt-6 pb-6">
        <h1 class="text-[18px] font-extrabold text-[#1d1c1b] text-center mb-3">
            Forgot your password?
        </h1>

        <p class="text-[13px] text-[#1d1c1b] leading-snug mb-3.5">
            Enter your email address below and we&rsquo;ll send you password reset instructions.
        </p>

        @if (session('status'))
        <div class="mb-3.5 bg-[#ecfdf5] border border-[#a7f3d0] text-[#065f46] text-[12.5px] rounded-[6px] px-3 py-2.5 font-medium">
            {{ session('status') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="mb-3.5 bg-[#fef2f2] border border-[#fecaca] text-[#dc2626] text-[12px] rounded-[6px] p-2.5 space-y-0.5">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   placeholder="Enter your email address..."
                   class="w-full h-[38px] px-3 rounded-[5px] border border-[#d4d0c8] bg-white text-[13.5px] text-[#1d1c1b] placeholder-[#8c8882] focus:outline-none focus:border-[#2563eb] focus:ring-2 focus:ring-[#93c5fd]/40">

            <button type="submit"
                    class="mt-3 w-full h-[40px] rounded-[5px] bg-[#1b6ac9] hover:bg-[#1558a8] text-white text-[13.5px] font-bold transition-colors cursor-pointer">
                Email me reset instructions
            </button>
        </form>

        <div class="my-5 border-t border-[#e5e2dc]"></div>

        <p class="text-[12px] text-[#1d1c1b] leading-snug">
            <strong class="font-bold">If you don&rsquo;t see your reset email</strong> be sure to check your spam filter for an email from <em class="italic">support@basecamp.com</em>
        </p>
    </div>

    {{-- Bottom Link: Never mind, go back --}}
    <div class="mt-5 text-center text-[12px]">
        <a href="{{ route('login') }}" class="underline text-[#1d64c8] hover:opacity-80">Never mind, go back</a>
    </div>
</div>
@endsection

