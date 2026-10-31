<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Change how you log in - Basecamp</title>
    <script type="module" src="/js/firebase-init.js?v=20261030_5"></script>
    <script>
        (function () {
            try {
                var mode = localStorage.getItem('bc_theme_pref_v2') || 'os';
                var h = new Date().getHours();
                var osDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var isDark = mode === 'dark' ? true : (mode === 'light' ? false : ((h >= 18 || h < 6) || osDark));
                document.documentElement.classList.toggle('bc-theme-dark', isDark);
            } catch (e) {}
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            font-size: 18px;
            background-color: #faf9f7;
            color: #1d2d35;
        }
        .text-\[13px\] { font-size: 15px !important; }
        .text-\[13\.5px\] { font-size: 15.5px !important; }
        .text-\[14px\] { font-size: 16px !important; }
        .text-\[14\.5px\] { font-size: 16.5px !important; }
        .text-\[15px\] { font-size: 17px !important; }
        .text-\[17px\] { font-size: 19px !important; }
        .text-\[19px\] { font-size: 21px !important; }
        .text-\[26px\] { font-size: 28px !important; }
        html.bc-theme-dark, html.bc-theme-dark body {
            background-color: #152026 !important;
            color: #e6eaed !important;
        }
        html.bc-theme-dark .bg-white {
            background-color: #212c34 !important;
            border-color: #34414b !important;
            color: #e6eaed !important;
        }
        html.bc-theme-dark .bg-\[\#f5f5f5\],
        html.bc-theme-dark .bg-\[\#faf9f7\] {
            background-color: #28343d !important;
            color: #e6eaed !important;
        }
        html.bc-theme-dark .border-\[\#ece9e2\],
        html.bc-theme-dark .border-\[\#e5e2dc\],
        html.bc-theme-dark .border-\[\#d4d0c8\] {
            border-color: #34414b !important;
        }
        html.bc-theme-dark .text-\[\#1d2d35\],
        html.bc-theme-dark .text-\[\#28363e\] {
            color: #e6eaed !important;
        }
        html.bc-theme-dark .text-\[\#6d6a65\] {
            color: #9da7b1 !important;
        }
        html.bc-theme-dark .text-\[\#1d6ce0\] {
            color: #58a6ff !important;
        }
        html.bc-theme-dark input {
            background-color: #182229 !important;
            border-color: #3b4954 !important;
            color: #e6eaed !important;
        }
    </style>
</head>
<body class="min-h-screen antialiased pb-20"
      x-data="{
          activeModal: null,
          twoFactorEnabled: false,
          twoFactorCode: ''
      }">

    <!-- Top Centered "← Back to Basecamp" Link -->
    <div class="pt-6 pb-4 text-center">
        <a href="{{ route('my.profile', ['edit' => 1]) }}"
           class="inline-flex items-center gap-1.5 text-[14.5px] font-normal text-[#1d2d35] hover:opacity-80">
            <span class="text-[15px] leading-none">&larr;</span>
            <span class="text-[#1d6ce0] underline">Back to Basecamp</span>
        </a>
    </div>

    @if(session('success'))
        <div class="w-[780px] max-w-[95vw] mx-auto mb-4 px-4 py-3 rounded-[6px] bg-[#ecfdf5] border border-[#a7f3d0] text-[#065f46] text-[14px] text-center font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="w-[780px] max-w-[95vw] mx-auto mb-4 px-4 py-3 rounded-[6px] bg-[#fef2f2] border border-[#fecaca] text-[#991b1b] text-[14px] text-center font-medium">
            {{ session('error') }}
        </div>
    @endif

    <!-- Main White Container Card (Exact Match to media_1793292630520_6ff4c6bd.jpg) -->
    <div class="w-[780px] max-w-[95vw] mx-auto bg-white rounded-[8px] border border-[#ece9e2] shadow-[0_1px_6px_rgba(0,0,0,0.03)] px-6 sm:px-14 pt-9 pb-12">

        <!-- ========================================================================= -->
        <!-- SECTION 1: LOGIN                                                          -->
        <!-- ========================================================================= -->
        <h1 class="text-[26px] font-bold text-[#1d2d35] text-center tracking-[-0.015em] mb-7">
            Login
        </h1>

        @php
            $usingGoogle = session('login_method') === 'google';
            $googleLinkedEmail = session('google_linked_email', $user->email);
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-7 items-stretch max-w-[660px] mx-auto">

            <!-- Left Card: My Google account -->
            <div class="relative rounded-[6px] {{ $usingGoogle ? 'border-[1.5px] border-[#6b5b95]' : 'border border-[#e5e2dc]' }} bg-white px-7 pt-8 pb-7 flex flex-col items-center text-center">
                @if($usingGoogle)
                <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3.5 py-1 rounded-[5px] bg-[#6c5ce7] text-white text-[14px] font-bold leading-none whitespace-nowrap">
                    You&rsquo;re using this
                </span>
                @endif
                <!-- Google G Circle Icon -->
                <div class="w-[54px] h-[54px] rounded-full bg-[#f5f5f5] flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" viewBox="0 0 48 48">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                </div>

                <h2 class="text-[17px] font-bold text-[#1d2d35] mb-3.5">
                    My Google account
                </h2>

                @if($usingGoogle)
                    <p class="text-[13.5px] text-[#28363e] leading-[1.45]">
                        You&rsquo;re currently signed in with Google ({{ $googleLinkedEmail }}).<br>
                        <button type="button"
                                @click="if (window.bcSignInWithGoogle) { window.bcSignInWithGoogle({ csrfToken: '{{ csrf_token() }}', mode: 'switch' }); }"
                                class="text-[#1d6ce0] underline hover:opacity-80 cursor-pointer mt-0.5 inline-block">
                            Change Google account...
                        </button>
                    </p>
                @else
                    <button type="button"
                            @click="if (window.bcSignInWithGoogle) { window.bcSignInWithGoogle({ csrfToken: '{{ csrf_token() }}', mode: 'switch' }); } else { activeModal = 'google'; }"
                            class="w-full py-2.5 px-4 rounded-[4px] bg-[#1d6ce0] hover:bg-[#1557b8] text-white font-semibold text-[15px] transition-colors cursor-pointer">
                        Switch to Google
                    </button>

                    <p class="text-[13.5px] text-[#28363e] leading-[1.45] mt-4">
                        This will replace your current email address and password login.
                    </p>
                @endif
            </div>

            <!-- Right Card: Your email address & password -->
            <div class="relative rounded-[6px] {{ !$usingGoogle ? 'border-[1.5px] border-[#6b5b95]' : 'border border-[#e5e2dc]' }} bg-white px-7 pt-8 pb-7 flex flex-col items-center text-center">
                @if(!$usingGoogle)
                <!-- Overlapping Top Purple Badge: "You're using this" -->
                <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3.5 py-1 rounded-[5px] bg-[#6c5ce7] text-white text-[14px] font-bold leading-none whitespace-nowrap">
                    You&rsquo;re using this
                </span>
                @endif

                <!-- Airmail Postcard Envelope Illustration SVG -->
                <div class="w-[62px] h-[48px] mb-4 flex items-center justify-center">
                    <svg class="w-[60px] h-[44px]" viewBox="0 0 60 44" fill="none">
                        <!-- Card Base -->
                        <rect x="2" y="2" width="56" height="40" rx="4" fill="#f5f5f5"/>
                        <!-- Red Stamp Top Right -->
                        <rect x="43" y="8" width="9" height="9" rx="1" fill="#e0533c"/>
                        <!-- Address Lines -->
                        <rect x="14" y="22" width="28" height="2" rx="1" fill="#8c969e"/>
                        <rect x="14" y="27" width="20" height="2" rx="1" fill="#8c969e"/>
                        <!-- Bottom Airmail Stripes -->
                        <path d="M5 39H13L15 42H7L5 39Z" fill="#e0533c"/>
                        <path d="M21 39H29L31 42H23L21 39Z" fill="#2b6cb0"/>
                        <path d="M37 39H45L47 42H39L37 39Z" fill="#e0533c"/>
                        <path d="M51 39H55L56 42H52L51 39Z" fill="#2b6cb0"/>
                    </svg>
                </div>

                <h2 class="text-[17px] font-bold text-[#1d2d35] mb-3">
                    Your email address &amp; password
                </h2>

                <p class="text-[13.5px] text-[#28363e] leading-[1.45]">
                    You&rsquo;re currently using your email address and password.<br>
                    <button type="button"
                            @click="activeModal = 'password'"
                            class="text-[#1d6ce0] underline hover:opacity-80 cursor-pointer mt-0.5 inline-block">
                        Change my password...
                    </button>
                </p>
            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- SECTION 2: SECURITY                                                       -->
        <!-- ========================================================================= -->
        <h2 class="text-[26px] font-bold text-[#1d2d35] text-center tracking-[-0.015em] mt-11 mb-6">
            Security
        </h2>

        <div class="max-w-[318px] mx-auto rounded-[6px] border border-[#e5e2dc] bg-white px-7 pt-7 pb-7 flex flex-col items-center text-center">
            <!-- Smartphone with Padlock Illustration SVG -->
            <div class="w-[56px] h-[54px] mb-3.5 flex items-center justify-center">
                <svg class="w-[54px] h-[52px]" viewBox="0 0 54 52" fill="none">
                    <!-- Phone Body -->
                    <rect x="8" y="3" width="26" height="45" rx="4" fill="#1e191a"/>
                    <!-- Red Earpiece Speaker -->
                    <rect x="17" y="6.5" width="8" height="2" rx="1" fill="#d9534f"/>
                    <!-- Phone White Screen -->
                    <rect x="11" y="11" width="20" height="28" rx="1" fill="#ffffff"/>
                    <!-- Text Lines on Screen -->
                    <rect x="14" y="16" width="12" height="2" rx="1" fill="#7c878e"/>
                    <rect x="14" y="21" width="14" height="2" rx="1" fill="#7c878e"/>
                    <rect x="14" y="26" width="10" height="2" rx="1" fill="#7c878e"/>
                    <!-- Home Button Dot -->
                    <circle cx="21" cy="43.5" r="2" fill="#ffffff"/>
                    <!-- Gold Padlock Shackle -->
                    <path d="M32 21V17.5C32 14.46 34.46 12 37.5 12C40.54 12 43 14.46 43 17.5V21" stroke="#f4a261" stroke-width="3.2" stroke-linecap="round"/>
                    <!-- Gold Padlock Body -->
                    <rect x="28" y="20" width="19" height="16" rx="3" fill="#fbd36b"/>
                    <!-- Padlock Keyhole -->
                    <circle cx="37.5" cy="26.5" r="2" fill="#e6a100"/>
                    <rect x="36.5" y="27.5" width="2" height="4" rx="1" fill="#e6a100"/>
                </svg>
            </div>

            <h3 class="text-[17px] font-bold text-[#1d2d35] mb-3.5">
                Two-factor authentication
            </h3>

            <button type="button"
                    @click="activeModal = '2fa'"
                    class="w-full py-2.5 px-4 rounded-[4px] bg-[#1d6ce0] hover:bg-[#1557b8] text-white font-semibold text-[15px] transition-colors cursor-pointer">
                <span x-text="twoFactorEnabled ? 'Manage 2FA (Enabled)' : 'Set up 2FA'">Set up 2FA</span>
            </button>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- INTERACTIVE MODALS (Password Change / 2FA Setup / Google Switch)          -->
    <!-- ========================================================================= -->
    <div x-show="activeModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/40" @click="activeModal = null"></div>

        <div class="relative z-10 w-[440px] max-w-[95vw] bg-white rounded-[8px] border border-[#e5e2dc] shadow-2xl p-6 text-[#1d2d35]">
            <button type="button" @click="activeModal = null" class="absolute top-3.5 right-3.5 p-1 text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer">✕</button>

            <!-- Change Password Modal -->
            <div x-show="activeModal === 'password'">
                <h3 class="text-[20px] font-bold text-[#1d2d35] mb-1">Change your password</h3>
                <p class="text-[13px] text-[#6d6a65] mb-4">Enter your current password and choose a new password for {{ $user->email }}.</p>

                <form action="{{ route('my.settings.update') }}" method="POST" class="space-y-3.5 text-left">
                    @csrf
                    <div>
                        <label class="block text-[13.5px] font-bold text-[#1d2d35] mb-1">Current password</label>
                        <input type="password" name="current_password" required
                               class="w-full h-[38px] px-3 rounded-[6px] border border-[#d6d2c9] text-[14px] focus:outline-none focus:border-[#1d6ce0]">
                    </div>
                    <div>
                        <label class="block text-[13.5px] font-bold text-[#1d2d35] mb-1">New password</label>
                        <input type="password" name="new_password" required minlength="6"
                               class="w-full h-[38px] px-3 rounded-[6px] border border-[#d6d2c9] text-[14px] focus:outline-none focus:border-[#1d6ce0]">
                    </div>
                    <div>
                        <label class="block text-[13.5px] font-bold text-[#1d2d35] mb-1">Confirm new password</label>
                        <input type="password" name="new_password_confirmation" required minlength="6"
                               class="w-full h-[38px] px-3 rounded-[6px] border border-[#d6d2c9] text-[14px] focus:outline-none focus:border-[#1d6ce0]">
                    </div>
                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button type="button" @click="activeModal = null" class="px-4 py-2 rounded-[5px] border border-[#d6d2c9] text-[13.5px] font-medium hover:bg-[#f6f4f0] cursor-pointer">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-[5px] bg-[#1d6ce0] hover:bg-[#1557b8] text-white text-[13.5px] font-semibold cursor-pointer">Save new password</button>
                    </div>
                </form>
            </div>

            <!-- Set up 2FA Modal -->
            <div x-show="activeModal === '2fa'">
                <h3 class="text-[20px] font-bold text-[#1d2d35] mb-1">Set up Two-Factor Authentication</h3>
                <p class="text-[13px] text-[#6d6a65] mb-4">Scan the QR code below with your authenticator app (Google Authenticator, Authy, or 1Password) and enter the 6-digit verification code.</p>

                <div class="p-4 rounded-[8px] bg-[#faf9f7] border border-[#e5e2dc] text-center mb-4">
                    <div class="inline-block p-3 bg-white rounded-[6px] border border-[#e5e2dc] mb-2">
                        <svg class="w-28 h-28 text-[#1d2d35]" viewBox="0 0 100 100" fill="currentColor">
                            <rect x="10" y="10" width="25" height="25" fill="none" stroke="currentColor" stroke-width="6"/>
                            <rect x="17" y="17" width="11" height="11"/>
                            <rect x="65" y="10" width="25" height="25" fill="none" stroke="currentColor" stroke-width="6"/>
                            <rect x="72" y="17" width="11" height="11"/>
                            <rect x="10" y="65" width="25" height="25" fill="none" stroke="currentColor" stroke-width="6"/>
                            <rect x="17" y="72" width="11" height="11"/>
                            <rect x="44" y="14" width="8" height="8"/><rect x="44" y="28" width="8" height="8"/>
                            <rect x="14" y="44" width="8" height="8"/><rect x="28" y="44" width="8" height="8"/>
                            <rect x="44" y="44" width="12" height="12"/><rect x="64" y="44" width="8" height="8"/>
                            <rect x="78" y="52" width="10" height="10"/><rect x="44" y="66" width="8" height="18"/>
                            <rect x="60" y="68" width="14" height="8"/><rect x="66" y="80" width="20" height="8"/>
                        </svg>
                    </div>
                    <div class="text-[11.5px] font-mono text-[#5c5955]">Secret key: AJTH-BC40-2FA9-7781</div>
                </div>

                <div class="mb-4">
                    <label class="block text-[13.5px] font-bold text-[#1d2d35] mb-1">6-digit verification code</label>
                    <input type="text" x-model="twoFactorCode" maxlength="6" placeholder="123456"
                           class="w-full h-[38px] px-3 rounded-[6px] border border-[#d6d2c9] text-[15px] tracking-widest font-mono focus:outline-none focus:border-[#1d6ce0]">
                </div>

                <div class="flex items-center justify-end gap-2">
                    <button type="button" @click="activeModal = null" class="px-4 py-2 rounded-[5px] border border-[#d6d2c9] text-[13.5px] font-medium hover:bg-[#f6f4f0] cursor-pointer">Cancel</button>
                    <button type="button" @click="twoFactorEnabled = true; activeModal = null" class="px-4 py-2 rounded-[5px] bg-[#1d6ce0] hover:bg-[#1557b8] text-white text-[13.5px] font-semibold cursor-pointer">Enable 2FA</button>
                </div>
            </div>

            <!-- Switch to Google Modal -->
            <div x-show="activeModal === 'google'">
                <h3 class="text-[20px] font-bold text-[#1d2d35] mb-1">Switch to Google login</h3>
                <p class="text-[13.5px] text-[#5c5955] leading-relaxed mb-4">
                    Your account (<strong>{{ $user->email }}</strong>) is currently configured to sign in with an email address and password on <strong>workspace.ajath.com</strong>.
                </p>
                <div class="flex items-center justify-end gap-2">
                    <button type="button" @click="activeModal = null" class="px-4 py-2 rounded-[5px] bg-[#1d6ce0] hover:bg-[#1557b8] text-white text-[13.5px] font-semibold cursor-pointer">Got it</button>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
