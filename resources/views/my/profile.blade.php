@extends('layouts.app')

@section('title', 'My Profile - ' . ($org->name ?? 'Ajath Infotech Pvt Ltd'))

@section('content')
<div class="w-full px-4 pt-6 pb-20 font-sans text-[#1d2d35]"
     x-data="{
         editing: {{ request()->boolean('edit') ? 'true' : 'false' }},
         removeAvatarFlag: false
     }">

    <!-- ========================================================================= -->
    <!-- VIEW 1: PURPLE PROFILE CARD (Exact Match to media_1793277554869_1c31c27d.jpg) -->
    <!-- ========================================================================= -->
    <div x-show="!editing" class="flex justify-center pt-4">
        <div class="w-[306px] rounded-[10px] bg-[#c084fc] text-[#1d2d35] shadow-2xl px-5 pt-6 pb-5 text-center">
            <!-- Avatar Circle + Owner Pill -->
            <div class="relative inline-flex flex-col items-center">
                <img src="{{ ($user->avatar_url && !str_contains($user->avatar_url, 'dicebear')) ? $user->avatar_url : '/uploads/avatars/22369668.png' }}"
                     alt="{{ $user->name }}"
                     class="w-[104px] h-[104px] rounded-full object-cover shadow-md">
                <span class="-mt-3 px-3 py-0.5 rounded-full text-[12px] font-bold leading-tight shadow-xs bg-[#d870ad] text-[#1d2d35]">
                    Owner
                </span>
            </div>

            <!-- Name, Job Title & Local Time -->
            <h3 class="text-[21px] font-bold tracking-[-0.02em] leading-tight mt-2.5 text-[#1d2d35]">
                {{ $user->name ?? 'Manjot Singh' }}
            </h3>
            <p class="text-[13.5px] font-bold leading-snug mt-0.5 text-[#1d2d35]">
                {{ $user->job_title ?: 'Global VP & Partner at Ajath Infotech Pvt Ltd' }}
            </p>
            <p class="text-[13px] font-normal mt-1 text-[#1d2d35]/85">
                {{ now()->timezone('Asia/Kolkata')->format('g:i a') }} IST
            </p>

            <!-- Action Links Stack -->
            <div class="mt-4 space-y-1 text-[13.5px] text-[#1d2d35]">
                <a href="{{ route('my.activity') }}"
                   class="block w-full py-2 px-3 rounded-[6px] bg-white/30 hover:bg-white/45 font-normal transition-colors">
                    What I’ve been up to
                </a>
                <a href="{{ route('my.tasks') }}"
                   class="block w-full py-1.5 px-3 rounded-[6px] hover:bg-white/25 font-normal transition-colors">
                    My tasks
                </a>
                <a href="{{ route('projects.docs', 4) }}"
                   class="block w-full py-1.5 px-3 rounded-[6px] hover:bg-white/25 font-normal transition-colors">
                    My recently shared files
                </a>
                <div class="pt-2"></div>
                <button type="button"
                        @click="editing = true"
                        class="block w-full py-1.5 px-3 rounded-[6px] hover:bg-white/25 font-normal transition-colors cursor-pointer">
                    Edit my profile
                </button>
                <button type="button"
                        @click="editing = true"
                        class="block w-full py-1.5 px-3 rounded-[6px] hover:bg-white/25 font-normal transition-colors cursor-pointer">
                    Set Out of Office
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- VIEW 2: EDIT MY PROFILE CARD (Exact Match to media_1793277548776_7e98a10d.jpg) -->
    <!-- ========================================================================= -->
    <div x-show="editing" x-cloak class="w-[490px] max-w-[95vw] mx-auto rounded-[12px] bg-white border border-[#e5e2dc] shadow-[0_2px_12px_rgba(0,0,0,0.06)] px-7 pt-5 pb-6 text-[#1d2d35] mt-6">
        <form action="{{ route('my.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="remove_avatar" :value="removeAvatarFlag ? 1 : 0">

            <!-- Top Centered Avatar & Controls -->
            <div class="flex flex-col items-center text-center -mt-10">
                <img src="{{ ($user->avatar_url && !str_contains($user->avatar_url, 'dicebear')) ? $user->avatar_url : '/uploads/avatars/22369668.png' }}"
                     alt="{{ $user->name }}"
                     class="w-[104px] h-[104px] rounded-full border-2 border-[#1b6ac9] object-cover shadow-md bg-white">

                <label class="mt-2.5 px-3 py-1 rounded-[6px] border border-[#d6d2c9] bg-white hover:bg-[#f6f4f0] text-[13px] text-[#1d2d35] cursor-pointer transition-colors">
                    <span>Change your avatar...</span>
                    <input type="file" name="avatar" accept="image/*" class="hidden" onchange="this.form.submit()">
                </label>

                <button type="button"
                        @click="removeAvatarFlag = true; $el.closest('form').submit()"
                        class="mt-2 text-[12.5px] text-[#1b6ac9] underline hover:opacity-80 cursor-pointer">
                    Remove my avatar and show my initials instead
                </button>
            </div>

            <!-- Form Fields -->
            <div class="mt-5 space-y-4 text-left">
                <div>
                    <label class="block text-[14px] font-bold text-[#1d2d35] mb-1">
                        Name <span class="text-[12px] font-normal text-[#6d6a65]">required</span>
                    </label>
                    <input type="text"
                           name="name"
                           required
                           value="{{ old('name', $user->name ?? 'Manjot Singh') }}"
                           class="w-full h-[38px] px-3 rounded-[6px] border border-[#d6d2c9] bg-white text-[14.5px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                </div>

                <div>
                    <label class="block text-[14px] font-bold text-[#1d2d35] mb-1">
                        Title at {{ $org->name ?? 'Ajath Infotech Pvt Ltd' }}
                    </label>
                    <input type="text"
                           name="job_title"
                           value="{{ old('job_title', str_replace(' at Ajath Infotech Pvt Ltd', '', $user->job_title ?? 'Global VP & Partner')) }}"
                           class="w-full h-[38px] px-3 rounded-[6px] border border-[#d6d2c9] bg-white text-[14.5px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                </div>

                <div>
                    <label class="block text-[14px] font-bold text-[#1d2d35] mb-1">
                        Location
                    </label>
                    <input type="text"
                           name="location"
                           value=""
                           class="w-full h-[38px] px-3 rounded-[6px] border border-[#d6d2c9] bg-white text-[14.5px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                </div>

                <div>
                    <label class="block text-[14px] font-bold text-[#1d2d35] mb-1">
                        Current status
                    </label>
                    <input type="text"
                           name="bio"
                           value="{{ old('bio', $user->bio ?? '') }}"
                           class="w-full h-[38px] px-3 rounded-[6px] border border-[#d6d2c9] bg-white text-[14.5px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                </div>

                <div>
                    <label class="block text-[14px] font-bold text-[#1d2d35] mb-1">
                        Email address <span class="text-[12px] font-normal text-[#6d6a65]">required</span>
                    </label>
                    <input type="email"
                           name="email"
                           required
                           value="{{ old('email', $user->email ?? 'Manjot@ajath.com') }}"
                           class="w-full h-[38px] px-3 rounded-[6px] border border-[#d6d2c9] bg-white text-[14.5px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                </div>

                <div class="pt-1 text-[13.5px] leading-relaxed">
                    <div class="font-bold text-[#1d2d35]">Login info</div>
                    <div class="text-[#1d2d35]">You log in with a password.</div>
                    <a href="{{ route('my.login_option', ['current_user_id' => $user->id]) }}" class="text-[#1b6ac9] underline hover:opacity-80">
                        Set up 2FA or change your login info here.
                    </a>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit"
                        class="w-full py-2.5 rounded-[8px] bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-semibold text-[14.5px] transition-colors cursor-pointer">
                    Save my changes
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
