<!DOCTYPE html>
<html lang="en" class="h-full bg-[#f4f6f5]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Home')</title>
    <script>
        (function () {
            try {
                var matches = document.cookie.match(/humans_21909=/g);
                if (!matches) {
                    document.cookie = 'humans_21909=1; path=/';
                } else if (matches.length > 1) {
                    window.location.replace('/clear-subpath-cookies?redirect=' + encodeURIComponent(window.location.pathname + window.location.search));
                }
            } catch (e) {}

            // Global Basecamp Theme Engine: Always light ('light'), Always dark ('dark'), or Time-bound / Same as OS ('os')
            window.getBasecampAppearance = function () {
                try {
                    return localStorage.getItem('bc_theme_pref_v2') || 'os';
                } catch (e) {
                    return 'os';
                }
            };
            window.isBasecampDarkTime = function () {
                var h = new Date().getHours();
                return (h >= 18 || h < 6);
            };
            window.applyBasecampTheme = function (mode) {
                try {
                    if (mode) {
                        localStorage.setItem('bc_theme_pref_v2', mode);
                        localStorage.setItem('bc_appearance', mode);
                    }
                    var currentMode = mode || window.getBasecampAppearance();
                    var shouldBeDark = false;
                    if (currentMode === 'dark') {
                        shouldBeDark = true;
                    } else if (currentMode === 'light') {
                        shouldBeDark = false;
                    } else {
                        // 'os' (Same as OS / Time-bound: dark from 6:00 PM to 6:00 AM or when OS prefers dark)
                        var osDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                        shouldBeDark = window.isBasecampDarkTime() || osDark;
                    }
                    document.documentElement.classList.toggle('bc-theme-dark', shouldBeDark);
                    if (document.body) {
                        document.body.classList.toggle('bc-theme-dark', shouldBeDark);
                    }
                    window.dispatchEvent(new CustomEvent('bc-theme-changed', {
                        detail: { mode: currentMode, isDark: shouldBeDark }
                    }));
                } catch (e) {}
            };
            window.toggleBasecampDarkLight = function () {
                var isCurrentlyDark = document.documentElement.classList.contains('bc-theme-dark');
                window.applyBasecampTheme(isCurrentlyDark ? 'light' : 'dark');
            };
            window.applyBasecampTheme();
            setInterval(function () {
                window.applyBasecampTheme();
            }, 60000);
            if (window.matchMedia) {
                try {
                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
                        window.applyBasecampTheme();
                    });
                } catch (e) {}
            }
            window.addEventListener('pageshow', function (e) {
                if (e.persisted) {
                    window.applyBasecampTheme();
                    window.location.reload();
                }
            });
        })();
    </script>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        basecamp: {
                            green: '#199849',
                            darkGreen: '#127337',
                            canvas: '#f4f6f5',
                            cream: '#fbfaf8',
                        }
                    },
                    fontFamily: {
                        sans: ['-apple-system', 'system-ui', 'BlinkMacSystemFont', 'Aptos', 'Roboto', '"Segoe UI"', 'Helvetica', 'Arial', 'sans-serif', '"Apple Color Emoji"', '"Segoe UI Emoji"', '"Segoe UI Symbol"'],
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Firebase SDK (workspace-bb7f2) -->
    <script type="module" src="/js/firebase-init.js?v=20261030_5"></script>
    <style>
        [x-cloak] { display: none !important; }
        html {
            scrollbar-gutter: stable;
            overflow-x: clip;
        }
        body { 
            overflow-x: clip;
            background-color: #fbfaf8; 
            color: #1d2d35; 
            font-family: -apple-system, system-ui, BlinkMacSystemFont, Aptos, Roboto, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            font-size: 18px;
            letter-spacing: -0.00625em;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            margin: 0;
        }
        /* Global +2px Platform Font Size Scale */
        .text-xs { font-size: 14px !important; }
        .text-sm { font-size: 16px !important; }
        .text-base { font-size: 18px !important; }
        .text-lg { font-size: 20px !important; }
        .text-xl { font-size: 22px !important; }
        .text-2xl { font-size: 26px !important; }
        .text-3xl { font-size: 32px !important; }
        .text-4xl { font-size: 38px !important; }
        .text-\[8\.5px\] { font-size: 10.5px !important; }
        .text-\[9px\] { font-size: 11px !important; }
        .text-\[9\.5px\] { font-size: 11.5px !important; }
        .text-\[10px\] { font-size: 12px !important; }
        .text-\[10\.5px\] { font-size: 12.5px !important; }
        .text-\[11px\] { font-size: 13px !important; }
        .text-\[11\.5px\] { font-size: 13.5px !important; }
        .text-\[12px\] { font-size: 14px !important; }
        .text-\[12\.5px\] { font-size: 14.5px !important; }
        .text-\[13px\] { font-size: 15px !important; }
        .text-\[13\.5px\] { font-size: 15.5px !important; }
        .text-\[14px\] { font-size: 16px !important; }
        .text-\[14\.5px\] { font-size: 16.5px !important; }
        .text-\[15px\] { font-size: 17px !important; }
        .text-\[15\.5px\] { font-size: 17.5px !important; }
        .text-\[16px\] { font-size: 18px !important; }
        .text-\[16\.5px\] { font-size: 18.5px !important; }
        .text-\[17px\] { font-size: 19px !important; }
        .text-\[17\.5px\] { font-size: 19.5px !important; }
        .text-\[18px\] { font-size: 20px !important; }
        .text-\[18\.5px\] { font-size: 20.5px !important; }
        .text-\[19px\] { font-size: 21px !important; }
        .text-\[19\.5px\] { font-size: 21.5px !important; }
        .text-\[20px\] { font-size: 22px !important; }
        .text-\[21px\] { font-size: 23px !important; }
        .text-\[22px\] { font-size: 24px !important; }
        .text-\[24px\] { font-size: 26px !important; }
        .text-\[26px\] { font-size: 28px !important; }
        .text-\[28px\] { font-size: 30px !important; }
        .text-\[30px\] { font-size: 32px !important; }
        .text-\[31px\] { font-size: 33px !important; }
        .text-\[32px\] { font-size: 34px !important; }
        .text-\[34px\] { font-size: 36px !important; }
        .text-\[36px\] { font-size: 38px !important; }
        .text-\[40px\] { font-size: 42px !important; }
        .text-\[42px\] { font-size: 44px !important; }
        .text-\[44px\] { font-size: 46px !important; }
        @media (min-width: 640px) {
            .sm\:text-\[18px\] { font-size: 20px !important; }
            .sm\:text-\[19px\] { font-size: 21px !important; }
            .sm\:text-\[19\.5px\] { font-size: 21.5px !important; }
            .sm\:text-\[20px\] { font-size: 22px !important; }
            .sm\:text-\[27px\] { font-size: 29px !important; }
            .sm\:text-\[28px\] { font-size: 30px !important; }
            .sm\:text-\[29px\] { font-size: 31px !important; }
            .sm\:text-\[30px\] { font-size: 32px !important; }
            .sm\:text-\[31px\] { font-size: 33px !important; }
            .sm\:text-\[32px\] { font-size: 34px !important; }
            .sm\:text-\[34px\] { font-size: 36px !important; }
            .sm\:text-\[36px\] { font-size: 38px !important; }
            .sm\:text-\[46px\] { font-size: 48px !important; }
            .sm\:text-\[48px\] { font-size: 50px !important; }
        }
        h1, h2, h3, h4 {
            letter-spacing: -0.02375em;
        }
        .basecamp-card-shadow { box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04); }
        .basecamp-card-shadow:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04); }
        .bc-sheet {
            background-color: #ffffff;
            border: 1px solid #e5e2dc;
            border-radius: 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }
        /* Pre-Tailwind critical layout & icon lock to prevent left-to-right FOUC shift on load */
        svg { max-width: 100%; }
        .w-3\.5 { width: 0.875rem; } .h-3\.5 { height: 0.875rem; }
        .w-4 { width: 1rem; } .h-4 { height: 1rem; }
        .w-5 { width: 1.25rem; } .h-5 { height: 1.25rem; }
        .w-6 { width: 1.5rem; } .h-6 { height: 1.5rem; }
        .w-7 { width: 1.75rem; } .h-7 { height: 1.75rem; }
        .h-11 { height: 2.75rem; }
        header > div > div { display: flex; align-items: center; justify-content: space-between; height: 3.5rem; }
        footer { position: fixed; bottom: 0; left: 0; right: 0; height: 2.5rem; }
        footer > div { display: flex; align-items: center; justify-content: space-between; height: 100%; }
        .bc-home-shell { width: 100%; max-width: 1432px; margin-left: auto; margin-right: auto; padding-left: 1rem; padding-right: 1rem; padding-top: 0.25rem; }
        .bc-home-middle {
            width: 480px;
            max-width: 100%;
            margin-left: auto;
            margin-right: auto;
            container-type: inline-size;
            position: relative;
            transition: width 320ms cubic-bezier(0.25, 1.35, 0.5, 1);
            will-change: width;
        }
        .bc-home-middle.is-expanded {
            width: 1088px;
            max-width: 100%;
        }
        @media (min-width: 1024px) {
            .bc-home-row:not(.is-expanded) {
                display: grid !important;
                grid-template-columns: 1fr auto 1fr;
                align-items: start;
                gap: 0;
            }
            .bc-home-row.is-expanded {
                display: flex !important;
                flex-direction: column;
                align-items: center;
            }
            .bc-home-left {
                width: auto;
                max-width: 360px;
                margin: 76px 0 auto auto;
                padding-right: 30px;
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                text-align: right;
            }
            .bc-home-right {
                width: 360px;
                max-width: 360px;
                margin: 76px auto 0 30px;
                text-align: left;
            }
        }
        .bc-home-well {
            background-color: #f2f0ec;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            padding: 16px 16px 40px;
            position: relative;
            min-height: calc(100vh - 88px);
            transition: background-color 280ms ease, padding 280ms ease;
        }
        .bc-home-well.is-expanded {
            background-color: transparent;
            padding: 4px 4px 64px;
        }
        .bc-home-logo {
            height: clamp(44px, 7.5cqi, 76px);
            width: auto;
            transition: height 280ms cubic-bezier(0.25, 1.35, 0.5, 1), transform 280ms cubic-bezier(0.25, 1.35, 0.5, 1);
        }
        .bc-home-prompt {
            font-size: clamp(13px, 1.8cqi, 15px);
        }
        .bc-home-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(min(100%, max(calc(25% - 12px), 196px)), 1fr));
            gap: 10px;
            padding-top: 4px;
            transition: gap 280ms ease;
        }
        .bc-home-grid.is-expanded {
            gap: 12px;
        }

        /* =========================================================================
           Basecamp 1:1 Dark Theme (.bc-theme-dark)
           Active when Appearance is "Always dark", or in "Same as OS" / Time-bound
           mode between 6:00 PM and 6:00 AM (or when OS prefers dark).
           ========================================================================= */
        html.bc-theme-dark,
        html.bc-theme-dark body {
            background-color: #152026 !important;
            color: #e6eaed !important;
        }
        html.bc-theme-dark header {
            background-color: #152026 !important;
        }
        html.bc-theme-dark footer {
            background-color: #1c262d !important;
            border-color: #2f3b44 !important;
        }
        html.bc-theme-dark .bc-home-well:not(.is-expanded) {
            background-color: #1c262d !important;
        }
        html.bc-theme-dark .bg-white,
        html.bc-theme-dark .bg-white\/80,
        html.bc-theme-dark .bg-white\/90,
        html.bc-theme-dark .bg-white\/95,
        html.bc-theme-dark .bg-\[\#fdfcfa\],
        html.bc-theme-dark .bc-sheet {
            background-color: #212c34 !important;
            border-color: #34414b !important;
            color: #e6eaed !important;
        }
        html.bc-theme-dark .bc-sheet h1,
        html.bc-theme-dark .bc-sheet h2,
        html.bc-theme-dark .bc-sheet h3,
        html.bc-theme-dark .bc-sheet h4,
        html.bc-theme-dark .bc-sheet p,
        html.bc-theme-dark .bc-sheet strong {
            color: #e6eaed;
        }
        html.bc-theme-dark .bg-\[\#fbfaf8\],
        html.bc-theme-dark .bg-\[\#f4f6f5\],
        html.bc-theme-dark .bg-\[\#f6f4f0\],
        html.bc-theme-dark .bg-\[\#f2f0ec\],
        html.bc-theme-dark .bg-\[\#f7f5f0\],
        html.bc-theme-dark .bg-\[\#f7f5f2\],
        html.bc-theme-dark .bg-\[\#f7f5f2\]\/60,
        html.bc-theme-dark .bg-\[\#f7f5f2\]\/80,
        html.bc-theme-dark .bg-\[\#f7f5f2\]\/90,
        html.bc-theme-dark .bg-\[\#f2efe9\],
        html.bc-theme-dark .bg-\[\#fffef9\],
        html.bc-theme-dark .bg-\[\#fffdf7\],
        html.bc-theme-dark .bg-\[\#faf9f7\],
        html.bc-theme-dark .bg-\[\#faf9f6\],
        html.bc-theme-dark .bg-\[\#faf8f5\],
        html.bc-theme-dark .bg-\[\#faf6f0\],
        html.bc-theme-dark .bg-\[\#fafafa\],
        html.bc-theme-dark .bg-\[\#fbf7f2\],
        html.bc-theme-dark .bg-\[\#fbf9f5\],
        html.bc-theme-dark .bg-\[\#fcfbf9\],
        html.bc-theme-dark .bg-\[\#f9f8f5\],
        html.bc-theme-dark .bg-\[\#f9f8f6\],
        html.bc-theme-dark .bg-\[\#f7f2e7\],
        html.bc-theme-dark .bg-\[\#f6f5f1\],
        html.bc-theme-dark .bg-\[\#f6f4ef\],
        html.bc-theme-dark .bg-\[\#f6f2ec\],
        html.bc-theme-dark .bg-\[\#f6f2eb\],
        html.bc-theme-dark .bg-\[\#f5f5f5\],
        html.bc-theme-dark .bg-\[\#f5f2ec\],
        html.bc-theme-dark .bg-\[\#eaebed\],
        html.bc-theme-dark .bg-\[\#ebebeb\],
        html.bc-theme-dark .bg-\[\#e8eef3\],
        html.bc-theme-dark .bg-\[\#eef1f5\],
        html.bc-theme-dark .bg-\[\#e8e5df\],
        html.bc-theme-dark .bg-\[\#e8e4dc\],
        html.bc-theme-dark .bg-\[\#e6e3dd\],
        html.bc-theme-dark .bg-\[\#e5e7eb\],
        html.bc-theme-dark .bg-\[\#e5e2dc\]\/40,
        html.bc-theme-dark .bg-\[\#e5e1d8\],
        html.bc-theme-dark .bg-\[\#dfd9ce\],
        html.bc-theme-dark .bg-gray-50,
        html.bc-theme-dark .bg-slate-50,
        html.bc-theme-dark .bg-slate-100 {
            background-color: #28343d !important;
            color: #e6eaed !important;
        }
        html.bc-theme-dark .bg-\[\#eef8f1\],
        html.bc-theme-dark .bg-\[\#ecfdf5\],
        html.bc-theme-dark .bg-\[\#e8f5e9\],
        html.bc-theme-dark .bg-\[\#e9f5e9\],
        html.bc-theme-dark .bg-\[\#dcefdc\] {
            background-color: rgba(22, 101, 52, 0.28) !important;
            border-color: #22c55e !important;
            color: #e6eaed !important;
        }
        html.bc-theme-dark .bg-\[\#fef2f2\],
        html.bc-theme-dark .bg-\[\#fef2f0\],
        html.bc-theme-dark .bg-\[\#fee2e2\] {
            background-color: rgba(153, 27, 27, 0.28) !important;
            border-color: #ef4444 !important;
            color: #fecaca !important;
        }
        html.bc-theme-dark .hover\:bg-\[\#f6f4f0\]:hover,
        html.bc-theme-dark .hover\:bg-\[\#f2f0ec\]:hover,
        html.bc-theme-dark .hover\:bg-\[\#f7f5f2\]:hover,
        html.bc-theme-dark .hover\:bg-\[\#f2efe9\]:hover,
        html.bc-theme-dark .hover\:bg-\[\#fbfaf8\]:hover,
        html.bc-theme-dark .hover\:bg-\[\#f9f8f6\]:hover,
        html.bc-theme-dark .hover\:bg-\[\#ebebeb\]:hover,
        html.bc-theme-dark .hover\:bg-\[\#e8e5df\]:hover,
        html.bc-theme-dark .hover\:bg-\[\#e6e3dd\]:hover,
        html.bc-theme-dark .hover\:bg-slate-50:hover,
        html.bc-theme-dark .hover\:bg-slate-100:hover {
            background-color: #303e49 !important;
        }
        html.bc-theme-dark .bg-\[\#fbf0df\],
        html.bc-theme-dark .bg-\[\#f5ead8\],
        html.bc-theme-dark .bg-\[\#faecd8\],
        html.bc-theme-dark .bg-\[\#f5ebe1\],
        html.bc-theme-dark .bg-\[\#fcf0e1\],
        html.bc-theme-dark .bg-\[\#fae8d4\],
        html.bc-theme-dark .bg-\[\#fdfae5\],
        html.bc-theme-dark .bg-\[\#fef3c7\] {
            background-color: #493829 !important;
            border-color: #5c4734 !important;
            color: #f5ead8 !important;
        }
        html.bc-theme-dark .bg-\[\#fcf0e1\] span,
        html.bc-theme-dark .bg-\[\#fae8d4\] span {
            color: #f5ead8 !important;
        }
        html.bc-theme-dark .border-\[\#f5dec3\] {
            border-color: #5c4734 !important;
        }
        html.bc-theme-dark .hover\:bg-\[\#ede0d3\]:hover,
        html.bc-theme-dark .hover\:bg-\[\#f7e5ce\]:hover,
        html.bc-theme-dark .hover\:bg-\[\#f5dcc0\]:hover {
            background-color: #574332 !important;
        }
        html.bc-theme-dark .bg-\[\#e6f2fa\],
        html.bc-theme-dark .bg-\[\#e6effa\],
        html.bc-theme-dark .bg-\[\#e8f0fe\],
        html.bc-theme-dark .bg-\[\#eef2ff\],
        html.bc-theme-dark .bg-\[\#eef3fe\],
        html.bc-theme-dark .bg-\[\#eef5fc\],
        html.bc-theme-dark .bg-\[\#eff6ff\],
        html.bc-theme-dark .bg-\[\#dbeafe\],
        html.bc-theme-dark .bg-\[\#d9ebf7\] {
            background-color: #1e3a5f !important;
            border-color: #2b5282 !important;
            color: #e6eaed !important;
        }
        html.bc-theme-dark .bg-\[\#c084fc\],
        html.bc-theme-dark .bg-\[\#c084fc\] .text-\[\#1d2d35\],
        html.bc-theme-dark .bg-\[\#d870ad\],
        html.bc-theme-dark .bg-\[\#2bbbad\],
        html.bc-theme-dark .bc-badge-dark-text,
        html.bc-theme-dark .bg-\[\#facc15\] span {
            color: #1d2d35 !important;
        }
        html.bc-theme-dark .bg-\[\#60a5fa\] {
            color: #0f172a !important;
        }
        html.bc-theme-dark .bg-\[\#fef08a\],
        html.bc-theme-dark .bg-\[\#bbf7d0\] {
            color: #1d1c1b !important;
        }
        html.bc-theme-dark .border-\[\#e5e2dc\],
        html.bc-theme-dark .border-\[\#ece9e2\],
        html.bc-theme-dark .border-\[\#e8e5df\],
        html.bc-theme-dark .border-\[\#dcd8d0\],
        html.bc-theme-dark .border-\[\#d6d2c9\],
        html.bc-theme-dark .border-\[\#d8d4cc\],
        html.bc-theme-dark .border-\[\#d4d0c8\],
        html.bc-theme-dark .border-\[\#e2dfd9\],
        html.bc-theme-dark .border-\[\#f2ece1\],
        html.bc-theme-dark .border-\[\#f0ede8\],
        html.bc-theme-dark .border-slate-100,
        html.bc-theme-dark .border-slate-200,
        html.bc-theme-dark .border-slate-300,
        html.bc-theme-dark .divide-\[\#ece9e2\] > :not([hidden]) ~ :not([hidden]),
        html.bc-theme-dark .divide-\[\#e5e2dc\] > :not([hidden]) ~ :not([hidden]),
        html.bc-theme-dark .divide-\[\#e5e2dc\]\/60 > :not([hidden]) ~ :not([hidden]),
        html.bc-theme-dark .divide-\[\#f2efe9\] > :not([hidden]) ~ :not([hidden]),
        html.bc-theme-dark .divide-\[\#f0ede8\] > :not([hidden]) ~ :not([hidden]) {
            border-color: #34414b !important;
        }
        html.bc-theme-dark .bg-\[\#e5e2dc\],
        html.bc-theme-dark .bg-\[\#ece9e2\] {
            background-color: #34414b !important;
        }
        html.bc-theme-dark .text-\[\#1d2d35\],
        html.bc-theme-dark .text-\[\#1d2d35\]\/85,
        html.bc-theme-dark .text-\[\#1d1c1b\],
        html.bc-theme-dark .text-\[\#1d1c1b\]\/70,
        html.bc-theme-dark .text-\[\#1d1c1b\]\/75,
        html.bc-theme-dark .text-\[\#0e151a\],
        html.bc-theme-dark .text-\[\#131b21\],
        html.bc-theme-dark .text-\[\#28363e\],
        html.bc-theme-dark .text-\[\#283c46\],
        html.bc-theme-dark .text-\[\#37474f\],
        html.bc-theme-dark .text-\[\#4a4640\],
        html.bc-theme-dark .text-\[\#3d3a35\],
        html.bc-theme-dark .text-black,
        html.bc-theme-dark .text-slate-900,
        html.bc-theme-dark .text-slate-800,
        html.bc-theme-dark .text-slate-700,
        html.bc-theme-dark .text-gray-900,
        html.bc-theme-dark .text-gray-800,
        html.bc-theme-dark .text-gray-700 {
            color: #e6eaed !important;
        }
        html.bc-theme-dark .text-\[\#6d6a65\],
        html.bc-theme-dark .text-\[\#6d6a65\]\/60,
        html.bc-theme-dark .text-\[\#5c5955\],
        html.bc-theme-dark .text-\[\#4a5560\],
        html.bc-theme-dark .text-\[\#4b5563\],
        html.bc-theme-dark .text-\[\#4b5e69\],
        html.bc-theme-dark .text-\[\#6b7280\],
        html.bc-theme-dark .text-\[\#8c8881\],
        html.bc-theme-dark .text-\[\#87837c\],
        html.bc-theme-dark .text-\[\#9b978f\],
        html.bc-theme-dark .text-\[\#7d7a75\],
        html.bc-theme-dark .text-\[\#7c7872\],
        html.bc-theme-dark .text-\[\#5a656d\],
        html.bc-theme-dark .text-gray-600,
        html.bc-theme-dark .text-gray-500,
        html.bc-theme-dark .text-slate-600,
        html.bc-theme-dark .text-slate-500 {
            color: #9da7b1 !important;
        }
        html.bc-theme-dark .text-\[\#1b6ac9\],
        html.bc-theme-dark .text-\[\#124b8f\],
        html.bc-theme-dark .text-\[\#1458a6\],
        html.bc-theme-dark .text-\[\#1557a6\],
        html.bc-theme-dark .text-\[\#1557a8\],
        html.bc-theme-dark .text-\[\#1557b8\],
        html.bc-theme-dark .text-\[\#1d64c8\],
        html.bc-theme-dark .text-\[\#1d6ce0\],
        html.bc-theme-dark .text-\[\#2563eb\] {
            color: #58a6ff !important;
        }
        html.bc-theme-dark .text-\[\#1f883d\],
        html.bc-theme-dark .text-\[\#198744\],
        html.bc-theme-dark .text-\[\#1b813e\],
        html.bc-theme-dark .text-\[\#065f46\] {
            color: #4ade80 !important;
        }
        html.bc-theme-dark .text-\[\#9c3016\],
        html.bc-theme-dark .text-\[\#9c2f0f\],
        html.bc-theme-dark .text-\[\#a62c21\],
        html.bc-theme-dark .text-\[\#991b1b\],
        html.bc-theme-dark .text-\[\#b91c1c\],
        html.bc-theme-dark .text-\[\#9c4221\] {
            color: #e68566 !important;
        }
        html.bc-theme-dark .text-\[\#f0efed\],
        html.bc-theme-dark .text-\[\#eceae4\] {
            color: #2c3942 !important;
        }
        html.bc-theme-dark .bg-\[\#eff6ff\]\/70 {
            background-color: rgba(30, 58, 138, 0.28) !important;
            border-color: #3b82f6 !important;
        }
        html.bc-theme-dark .hover\:bg-\[\#dbeafe\]\/60:hover {
            background-color: rgba(30, 58, 138, 0.42) !important;
        }
        html.bc-theme-dark .border-\[\#93c5fd\] {
            border-color: #3b82f6 !important;
        }
        html.bc-theme-dark .bg-\[\#1d1c1b\] {
            background-color: #152026 !important;
            border-color: #4b5563 !important;
            color: #ffffff !important;
        }
        html.bc-theme-dark .bg-\[\#fef08a\]\/85,
        html.bc-theme-dark .bg-\[\#fef08a\]\/70 {
            background-color: rgba(161, 98, 7, 0.42) !important;
            color: #fef08a !important;
        }
        html.bc-theme-dark {
            color-scheme: dark;
        }
        html.bc-theme-dark input[type="radio"],
        html.bc-theme-dark input[type="checkbox"] {
            accent-color: #3b82f6 !important;
            color-scheme: dark;
        }
        html.bc-theme-dark .bc-radio-selected,
        html.bc-theme-dark .bc-custom-radio.is-selected,
        html.bc-theme-dark .rounded-full.bg-\[\#1d2d35\] {
            background-color: #3b82f6 !important;
            border-color: #60a5fa !important;
            box-shadow: 0 0 0 2px rgba(96, 165, 250, 0.25) !important;
        }
        html.bc-theme-dark .bc-radio-unselected,
        html.bc-theme-dark .bc-custom-radio:not(.is-selected) {
            background-color: #152026 !important;
            border-color: #8c969e !important;
        }
        html.bc-theme-dark .bc-radio-dot,
        html.bc-theme-dark .bc-custom-radio-dot,
        html.bc-theme-dark .bc-radio-selected > span,
        html.bc-theme-dark .rounded-full.bg-\[\#1d2d35\] > .rounded-full {
            background-color: #ffffff !important;
        }
        html.bc-theme-dark input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]),
        html.bc-theme-dark select,
        html.bc-theme-dark textarea {
            background-color: #182229 !important;
            border-color: #3b4954 !important;
            color: #e6eaed !important;
        }
        html.bc-theme-dark .bg-\[\#74d38a\] {
            background-color: #74d38a !important;
            border-color: #5cc073 !important;
            color: #052e16 !important;
        }
        html.bc-theme-dark .bg-\[\#74d38a\] span,
        html.bc-theme-dark .bg-\[\#74d38a\] svg {
            color: #052e16 !important;
        }
    </style>
    @php
        $navUserId = \Illuminate\Support\Facades\Auth::id();
        $navUserRole = session('user_role');
        if (!$navUserRole && $navUserId) {
            $navUserRole = \App\Models\OrganizationMember::where('user_id', $navUserId)->value('role') ?: 'MEMBER';
        }
        $navIsFullAccess = in_array($navUserRole, ['OWNER', 'ADMIN']);
        $navAccessibleProjectIds = [];
        if (!$navIsFullAccess && $navUserId) {
            $navAccessibleProjectIds = \App\Models\ProjectMember::where('user_id', $navUserId)
                ->pluck('project_id')
                ->map(fn($v) => (int) $v)
                ->all();
        }

        $liveNotifs = \Illuminate\Support\Facades\Cache::get('bc_live_notifications', []);
        if (!is_array($liveNotifs)) {
            $liveNotifs = [];
        }
        if (!$navIsFullAccess) {
            $liveNotifs = array_values(array_filter($liveNotifs, function ($n) use ($navAccessibleProjectIds) {
                if (preg_match('#/projects/(\d+)#', $n['url'] ?? '', $m)) {
                    return in_array((int) $m[1], $navAccessibleProjectIds, true);
                }
                return false;
            }));
        }
        $seenNotifIds = collect($liveNotifs)->pluck('id')->all();

        try {
            $recentDbComments = \App\Models\DiscussionComment::with(['author', 'discussion.project'])
                ->orderByDesc('id')
                ->limit(6)
                ->get();
            foreach ($recentDbComments as $rc) {
                $nid = 'nfy_dc_' . $rc->id;
                if (!in_array($nid, $seenNotifIds, true) && $rc->discussion && ($navIsFullAccess || in_array((int) $rc->discussion->project_id, $navAccessibleProjectIds, true))) {
                    $cAuthor = $rc->author->name ?? 'Shachish Sneh';
                    $cWords = preg_split('/\s+/', trim($cAuthor));
                    $cInits = count($cWords) >= 2 ? strtoupper(substr($cWords[0], 0, 1) . substr(end($cWords), 0, 1)) : strtoupper(substr($cAuthor, 0, 1));
                    $liveNotifs[] = [
                        'id' => $nid,
                        'initials' => $cInits,
                        'bg' => 'bg-[#4f46e5]',
                        'badge_type' => 'message',
                        'title' => 'Re: ' . $rc->discussion->title,
                        'snippet' => \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', trim($rc->content)), 100),
                        'meta' => ($rc->created_at ? $rc->created_at->timezone('Asia/Kolkata')->format('g:ia') : 'Just now') . ' · ' . $cAuthor . ' · ' . ($rc->discussion->project->name ?? 'Project'),
                        'url' => route('projects.discussions.show', [$rc->discussion->project_id, $rc->discussion->id]),
                        'unread' => true,
                    ];
                    $seenNotifIds[] = $nid;
                }
            }

            $recentDbDiscs = \App\Models\Discussion::with(['author', 'project'])
                ->where('id', '>=', 14982)
                ->when(!$navIsFullAccess, fn($q) => $q->whereIn('project_id', $navAccessibleProjectIds))
                ->orderByDesc('id')
                ->limit(6)
                ->get();
            foreach ($recentDbDiscs as $rd) {
                $nid = 'nfy_disc_' . $rd->id;
                if (!in_array($nid, $seenNotifIds, true)) {
                    $dAuthor = $rd->author->name ?? 'Shachish Sneh';
                    $dWords = preg_split('/\s+/', trim($dAuthor));
                    $dInits = count($dWords) >= 2 ? strtoupper(substr($dWords[0], 0, 1) . substr(end($dWords), 0, 1)) : strtoupper(substr($dAuthor, 0, 1));
                    $liveNotifs[] = [
                        'id' => $nid,
                        'initials' => $dInits,
                        'bg' => 'bg-[#15b79e]',
                        'badge_type' => 'message',
                        'title' => $rd->title,
                        'snippet' => \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', trim($rd->content)), 100),
                        'meta' => ($rd->created_at ? $rd->created_at->timezone('Asia/Kolkata')->format('g:ia') : 'Just now') . ' · ' . $dAuthor . ' · ' . ($rd->project->name ?? 'Trading Bot'),
                        'url' => route('projects.discussions.show', [$rd->project_id, $rd->id]),
                        'unread' => true,
                    ];
                    $seenNotifIds[] = $nid;
                }
            }
        } catch (\Throwable $e) {
        }
    @endphp
    <script>
        window.BC_LIVE_NOTIFS = @json($liveNotifs);
    </script>
</head>
<body x-data="{ 
    navDropdown: false, 
    pingsModal: false, 
    navSearchQuery: '',
    navSelectedIndex: 0,
    includeArchivedSearch: false,
    folders: [],
    addFolder() {
        this.folders.unshift({
            id: Date.now(),
            name: 'New folder',
            projectsCount: 0
        });
    },
    pingFilter: '',
    allRead: false,
    silencingNotifications: false,
    allPingsView: false,
    newPingInputOpen: false,
    newPingQuery: '',
    newPingDropdownOpen: false,
    newPingSelectedUser: null,
    newPingDirectory: [
        { id: 62, name: 'Shiv Narayan', role: 'Developer', initials: 'SN', bg: 'bg-[#9333ea]', avatar: null },
        { id: 202, name: 'Kashish', role: 'Team Member', initials: 'K', bg: 'bg-[#10b981]', avatar: null },
        { id: 153, name: 'Tanu Shree', role: 'HR Recruiter', initials: 'TS', bg: 'bg-[#eab308]', avatar: null },
        { id: 146, name: 'Anushka Garg', role: 'HR Recruiter', initials: 'AG', bg: 'bg-[#ea580c]', avatar: null },
        { id: 60, name: 'Shachish Sneh', role: 'Solution Architect & Technology Master', initials: 'SS', bg: 'bg-[#4f46e5]', avatar: '/uploads/avatars/25036340.png' },
        { id: 147, name: 'Nikhil Aneja', role: 'Project Manager', initials: 'NA', bg: 'bg-[#0284c7]', avatar: '/uploads/avatars/48443395.png' },
        { id: 148, name: 'Nirdesh Verma', role: 'Senior Developer', initials: 'NV', bg: 'bg-[#0d9488]', avatar: '/uploads/avatars/48060513.png' },
        { id: 149, name: 'Rudra Banger', role: 'Developer', initials: 'RB', bg: 'bg-[#92400e]', avatar: null },
        { id: 2, name: 'Pawandeep Kaur', role: 'Manager', initials: 'PK', bg: 'bg-[#00897b]', avatar: null },
        { id: 4, name: 'Gaurav Khulbey', role: 'Security & QA Lead', initials: 'GK', bg: 'bg-[#d946ef]', avatar: null }
    ],
    selectNewPingPerson(person) {
        if (!person) return;
        this.newPingSelectedUser = person;
        this.newPingQuery = person.name;
        this.newPingDropdownOpen = false;
        this.$nextTick(() => {
            if (this.$refs.newPingSearchInput) this.$refs.newPingSearchInput.focus();
        });
    },
    submitNewPingDrawer() {
        const q = (this.newPingQuery || '').trim().toLowerCase();
        let target = this.newPingSelectedUser;
        if (!target && q) {
            target = this.newPingDirectory.find(p => p.name.toLowerCase().includes(q));
        }
        if (!target && !q) {
            if (this.$refs.newPingSearchInput) this.$refs.newPingSearchInput.focus();
            return;
        }
        if (target) {
            if (!window.DRAWER_PINGS_DATA[target.id]) {
                window.DRAWER_PINGS_DATA[target.id] = {
                    id: target.id,
                    name: target.name,
                    initials: target.initials,
                    bg: target.bg,
                    avatar: target.avatar,
                    messages: []
                };
            }
            this.newPingInputOpen = false;
            this.newPingQuery = '';
            this.newPingSelectedUser = null;
            this.newPingDropdownOpen = false;
            this.openDrawerPing(target.id);
        } else {
            const customId = 'custom_' + Date.now();
            const rawName = (this.newPingQuery || '').trim();
            const parts = rawName.split(/\s+/);
            const initials = parts.length > 1 ? (parts[0][0] + parts[parts.length - 1][0]).toUpperCase() : rawName.slice(0, 2).toUpperCase();
            window.DRAWER_PINGS_DATA[customId] = {
                id: customId,
                name: rawName,
                initials: initials || 'U',
                bg: 'bg-[#1b6ac9]',
                avatar: null,
                messages: []
            };
            this.newPingInputOpen = false;
            this.newPingQuery = '';
            this.newPingSelectedUser = null;
            this.newPingDropdownOpen = false;
            this.openDrawerPing(customId);
        }
    },
    activeDrawerPing: null,
    drawerMessageInput: '',
    bubbleMenuKey: null,
    bubbledItems: [
        {
            id: 'bub_default_1',
            initials: 'S',
            bg: 'bg-[#2ebd85]',
            badge_type: null,
            title: 'Re: Shubham Work log - October - 2021',
            snippet: 'Work Log 25 Nov 2021 : • Working in mayadin superadmin Panel • Superadmin can edit...',
            meta: 'Nov 25, 2021 · Shubham · Audit Team',
            url: '{{ url('/projects/48/discussions/6013') }}',
            done: false
        }
    ],
    newForYouList: [
        ...(window.BC_LIVE_NOTIFS || []),
        { id: 'nfy_1', initials: 'PK', bg: 'bg-[#00897b]', badge_type: 'file', title: 'Copy of Commercial bid.pdf', snippet: null, meta: '12:00pm · Pawandeep Kaur · Tender', url: '{{ route('projects.docs', ['id' => 29, 'folder' => '8159428', 'file' => 'Copy of Commercial bid.pdf']) }}', unread: true },
        { id: 'nfy_2', initials: 'PK', bg: 'bg-[#00897b]', badge_type: 'file', title: 'Copy of UPDC Technical bid.pdf', snippet: null, meta: '12:00pm · Pawandeep Kaur · Tender', url: '{{ route('projects.docs', ['id' => 29, 'folder' => '8159428', 'file' => 'Copy of UPDC Technical bid.pdf']) }}', unread: true },
        { id: 'nfy_3', initials: 'PK', bg: 'bg-[#00897b]', badge_type: 'file', title: 'GeM-Bidding-10033548.pdf', snippet: null, meta: '12:00pm · Pawandeep Kaur · Tender', url: '{{ route('projects.docs', ['id' => 29, 'folder' => '8159428', 'file' => 'GeM-Bidding-10033548.pdf']) }}', unread: true },
        { id: 'nfy_4', initials: 'PK', bg: 'bg-[#00897b]', badge_type: 'file', title: 'UPDC Technical bid.docx.pdf', snippet: null, meta: '11:17am · Pawandeep Kaur · Tender', url: '{{ route('projects.docs', ['id' => 29, 'folder' => '8176205', 'file' => 'UPDC Technical bid.docx.pdf']) }}', unread: true },
        { id: 'nfy_5', initials: 'PK', bg: 'bg-[#00897b]', badge_type: 'file', title: 'Commercial bid.docx (1).pdf', snippet: null, meta: '11:17am · Pawandeep Kaur · Tender', url: '{{ route('projects.docs', ['id' => 29, 'folder' => '8176205', 'file' => 'Commercial bid.docx (1).pdf']) }}', unread: true },
        { id: 'nfy_6', initials: 'PK', bg: 'bg-[#00897b]', badge_type: 'file', title: 'GeM-Bidding-10033548.pdf', snippet: null, meta: '11:17am · Pawandeep Kaur · Tender', url: '{{ route('projects.docs', ['id' => 29, 'folder' => '8176205', 'file' => 'GeM-Bidding-10033548.pdf']) }}', unread: true },
        { id: 'nfy_7', initials: 'GK', bg: 'bg-[#d946ef]', badge_type: 'message', title: 'Worklog 26/10/2026', snippet: 'Today i submit the RAMS project to the developer and continue working now on...', meta: 'Oct 26 · Gaurav Khulbey · Ajath Infotech Pvt Ltd HQ', url: '{{ url('/projects/4/discussions/13829') }}', unread: true },
        { id: 'nfy_8', initials: 'PK', bg: 'bg-[#00897b]', badge_type: 'file', title: 'JayaPaswan_Full_Stack.pdf', snippet: null, meta: 'Oct 21 · Pawandeep Kaur · Tender', url: '{{ route('projects.docs', ['id' => 29, 'folder' => '8159428', 'file' => 'JayaPaswan_Full_Stack.pdf']) }}', unread: true },
        { id: 'nfy_9', initials: 'PK', bg: 'bg-[#00897b]', badge_type: 'file', title: 'GeM-Bidding-9902486.pdf', snippet: null, meta: 'Oct 21 · Pawandeep Kaur · Tender', url: '{{ route('projects.docs', ['id' => 29, 'folder' => '8159428', 'file' => 'GeM-Bidding-9902486.pdf']) }}', unread: true },
        { id: 'nfy_10', initials: 'PK', bg: 'bg-[#00897b]', badge_type: 'file', title: 'DGMSTechnical.pdf', snippet: null, meta: 'Oct 21 · Pawandeep Kaur · Tender', url: '{{ route('projects.docs', ['id' => 29, 'folder' => '8159428', 'file' => 'DGMSTechnical.pdf']) }}', unread: true },
        { id: 'nfy_11', initials: 'PK', bg: 'bg-[#00897b]', badge_type: 'file', title: 'AHDCCommercial.pdf', snippet: null, meta: 'Oct 21 · Pawandeep Kaur · Tender', url: '{{ route('projects.docs', ['id' => 29, 'folder' => '8159428', 'file' => 'AHDCCommercial.pdf']) }}', unread: true },
        { id: 'nfy_12', initials: 'PK', bg: 'bg-[#00897b]', badge_type: 'file', title: '1791787160 (1).pdf', snippet: null, meta: 'Oct 21 · Pawandeep Kaur · Tender', url: '{{ route('projects.docs', ['id' => 29, 'folder' => '8159428', 'file' => '1791787160 (1).pdf']) }}', unread: true },
        { id: 'nfy_13', initials: 'PK', bg: 'bg-[#00897b]', badge_type: 'file', title: 'Dhanbad technical bid.pdf', snippet: null, meta: 'Oct 19 · Pawandeep Kaur · Tender', url: '{{ route('projects.docs', ['id' => 29, 'folder' => '8159428', 'file' => 'Dhanbad technical bid.pdf']) }}', unread: true },
        { id: 'nfy_14', initials: 'PK', bg: 'bg-[#00897b]', badge_type: 'file', title: '1791356501.pdf', snippet: null, meta: 'Oct 19 · Pawandeep Kaur · Tender', url: '{{ route('projects.docs', ['id' => 29, 'folder' => '8159428', 'file' => '1791356501.pdf']) }}', unread: true },
        { id: 'nfy_15', initials: 'PK', bg: 'bg-[#00897b]', badge_type: 'file', title: 'GeM-Bidding-9996916.pdf', snippet: null, meta: 'Oct 19 · Pawandeep Kaur · Tender', url: '{{ route('projects.docs', ['id' => 29, 'folder' => '8159428', 'file' => 'GeM-Bidding-9996916.pdf']) }}', unread: true },
        { id: 'nfy_16', initials: 'PK', bg: 'bg-[#00897b]', badge_type: 'file', title: 'ShudhakarJava4.pdf', snippet: null, meta: 'Oct 19 · Pawandeep Kaur · Tender', url: '{{ route('projects.docs', ['id' => 29, 'folder' => '8159428', 'file' => 'ShudhakarJava4.pdf']) }}', unread: true },
        { id: 'nfy_17', initials: 'PK', bg: 'bg-[#00897b]', badge_type: 'file', title: 'GeM-Bidding-9834446.pdf', snippet: null, meta: 'Oct 19 · Pawandeep Kaur · Tender', url: '{{ route('projects.docs', ['id' => 29, 'folder' => '8159428', 'file' => 'GeM-Bidding-9834446.pdf']) }}', unread: true },
        { id: 'nfy_18', initials: 'PK', bg: 'bg-[#00897b]', badge_type: 'file', title: '1788323133.pdf', snippet: null, meta: 'Oct 19 · Pawandeep Kaur · Tender', url: '{{ route('projects.docs', ['id' => 29, 'folder' => '8159428', 'file' => '1788323133.pdf']) }}', unread: true },
        { id: 'nfy_19', initials: 'PK', bg: 'bg-[#00897b]', badge_type: 'file', title: 'Commercial bid.pdf', snippet: null, meta: 'Oct 19 · Pawandeep Kaur · Tender', url: '{{ route('projects.docs', ['id' => 29, 'folder' => '8159428', 'file' => 'Commercial bid.pdf']) }}', unread: true }
    ],
    bubbleUpFromNew(idx) {
        const item = this.newForYouList[idx];
        if (!item) return;
        this.bubbledItems.unshift({
            id: 'bub_' + Date.now() + '_' + idx,
            initials: item.initials,
            bg: item.bg,
            badge_type: item.badge_type,
            title: item.title,
            snippet: item.snippet || null,
            meta: item.meta,
            url: item.url,
            done: false
        });
        this.newForYouList.splice(idx, 1);
        this.bubbleMenuKey = null;
    },
    bubbleUpCustom(obj) {
        if (!obj) return;
        this.bubbledItems.unshift({
            id: 'bub_' + Date.now(),
            initials: obj.initials || 'PK',
            bg: obj.bg || 'bg-[#00897b]',
            badge_type: obj.badge_type || null,
            title: obj.title || 'Notification',
            snippet: obj.snippet || null,
            meta: obj.meta || '',
            url: obj.url || '#',
            done: false
        });
        this.bubbleMenuKey = null;
    },
    openDrawerPing(userId) {
        const data = window.DRAWER_PINGS_DATA && window.DRAWER_PINGS_DATA[userId];
        if (data) {
            this.activeDrawerPing = data;
            this.pingsModal = true;
            this.$nextTick(() => {
                const el = document.getElementById('drawer-ping-messages');
                if (el) el.scrollTop = el.scrollHeight;
                const inp = document.getElementById('drawer-ping-input');
                if (inp) inp.focus();
            });
        } else {
            window.location.href = '/pings/' + userId;
        }
    },
    sendDrawerPing() {
        const text = (this.drawerMessageInput || '').trim();
        if (!text || !this.activeDrawerPing) return;
        const now = new Date();
        let hours = now.getHours();
        const mins = String(now.getMinutes()).padStart(2, '0');
        const ampm = hours >= 12 ? 'pm' : 'am';
        hours = hours % 12 || 12;
        const timeStr = hours + ':' + mins + ampm;
        this.activeDrawerPing.messages.push({
            sender: 'me',
            time: timeStr,
            fullTime: 'Today at ' + timeStr + ' IST',
            text: text
        });
        const targetId = this.activeDrawerPing.id;
        this.drawerMessageInput = '';
        this.$nextTick(() => {
            const el = document.getElementById('drawer-ping-messages');
            if (el) el.scrollTop = el.scrollHeight;
        });
        const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
        fetch('/pings/' + targetId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': token || ''
            },
            body: new URLSearchParams({ content: text, _token: token || '' })
        }).catch(() => {});
    },
    profileModal: false,
    editProfileModal: false,
    removeAvatarFlag: false,
    pingDocModal: false,
    activePingDoc: null,
    openPingDoc(doc) {
        if (!doc) return;
        let cleanUrl = doc.url || '';
        if (cleanUrl && !cleanUrl.startsWith('blob:') && !cleanUrl.includes('v=3')) {
            cleanUrl += (cleanUrl.includes('?') ? '&' : '?') + 'v=3';
        }
        this.activePingDoc = Object.assign({}, doc, { url: cleanUrl });
        this.pingDocModal = true;
    },
    attachDrawerPingFile(e) {
        const file = e && e.target && e.target.files ? e.target.files[0] : null;
        if (!file || !this.activeDrawerPing) return;
        const sizeKB = Math.max(1, Math.round(file.size / 1024));
        const sizeLabel = sizeKB > 1024 ? (file.size / (1024 * 1024)).toFixed(2) + ' MB' : sizeKB + ' KB';
        const blobUrl = URL.createObjectURL(file);
        const now = new Date();
        let hours = now.getHours();
        const mins = String(now.getMinutes()).padStart(2, '0');
        const ampm = hours >= 12 ? 'pm' : 'am';
        hours = hours % 12 || 12;
        const timeStr = hours + ':' + mins + ampm;
        const label = file.name + ' (' + sizeLabel + ')';
        this.activeDrawerPing.messages.push({
            sender: 'me',
            time: timeStr,
            fullTime: 'Today at ' + timeStr + ' IST',
            text: label,
            file: {
                name: file.name,
                size: sizeLabel,
                type: file.type || 'Document',
                url: blobUrl,
                thumb: '/uploads/docs/thumb_1791962446.png'
            }
        });
        e.target.value = '';
        this.$nextTick(() => {
            const el = document.getElementById('drawer-ping-messages');
            if (el) el.scrollTop = el.scrollHeight;
        });
    },
    profileUser: {
        id: 1,
        isSelf: false,
        name: 'Pawandeep Kaur',
        firstName: 'Pawandeep',
        initials: 'PK',
        bg: 'bg-[#00897b]',
        roleBadge: 'Admin',
        jobTitle: 'Manager at Ajath Infotech Pvt Ltd',
        avatar: null,
        docsUrl: '/projects/29/docs'
    },
    openMyProfileModal() {
        this.profileUser = {
            id: {{ Auth::id() ?? 1 }},
            isSelf: true,
            name: '{{ addslashes(Auth::user()->name ?? "Manjot Singh") }}',
            firstName: '{{ addslashes(explode(" ", trim(Auth::user()->name ?? "Manjot"))[0]) }}',
            initials: 'M',
            bg: 'bg-[#1d4ed8]',
            roleBadge: 'Owner',
            jobTitle: '{{ addslashes(Auth::user()->job_title ?: "Global VP & Partner at Ajath Infotech Pvt Ltd") }}',
            avatar: '{{ (Auth::check() && Auth::user()->avatar_url && !str_contains(Auth::user()->avatar_url, "dicebear")) ? Auth::user()->avatar_url : "/uploads/avatars/22369668.png" }}',
            docsUrl: '/projects/4/docs'
        };
        this.profileModal = true;
    },
    openUserProfile(u) {
        this.profileUser = Object.assign({
            id: 45,
            isSelf: false,
            name: 'Pawandeep Kaur',
            firstName: 'Pawandeep',
            initials: 'PK',
            bg: 'bg-[#00897b]',
            roleBadge: 'Admin',
            jobTitle: 'Manager at Ajath Infotech Pvt Ltd',
            avatar: null,
            docsUrl: '/projects/29/docs'
        }, u);
        this.profileModal = true;
    },
    toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            if (document.exitFullscreen) { document.exitFullscreen(); }
        }
    }
}" 
@open-user-profile.window="openUserProfile($event.detail)"
@open-ping-doc.window="openPingDoc($event.detail)"
@keydown.window="if (!['INPUT', 'TEXTAREA'].includes($event.target.tagName) && !$event.target.isContentEditable && (($event.shiftKey && ($event.key === 'J' || $event.key === 'j' || $event.code === 'KeyJ')) || $event.key === '/')) { $event.preventDefault(); navDropdown = true; navSelectedIndex = 0; $nextTick(() => $refs.navSearchInput && $refs.navSearchInput.focus()); } if ($event.key === 'Escape') { navDropdown = false; pingsModal = false; profileModal = false; pingDocModal = false; }"
class="min-h-full flex flex-col font-sans antialiased selection:bg-amber-400 selection:text-slate-900">

    <!-- Top Basecamp Header -->
    <header class="bg-[#fbfaf8] sticky top-0 z-40 transition-all duration-150" :class="pingsModal ? 'sm:mr-[360px]' : ''">
        <div class="w-full px-3 sm:px-5">
            <div class="flex items-center justify-between h-14">
                
                <!-- Left: Home link / Breadcrumb -->
                <div class="w-44 sm:w-56 flex items-center gap-2">
                    @if(!request()->routeIs('home') && !request()->routeIs('dashboard'))
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[13px] font-medium text-[#1d2d35] bg-[#f2f0ec] hover:bg-[#e6e3dd] rounded-md transition-colors" title="Go to Home">
                        <i data-lucide="home" class="w-3.5 h-3.5"></i>
                        <span>Home</span>
                    </a>
                    @endif
                </div>

                <!-- Center: Basecamp Logo & Switcher Dropdown (Matches Basecamp Screenshot) -->
                <div class="relative flex items-center justify-center flex-1">
                    <button @click="navDropdown = !navDropdown; navSelectedIndex = -1; if(navDropdown) $nextTick(() => $refs.navSearchInput && $refs.navSearchInput.focus())" 
                            class="flex items-center gap-2 px-3 py-1 rounded-full border transition-all group cursor-pointer focus:outline-none"
                            :class="navDropdown ? 'border-[#1d70b8] bg-white text-[#1d2d35] shadow-2xs' : 'border-transparent hover:bg-slate-200/50 text-[#1d2d35]'">
                        <!-- Basecamp Circular Mountain Icon -->
                        <span class="w-6 h-6 rounded-full bg-[#3cb371] flex items-center justify-center shadow-2xs shrink-0 overflow-hidden">
                            <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none">
                                <path d="M3 19L10.5 7.5L14.5 13.5L17.5 9.5L21 19H3Z" fill="#1e7e48"/>
                                <path d="M10.5 7.5L7.5 12L10 13L12 11L13.5 12L10.5 7.5Z" fill="#ffffff"/>
                            </svg>
                        </span>
                        <span class="font-bold text-[19px] tracking-[-0.02em] text-[#1d2d35]">Basecamp</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-[#1d2d35] stroke-[2.2] transition-transform" :class="navDropdown ? 'rotate-180 text-blue-600' : ''"></i>
                    </button>

                    <!-- Basecamp Jump & Navigation Dropdown Menu (Matches Basecamp Screenshot media_1793274344519_1e749251.jpg) -->
                    <div x-show="navDropdown" 
                         x-cloak 
                         @click.outside="navDropdown = false" 
                         class="absolute top-0 left-1/2 -translate-x-1/2 bg-white rounded-[14px] shadow-[0_8px_32px_rgba(0,0,0,0.16)] border border-[#e5e2dc] px-3.5 py-3.5 w-[492px] max-w-[95vw] z-50 font-sans text-left">
                        
                        <!-- 1. Top 4 Navigation Tiles (Activity, Calendar, Reports, Everything) -->
                        <div class="grid grid-cols-4 gap-2 mb-3">
                            <!-- Activity -->
                            <a href="{{ route('my.activity', ['scope' => 'all']) }}" class="h-[76px] bg-[#f5f5f5] hover:bg-[#ebebeb] rounded-[10px] px-2 flex flex-col items-center justify-center gap-1.5 transition-colors group cursor-pointer text-center">
                                <svg class="w-[24px] h-[24px] text-[#1d2d35]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 17 9 11 13 15 21 7"/>
                                </svg>
                                <span class="text-[14.5px] font-normal text-[#1d2d35] leading-none">Activity</span>
                            </a>

                            <!-- Calendar -->
                            <a href="{{ route('my.events') }}" class="h-[76px] bg-[#f5f5f5] hover:bg-[#ebebeb] rounded-[10px] px-2 flex flex-col items-center justify-center gap-1.5 transition-colors group cursor-pointer text-center">
                                <svg class="w-[24px] h-[24px] text-[#1d2d35]" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M7 2a1 1 0 011 1v1h8V3a1 1 0 112 0v1h1.5A2.5 2.5 0 0122 6.5v13a2.5 2.5 0 01-2.5 2.5h-15A2.5 2.5 0 012 19.5v-13A2.5 2.5 0 014.5 4H6V3a1 1 0 011-1zm-3 7v10.5a.5.5 0 00.5.5h15a.5.5 0 00.5-.5V9H4zm3.5 3h2a.5.5 0 01.5.5v2a.5.5 0 01-.5.5h-2a.5.5 0 01-.5-.5v-2a.5.5 0 01.5-.5zm5 0h2a.5.5 0 01.5.5v2a.5.5 0 01-.5.5h-2a.5.5 0 01-.5-.5v-2a.5.5 0 01.5-.5z"/>
                                </svg>
                                <span class="text-[14.5px] font-normal text-[#1d2d35] leading-none">Calendar</span>
                            </a>

                            <!-- Reports -->
                            <a href="{{ route('my.tasks') }}" class="h-[76px] bg-[#f5f5f5] hover:bg-[#ebebeb] rounded-[10px] px-2 flex flex-col items-center justify-center gap-1.5 transition-colors group cursor-pointer text-center">
                                <svg class="w-[24px] h-[24px] text-[#1d2d35]" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M11 2.05V13h10.95A10.001 10.001 0 0112 22C6.477 22 2 17.523 2 12c0-5.185 3.947-9.449 9-9.95zm2 0A10.003 10.003 0 0121.95 11H13V2.05z"/>
                                </svg>
                                <span class="text-[14.5px] font-normal text-[#1d2d35] leading-none">Reports</span>
                            </a>

                            <!-- Everything -->
                            <a href="{{ route('projects.index') }}" class="h-[76px] bg-[#f5f5f5] hover:bg-[#ebebeb] rounded-[10px] px-2 flex flex-col items-center justify-center gap-1.5 transition-colors group cursor-pointer text-center">
                                <svg class="w-[24px] h-[24px] text-[#1d2d35]" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                </svg>
                                <span class="text-[14.5px] font-normal text-[#1d2d35] leading-none">Everything</span>
                            </a>
                        </div>

                        <!-- 2. Search & Jump Input -->
                        <div class="mb-3.5 relative">
                            <input x-ref="navSearchInput" 
                                   x-model="navSearchQuery"
                                   @input="navSelectedIndex = 0"
                                   @keydown.arrow-down.prevent="navSelectedIndex = Math.min(navSelectedIndex + 1, 25)"
                                   @keydown.arrow-up.prevent="navSelectedIndex = Math.max(navSelectedIndex - 1, 0)"
                                   @keydown.enter.prevent="if ($event.shiftKey && navSearchQuery.trim() !== '') { window.location.href = '{{ route('projects.index') }}?q=' + encodeURIComponent(navSearchQuery.trim()); return; } const activeEl = $el.closest('[x-show]').querySelector('[data-nav-active=true]'); if (activeEl && activeEl.href) window.location.href = activeEl.href;"
                                   type="text" 
                                   placeholder="Search or jump to a project, person, or recent page" 
                                   :class="navSearchQuery.trim() !== '' ? 'pr-[132px]' : 'pr-3'"
                                   class="w-full h-[43px] pl-3 bg-white border-[1.5px] border-[#1b6ac9] rounded-[7px] text-[15.5px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none">

                            <!-- "Search for this" inline button inside input when typing (Matches media_1793293525276_f6e7b7f7.jpg) -->
                            <a x-show="navSearchQuery.trim() !== ''"
                               x-cloak
                               :href="'{{ route('projects.index') }}?q=' + encodeURIComponent(navSearchQuery.trim())"
                               class="absolute right-1.5 top-1/2 -translate-y-1/2 px-2.5 py-1 rounded-[5px] bg-[#f2f0ec] hover:bg-[#e5e2dc] text-[#1d2d35] text-[12.5px] font-semibold inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <circle cx="11" cy="11" r="7"/>
                                    <path stroke-linecap="round" d="M20 20l-3.5-3.5"/>
                                </svg>
                                <span>Search for this</span>
                            </a>
                        </div>

                        <!-- 3. Section: Recently visited -->
                        <div class="mb-3.5 pb-3.5 border-b border-[#ece9e2]" x-show="navSearchQuery === '' || 'campfire files posted to this chat ajath infotech hq birthday list to-dos ride my cars (edward) - for ndc homes'.includes(navSearchQuery.toLowerCase())">
                            <h3 class="text-[14.5px] font-bold text-[#1d2d35] mb-2">Recently visited</h3>
                            <div class="space-y-1">
                                <!-- Search-Matched Recently Visited Project: Ride My Cars (Edward) - for NDC Homes -->
                                <a href="{{ route('projects.show', 26) }}"
                                   x-show="navSearchQuery.trim() !== '' && 'ride my cars (edward) - for ndc homes'.includes(navSearchQuery.toLowerCase())"
                                   @mouseenter="navSelectedIndex = 0"
                                   :data-nav-active="navSelectedIndex === 0 && 'ride my cars (edward) - for ndc homes'.includes(navSearchQuery.toLowerCase())"
                                   :class="navSelectedIndex === 0 ? 'bg-[#1b6ac9] text-white' : 'text-[#1d2d35] hover:bg-[#1b6ac9] hover:text-white'"
                                   class="flex items-center gap-2.5 px-2 py-1 -mx-1.5 rounded-full text-[15px] transition-colors group">
                                    <span class="w-[24px] h-[24px] rounded-full bg-[#6d6a65]/30 text-[#1d2d35] inline-flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 3C6.5 3 2 7.5 2 13c0 2.1.7 4 1.8 5.6L9 11l3.5 4.5 2.5-3.5 5.2 6.6c1.1-1.6 1.8-3.5 1.8-5.6 0-5.5-4.5-10-10-10z"/></svg>
                                    </span>
                                    <div class="truncate">
                                        <span class="font-semibold">Ride My Cars (Edward)</span>
                                        <span :class="navSelectedIndex === 0 ? 'text-white/90' : 'text-[#6d6a65] group-hover:text-white/90'" class="text-[13.5px]"> - for NDC Homes</span>
                                    </div>
                                </a>

                                <!-- Item 1: Campfire - Ajath Infotech Pvt Ltd HQ -->
                                <a href="{{ route('projects.chat', 4) }}" 
                                   x-show="navSearchQuery === '' || 'campfire - ajath infotech pvt ltd hq'.includes(navSearchQuery.toLowerCase())"
                                   @mouseenter="navSelectedIndex = 0"
                                   :data-nav-active="navSelectedIndex === 0 && (navSearchQuery === '' || 'campfire - ajath infotech pvt ltd hq'.includes(navSearchQuery.toLowerCase()))"
                                   :class="navSelectedIndex === 0 ? 'bg-[#1b6ac9] text-white' : 'text-[#1d2d35] hover:bg-[#1b6ac9] hover:text-white'"
                                   class="flex items-center gap-2.5 px-1.5 py-1 -mx-1.5 rounded-full text-[15.5px] transition-colors group">
                                    <span class="w-[25px] h-[25px] rounded-full bg-[#49a3df] text-white inline-flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                                    </span>
                                    <div class="truncate">
                                        <span>Campfire</span>
                                        <span :class="navSelectedIndex === 0 ? 'text-white/90' : 'text-[#6d6a65] group-hover:text-white/90'" class="text-[13.5px]"> - Ajath Infotech Pvt Ltd HQ</span>
                                    </div>
                                </a>

                                <!-- Item 2: Files posted to this chat -->
                                <a href="{{ route('projects.chat', 4) }}" 
                                   x-show="navSearchQuery === '' || 'files posted to this chat'.includes(navSearchQuery.toLowerCase())"
                                   @mouseenter="navSelectedIndex = 1"
                                   :data-nav-active="navSelectedIndex === 1"
                                   :class="navSelectedIndex === 1 ? 'bg-[#1b6ac9] text-white' : 'text-[#1d2d35] hover:bg-[#1b6ac9] hover:text-white'"
                                   class="flex items-center gap-2.5 px-1.5 py-1 -mx-1.5 rounded-full text-[15.5px] transition-colors group">
                                    <span class="w-[25px] h-[25px] rounded-full bg-[#f5a623] text-white inline-flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    </span>
                                    <span class="truncate">Files posted to this chat</span>
                                </a>

                                <!-- Item 3: Ajath Infotech Pvt Ltd HQ -->
                                <a href="{{ route('projects.show', 4) }}" 
                                   x-show="navSearchQuery === '' || 'ajath infotech pvt ltd hq'.includes(navSearchQuery.toLowerCase())"
                                   @mouseenter="navSelectedIndex = 2"
                                   :data-nav-active="navSelectedIndex === 2"
                                   :class="navSelectedIndex === 2 ? 'bg-[#1b6ac9] text-white' : 'text-[#1d2d35] hover:bg-[#1b6ac9] hover:text-white'"
                                   class="flex items-center gap-2.5 px-1.5 py-1 -mx-1.5 rounded-full text-[15.5px] transition-colors group">
                                    <span class="w-[25px] h-[25px] rounded-full bg-[#f5a623] text-white inline-flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    </span>
                                    <span class="truncate">Ajath Infotech Pvt Ltd HQ</span>
                                </a>

                                <!-- Item 4: Birthday List - Ajath Infotech Pvt Ltd HQ -->
                                <a href="{{ route('projects.tasks', ['id' => 4, 'list' => 'Birthday List']) }}" 
                                   x-show="navSearchQuery === '' || 'birthday list - ajath infotech pvt ltd hq'.includes(navSearchQuery.toLowerCase())"
                                   @mouseenter="navSelectedIndex = 3"
                                   :data-nav-active="navSelectedIndex === 3"
                                   :class="navSelectedIndex === 3 ? 'bg-[#1b6ac9] text-white' : 'text-[#1d2d35] hover:bg-[#1b6ac9] hover:text-white'"
                                   class="flex items-center gap-2.5 px-1.5 py-1 -mx-1.5 rounded-full text-[15.5px] transition-colors group">
                                    <span class="w-[25px] h-[25px] rounded-full bg-[#2ea44f] text-white inline-flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                    <div class="truncate">
                                        <span>Birthday List</span>
                                        <span :class="navSelectedIndex === 3 ? 'text-white/90' : 'text-[#6d6a65] group-hover:text-white/90'" class="text-[13.5px]"> - Ajath Infotech Pvt Ltd HQ</span>
                                    </div>
                                </a>

                                <!-- Item 5: To-dos - Ajath Infotech Pvt Ltd HQ -->
                                <a href="{{ route('projects.tasks', 4) }}" 
                                   x-show="navSearchQuery === '' || 'to-dos - ajath infotech pvt ltd hq'.includes(navSearchQuery.toLowerCase())"
                                   @mouseenter="navSelectedIndex = 4"
                                   :data-nav-active="navSelectedIndex === 4"
                                   :class="navSelectedIndex === 4 ? 'bg-[#1b6ac9] text-white' : 'text-[#1d2d35] hover:bg-[#1b6ac9] hover:text-white'"
                                   class="flex items-center gap-2.5 px-1.5 py-1 -mx-1.5 rounded-full text-[15.5px] transition-colors group">
                                    <span class="w-[25px] h-[25px] rounded-full bg-[#2ea44f] text-white inline-flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                    <div class="truncate">
                                        <span>To-dos</span>
                                        <span :class="navSelectedIndex === 4 ? 'text-white/90' : 'text-[#6d6a65] group-hover:text-white/90'" class="text-[13.5px]"> - Ajath Infotech Pvt Ltd HQ</span>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- 4. Section: Projects — See all -->
                        @php
                            $pinnedOrder = [
                                'Ajath Infotech Pvt Ltd HQ' => 1,
                                'Ajath Management - 2026' => 2,
                                'Budfit' => 3,
                                'Dubbly - Video Enhancement' => 4,
                                'Readiverse - AI Development' => 5,
                                'Taskmaster App' => 6,
                                'Stalker AI x Orahi' => 7,
                                'Urban Racks x BrancoSoft' => 8,
                                'Urban Racks x Brancosoft' => 8,
                                'HARSAC Website Development' => 9,
                                'HRMS - Sena Bhawan' => 10,
                            ];
                            $accessOrder = [
                                'Ajath Infotech Pvt Ltd HQ' => 1,
                                'Ajath Management - 2026' => 2,
                                'Budfit' => 3,
                                'Dubbly - Video Enhancement' => 4,
                                'Readiverse - AI Development' => 5,
                                'Taskmaster App' => 6,
                                'Stalker AI x Orahi' => 7,
                                'Urban Racks x BrancoSoft' => 8,
                                'Urban Racks x Brancosoft' => 8,
                                'HARSAC Website Development' => 9,
                                'HRMS - Sena Bhawan' => 10,
                                'Tender' => 11,
                                'Ride My Cars (Edward)' => 12,
                                'Name the project' => 13,
                                'ClubGo' => 14,
                                'Exibine Mobile App' => 15,
                                'Adhyatmah Bharat Version 2.0' => 16,
                                'BIPL APP' => 17,
                                'Adhyatmah Bharat - e Commerce App and Website' => 18,
                                'Gradmo (Ed.Tech) Mobile Apps' => 19,
                                'Three Parcel App' => 20,
                            ];
                            $dropdownProjects = \App\Models\Project::whereNotIn('id', [6, 12, 19, 30, 32])
                                ->when(!$navIsFullAccess, function ($q) use ($navAccessibleProjectIds) {
                                    $q->whereIn('id', $navAccessibleProjectIds ?: [0]);
                                })
                                ->get()
                                ->sortBy(function ($p) use ($accessOrder) {
                                    return sprintf('%02d_%s', $accessOrder[$p->name] ?? 99, strtolower($p->name));
                                });
                            $projectSubtitles = [
                                'Ajath Infotech Pvt Ltd HQ' => 'Company-wide announcemen...',
                                'Budfit' => 'for Healthline - Budget fit',
                                'Dubbly - Video Enhancement' => 'for Healthline',
                                'Readiverse - AI Development' => 'for Healthline',
                                'Taskmaster App' => 'for Healthline',
                                'Stalker AI x Orahi' => 'for Stalkre',
                                'Urban Racks x BrancoSoft' => 'for Urban Racks',
                                'Urban Racks x Brancosoft' => 'for Urban Racks',
                                'HARSAC Website Development' => 'for Client',
                                'HRMS - Sena Bhawan' => 'for Client',
                                'Ride My Cars (Edward)' => 'for NDC Homes',
                                'ClubGo' => 'for ClubGo',
                                'Exibine Mobile App' => 'for Universal Business Identity App',
                                'BIPL APP' => 'for BIPL',
                            ];
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-[14.5px] font-bold text-[#1d2d35] mb-2">
                                <span>
                                    Projects
                                    <span x-show="navSearchQuery.trim() === ''" class="font-normal text-[#6d6a65]">– <a href="{{ route('projects.index') }}" class="text-[#6d6a65] hover:text-[#1d2d35] font-normal underline">See all</a></span>
                                </span>
                            </div>
                            <div class="max-h-[268px] overflow-y-auto space-y-1 pr-1">
                                <!-- Dynamic Folders (e.g. "New folder" / "No projects") matching media_1793274344519_1e749251.jpg -->
                                <template x-for="folder in folders" :key="folder.id">
                                    <div x-show="navSearchQuery.trim() === ''" class="pb-1">
                                        <div class="flex items-center gap-2.5 py-0.5 text-[#1d2d35] text-[15.5px]">
                                            <span class="w-[25px] h-[22px] inline-flex items-center justify-center flex-shrink-0 text-[#6d6a65]">
                                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/></svg>
                                            </span>
                                            <span class="truncate" x-text="folder.name"></span>
                                        </div>
                                        <div class="pl-[35px] text-[13.5px] italic text-[#8c8881] leading-tight">
                                            No projects
                                        </div>
                                    </div>
                                </template>

                                @foreach($dropdownProjects as $idx => $p)
                                @php
                                    $sub = $projectSubtitles[$p->name] ?? ($p->client_subtitle ? 'for ' . $p->client_subtitle : $p->description);
                                    $isStarredProj = isset($pinnedOrder[$p->name]);
                                    $isArchivedProj = ($p->status === 'ARCHIVED');
                                    $navIdx = 5 + $loop->index;
                                @endphp
                                <a href="{{ route('projects.show', $p->id) }}" 
                                   x-show="({{ $isArchivedProj ? 'includeArchivedSearch' : 'true' }}) && ((navSearchQuery.trim() === '' && {{ $loop->index < 20 && !$isArchivedProj ? 'true' : 'false' }}) || (navSearchQuery.trim() !== '' && '{{ strtolower(addslashes($p->name . ' ' . $sub)) }}'.includes(navSearchQuery.toLowerCase())))"
                                   @mouseenter="navSelectedIndex = {{ $navIdx }}"
                                   :data-nav-active="navSelectedIndex === {{ $navIdx }}"
                                   :class="navSelectedIndex === {{ $navIdx }} ? 'bg-[#1b6ac9] text-white' : 'text-[#1d2d35] hover:bg-[#1b6ac9] hover:text-white'"
                                   class="flex items-center gap-2.5 px-1.5 py-1 -mx-1.5 rounded-full text-[15px] transition-colors group">
                                    @if($isStarredProj)
                                        <span class="w-[24px] h-[24px] rounded-full bg-[#f5a623] text-white inline-flex items-center justify-center flex-shrink-0">
                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        </span>
                                    @else
                                        <span class="w-[24px] h-[24px] rounded-full bg-[#6d6a65]/30 text-[#1d2d35] inline-flex items-center justify-center flex-shrink-0">
                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 3C6.5 3 2 7.5 2 13c0 2.1.7 4 1.8 5.6L9 11l3.5 4.5 2.5-3.5 5.2 6.6c1.1-1.6 1.8-3.5 1.8-5.6 0-5.5-4.5-10-10-10z"/></svg>
                                        </span>
                                    @endif
                                    <div class="flex items-baseline gap-1.5 truncate">
                                        <span class="truncate">{{ $p->name }}</span>
                                        @if($sub)
                                        <span :class="navSelectedIndex === {{ $navIdx }} ? 'text-white/90' : 'text-[#6d6a65] group-hover:text-white/90'" class="font-normal truncate text-[13.5px]">- {{ $sub }}</span>
                                        @endif
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>

                        <!-- 5. Search Footer: "Search inside all projects [SHIFT][RETURN]" & "Include archived projects" toggle (Matches media_1793293525276_f6e7b7f7.jpg) -->
                        <div x-show="navSearchQuery.trim() !== ''"
                             x-cloak
                             class="mt-3 pt-3 border-t border-[#ece9e2] space-y-2.5">
                            <a :href="'{{ route('projects.index') }}?q=' + encodeURIComponent(navSearchQuery.trim())"
                               class="flex items-center justify-between px-1.5 py-1 -mx-1.5 rounded-full hover:bg-[#f2f0ec] transition-colors cursor-pointer group">
                                <div class="flex items-center gap-2.5 text-[14.5px] text-[#1d2d35]">
                                    <span class="w-[24px] h-[24px] rounded-full bg-[#6d6a65]/25 text-[#1d2d35] inline-flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                            <circle cx="11" cy="11" r="7"/>
                                            <path stroke-linecap="round" d="M20 20l-3.5-3.5"/>
                                        </svg>
                                    </span>
                                    <span>Search inside all projects</span>
                                </div>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <span class="px-1.5 py-0.5 rounded-[3px] border border-[#8c8881]/70 text-[10px] font-bold uppercase tracking-wider text-[#6d6a65]">SHIFT</span>
                                    <span class="px-1.5 py-0.5 rounded-[3px] border border-[#8c8881]/70 text-[10px] font-bold uppercase tracking-wider text-[#6d6a65]">RETURN</span>
                                </div>
                            </a>

                            <div class="flex items-center justify-between pt-0.5">
                                <span class="text-[14.5px] text-[#1d2d35]">Include archived projects</span>
                                <button type="button"
                                        @click="includeArchivedSearch = !includeArchivedSearch"
                                        :class="includeArchivedSearch ? 'border-[#2573d8] bg-[#2573d8]/15' : 'border-[#8c8881]/70 bg-transparent'"
                                        class="w-[42px] h-[22px] rounded-[5px] border p-0.5 flex items-center transition-colors cursor-pointer">
                                    <span :class="includeArchivedSearch ? 'translate-x-[18px] bg-[#2573d8]' : 'translate-x-0 bg-[#8c8881]'"
                                          class="w-[16px] h-[16px] rounded-[3px] transition-transform block"></span>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Right: Pings/Notifications (Matches Basecamp UI) -->
                <div class="w-44 sm:w-56 flex items-center justify-end gap-2">
                    <!-- Pings + 🔴 New for you OR 🔕 Pings + Notifications -->
                    <button x-show="!pingsModal"
                            @click="pingsModal = true"
                            :class="silencingNotifications ? 'bg-[#f2f0ec] hover:bg-[#e8e5df] text-[#1d2d35] border-[#e5e2dc]' : 'bg-[#fcf0e1] hover:bg-[#f7e5ce] text-[#1d2d35] border-[#f5dec3]'"
                            class="flex items-center gap-1.5 px-3 py-1.5 font-semibold text-[13.5px] rounded-[8px] border transition-all cursor-pointer whitespace-nowrap">
                        <template x-if="!silencingNotifications">
                            <span class="inline-flex items-center gap-1.5">
                                <span>Pings +</span>
                                <span class="w-2.5 h-2.5 rounded-full bg-[#e53935]"></span>
                                <span>New for you</span>
                            </span>
                        </template>
                        <template x-if="silencingNotifications">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#c84b31]" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M3.28 2.22a.75.75 0 00-1.06 1.06l18.5 18.5a.75.75 0 101.06-1.06l-2.34-2.34H19a1 1 0 00.8-1.6l-1.3-1.73V10a6.5 6.5 0 00-5.25-6.38V3a1.25 1.25 0 10-2.5 0v.62c-.86.17-1.67.51-2.38.98L3.28 2.22zM6.5 10c0-.47.05-.93.15-1.37l9.22 9.22H5a1 1 0 01-.8-1.6l1.3-1.73V10zm3.05 9.5a2.5 2.5 0 004.9 0h-4.9z"/>
                                </svg>
                                <span>Pings + Notifications</span>
                            </span>
                        </template>
                    </button>
                </div>

            </div>
        </div>
    </header>

    <!-- Global Flash Alerts -->
    @if(session('success'))
    <div class="bg-emerald-600 text-white text-xs font-semibold px-4 py-2 shadow-sm text-center flex items-center justify-center gap-2 animate-in fade-in" :class="pingsModal ? 'sm:mr-[360px]' : ''">
        <i data-lucide="check-circle" class="w-4 h-4"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-rose-600 text-white text-xs font-semibold px-4 py-2 shadow-sm text-center flex items-center justify-center gap-2 animate-in fade-in" :class="pingsModal ? 'sm:mr-[360px]' : ''">
        <i data-lucide="alert-circle" class="w-4 h-4"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Main Content Container (Resizes Left When Right Pings Drawer Is Open) -->
    <main class="flex-1 pt-0 pb-12 transition-all duration-150"
          :class="pingsModal ? 'w-full sm:w-[calc(100%-360px)] sm:mr-[360px] px-3 sm:px-6' : 'max-w-[1400px] w-full mx-auto px-2 sm:px-4 lg:px-6'">
        @yield('content')
    </main>

    <!-- Basecamp Floating Bottom Dock: My Stuff (Matches Basecamp UI) -->
    <div class="fixed bottom-0 left-0 z-40 bg-white border-t border-[#e5e2dc] px-3 sm:px-5 h-[40px] flex items-center transition-all duration-150"
         :class="pingsModal ? 'right-0 sm:right-[360px]' : 'right-0'">
        <div class="w-full flex items-center justify-between">
            <!-- Left: Current User Avatar Profile & "My Account" Popover -->
            @auth
            <div class="relative" x-data="{ accountMenu: false, dockOoo: localStorage.getItem('bc_ooo_active') !== '0' }">
                <button type="button" 
                        @click="accountMenu = !accountMenu" 
                        class="flex items-center gap-2 group cursor-pointer focus:outline-none" 
                        title="My Account">
                    <span class="relative w-7 h-7 rounded-full overflow-hidden border border-slate-300 shadow-2xs group-hover:scale-105 transition-transform inline-block">
                        <img src="{{ (Auth::user()->avatar_url && !str_contains(Auth::user()->avatar_url, 'dicebear')) ? Auth::user()->avatar_url : '/uploads/avatars/22369668.png' }}" 
                             class="w-full h-full object-cover">
                        <span x-show="dockOoo" class="absolute bottom-0 inset-x-0 h-[38%] bg-[#facc15] flex items-center justify-center">
                            <span class="text-[6.5px] font-black text-[#1d1c1b] leading-none tracking-tight">OUT</span>
                        </span>
                    </span>
                </button>

                <!-- My Account Popover (Matches screenshot media_1793216011051_5814849e.png) -->
                <div x-show="accountMenu" 
                     x-cloak 
                     @click.outside="accountMenu = false" 
                     class="absolute bottom-9 left-0 z-50 w-56 bg-white rounded-2xl shadow-2xl border border-slate-200 py-2.5 animate-in fade-in zoom-in-95">
                    
                    <div class="px-4 pb-2 border-b border-slate-100">
                        <h4 class="text-xs font-black text-[#9c2f0f] tracking-wide">My Account</h4>
                    </div>

                    <div class="py-1 text-xs font-bold text-slate-700">
                        <a href="{{ route('my.profile') }}" @click.prevent="accountMenu = false; openMyProfileModal()" class="flex items-center gap-3 px-4 py-2 hover:bg-slate-50 hover:text-slate-900 transition-colors cursor-pointer">
                            <i data-lucide="user" class="w-4 h-4 text-slate-500"></i>
                            <span>My Profile</span>
                        </a>

                        <a href="{{ route('my.drafts') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                            <i data-lucide="file-text" class="w-4 h-4 text-slate-500"></i>
                            <span>My Drafts</span>
                        </a>

                        <a href="{{ route('my.boosts') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                            <i data-lucide="rocket" class="w-4 h-4 text-slate-500"></i>
                            <span>My Boosts</span>
                        </a>

                        <a href="{{ route('my.settings') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                            <i data-lucide="settings" class="w-4 h-4 text-slate-500"></i>
                            <span>Account & Settings</span>
                        </a>

                        <div class="pt-1 mt-1 border-t border-slate-100">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-rose-600 hover:bg-rose-50 transition-colors text-left font-bold cursor-pointer">
                                    <i data-lucide="log-out" class="w-4 h-4 text-rose-500"></i>
                                    <span>Log out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="w-6"></div>
            @endauth

            <!-- Center: My Tasks, My Events, My Bookmarks, My Activity, My Notes -->
            <nav class="flex items-center gap-3 sm:gap-4 text-[13.5px] font-normal text-[#1d2d35] overflow-x-auto py-0.5">
                <a href="{{ request()->routeIs('my.tasks') ? route('home') : route('my.tasks') }}" class="transition-colors whitespace-nowrap {{ request()->routeIs('my.tasks') ? 'bg-[#1d2d35] text-white font-semibold px-3 py-1 rounded-[6px]' : 'px-1.5 py-1 hover:underline' }}">
                    My Tasks
                </a>
                <a href="{{ request()->routeIs('my.events') ? route('home') : route('my.events') }}" class="transition-colors whitespace-nowrap {{ request()->routeIs('my.events') ? 'bg-[#1d2d35] text-white font-semibold px-3 py-1 rounded-[6px]' : 'px-1.5 py-1 hover:underline' }}">
                    My Events
                </a>
                <a href="{{ request()->routeIs('my.bookmarks') ? route('home') : route('my.bookmarks') }}" class="transition-colors whitespace-nowrap {{ request()->routeIs('my.bookmarks') ? 'bg-[#1d2d35] text-white font-semibold px-3 py-1 rounded-[6px]' : 'px-1.5 py-1 hover:underline' }}">
                    My Bookmarks
                </a>
                @php $isMyOwnActivity = request()->routeIs('my.activity') && !request()->has('user_id') && !request()->has('scope'); @endphp
                <a href="{{ $isMyOwnActivity ? route('home') : route('my.activity') }}" class="transition-colors whitespace-nowrap {{ $isMyOwnActivity ? 'bg-[#1d2d35] text-white font-semibold px-3 py-1 rounded-[6px]' : 'px-1.5 py-1 hover:underline' }}">
                    My Activity
                </a>
                <a href="{{ request()->routeIs('my.notes') ? route('home') : route('my.notes') }}" class="transition-colors whitespace-nowrap {{ request()->routeIs('my.notes') ? 'bg-[#1d2d35] text-white font-semibold px-3 py-1 rounded-[6px]' : 'px-1.5 py-1 hover:underline' }}">
                    My Notes
                </a>
            </nav>

            <!-- Right: Support & AI Support Agent Popover (Matches media_1793278525030 & media_1793282301545) -->
            <script>
                window.bcSupportWidgetData = function () {
                    return {
                        supportOpen: false,
                        supportTab: 'guides',
                        contactStep: 1,
                        helpTopic: '---',
                        guideSearch: '',
                        selectedGuide: null,
                        aiInput: '',
                        aiTyping: false,
                        aiMessages: [
                            {
                                role: 'assistant',
                                time: 'Just now',
                                text: 'Hi {{ addslashes(Auth::user()->name ?? 'Manjot') }}! 👋 I’m your Basecamp AI Support Agent. Ask me anything about projects, to-dos, Card Tables, Adminland, Pings, or account settings.'
                            }
                        ],
                        startTopicChat() {
                            this.contactStep = 2;
                            if (this.helpTopic && this.helpTopic !== '---') {
                                this.sendAiMessage(this.helpTopic);
                            }
                        },
                        formatAiReply(text) {
                            const esc = String(text || '')
                                .replace(/&/g, '&amp;')
                                .replace(/</g, '&lt;')
                                .replace(/>/g, '&gt;');
                            return esc.replace(/(\/[a-zA-Z0-9_\/\-\?=]+)/g, '<a href="$1" class="underline font-semibold text-[#1b6ac9] hover:text-[#1458a6]">$1</a>');
                        },
                        guides: [
                            {
                                title: 'Adminland overview',
                                summary: 'Adminland is the control center for your Basecamp account. It’s where administrators manage people and permissions, and where account owners handle billing, upgrades, and account-level settings. If',
                                body: 'Adminland is the control center for your Basecamp account. Administrators can manage people, add/remove administrators, manage groups and companies, rename project tools, change message categories, toggle Voice Notes, merge people, and configure Pings & Chat history retention.'
                            },
                            {
                                title: 'Owner and Admin Actions',
                                summary: 'Adminland is organized around two tiers of users. Administrators manage the people and structural settings that keep the account running day-to-day. Account owners can do all of that, plus control',
                                body: 'Adminland is organized around two tiers of users. Administrators manage the people and structural settings that keep the account running day-to-day. Account owners can do all of that, plus handle billing, manage storage, add/remove account owners, rename the account, reassign someone’s to-dos, and export account data.'
                            },
                            {
                                title: 'Account Owners and ownership',
                                summary: 'Every Basecamp account starts with one owner, the person who created the account. But accounts can have multiple owners, and adding at least one co-owner is one of the most important things you can',
                                body: 'Every Basecamp account starts with one owner, the person who created the account. But accounts can have multiple owners, and adding at least one co-owner ensures someone else can always manage billing, security, and account-wide exports.'
                            },
                            {
                                title: 'Inviting coworkers and clients',
                                summary: 'Use Invite people from the home screen or Adminland → Manage people to send email invitations to coworkers, clients, or contractors.',
                                body: 'Go to Adminland → Manage people and click + Add people. Choose whether they work at Ajath Infotech Pvt Ltd, a client company, or help out as a contractor, then click Email invitation now.'
                            }
                        ],
                        sendAiMessage(customPrompt = null) {
                            const q = (customPrompt || this.aiInput || '').trim();
                            if (!q) return;
                            this.supportTab = 'contact';
                            this.contactStep = 2;
                            this.aiMessages.push({ role: 'user', time: 'Just now', text: q });
                            if (!customPrompt) this.aiInput = '';
                            this.aiTyping = true;
                            const self = this;
                            this.$nextTick(function() {
                                const box = document.getElementById('bc-ai-support-scroll');
                                if (box) box.scrollTop = box.scrollHeight;
                            });

                            const lower = q.toLowerCase();
                            let fallbackReply = 'I can help with that! You can manage projects from the Home dashboard (/projects), check your assigned work in My Tasks (/my/tasks), or configure account-wide settings in Adminland (/adminland).';
                            if (lower.includes('merge')) {
                                fallbackReply = 'To merge duplicate accounts or transfer someone’s work: go to Adminland → Merge people (/account/person_merges), select the account to merge from in Step 1, and choose the destination person in Step 2.';
                            } else if (lower.includes('tool') || lower.includes('rename')) {
                                fallbackReply = 'You can rename default project tools (To-dos, Card Table, Message Board, Docs & Files, Schedule, Campfire, Check-ins) in Adminland → Rename project tools (/account/custom_tool_names).';
                            } else if (lower.includes('2fa') || lower.includes('two-factor') || lower.includes('password') || lower.includes('login')) {
                                fallbackReply = 'To set up 2FA, switch to Google login, or change your password, open /identity/login_option (or go to /my/profile?edit=1 and click "Set up 2FA or change your login info here").';
                            } else if (lower.includes('dark') || lower.includes('theme') || lower.includes('appearance')) {
                                fallbackReply = 'To switch between Always light, Always dark, or Same as OS theme, open Account & Settings → Preferences (/my/settings?preferences=1).';
                            } else if (lower.includes('out of office') || lower.includes('ooo')) {
                                fallbackReply = 'To set or turn off Out of Office, open Account & Settings → Out of Office (/my/settings?ooo=1), toggle "I’m out", choose your dates, and click Save.';
                            } else if (lower.includes('categor')) {
                                fallbackReply = 'To customize message categories (📢 Announcement, ✨ FYI, ❤️ Heartbeat, 💡 Pitch, 👋 Question), open Adminland → Change message categories (/account/categories).';
                            } else if (lower.includes('invite') || lower.includes('client') || lower.includes('people')) {
                                fallbackReply = 'To invite coworkers or clients, go to Adminland → Manage people (/people) and click + Add people, or click Invite people to the account (/account/enrollments/new) on the Home dashboard.';
                            } else if (lower.includes('ping') || lower.includes('retention') || lower.includes('history')) {
                                fallbackReply = 'You can configure how long Pings and Chats are kept in Adminland → Change Ping & Chat history settings (/account/retention), or open all Pings at /pings.';
                            } else if (lower.includes('task') || lower.includes('to-do') || lower.includes('todo')) {
                                fallbackReply = 'Click My Tasks in the bottom bar (/my/tasks) to view all tasks assigned to you or tasks you’ve assigned to others, or use Adminland → Reassign someone’s to-dos (/account/todos/mass_reassignments/new).';
                            }

                            const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
                            fetch('{{ route('my.support.agent') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': token
                                },
                                body: JSON.stringify({ message: q })
                            })
                            .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
                            .then(function(data) {
                                self.aiTyping = false;
                                self.aiMessages.push({ role: 'assistant', time: 'Just now', text: (data && data.reply) ? data.reply : fallbackReply });
                                self.$nextTick(function() {
                                    const box = document.getElementById('bc-ai-support-scroll');
                                    if (box) box.scrollTop = box.scrollHeight;
                                });
                            })
                            .catch(function() {
                                self.aiTyping = false;
                                self.aiMessages.push({ role: 'assistant', time: 'Just now', text: fallbackReply });
                                self.$nextTick(function() {
                                    const box = document.getElementById('bc-ai-support-scroll');
                                    if (box) box.scrollTop = box.scrollHeight;
                                });
                            });
                        }
                    };
                };
            </script>
            <div class="relative flex-shrink-0"
                 @open-support-widget.window="supportOpen = true; supportTab = ($event.detail && $event.detail.tab) ? $event.detail.tab : 'contact'; contactStep = 1"
                 x-data="bcSupportWidgetData()">
                <button type="button"
                        @click="supportOpen = !supportOpen"
                        class="flex items-center gap-1 text-[13px] font-normal text-[#1d2d35] hover:underline transition-colors flex-shrink-0 cursor-pointer"
                        title="Support">
                    <span class="w-4 h-4 rounded-full bg-[#283c46] text-white text-[10px] font-bold inline-flex items-center justify-center">?</span>
                    <span class="hidden sm:inline">Support</span>
                </button>

                <!-- Floating Basecamp Support & AI Support Agent Widget -->
                <div x-show="supportOpen"
                     x-cloak
                     @click.outside="supportOpen = false"
                     class="fixed bottom-[48px] right-4 z-[90] w-[345px] max-w-[calc(100vw-24px)] flex flex-col items-end">

                    <!-- Top Floating "Close ✕" Pill -->
                    <button type="button"
                            @click="supportOpen = false"
                            class="mb-2 px-3 py-1 rounded-full bg-[#22292f] hover:bg-[#111518] text-white text-[12px] font-semibold inline-flex items-center gap-1.5 shadow-md cursor-pointer transition-colors">
                        <span>Close</span>
                        <span class="text-[11px] leading-none">✕</span>
                    </button>

                    <!-- Main Popover Card -->
                    <div class="w-full rounded-[10px] overflow-hidden shadow-[0_12px_36px_rgba(0,0,0,0.28)] border border-[#d4d0c8] bg-[#eef1f5] flex flex-col h-[510px] max-h-[calc(100vh-120px)]">

                        <!-- Blue Header -->
                        <div class="bg-[#1b74d8] text-white pt-3.5 pb-4 px-4 text-center flex-shrink-0">
                            <div class="flex items-center justify-center gap-3 text-[12.5px] font-semibold mb-3">
                                <button type="button"
                                        @click="supportTab = 'guides'; selectedGuide = null"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full transition-colors cursor-pointer"
                                        :class="supportTab === 'guides' ? 'bg-[#1458a6] text-white' : 'text-white/90 hover:bg-white/10'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                                    <span>Guides</span>
                                </button>
                                <button type="button"
                                        @click="supportTab = 'contact'; contactStep = 1"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full transition-colors cursor-pointer"
                                        :class="supportTab === 'contact' ? 'bg-[#1458a6] text-white' : 'text-white/90 hover:bg-white/10'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    <span>Contact Support</span>
                                </button>
                            </div>

                            <!-- Guides Header Title -->
                            <h3 x-show="supportTab === 'guides'" class="text-[18px] font-bold tracking-tight">Basecamp Guides</h3>

                            <!-- Contact Support Header: 5 Overlapping Avatars + "Friendly folks - standing by." (Matches media_1793282301545) -->
                            <div x-show="supportTab === 'contact'" x-cloak class="pt-0.5 pb-1">
                                <div class="flex items-center justify-center -space-x-2 mb-2.5">
                                    <img src="https://i.pravatar.cc/80?img=47" alt="Support" class="w-10 h-10 rounded-full border-2 border-white object-cover shadow-xs">
                                    <img src="https://i.pravatar.cc/80?img=12" alt="Support" class="w-10 h-10 rounded-full border-2 border-white object-cover shadow-xs">
                                    <img src="https://i.pravatar.cc/80?img=33" alt="Support" class="w-10 h-10 rounded-full border-2 border-white object-cover shadow-xs">
                                    <img src="https://i.pravatar.cc/80?img=32" alt="Support" class="w-10 h-10 rounded-full border-2 border-white object-cover shadow-xs">
                                    <img src="https://i.pravatar.cc/80?img=68" alt="Support" class="w-10 h-10 rounded-full border-2 border-white object-cover shadow-xs">
                                </div>
                                <h3 class="text-[16px] font-bold tracking-tight leading-snug">Friendly folks - standing by.</h3>
                                <p class="text-[11.5px] text-white/90 mt-0.5">We&rsquo;ll reply back as soon as we can.</p>
                            </div>
                        </div>

                        <!-- TAB 1: Basecamp Guides List -->
                        <div x-show="supportTab === 'guides'" class="flex-1 flex flex-col min-h-0">
                            <div class="flex-1 overflow-y-auto px-3 py-3 space-y-2.5">
                                <!-- Single Guide Detail View -->
                                <template x-if="selectedGuide">
                                    <div class="bg-white rounded-[6px] p-4 shadow-2xs border border-[#e2e5ea] text-left">
                                        <button type="button" @click="selectedGuide = null" class="text-[12px] font-semibold text-[#1b6ac9] hover:underline mb-2 inline-flex items-center gap-1 cursor-pointer">
                                            ← Back to guides
                                        </button>
                                        <h4 class="text-[15px] font-bold text-[#1d2d35] mb-2" x-text="selectedGuide.title"></h4>
                                        <p class="text-[13px] text-[#4b5563] leading-relaxed mb-4" x-text="selectedGuide.body"></p>
                                        <button type="button"
                                                @click="sendAiMessage('Tell me more about ' + selectedGuide.title)"
                                                class="w-full py-1.5 px-3 rounded-[6px] bg-[#eff6ff] hover:bg-[#dbeafe] text-[#1b6ac9] text-[12.5px] font-semibold transition-colors cursor-pointer">
                                            Ask AI Support Agent about this →
                                        </button>
                                    </div>
                                </template>

                                <!-- Guide Cards List -->
                                <template x-if="!selectedGuide">
                                    <div class="space-y-2.5">
                                        <template x-for="(g, gIdx) in guides" :key="gIdx">
                                            <div x-show="!guideSearch || (g.title + ' ' + g.summary).toLowerCase().includes(guideSearch.toLowerCase())"
                                                 @click="selectedGuide = g"
                                                 class="bg-white rounded-[6px] p-3.5 shadow-2xs border border-[#e2e5ea] hover:border-[#93c5fd] transition-colors cursor-pointer text-left">
                                                <div class="text-[13.5px] font-semibold text-[#1b6ac9] mb-1" x-text="g.title"></div>
                                                <p class="text-[12.5px] text-[#4b5563] leading-[1.45]" x-text="g.summary"></p>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            <!-- Bottom Search Bar -->
                            <div class="bg-white border-t border-[#dcdfe4] px-3.5 py-2.5 flex items-center gap-2 flex-shrink-0">
                                <input type="text"
                                       x-model="guideSearch"
                                       @keydown.enter.prevent="if (guideSearch.trim()) { sendAiMessage(guideSearch); guideSearch = ''; }"
                                       placeholder="Search the Basecamp help guides"
                                       class="flex-1 text-[13px] text-[#1d2d35] placeholder-[#6b7280] focus:outline-none bg-transparent">
                                <button type="button"
                                        @click="if (guideSearch.trim()) { sendAiMessage(guideSearch); guideSearch = ''; }"
                                        class="text-[#4b5563] hover:text-[#1d2d35] cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- TAB 2: Contact Support ("How can we help?" Step 1 + AI Support Agent Step 2) -->
                        <div x-show="supportTab === 'contact'" x-cloak class="flex-1 flex flex-col min-h-0 bg-white">
                            <!-- STEP 1: "How can we help?" Dropdown + Next Button (Matches media_1793282301545 & media_1793282305279) -->
                            <div x-show="contactStep === 1" class="p-4 text-left flex-1 flex flex-col">
                                <label class="block text-[12px] text-[#4b5563] mb-1.5">How can we help?</label>
                                <select x-model="helpTopic"
                                        class="w-full h-[36px] px-2.5 rounded-[5px] border border-[#d1d5db] bg-white text-[13px] text-[#1d2d35] focus:outline-none focus:border-[#1b74d8] mb-3">
                                    <option value="---">---</option>
                                    <option value="I've got a feature request">I&rsquo;ve got a feature request</option>
                                    <option value="I'm not sure how something works">I&rsquo;m not sure how something works</option>
                                    <option value="I've got an access/login problem">I&rsquo;ve got an access/login problem</option>
                                    <option value="I think something's broken">I think something&rsquo;s broken</option>
                                    <option value="I have a billing question">I have a billing question</option>
                                    <option value="I've got an emergency">I&rsquo;ve got an emergency</option>
                                    <option value="Other">Other</option>
                                </select>

                                <button type="button"
                                        @click="startTopicChat()"
                                        class="w-full py-2.5 rounded-[5px] bg-[#1b74d8] hover:bg-[#1458a6] text-white text-[13px] font-semibold transition-colors cursor-pointer">
                                    Next
                                </button>
                            </div>

                            <!-- STEP 2: Interactive AI Support Agent Chat -->
                            <div x-show="contactStep === 2" x-cloak class="flex-1 flex flex-col min-h-0 bg-[#eef1f5]">
                                <div class="px-3.5 py-2 bg-white border-b border-[#dcdfe4] flex items-center justify-between text-[12px]">
                                    <button type="button" @click="contactStep = 1" class="text-[#1b6ac9] font-semibold hover:underline cursor-pointer">
                                        &larr; Change topic
                                    </button>
                                    <span class="text-[#6b7280] truncate max-w-[180px]" x-text="helpTopic !== '---' ? helpTopic : 'AI Support Agent'"></span>
                                </div>

                                <div id="bc-ai-support-scroll" class="flex-1 overflow-y-auto px-3 py-3 space-y-2.5 text-left">
                                    <template x-for="(m, mIdx) in aiMessages" :key="mIdx">
                                        <div class="flex flex-col" :class="m.role === 'user' ? 'items-end' : 'items-start'">
                                            <div class="max-w-[88%] rounded-[8px] px-3 py-2 text-[12.5px] leading-relaxed shadow-2xs"
                                                 :class="m.role === 'user' ? 'bg-[#1b74d8] text-white' : 'bg-white text-[#1d2d35] border border-[#e2e5ea]'">
                                                <div x-show="m.role === 'assistant'" class="text-[11px] font-bold text-[#1b6ac9] mb-0.5">Basecamp AI Support</div>
                                                <template x-if="m.role === 'assistant'">
                                                    <div x-html="formatAiReply(m.text)"></div>
                                                </template>
                                                <template x-if="m.role === 'user'">
                                                    <div x-text="m.text"></div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <div x-show="aiTyping" x-cloak class="flex flex-col items-start">
                                        <div class="rounded-[8px] px-3 py-2 text-[12px] bg-white text-[#6b7280] border border-[#e2e5ea] shadow-2xs italic">
                                            Basecamp AI Support is typing...
                                        </div>
                                    </div>

                                    <!-- Quick Prompt Chips -->
                                    <div class="pt-1 flex flex-wrap gap-1.5">
                                        <button type="button" @click="sendAiMessage('How do I invite a client?')" class="px-2.5 py-1 rounded-full bg-white border border-[#d4d0c8] hover:border-[#1b74d8] text-[11.5px] text-[#1d2d35] cursor-pointer transition-colors">
                                            How do I invite a client?
                                        </button>
                                        <button type="button" @click="sendAiMessage('How do I rename project tools?')" class="px-2.5 py-1 rounded-full bg-white border border-[#d4d0c8] hover:border-[#1b74d8] text-[11.5px] text-[#1d2d35] cursor-pointer transition-colors">
                                            Rename project tools
                                        </button>
                                        <button type="button" @click="sendAiMessage('How do I merge people?')" class="px-2.5 py-1 rounded-full bg-white border border-[#d4d0c8] hover:border-[#1b74d8] text-[11.5px] text-[#1d2d35] cursor-pointer transition-colors">
                                            Merge people
                                        </button>
                                    </div>
                                </div>

                                <!-- AI Support Chat Input -->
                                <form @submit.prevent="sendAiMessage()" class="bg-white border-t border-[#dcdfe4] px-3.5 py-2.5 flex items-center gap-2 flex-shrink-0">
                                    <input type="text"
                                           x-model="aiInput"
                                           placeholder="Ask AI Support anything..."
                                           class="flex-1 text-[13px] text-[#1d2d35] placeholder-[#6b7280] focus:outline-none bg-transparent">
                                    <button type="submit" class="px-2.5 py-1 rounded-[5px] bg-[#1b74d8] hover:bg-[#1458a6] text-white text-[12px] font-semibold cursor-pointer transition-colors">
                                        Send
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pings & Notifications Right Panel (Exact Basecamp Side-by-Side Drawer Match) -->
    <aside x-show="pingsModal" x-cloak class="fixed top-0 right-0 bottom-0 h-screen max-h-screen w-[360px] max-w-full bg-[#fbfaf8] border-l border-[#e5e2dc] flex flex-col z-50 overflow-hidden" role="complementary" aria-label="Pings and Notifications">
            
            <!-- Top Header: Back Arrow (when in Ping chat or All Pings), Peach 'Pings + 🔴 New for you' Pill & Close X -->
            <div class="px-4 pt-3 pb-2.5 flex items-center justify-between flex-shrink-0">
                <button type="button"
                        x-show="activeDrawerPing || allPingsView"
                        x-cloak
                        @click="if (activeDrawerPing) { activeDrawerPing = null; } else { allPingsView = false; }"
                        class="p-1 text-[#1d2d35] hover:text-black transition-colors cursor-pointer"
                        title="Back">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m0 0l6-6m-6 6l6 6"/>
                    </svg>
                </button>

                <button type="button"
                        @click="if (activeDrawerPing) { activeDrawerPing = null; } else if (allPingsView) { allPingsView = false; } else { pingsModal = false; }"
                        :class="silencingNotifications ? 'bg-[#f2f0ec] hover:bg-[#e8e5df] text-[#1d2d35]' : 'bg-[#fae8d4] hover:bg-[#f5dcc0] text-[#1d2d35]'"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[8px] font-semibold text-[13px] transition-colors cursor-pointer">
                    <template x-if="!silencingNotifications">
                        <span class="inline-flex items-center gap-1.5">
                            <span>Pings +</span>
                            <span class="w-2.5 h-2.5 rounded-full bg-[#e53935] inline-block flex-shrink-0"></span>
                            <span>New for you</span>
                        </span>
                    </template>
                    <template x-if="silencingNotifications">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#c84b31]" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3.28 2.22a.75.75 0 00-1.06 1.06l18.5 18.5a.75.75 0 101.06-1.06l-2.34-2.34H19a1 1 0 00.8-1.6l-1.3-1.73V10a6.5 6.5 0 00-5.25-6.38V3a1.25 1.25 0 10-2.5 0v.62c-.86.17-1.67.51-2.38.98L3.28 2.22zM6.5 10c0-.47.05-.93.15-1.37l9.22 9.22H5a1 1 0 01-.8-1.6l1.3-1.73V10zm3.05 9.5a2.5 2.5 0 004.9 0h-4.9z"/>
                            </svg>
                            <span>Pings + Notifications</span>
                        </span>
                    </template>
                </button>

                <button type="button" @click="pingsModal = false; activeDrawerPing = null; allPingsView = false" class="p-1 text-[#1d2d35] hover:text-black transition-colors cursor-pointer" title="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- ========================================================================= -->
            <!-- VIEW A: INLINE PING CHAT INSIDE DRAWER (Exact Match to ac5508bb.jpg)      -->
            <!-- ========================================================================= -->
            <div x-show="activeDrawerPing" x-cloak class="flex-1 flex flex-col min-h-0 overflow-hidden">
                <!-- Contact Header Bar -->
                <div class="px-4 py-2 border-b border-[#ece9e2] flex items-center justify-between bg-[#fbfaf8] flex-shrink-0">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <template x-if="activeDrawerPing && activeDrawerPing.avatar">
                            <img :src="activeDrawerPing.avatar" :alt="activeDrawerPing.name" class="w-[36px] h-[36px] rounded-full object-cover flex-shrink-0">
                        </template>
                        <template x-if="activeDrawerPing && !activeDrawerPing.avatar">
                            <span class="w-[36px] h-[36px] rounded-full text-white font-bold text-[13px] flex items-center justify-center flex-shrink-0 select-none"
                                  :class="activeDrawerPing ? activeDrawerPing.bg : 'bg-[#9333ea]'"
                                  x-text="activeDrawerPing ? activeDrawerPing.initials : ''"></span>
                        </template>
                        <div class="min-w-0">
                            <div class="text-[15px] font-bold text-[#1d2d35] leading-tight truncate" x-text="activeDrawerPing ? activeDrawerPing.name : ''"></div>
                            <div x-show="!activeDrawerPing || !activeDrawerPing.removed" class="text-[11.5px] text-[#6d6a65] leading-tight mt-0.5">{{ now()->timezone('Asia/Kolkata')->format('g:i a') }} IST</div>
                        </div>
                    </div>

                    <!-- Expand to Full Page Icon (↗) -->
                    <a :href="activeDrawerPing ? ('/pings/' + activeDrawerPing.id) : '#'"
                       class="p-1.5 text-[#4a4640] hover:text-[#1d2d35] transition-colors flex-shrink-0"
                       title="Open Ping in full page">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 3h6v6M14 10l6.1-6.1M9 21H3v-6M10 14l-6.1 6.1"/>
                        </svg>
                    </a>
                </div>

                <!-- Chat Messages Stream -->
                <div id="drawer-ping-messages" class="flex-1 min-h-0 overflow-y-auto overscroll-contain px-3.5 py-3 space-y-2.5">
                    <!-- Basecamp Ping Intro Header & Date Divider (Enables Full Vertical Scroll like Basecamp) -->
                    <div class="pt-2 pb-3 text-center border-b border-[#ece9e2] mb-2">
                        <div class="inline-flex items-center justify-center mb-1.5">
                            <template x-if="activeDrawerPing && activeDrawerPing.avatar">
                                <img :src="activeDrawerPing.avatar" :alt="activeDrawerPing.name" class="w-[44px] h-[44px] rounded-full object-cover">
                            </template>
                            <template x-if="activeDrawerPing && !activeDrawerPing.avatar">
                                <span class="w-[44px] h-[44px] rounded-full text-white font-bold text-[15px] flex items-center justify-center select-none"
                                      :class="activeDrawerPing ? activeDrawerPing.bg : 'bg-[#9333ea]'"
                                      x-text="activeDrawerPing ? activeDrawerPing.initials : ''"></span>
                            </template>
                        </div>
                        <div class="text-[13.5px] font-bold text-[#1d2d35]" x-text="activeDrawerPing ? activeDrawerPing.name : ''"></div>
                        <p class="text-[12px] text-[#6d6a65] leading-snug mt-1 px-3">
                            This is the start of your private Ping history with <span x-text="activeDrawerPing ? activeDrawerPing.name : ''"></span>.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 py-1">
                        <div class="flex-1 border-t border-[#e5e2dc]"></div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#6d6a65]" x-text="(activeDrawerPing && activeDrawerPing.dateLabel) ? activeDrawerPing.dateLabel : 'Wednesday, August 5'"></span>
                        <div class="flex-1 border-t border-[#e5e2dc]"></div>
                    </div>

                    <template x-for="(msg, idx) in (activeDrawerPing ? activeDrawerPing.messages : [])" :key="idx">
                        <div>
                            <!-- Optional Date Divider Before Message (e.g. Nikhil Aneja thread) -->
                            <template x-if="msg.dateDivider">
                                <div class="flex items-center gap-2 py-2.5">
                                    <div class="flex-1 border-t border-[#e5e2dc]"></div>
                                    <span class="text-[10.5px] font-bold uppercase tracking-wider text-[#6d6a65]" x-text="msg.dateDivider"></span>
                                    <div class="flex-1 border-t border-[#e5e2dc]"></div>
                                </div>
                            </template>

                            <!-- Cover-Style Document Bubble (Matches Nikhil Aneja PDF Preview in media_1793328264963_cdb10dc9.png) -->
                            <template x-if="msg.coverFile">
                                <div class="flex justify-center my-1">
                                    <div @click="openPingDoc(msg.coverFile)"
                                         class="bg-[#eaebed] rounded-[12px] p-3 cursor-pointer hover:bg-[#e2e4e7] transition-colors w-[248px] text-center shadow-2xs">
                                        <div class="bg-white border border-[#e5e2dc] rounded-[4px] mx-auto w-[210px] h-[256px] p-4 flex flex-col justify-between items-center shadow-xs overflow-hidden">
                                            <div class="text-center pt-2">
                                                <div class="text-[10.5px] font-serif font-bold text-[#1d2d35] leading-snug">Vertical Video Streaming App Development</div>
                                                <div class="text-[6.5px] uppercase tracking-wider text-[#6d6a65] mt-1">MOBILE APP DEVELOPMENT PROPOSAL</div>
                                            </div>
                                            <div class="my-auto flex items-center justify-center">
                                                <img :src="msg.coverFile.thumb" :alt="msg.coverFile.name" class="max-h-[115px] object-contain">
                                            </div>
                                            <div class="w-full flex items-center justify-center gap-4 pb-1 text-[7px] text-[#6d6a65]">
                                                <div>Prepared for:<br><strong class="text-[#1d2d35]">Client</strong></div>
                                                <div>Created by:<br><strong class="text-[#1d2d35]">BrancoSoft</strong></div>
                                            </div>
                                        </div>
                                        <div class="mt-2.5 text-[13px] font-normal text-[#1d2d35] truncate" x-text="msg.coverFile.name"></div>
                                    </div>
                                </div>
                            </template>

                            <!-- Incoming Message ('them') -->
                            <template x-if="msg.sender === 'them' && !msg.coverFile">
                                <div class="flex items-start gap-2">
                                    <!-- Show 24px avatar on first bubble or when previous bubble was 'me' or standalone -->
                                    <div class="w-[24px] flex-shrink-0 mt-0.5">
                                        <template x-if="!msg.hideAvatar">
                                            <div>
                                                <template x-if="activeDrawerPing.avatar">
                                                    <img :src="activeDrawerPing.avatar" :alt="activeDrawerPing.name" class="w-[24px] h-[24px] rounded-full object-cover">
                                                </template>
                                                <template x-if="!activeDrawerPing.avatar">
                                                    <span class="w-[24px] h-[24px] rounded-full text-white font-bold text-[10px] flex items-center justify-center select-none"
                                                          :class="activeDrawerPing.bg"
                                                          x-text="activeDrawerPing.initials"></span>
                                                </template>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="bg-[#eaebed] rounded-[10px] px-3 py-2 text-[#1d2d35] max-w-[278px]">
                                        <div class="flex items-center gap-1 text-[11.5px] leading-tight mb-0.5">
                                            <span x-show="!msg.hideName" class="font-bold text-[#1d2d35]" x-text="activeDrawerPing.name"></span>
                                            <span class="text-[#6d6a65] cursor-default" :title="msg.fullTime || ''" x-text="msg.time"></span>
                                            <span class="text-[#6d6a65] font-bold tracking-tighter ml-0.5">•••</span>
                                        </div>

                                        <!-- Clickable Document Attachment Card -->
                                        <template x-if="msg.file">
                                            <div class="mt-1">
                                                <div @click="openPingDoc(msg.file)"
                                                     class="group cursor-pointer bg-white hover:bg-[#f9f8f6] border border-[#dcd8d0] rounded-[8px] p-2.5 flex items-start gap-2.5 transition-all shadow-2xs">
                                                    <div class="w-[38px] h-[48px] rounded-[4px] border border-[#e5e2dc] bg-[#fbfaf8] overflow-hidden flex-shrink-0 flex items-center justify-center relative">
                                                        <template x-if="msg.file.thumb">
                                                            <img :src="msg.file.thumb" :alt="msg.file.name" class="w-full h-full object-cover">
                                                        </template>
                                                        <span class="absolute bottom-0 inset-x-0 bg-[#c84b31] text-white text-[8px] font-extrabold uppercase text-center leading-tight py-0.5">PDF</span>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <div class="text-[13px] font-bold text-[#1b6ac9] group-hover:underline leading-snug break-words" x-text="msg.file.name"></div>
                                                        <div class="text-[11.5px] text-[#6d6a65] mt-0.5" x-text="msg.file.size"></div>
                                                        <div class="mt-1.5 flex items-center gap-2.5 text-[11.5px] font-semibold">
                                                            <button type="button" @click.stop="openPingDoc(msg.file)" class="text-[#1b6ac9] hover:underline cursor-pointer">View document</button>
                                                            <span class="text-[#c5c0b7]">•</span>
                                                            <a :href="msg.file.url" :download="msg.file.name" @click.stop class="text-[#6d6a65] hover:text-[#1d2d35] hover:underline">Download</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <template x-if="msg.caption">
                                                    <div class="text-[13px] leading-[1.35] text-[#1d2d35] mt-1.5" x-text="msg.caption"></div>
                                                </template>
                                            </div>
                                        </template>

                                        <!-- Clickable URL / Email / Text Message -->
                                        <template x-if="!msg.file && msg.linkUrl">
                                            <a :href="msg.linkUrl" target="_blank" rel="noopener noreferrer" class="text-[13.5px] leading-[1.35] text-[#1b6ac9] underline hover:opacity-80 break-all block" x-text="msg.text"></a>
                                        </template>
                                        <template x-if="!msg.file && msg.email">
                                            <a :href="'mailto:' + msg.email" class="text-[13.5px] leading-[1.35] text-[#1b6ac9] underline hover:opacity-80 break-all block" x-text="msg.text"></a>
                                        </template>
                                        <template x-if="!msg.file && !msg.linkUrl && !msg.email">
                                            <div class="text-[13.5px] leading-[1.35] text-[#1d2d35] break-words" x-text="msg.text"></div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- Outgoing Message ('me') -->
                            <template x-if="msg.sender === 'me'">
                                <div class="flex items-start justify-end gap-2">
                                    <div class="bg-white border border-[#ece9e2] rounded-[10px] px-3 py-2 text-[#1d2d35] max-w-[278px] shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                                        <div class="flex items-center justify-end gap-1 text-[11.5px] leading-tight mb-0.5">
                                            <span class="text-[#6d6a65] font-bold tracking-tighter mr-0.5">•••</span>
                                            <span class="text-[#6d6a65] cursor-default" :title="msg.fullTime || ''" x-text="msg.time"></span>
                                            <span x-show="idx === 0 || activeDrawerPing.messages[idx - 1].sender !== 'me'" class="font-bold text-[#1d2d35]">Me</span>
                                        </div>

                                        <template x-if="msg.file">
                                            <div @click="openPingDoc(msg.file)"
                                                 class="mt-1 group cursor-pointer bg-[#fbfaf8] hover:bg-[#f2f0ec] border border-[#dcd8d0] rounded-[8px] p-2.5 flex items-start gap-2.5 transition-all text-left">
                                                <div class="w-[38px] h-[48px] rounded-[4px] border border-[#e5e2dc] bg-white overflow-hidden flex-shrink-0 flex items-center justify-center relative">
                                                    <template x-if="msg.file.thumb">
                                                        <img :src="msg.file.thumb" :alt="msg.file.name" class="w-full h-full object-cover">
                                                    </template>
                                                    <span class="absolute bottom-0 inset-x-0 bg-[#c84b31] text-white text-[8px] font-extrabold uppercase text-center leading-tight py-0.5">FILE</span>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-[13px] font-bold text-[#1b6ac9] group-hover:underline leading-snug break-words" x-text="msg.file.name"></div>
                                                    <div class="text-[11.5px] text-[#6d6a65] mt-0.5" x-text="msg.file.size"></div>
                                                    <div class="mt-1.5 flex items-center gap-2.5 text-[11.5px] font-semibold">
                                                        <button type="button" @click.stop="openPingDoc(msg.file)" class="text-[#1b6ac9] hover:underline cursor-pointer">View document</button>
                                                        <span class="text-[#c5c0b7]">•</span>
                                                        <a :href="msg.file.url" :download="msg.file.name" @click.stop class="text-[#6d6a65] hover:text-[#1d2d35] hover:underline">Download</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        <template x-if="!msg.file">
                                            <div class="text-[13.5px] leading-[1.35] text-[#1d2d35] break-words" x-text="msg.text"></div>
                                        </template>
                                    </div>

                                    <div class="w-[24px] flex-shrink-0 mt-0.5">
                                        <template x-if="idx === 0 || activeDrawerPing.messages[idx - 1].sender !== 'me'">
                                            <img src="/uploads/avatars/22369668.png" alt="Me" class="w-[24px] h-[24px] rounded-full object-cover">
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Removed From Basecamp Notice (Nikhil Aneja match to media_1793328264963_cdb10dc9.png) -->
                    <template x-if="activeDrawerPing && activeDrawerPing.removed">
                        <div class="pt-3 pb-2 px-1 text-[13.5px] leading-[1.38] text-[#1d2d35]">
                            <div class="font-bold text-[#1d2d35]">
                                <span x-text="activeDrawerPing.name"></span> has been removed from Basecamp.
                            </div>
                            <div class="text-[#1d2d35] font-normal mt-0.5">
                                You can see your past conversations, but you can’t send new pings.
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Bottom Input Box & Toolbar (Exact Match to ac5508bb.jpg & media_1793328264963_cdb10dc9.png) -->
                <div class="px-3.5 pt-2 pb-2.5 border-t border-[#ece9e2] bg-[#fbfaf8]">
                    <template x-if="!activeDrawerPing || !activeDrawerPing.removed">
                        <div>
                            <form @submit.prevent="sendDrawerPing()">
                                <input id="drawer-ping-input"
                                       type="text"
                                       x-model="drawerMessageInput"
                                       autocomplete="off"
                                       class="w-full border-[1.5px] border-[#1b6ac9] rounded-[7px] bg-white px-3 py-2 text-[13.5px] text-[#1d2d35] focus:outline-none">
                            </form>

                            <div class="mt-2 flex items-center justify-between text-[#4a4640] px-0.5">
                                <button type="button" @click="activeDrawerPing = null" class="p-1 hover:text-[#1d2d35] transition-colors cursor-pointer" title="Back">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m0 0l6-6m-6 6l6 6"/>
                                    </svg>
                                </button>

                                <div class="flex items-center gap-3 text-[#4a4640]">
                                    <button type="button" @click="drawerMessageInput += ' 😊'; document.getElementById('drawer-ping-input').focus()" class="hover:text-[#1d2d35] cursor-pointer" title="Emoji">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10"/>
                                            <path stroke-linecap="round" d="M8 14s1.5 2 4 2 4-2 4-2"/>
                                            <line x1="9" y1="9" x2="9.01" y2="9" stroke-width="2.5" stroke-linecap="round"/>
                                            <line x1="15" y1="9" x2="15.01" y2="9" stroke-width="2.5" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                    <input id="drawer-ping-file-input" type="file" class="hidden" @change="attachDrawerPingFile($event)">
                                    <button type="button" @click="document.getElementById('drawer-ping-file-input').click()" class="hover:text-[#1d2d35] cursor-pointer" title="Attach file">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                    </button>
                                    <button type="button" @click="sendDrawerPing()" class="hover:text-[#1d2d35] cursor-pointer" title="Voice message">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 10v2a7 7 0 01-14 0v-2M12 19v4M8 23h8"/>
                                        </svg>
                                    </button>
                                    <button type="button" @click="sendDrawerPing()" class="hover:text-[#1d2d35] font-serif font-bold text-[14px] underline decoration-2 cursor-pointer" title="Formatting">
                                        A
                                    </button>
                                    <button type="button" @click="pingsModal = false; activeDrawerPing = null" class="hover:text-[#1d2d35] cursor-pointer" title="Close">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template x-if="activeDrawerPing && activeDrawerPing.removed">
                        <div class="flex items-center justify-between text-[#4a4640] px-0.5 py-0.5">
                            <button type="button" @click="activeDrawerPing = null" class="p-1 hover:text-[#1d2d35] transition-colors cursor-pointer" title="Back">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m0 0l6-6m-6 6l6 6"/>
                                </svg>
                            </button>
                            <button type="button" @click="pingsModal = false; activeDrawerPing = null" class="p-1 hover:text-[#1d2d35] transition-colors cursor-pointer" title="Close">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- VIEW A2: ALL PINGS LIST INSIDE DRAWER (media_1793276095268_161db0ec.jpg)  -->
            <!-- ========================================================================= -->
            <div x-show="allPingsView && !activeDrawerPing" x-cloak class="flex-1 flex flex-col min-h-0 overflow-hidden">
                <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain px-4 pt-2 pb-4">
                    <!-- Sub-header: All Pings & View archived pings -->
                    <div class="flex items-baseline justify-between pb-3 border-b border-[#ece9e2]">
                        <h3 class="text-[16px] font-bold text-[#1d2d35]">All Pings</h3>
                        <a href="{{ route('my.pings') }}" class="text-[12px] text-[#6d6a65] hover:text-[#1d2d35] hover:underline">View archived pings</a>
                    </div>

                    <!-- Section: Previously seen -->
                    <h4 class="text-[12px] font-bold text-[#1d2d35] mt-3.5 mb-2">Previously seen</h4>

                    @php
                        $allDrawerPingsList = [
                            (object)[
                                'id' => 62,
                                'name' => 'Shiv Narayan',
                                'initials' => 'SN',
                                'bg' => 'bg-[#9333ea]',
                                'avatar' => null,
                                'snippet' => 'sir i have changed my password please s...',
                                'date' => 'Aug 5',
                            ],
                            (object)[
                                'id' => 202,
                                'name' => 'Kashish',
                                'initials' => 'K',
                                'bg' => 'bg-[#14b8a6]',
                                'avatar' => null,
                                'snippet' => 'Commercial ..pdf',
                                'date' => 'Dec 3, 2025',
                            ],
                            (object)[
                                'id' => 153,
                                'name' => 'Tanu Shree',
                                'initials' => 'TS',
                                'bg' => 'bg-[#eab308]',
                                'avatar' => null,
                                'snippet' => 'You: Nitin sharma - rkgit',
                                'date' => 'Nov 12, 2025',
                            ],
                            (object)[
                                'id' => 146,
                                'name' => 'Anushka Garg',
                                'initials' => 'AG',
                                'bg' => 'bg-[#ea580c]',
                                'avatar' => null,
                                'snippet' => 'Okay sir',
                                'date' => 'Nov 12, 2025',
                            ],
                            (object)[
                                'id' => 60,
                                'name' => 'Shachish Sneh',
                                'initials' => 'SS',
                                'bg' => 'bg-[#4f46e5]',
                                'avatar' => '/uploads/avatars/25036340.png',
                                'snippet' => 'P@sswOrd1111',
                                'date' => 'Oct 24, 2025',
                            ],
                            (object)[
                                'id' => 147,
                                'name' => 'Nikhil Aneja',
                                'initials' => 'NA',
                                'bg' => 'bg-[#0284c7]',
                                'avatar' => '/uploads/avatars/48443395.png',
                                'snippet' => 'itramanmcd@gmail.com',
                                'date' => 'Sep 10, 2025',
                            ],
                            (object)[
                                'id' => 148,
                                'name' => 'Nirdesh Verma',
                                'initials' => 'NV',
                                'bg' => 'bg-[#0d9488]',
                                'avatar' => '/uploads/avatars/48060513.png',
                                'snippet' => 'https://docs.google.com/spreadsheets/d...',
                                'date' => 'Sep 10, 2025',
                            ],
                            (object)[
                                'id' => 149,
                                'name' => 'Rudra Banger',
                                'initials' => 'RB',
                                'bg' => 'bg-[#92400e]',
                                'avatar' => null,
                                'snippet' => 'You: Hnji aajao',
                                'date' => 'Sep 8, 2025',
                            ],
                        ];
                    @endphp

                    <div class="space-y-1">
                        @foreach($allDrawerPingsList as $ap)
                        <a href="{{ route('my.pings.show', $ap->id) }}"
                           @click.prevent="openDrawerPing({{ $ap->id }})"
                           x-show="!pingFilter || '{{ strtolower(addslashes($ap->name . ' ' . $ap->snippet)) }}'.includes(pingFilter.toLowerCase())"
                           class="flex items-start gap-2.5 py-2 px-1.5 -mx-1.5 rounded-lg hover:bg-[#f2f0ec] transition-colors cursor-pointer group">
                            <div class="flex-shrink-0 mt-0.5">
                                @if($ap->avatar)
                                    <img src="{{ $ap->avatar }}" alt="{{ $ap->name }}" class="w-[34px] h-[34px] rounded-full object-cover">
                                @else
                                    <span class="w-[34px] h-[34px] rounded-full {{ $ap->bg }} text-white font-bold text-[13px] flex items-center justify-center select-none">{{ $ap->initials }}</span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[13.5px] font-bold text-[#1d2d35] leading-snug truncate">{{ $ap->name }}</p>
                                <p class="text-[13px] text-[#1d2d35] leading-[1.3] truncate mt-0.5">{{ $ap->snippet }}</p>
                                <p class="text-[11.5px] text-[#6d6a65] mt-0.5">{{ $ap->date }}</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>

                <!-- Sticky Bottom Bar for All Pings: ← | Filter... | ✕ (media_1793276095268_161db0ec.jpg) -->
                <div class="px-3.5 py-2 border-t border-[#e5e2dc] bg-[#fbfaf8] flex items-center gap-2.5 flex-shrink-0">
                    <button type="button" @click="allPingsView = false" class="p-1 text-[#6d6a65] hover:text-[#1d2d35] transition-colors cursor-pointer" title="Back">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m0 0l6-6m-6 6l6 6"/>
                        </svg>
                    </button>
                    <input type="text"
                           x-model="pingFilter"
                           placeholder="Filter..."
                           class="flex-1 min-w-0 px-3 py-1 bg-white border border-[#d8d4cc] rounded-[6px] text-[13px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none focus:border-[#1b6ac9]">
                    <button type="button" @click="pingsModal = false; allPingsView = false" class="p-1 text-[#6d6a65] hover:text-[#1d2d35] transition-colors cursor-pointer" title="Close">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- VIEW B: DEFAULT PINGS & NOTIFICATIONS LIST                                -->
            <!-- ========================================================================= -->
            <!-- Scrollable Content -->
            <div x-show="!activeDrawerPing && !allPingsView" class="flex-1 min-h-0 overflow-y-auto overscroll-contain px-4 pb-4 space-y-4">
                
                <!-- 1. Ping someone -->
                <div class="space-y-2 pt-1">
                    <div class="flex items-center justify-between">
                        <h4 class="text-[12.5px] font-bold text-[#1d2d35]">Ping someone</h4>
                        <a href="{{ route('my.pings') }}" @click.prevent="allPingsView = true" class="text-[12px] text-[#6d6a65] underline hover:text-[#1d2d35] cursor-pointer">View all Pings</a>
                    </div>

                    <!-- Horizontal 8-Circle Row -->
                    <div class="grid grid-cols-8 gap-1 pt-0.5 text-center">
                        <!-- + Ping / × Ping Toggle -->
                        <button type="button"
                                @click="newPingInputOpen = !newPingInputOpen; newPingDropdownOpen = false; if (newPingInputOpen) { $nextTick(() => $refs.newPingSearchInput?.focus()); } else { newPingQuery = ''; newPingSelectedUser = null; }"
                                class="flex flex-col items-center gap-1 group cursor-pointer focus:outline-none">
                            <span class="w-[36px] h-[36px] rounded-full border border-dashed border-[#4a90e2] bg-white flex items-center justify-center text-[#1b6ac9] group-hover:bg-blue-50 transition-colors leading-none">
                                <span x-show="!newPingInputOpen" class="text-[20px] font-normal leading-none">+</span>
                                <svg x-show="newPingInputOpen" x-cloak class="w-3.5 h-3.5 text-[#1b6ac9]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </span>
                            <span class="text-[11px] text-[#1b6ac9] truncate w-full">Ping</span>
                        </button>

                        <!-- Shiv (SN) -->
                        <a href="{{ route('my.pings.show', 62) }}" @click.prevent="openDrawerPing(62)" class="flex flex-col items-center gap-1 group cursor-pointer" title="Shiv Narayan">
                            <span class="w-[36px] h-[36px] rounded-full bg-[#9333ea] text-white font-bold text-[13px] flex items-center justify-center group-hover:opacity-90 transition-opacity">SN</span>
                            <span class="text-[11px] text-[#6d6a65] group-hover:text-[#1d2d35] truncate w-full">Shiv</span>
                        </a>

                        <!-- Kashish (K) -->
                        <a href="{{ route('my.pings.show', 202) }}" @click.prevent="openDrawerPing(202)" class="flex flex-col items-center gap-1 group cursor-pointer" title="Kashish">
                            <span class="w-[36px] h-[36px] rounded-full bg-[#10b981] text-white font-bold text-[14px] flex items-center justify-center group-hover:opacity-90 transition-opacity">K</span>
                            <span class="text-[11px] text-[#6d6a65] group-hover:text-[#1d2d35] truncate w-full">Kash...</span>
                        </a>

                        <!-- Tanu (TS) -->
                        <a href="{{ route('my.pings.show', 153) }}" @click.prevent="openDrawerPing(153)" class="flex flex-col items-center gap-1 group cursor-pointer" title="Tanu Shree">
                            <span class="w-[36px] h-[36px] rounded-full bg-[#eab308] text-white font-bold text-[13px] flex items-center justify-center group-hover:opacity-90 transition-opacity">TS</span>
                            <span class="text-[11px] text-[#6d6a65] group-hover:text-[#1d2d35] truncate w-full">Tanu</span>
                        </a>

                        <!-- Anushka (AG) -->
                        <a href="{{ route('my.pings.show', 146) }}" @click.prevent="openDrawerPing(146)" class="flex flex-col items-center gap-1 group cursor-pointer" title="Anushka Garg">
                            <span class="w-[36px] h-[36px] rounded-full bg-[#ea580c] text-white font-bold text-[13px] flex items-center justify-center group-hover:opacity-90 transition-opacity">AG</span>
                            <span class="text-[11px] text-[#6d6a65] group-hover:text-[#1d2d35] truncate w-full">Anus...</span>
                        </a>

                        <!-- Shachish (Photo) -->
                        <a href="{{ route('my.pings.show', 60) }}" @click.prevent="openDrawerPing(60)" class="flex flex-col items-center gap-1 group cursor-pointer" title="Shachish Sneh">
                            <img src="/uploads/avatars/25036340.png" alt="Shachish" class="w-[36px] h-[36px] rounded-full object-cover group-hover:opacity-90 transition-opacity">
                            <span class="text-[11px] text-[#6d6a65] group-hover:text-[#1d2d35] truncate w-full">Shac...</span>
                        </a>

                        <!-- Nikhil (Photo) -->
                        <a href="{{ route('my.pings.show', 147) }}" @click.prevent="openDrawerPing(147)" class="flex flex-col items-center gap-1 group cursor-pointer" title="Nikhil Aneja">
                            <img src="/uploads/avatars/48443395.png" alt="Nikhil" class="w-[36px] h-[36px] rounded-full object-cover group-hover:opacity-90 transition-opacity">
                            <span class="text-[11px] text-[#6d6a65] group-hover:text-[#1d2d35] truncate w-full">Nikhil</span>
                        </a>

                        <!-- Nirdesh (Photo) -->
                        <a href="{{ route('my.pings.show', 148) }}" @click.prevent="openDrawerPing(148)" class="flex flex-col items-center gap-1 group cursor-pointer" title="Nirdesh Verma">
                            <img src="/uploads/avatars/48060513.png" alt="Nirdesh" class="w-[36px] h-[36px] rounded-full object-cover group-hover:opacity-90 transition-opacity">
                            <span class="text-[11px] text-[#6d6a65] group-hover:text-[#1d2d35] truncate w-full">Nird...</span>
                        </a>
                    </div>

                    <!-- Inline "Start a private chat..." + "Ping 'em" Row (Matches media_1793338267392_3e0f38da.jpg) -->
                    <div x-show="newPingInputOpen" x-cloak class="pt-1.5 relative" @click.outside="newPingDropdownOpen = false">
                        <div class="flex items-center gap-1.5">
                            <div class="flex-1 min-w-0 relative">
                                <input type="text"
                                       x-ref="newPingSearchInput"
                                       x-model="newPingQuery"
                                       @focus="newPingDropdownOpen = true"
                                       @input="newPingSelectedUser = null; newPingDropdownOpen = true"
                                       @keydown.enter.prevent="submitNewPingDrawer()"
                                       @keydown.escape.prevent="newPingInputOpen = false; newPingDropdownOpen = false"
                                       placeholder="Start a private chat..."
                                       class="w-full h-[35px] px-2.5 bg-white border border-[#1b6ac9] ring-2 ring-[#1b6ac9]/25 rounded-[6px] text-[13px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none focus:border-[#1b6ac9]">

                                <!-- Autocomplete Suggestions Popover -->
                                <div x-show="newPingDropdownOpen && newPingQuery.trim().length > 0"
                                     x-cloak
                                     class="absolute left-0 right-0 top-full mt-1 bg-white border border-[#d8d4cc] rounded-[8px] shadow-lg max-h-[210px] overflow-y-auto z-50 py-1">
                                    <template x-for="person in newPingDirectory.filter(p => p.name.toLowerCase().includes(newPingQuery.trim().toLowerCase()))" :key="person.id">
                                        <button type="button"
                                                @click="selectNewPingPerson(person)"
                                                class="w-full px-2.5 py-1.5 flex items-center gap-2 text-left hover:bg-[#f2f0ec] transition-colors cursor-pointer">
                                            <template x-if="person.avatar">
                                                <img :src="person.avatar" :alt="person.name" class="w-6 h-6 rounded-full object-cover flex-shrink-0">
                                            </template>
                                            <template x-if="!person.avatar">
                                                <span class="w-6 h-6 rounded-full text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0"
                                                      :class="person.bg"
                                                      x-text="person.initials"></span>
                                            </template>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-[12.5px] font-bold text-[#1d2d35] truncate" x-text="person.name"></div>
                                                <div class="text-[10.5px] text-[#6d6a65] truncate" x-text="person.role"></div>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <button type="button"
                                    @click="submitNewPingDrawer()"
                                    class="h-[35px] px-3 rounded-[6px] bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-semibold text-[13px] whitespace-nowrap transition-colors cursor-pointer flex-shrink-0">
                                Ping ’em
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 2. Recently Bubbled Up -->
                <div class="space-y-1.5 pt-1" x-show="bubbledItems.length > 0">
                    <h4 class="text-[12.5px] font-bold text-[#1d2d35]">Recently Bubbled Up</h4>

                    <template x-for="(bItem, bIdx) in bubbledItems" :key="bItem.id">
                        <a :href="bItem.url"
                           x-show="!pingFilter || (bItem.title + ' ' + (bItem.snippet || '') + ' ' + bItem.meta).toLowerCase().includes(pingFilter.toLowerCase())"
                           class="flex items-start gap-2.5 py-1.5 px-1.5 -mx-1.5 rounded-lg hover:bg-[#e8e6e1] transition-colors group relative">
                            <!-- Avatar -->
                            <div class="relative flex-shrink-0 mt-0.5">
                                <span class="w-[34px] h-[34px] rounded-full text-white font-bold text-[13px] flex items-center justify-center"
                                      :class="bItem.bg"
                                      x-text="bItem.initials"></span>
                                <template x-if="bItem.badge_type === 'file'">
                                    <span class="absolute -top-0.5 -right-0.5 w-[15px] h-[15px] rounded-full bg-[#e39600] border-[1.5px] border-white flex items-center justify-center text-white">
                                        <svg class="w-2 h-2 fill-current" viewBox="0 0 16 16"><path d="M4 1h5.5L13 4.5V14a1 1 0 01-1 1H4a1 1 0 01-1-1V2a1 1 0 011-1z"/></svg>
                                    </span>
                                </template>
                                <template x-if="bItem.badge_type === 'message'">
                                    <span class="absolute -top-0.5 -right-0.5 w-[15px] h-[15px] rounded-full bg-[#4a90e2] border-[1.5px] border-white flex items-center justify-center text-white">
                                        <svg class="w-2 h-2 fill-current" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                                    </span>
                                </template>
                            </div>

                            <!-- Text Content -->
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-bold text-[#1d2d35] leading-snug truncate" x-text="bItem.title"></p>
                                <template x-if="bItem.snippet">
                                    <p class="text-[12.5px] text-[#1d2d35] leading-[1.3] line-clamp-2 mt-0.5" x-text="bItem.snippet"></p>
                                </template>
                                <p class="text-[11.5px] text-[#6d6a65] mt-0.5 truncate" x-text="bItem.meta"></p>
                            </div>

                            <!-- Blue Checkmark Circle -->
                            <div class="flex-shrink-0 self-center pl-1">
                                <button type="button"
                                        @click.prevent.stop="bubbledItems.splice(bIdx, 1)"
                                        title="Mark done"
                                        class="w-[18px] h-[18px] rounded-full bg-[#1b6ac9] hover:bg-[#1557a8] text-white inline-flex items-center justify-center cursor-pointer transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </div>
                        </a>
                    </template>
                </div>

                <!-- 3. New for you -->
                <div class="space-y-1 pt-1">
                    <div class="flex items-center justify-between pb-1">
                        <h4 class="text-[12.5px] font-bold text-[#c25e00]">New for you</h4>
                        <button type="button" @click="allRead = true" class="text-[12px] text-[#6d6a65] underline hover:text-[#1d2d35] cursor-pointer">Mark all read</button>
                    </div>

                    <template x-for="(nItem, nIdx) in newForYouList" :key="nItem.id">
                        <a :href="nItem.url"
                           x-show="!pingFilter || (nItem.title + ' ' + nItem.meta + ' ' + (nItem.snippet || '')).toLowerCase().includes(pingFilter.toLowerCase())"
                           class="flex items-start gap-2.5 py-2 px-1.5 -mx-1.5 rounded-lg hover:bg-[#e8e6e1] transition-colors group relative">
                            <!-- Avatar with Sub-badge -->
                            <div class="relative flex-shrink-0 mt-0.5">
                                <span class="w-[34px] h-[34px] rounded-full text-white font-bold text-[13px] flex items-center justify-center"
                                      :class="nItem.bg"
                                      x-text="nItem.initials"></span>
                                <template x-if="nItem.badge_type === 'file'">
                                    <span class="absolute -top-0.5 -right-0.5 w-[15px] h-[15px] rounded-full bg-[#e39600] border-[1.5px] border-white flex items-center justify-center text-white">
                                        <svg class="w-2 h-2 fill-current" viewBox="0 0 16 16"><path d="M4 1h5.5L13 4.5V14a1 1 0 01-1 1H4a1 1 0 01-1-1V2a1 1 0 011-1z"/></svg>
                                    </span>
                                </template>
                                <template x-if="nItem.badge_type !== 'file'">
                                    <span class="absolute -top-0.5 -right-0.5 w-[15px] h-[15px] rounded-full bg-[#4a90e2] border-[1.5px] border-white flex items-center justify-center text-white">
                                        <svg class="w-2 h-2 fill-current" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                                    </span>
                                </template>
                            </div>

                            <!-- Item Details -->
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-bold text-[#1d2d35] leading-snug truncate" x-text="nItem.title"></p>
                                <template x-if="nItem.snippet">
                                    <p class="text-[12.5px] text-[#1d2d35] leading-[1.3] line-clamp-2 mt-0.5" x-text="nItem.snippet"></p>
                                </template>
                                <p class="text-[11.5px] text-[#6d6a65] mt-0.5 truncate" x-text="nItem.meta"></p>
                            </div>

                            <!-- Right: Hover "Bubble up..." Button + Unread Red Dot (Matches media_1793335664784_076d294b.jpg) -->
                            <div class="flex items-center gap-1.5 flex-shrink-0 self-center pl-1 relative">
                                <button type="button"
                                        @click.prevent.stop="bubbleMenuKey = (bubbleMenuKey === nItem.id ? null : nItem.id)"
                                        :class="bubbleMenuKey === nItem.id ? '!opacity-100' : ''"
                                        title="Bubble up..."
                                        class="w-[26px] h-[26px] rounded-[5px] bg-white hover:bg-[#f9f8f6] border border-[#e5e2dc] shadow-[0_1px_2px_rgba(0,0,0,0.08)] flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                                    <svg class="w-[15px] h-[15px] text-[#1b6ac9] fill-current" viewBox="0 0 20 20">
                                        <circle cx="8" cy="10" r="4.2"/>
                                        <circle cx="15" cy="5.5" r="1.7"/>
                                        <circle cx="15" cy="14" r="2.7"/>
                                    </svg>
                                </button>

                                <!-- Bubble up... Options Popover -->
                                <div x-show="bubbleMenuKey === nItem.id"
                                     x-cloak
                                     @click.outside="bubbleMenuKey = null"
                                     @click.prevent.stop
                                     class="absolute right-0 top-[calc(100%+4px)] z-50 w-[168px] bg-white rounded-[8px] border border-[#e5e2dc] shadow-[0_8px_24px_rgba(0,0,0,0.16)] py-1.5 text-left text-[13px]">
                                    <div class="px-3 py-1 text-[11.5px] font-bold text-[#6d6a65] border-b border-[#ece9e2] mb-1">Bubble up...</div>
                                    <button type="button" @click.prevent.stop="bubbleUpFromNew(nIdx)" class="w-full text-left px-3 py-1.5 hover:bg-[#1b6ac9] hover:text-white text-[#1d2d35] font-semibold cursor-pointer">Bubble up now</button>
                                    <button type="button" @click.prevent.stop="bubbleUpFromNew(nIdx)" class="w-full text-left px-3 py-1.5 hover:bg-[#1b6ac9] hover:text-white text-[#1d2d35] cursor-pointer">Tomorrow</button>
                                    <button type="button" @click.prevent.stop="bubbleUpFromNew(nIdx)" class="w-full text-left px-3 py-1.5 hover:bg-[#1b6ac9] hover:text-white text-[#1d2d35] cursor-pointer">Next week</button>
                                    <button type="button" @click.prevent.stop="bubbleUpFromNew(nIdx)" class="w-full text-left px-3 py-1.5 hover:bg-[#1b6ac9] hover:text-white text-[#1d2d35] cursor-pointer">In a month</button>
                                </div>

                                <span x-show="!allRead && nItem.unread" class="w-2.5 h-2.5 rounded-full bg-[#e53935] inline-block flex-shrink-0"></span>
                            </div>
                        </a>
                    </template>

                    <!-- Recent Ping Conversations Below -->
                    @php
                        $recentDrawerPings = \App\Models\ChatMessage::with(['sender', 'recipient'])
                            ->where('type', 'direct')
                            ->orderByDesc('id')
                            ->limit(15)
                            ->get();
                    @endphp
                    @foreach($recentDrawerPings as $ping)
                        @php
                            $pSender = $ping->sender;
                            $pRecip = $ping->recipient;
                            $sName = $pSender?->name ?? 'User';
                            $rName = $pRecip?->name ?? '';
                            $titlePair = ($rName && $rName !== $sName) ? ($sName . ' + ' . $rName) : $sName;
                            $otherUserId = ($ping->sender_id == auth()->id()) ? ($ping->recipient_id ?: $ping->sender_id) : ($ping->sender_id ?: 60);
                            $parts = explode(' ', trim($sName));
                            $in = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                            $pingMeta = ($ping->created_at ? $ping->created_at->format('M j, Y') : '') . ' · Ping';
                        @endphp
                        <a href="{{ route('my.pings.show', $otherUserId) }}"
                           x-show="!pingFilter || '{{ strtolower(addslashes($titlePair . ' ' . $ping->content)) }}'.includes(pingFilter.toLowerCase())"
                           class="flex items-start gap-2.5 py-2 px-1.5 -mx-1.5 rounded-lg hover:bg-[#e8e6e1] transition-colors group relative">
                            <div class="relative flex-shrink-0 mt-0.5">
                                @if($pSender?->avatar_url)
                                    <img src="{{ $pSender->avatar_url }}" alt="{{ $sName }}" class="w-[34px] h-[34px] rounded-full object-cover">
                                @else
                                    <span class="w-[34px] h-[34px] rounded-full bg-[#0d9488] text-white font-bold text-[12px] flex items-center justify-center">{{ $in }}</span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-bold text-[#1d2d35] leading-snug truncate">{{ $titlePair }}</p>
                                <p class="text-[12.5px] text-[#1d2d35] leading-[1.3] line-clamp-1 mt-0.5">{{ $ping->content }}</p>
                                <p class="text-[11.5px] text-[#6d6a65] mt-0.5">{{ $pingMeta }}</p>
                            </div>
                            <div class="flex items-center gap-1.5 flex-shrink-0 self-center pl-1">
                                <button type="button"
                                        @click.prevent.stop="bubbleUpCustom({ initials: '{{ addslashes($in) }}', bg: 'bg-[#0d9488]', title: '{{ addslashes($titlePair) }}', snippet: '{{ addslashes(\Illuminate\Support\Str::limit($ping->content, 90)) }}', meta: '{{ addslashes($pingMeta) }}', url: '{{ route('my.pings.show', $otherUserId) }}' })"
                                        title="Bubble up..."
                                        class="w-[26px] h-[26px] rounded-[5px] bg-white hover:bg-[#f9f8f6] border border-[#e5e2dc] shadow-[0_1px_2px_rgba(0,0,0,0.08)] flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                                    <svg class="w-[15px] h-[15px] text-[#1b6ac9] fill-current" viewBox="0 0 20 20">
                                        <circle cx="8" cy="10" r="4.2"/>
                                        <circle cx="15" cy="5.5" r="1.7"/>
                                        <circle cx="15" cy="14" r="2.7"/>
                                    </svg>
                                </button>
                            </div>
                        </a>
                    @endforeach
                </div>

            </div>

            <!-- Sticky Bottom Bar: Filter... & 🔔 Shhh... / 🔕 Silencing notifications -->
            <div x-show="!activeDrawerPing && !allPingsView" class="px-3.5 py-2 border-t border-[#e5e2dc] bg-[#fbfaf8] flex items-center justify-between gap-2 flex-shrink-0">
                <input type="text" 
                       x-model="pingFilter" 
                       placeholder="Filter..." 
                       class="flex-1 min-w-0 px-3 py-1 bg-white border border-[#d8d4cc] rounded-[6px] text-[13px] text-[#1d2d35] placeholder-[#8c8881] focus:outline-none focus:border-[#1b6ac9]">
                <button type="button"
                        @click="silencingNotifications = !silencingNotifications"
                        :class="silencingNotifications ? 'bg-[#c84b31] hover:bg-[#b33f27] text-white font-bold px-3 py-1.5 rounded-[6px]' : 'bg-transparent hover:bg-black/5 text-[#1d2d35] font-normal px-2.5 py-1 rounded-[6px]'"
                        class="inline-flex items-center gap-1.5 text-[12.5px] transition-colors flex-shrink-0 cursor-pointer whitespace-nowrap">
                    <template x-if="!silencingNotifications">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#1d2d35]" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span>Shhh...</span>
                        </span>
                    </template>
                    <template x-if="silencingNotifications">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3.28 2.22a.75.75 0 00-1.06 1.06l18.5 18.5a.75.75 0 101.06-1.06l-2.34-2.34H19a1 1 0 00.8-1.6l-1.3-1.73V10a6.5 6.5 0 00-5.25-6.38V3a1.25 1.25 0 10-2.5 0v.62c-.86.17-1.67.51-2.38.98L3.28 2.22zM6.5 10c0-.47.05-.93.15-1.37l9.22 9.22H5a1 1 0 01-.8-1.6l1.3-1.73V10zm3.05 9.5a2.5 2.5 0 004.9 0h-4.9z"/>
                            </svg>
                            <span>Silencing notifications</span>
                        </span>
                    </template>
                </button>
            </div>
        </aside>

    <!-- Basecamp User Profile Card Modal (Matches Purple Card Screenshot media_1793277554869_1c31c27d.jpg) -->
    <div x-show="profileModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <!-- Dimmed Backdrop -->
        <div class="fixed inset-0 bg-black/40 transition-opacity" @click="profileModal = false"></div>

        <!-- Purple Profile Card -->
        <div class="relative z-10 w-[324px] rounded-[10px] shadow-2xl px-5 pt-6 pb-5 text-center transition-colors"
             :class="profileUser.isSelf ? 'bg-[#c084fc] text-[#1d2d35]' : 'bg-[#8c3eff] text-white'">
            <!-- Avatar Circle -->
            <div class="relative inline-flex flex-col items-center">
                <template x-if="profileUser.avatar">
                    <img :src="profileUser.avatar" :alt="profileUser.name" class="w-[112px] h-[112px] rounded-full object-cover ring-[8px] ring-white/25 shadow-md">
                </template>
                <template x-if="!profileUser.avatar">
                    <div class="w-[112px] h-[112px] rounded-full flex items-center justify-center text-[42px] font-bold text-white ring-[8px] ring-white/25 shadow-md select-none"
                         :class="profileUser.bg || 'bg-[#00897b]'"
                         x-text="profileUser.initials">
                    </div>
                </template>

                <!-- Role Pill Badge (e.g. Owner / Admin / Client) -->
                <template x-if="profileUser.roleBadge">
                    <span class="-mt-3 relative z-10 px-3 py-0.5 rounded-full text-[12px] font-extrabold leading-tight shadow-xs"
                          :class="profileUser.isSelf ? 'bg-[#d870ad] text-[#1d2d35]' : (profileUser.roleBadge === 'Client' ? 'bg-[#f9d976] text-[#1d2d35]' : 'bg-[#5fd4a2] text-[#0b4f35]')"
                          x-text="profileUser.roleBadge"></span>
                </template>
            </div>

            <!-- Name, Job Title, Location, Local Time & Bio -->
            <h3 class="text-[21px] font-bold tracking-[-0.02em] leading-tight mt-2.5"
                :class="profileUser.isSelf ? 'text-[#1d2d35]' : 'text-white'"
                x-text="profileUser.name"></h3>
            <p class="text-[13px] font-bold leading-snug mt-0.5"
               :class="profileUser.isSelf ? 'text-[#1d2d35]' : 'text-white'"
               x-text="profileUser.jobTitle || 'Team Member at Ajath Infotech Pvt Ltd'"></p>
            <template x-if="profileUser.location">
                <p class="text-[13px] font-normal mt-2"
                   :class="profileUser.isSelf ? 'text-[#1d2d35]/85' : 'text-white/95'"
                   x-text="profileUser.location"></p>
            </template>
            <p class="text-[13px] font-normal mt-0.5"
               :class="profileUser.isSelf ? 'text-[#1d2d35]/85' : 'text-white/95'">{{ now()->timezone('Asia/Kolkata')->format('g i a') }} IST</p>
            <template x-if="profileUser.bio">
                <div>
                    <div class="w-24 h-px bg-white/30 mx-auto my-3"></div>
                    <p class="text-[13.5px] font-normal"
                       :class="profileUser.isSelf ? 'text-[#1d2d35]' : 'text-white/95'"
                       x-text="profileUser.bio"></p>
                </div>
            </template>

            <!-- Action Buttons Stack (Self vs Other Member) -->
            <template x-if="profileUser.isSelf">
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
                            @click="profileModal = false; editProfileModal = true"
                            class="block w-full py-1.5 px-3 rounded-[6px] hover:bg-white/25 font-normal transition-colors cursor-pointer">
                        Edit my profile
                    </button>
                    <a href="{{ route('my.settings', ['ooo' => 1]) }}"
                       class="block w-full py-1.5 px-3 rounded-[6px] hover:bg-white/25 font-normal transition-colors">
                        Set Out of Office
                    </a>
                </div>
            </template>

            <template x-if="!profileUser.isSelf">
                <div class="mt-4 space-y-1">
                    <a :href="'/pings/' + profileUser.id" 
                       class="block w-full py-2 px-3 rounded-[6px] bg-white/25 hover:bg-white/35 text-white text-[13.5px] font-normal transition-colors">
                        Ping <span x-text="profileUser.firstName"></span>
                    </a>
                    <a :href="'/my/activity?user_id=' + profileUser.id" 
                       class="block w-full py-1.5 px-3 rounded-[6px] hover:bg-white/15 text-white text-[13.5px] font-normal transition-colors">
                        See what <span x-text="profileUser.firstName"></span> has been up to
                    </a>
                    <a :href="'/my/tasks?user_id=' + profileUser.id" 
                       class="block w-full py-1.5 px-3 rounded-[6px] hover:bg-white/15 text-white text-[13.5px] font-normal transition-colors">
                        See <span x-text="profileUser.firstName"></span>’s tasks
                    </a>
                    <a :href="profileUser.docsUrl || '/projects/29/docs'" 
                       class="block w-full py-1.5 px-3 rounded-[6px] hover:bg-white/15 text-white text-[13.5px] font-normal transition-colors">
                        See <span x-text="profileUser.firstName"></span>’s recently shared files
                    </a>
                </div>
            </template>
        </div>
    </div>

    <!-- Basecamp Edit My Profile Modal (Exact Match to media_1793277548776_7e98a10d.jpg) -->
    @auth
    <div x-show="editProfileModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/45 transition-opacity" @click="editProfileModal = false"></div>

        <div class="relative z-10 w-[490px] max-w-[95vw] rounded-[12px] bg-white border border-[#e5e2dc] shadow-2xl px-7 pt-5 pb-6 text-[#1d2d35] my-8">
            <form action="{{ route('my.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="remove_avatar" :value="removeAvatarFlag ? 1 : 0">

                <!-- Top Centered Avatar & Controls -->
                <div class="flex flex-col items-center text-center -mt-9">
                    <img src="{{ (Auth::user()->avatar_url && !str_contains(Auth::user()->avatar_url, 'dicebear')) ? Auth::user()->avatar_url : '/uploads/avatars/22369668.png' }}"
                         alt="{{ Auth::user()->name }}"
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

                <!-- Form Fields Matching media_1793277548776_7e98a10d.jpg -->
                <div class="mt-5 space-y-4 text-left">
                    <div>
                        <label class="block text-[14px] font-bold text-[#1d2d35] mb-1">
                            Name <span class="text-[12px] font-normal text-[#6d6a65]">required</span>
                        </label>
                        <input type="text"
                               name="name"
                               required
                               value="{{ Auth::user()->name ?? 'Manjot Singh' }}"
                               class="w-full h-[38px] px-3 rounded-[6px] border border-[#d6d2c9] bg-white text-[14.5px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                    </div>

                    <div>
                        <label class="block text-[14px] font-bold text-[#1d2d35] mb-1">
                            Title at Ajath Infotech Pvt Ltd
                        </label>
                        <input type="text"
                               name="job_title"
                               value="{{ str_replace(' at Ajath Infotech Pvt Ltd', '', Auth::user()->job_title ?? 'Global VP & Partner') }}"
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
                               value="{{ Auth::user()->bio ?? '' }}"
                               class="w-full h-[38px] px-3 rounded-[6px] border border-[#d6d2c9] bg-white text-[14.5px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                    </div>

                    <div>
                        <label class="block text-[14px] font-bold text-[#1d2d35] mb-1">
                            Email address <span class="text-[12px] font-normal text-[#6d6a65]">required</span>
                        </label>
                        <input type="email"
                               name="email"
                               required
                               value="{{ Auth::user()->email ?? 'Manjot@ajath.com' }}"
                               class="w-full h-[38px] px-3 rounded-[6px] border border-[#d6d2c9] bg-white text-[14.5px] text-[#1d2d35] focus:outline-none focus:border-[#1b6ac9]">
                    </div>

                    <div class="pt-1 text-[13.5px] leading-relaxed">
                        <div class="font-bold text-[#1d2d35]">Login info</div>
                        <div class="text-[#1d2d35]">You log in with a password.</div>
                        <a href="{{ route('my.login_option', ['current_user_id' => Auth::id()]) }}" class="text-[#1b6ac9] underline hover:opacity-80">
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
    @endauth

    <!-- Basecamp Document Lightbox Viewer Modal (For Ping & Chat Document Attachments) -->
    <div x-show="pingDocModal" x-cloak class="fixed inset-0 z-[70] flex flex-col bg-[#152026]/95 text-white" role="dialog" aria-modal="true">
        <!-- Top Viewer Bar -->
        <div class="h-14 px-4 sm:px-6 border-b border-white/10 flex items-center justify-between gap-4 flex-shrink-0 bg-[#152026]">
            <div class="flex items-center gap-3 min-w-0">
                <span class="px-2 py-0.5 rounded bg-[#c84b31] text-white text-[10px] font-extrabold uppercase tracking-wider flex-shrink-0">PDF</span>
                <div class="min-w-0">
                    <div class="text-[14.5px] font-bold text-white truncate" x-text="activePingDoc ? activePingDoc.name : 'Document'"></div>
                    <div class="text-[11.5px] text-white/65" x-text="activePingDoc ? activePingDoc.size : ''"></div>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-shrink-0">
                <a :href="activePingDoc ? activePingDoc.url : '#'"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="px-3 py-1.5 rounded-full bg-white/10 hover:bg-white/20 text-[12.5px] font-semibold text-white transition-colors">
                    Open in new tab ↗
                </a>
                <a :href="activePingDoc ? activePingDoc.url : '#'"
                   :download="activePingDoc ? activePingDoc.name : 'document.pdf'"
                   class="px-3.5 py-1.5 rounded-full bg-[#1b6ac9] hover:bg-[#1557a8] text-[12.5px] font-semibold text-white transition-colors">
                    Download
                </a>
                <button type="button"
                        @click="pingDocModal = false"
                        class="ml-1 w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white cursor-pointer transition-colors"
                        title="Close viewer (Esc)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Embedded Document Frame -->
        <div class="flex-1 min-h-0 p-2 sm:p-5 flex items-center justify-center overflow-hidden" @click.self="pingDocModal = false">
            <template x-if="pingDocModal && activePingDoc">
                <iframe :src="activePingDoc.url"
                        class="w-full max-w-[1024px] h-full rounded-[8px] bg-white shadow-2xl border border-white/15"
                        title="Document Viewer"></iframe>
            </template>
        </div>
    </div>

    <script>
        lucide.createIcons();

        window.DRAWER_PINGS_DATA = {
            62: {
                id: 62,
                name: 'Shiv Narayan',
                initials: 'SN',
                bg: 'bg-[#9333ea]',
                avatar: null,
                messages: [
                    { sender: 'them', time: '11:01am', fullTime: 'August 5, 2026 at 11:01am IST', text: 'hii sir good morning' },
                    { sender: 'them', time: '11:02am', fullTime: 'August 5, 2026 at 11:02am IST', text: 'sir apko kaun si id share krni hai @shachish sir ne btaya hai', hideAvatar: true, hideName: true },
                    { sender: 'me', time: '11:04am', fullTime: 'August 5, 2026 at 11:04am IST', text: 'Kal hemant ko apni id login karwayi thi na iske laptop mein' },
                    { sender: 'me', time: '11:04am', fullTime: 'August 5, 2026 at 11:04am IST', text: 'Ya to usda password badal do' },
                    { sender: 'me', time: '11:04am', fullTime: 'August 5, 2026 at 11:04am IST', text: 'Ya doosri id de do fir anitgravity' },
                    { sender: 'them', time: '11:13am', fullTime: 'August 5, 2026 at 11:13am IST', text: 'ok sir' },
                    { sender: 'them', time: '11:25am', fullTime: 'August 5, 2026 at 11:25am IST', text: 'sir i have changed my password please sir add me on family group' }
                ]
            },
            202: {
                id: 202,
                name: 'Kashish',
                initials: 'K',
                bg: 'bg-[#10b981]',
                avatar: null,
                messages: [
                    { sender: 'me', time: '7:20am', fullTime: 'November 10, 2025 at 7:20am IST', text: 'No need to work from home.' },
                    { sender: 'me', time: '7:20am', fullTime: 'November 10, 2025 at 7:20am IST', text: 'Studies are more important. We will manage.' },
                    { sender: 'me', time: '7:20am', fullTime: 'November 10, 2025 at 7:20am IST', text: 'Don’t worry.' },
                    { sender: 'them', time: '7:20am', fullTime: 'November 10, 2025 at 7:20am IST', text: 'Okay sir' },
                    { sender: 'me', time: '7:21am', fullTime: 'November 10, 2025 at 7:21am IST', text: 'Send an email to HR for leave due to exams.' },
                    { sender: 'them', time: '7:22am', fullTime: 'November 10, 2025 at 7:22am IST', text: 'Ok sir' },
                    {
                        sender: 'them',
                        time: '4:15pm',
                        fullTime: 'December 3, 2025 at 4:15pm IST',
                        text: 'Commercial bid.docx (1).pdf (285 KB)',
                        file: {
                            name: 'Commercial bid.docx (1).pdf',
                            size: '285 KB',
                            type: 'PDF Document',
                            url: '/uploads/docs/Commercial%20bid.docx%20(1).pdf?v=3',
                            thumb: '/uploads/docs/thumb_commercial_bid.png'
                        }
                    }
                ]
            },
            153: {
                id: 153,
                name: 'Tanu Shree',
                initials: 'TS',
                bg: 'bg-[#eab308]',
                avatar: null,
                messages: [
                    { sender: 'me', time: '12:51pm', fullTime: 'November 12, 2025 at 12:51pm IST', text: 'Current CTC, expeditions and notice period?' },
                    { sender: 'them', time: '1:06pm', fullTime: 'November 12, 2025 at 1:06pm IST', text: 'Candidate name : Akshay Chauhan | Total Experience : 1 Year 3 Months | Current In Hand : 19k | Expected : 30k | Notice period : 15 Days' },
                    { sender: 'them', time: '1:06pm', fullTime: 'November 12, 2025 at 1:06pm IST', text: 'Candidate Name: Ankush Balhara | Total Experience: 1.3 years | Current In-Hand: 25k | Expected: 30k | Notice Period: Immediate', hideAvatar: true, hideName: true },
                    { sender: 'me', time: '4:53pm', fullTime: 'November 12, 2025 at 4:53pm IST', text: '9899576211' },
                    { sender: 'me', time: '4:54pm', fullTime: 'November 12, 2025 at 4:54pm IST', text: 'Nitin sharma - rkgit' }
                ]
            },
            146: {
                id: 146,
                name: 'Anushka Garg',
                initials: 'AG',
                bg: 'bg-[#ea580c]',
                avatar: null,
                messages: [
                    {
                        sender: 'them',
                        time: '2:29pm',
                        fullTime: 'November 3, 2025 at 2:29pm IST',
                        text: 'TritheshJainResume.pdf (405 KB) — this is for java developer',
                        caption: 'this is for java developer',
                        file: {
                            name: 'TritheshJainResume.pdf',
                            size: '405 KB',
                            type: 'PDF Document',
                            url: '/uploads/docs/TritheshJainResume.pdf?v=3',
                            thumb: '/uploads/docs/thumb_updc_technical.png'
                        }
                    },
                    { sender: 'them', time: '1:50pm', fullTime: 'November 6, 2025 at 1:50pm IST', text: 'sir when we can conduct the interview for college students?' },
                    { sender: 'me', time: '11:02am', fullTime: 'November 12, 2025 at 11:02am IST', text: 'Kindly schedule for tomorrow 12-4 pm' },
                    { sender: 'them', time: '11:03am', fullTime: 'November 12, 2025 at 11:03am IST', text: 'Okay sir' }
                ]
            },
            60: {
                id: 60,
                name: 'Shachish Sneh',
                initials: 'SS',
                bg: 'bg-[#4f46e5]',
                avatar: '/uploads/avatars/25036340.png',
                messages: [
                    { sender: 'them', time: '9:44pm', fullTime: 'October 2026 at 9:44pm IST', text: 'ohhh accha' },
                    { sender: 'me', time: '9:44pm', fullTime: 'October 2026 at 9:44pm IST', text: 'Info@ajath.in chal Rahi ha?' },
                    { sender: 'me', time: '9:44pm', fullTime: 'October 2026 at 9:44pm IST', text: 'Password Dena bounce kar hai hai' },
                    { sender: 'them', time: '9:54pm', fullTime: 'October 2026 at 9:54pm IST', text: 'kiska?' },
                    { sender: 'them', time: '9:56pm', fullTime: 'October 2026 at 9:56pm IST', text: 'P@ssw0rd1111', hideAvatar: true, hideName: true },
                    { sender: 'them', time: '9:57pm', fullTime: 'October 2026 at 9:57pm IST', text: 'yes', hideAvatar: true, hideName: true }
                ]
            },
            147: {
                id: 147,
                name: 'Nikhil Aneja',
                initials: 'NA',
                bg: 'bg-[#0284c7]',
                avatar: '/uploads/avatars/48443395.png',
                removed: true,
                messages: [
                    {
                        sender: 'them',
                        time: '6:22pm',
                        fullTime: 'August 5, 2025 at 6:22pm IST',
                        text: 'Video streaming app proposal.pdf (1.32 MB)',
                        coverFile: {
                            name: 'Video streaming app proposal.pdf',
                            size: '1.32 MB',
                            type: 'PDF Document',
                            url: '/uploads/docs/1791962446.pdf?v=3',
                            thumb: '/uploads/docs/thumb_1791962446.png'
                        }
                    },
                    {
                        sender: 'them',
                        dateDivider: 'WEDNESDAY, SEPTEMBER 10 2025',
                        time: '4:49pm',
                        fullTime: 'September 10, 2025 at 4:49pm IST',
                        text: 'itramanmcd@gmail.com',
                        email: 'itramanmcd@gmail.com'
                    }
                ]
            },
            148: {
                id: 148,
                name: 'Nirdesh Verma',
                initials: 'NV',
                bg: 'bg-[#0d9488]',
                avatar: '/uploads/avatars/48060513.png',
                messages: [
                    { sender: 'them', time: '10:15am', fullTime: 'September 10, 2025 at 10:15am IST', text: 'https://docs.google.com/spreadsheets/d/1x9K_vQpL7m3N8a2B4c6D/edit', linkUrl: 'https://docs.google.com/spreadsheets/d/1x9K_vQpL7m3N8a2B4c6D/edit' },
                    { sender: 'me', time: '10:18am', fullTime: 'September 10, 2025 at 10:18am IST', text: 'Okay, make sure QA verifies all flows today.' }
                ]
            },
            149: {
                id: 149,
                name: 'Rudra Banger',
                initials: 'RB',
                bg: 'bg-[#92400e]',
                avatar: null,
                messages: [
                    { sender: 'them', time: '11:30am', fullTime: 'September 8, 2025 at 11:30am IST', text: 'Sir meeting room mein aana hai?' },
                    { sender: 'me', time: '11:31am', fullTime: 'September 8, 2025 at 11:31am IST', text: 'Hnji aajao' }
                ]
            }
        };

        window.BC_USERS_BY_INITIALS = {
            'E': { id: 26, firstName: 'Edward', initials: 'E', bg: 'bg-[#e0522b]', name: 'Edward', roleBadge: 'Client', jobTitle: 'Project Owner at NDC Homes', avatar: null, docsUrl: '/projects/26/docs' },
            'RO': { id: 53, firstName: 'Rishabh', initials: 'RO', bg: 'bg-[#00796b]', name: 'Rishabh Ojha', roleBadge: '', jobTitle: 'Web Developer at Ajath Infotech Pvt Ltd', avatar: null, docsUrl: '/projects/26/docs' },
            'RC': { id: 53, firstName: 'Rishabh', initials: 'RO', bg: 'bg-[#00796b]', name: 'Rishabh Ojha', roleBadge: '', jobTitle: 'Web Developer at Ajath Infotech Pvt Ltd', avatar: null, docsUrl: '/projects/26/docs' },
            'PK': { id: 45, firstName: 'Pawandeep', initials: 'PK', bg: 'bg-[#00897b]', name: 'Pawandeep Kaur', roleBadge: 'Admin', jobTitle: 'Manager at Ajath Infotech Pvt Ltd', avatar: null, docsUrl: '/projects/29/docs' },
            'GK': { id: 28, firstName: 'Gaurav', initials: 'GK', bg: 'bg-[#9333ea]', name: 'Gaurav Khulbey', roleBadge: '', jobTitle: 'Cybersecurity Expert at Ajath Infotech Pvt Ltd', avatar: null, docsUrl: '/projects/4/docs' },
            'HK': { id: 32, firstName: 'Harshvardhan', initials: 'HK', bg: 'bg-[#c026d3]', name: 'Harshvardhan Singh', roleBadge: '', jobTitle: 'AI Consultant', avatar: '/uploads/avatars/47630095.png', docsUrl: '/projects/4/docs' },
            'HS': { id: 32, firstName: 'Harshvardhan', initials: 'HS', bg: 'bg-[#84cc16]', name: 'Harshvardhan Singh', roleBadge: '', jobTitle: 'AI Consultant', avatar: '/uploads/avatars/47630095.png', docsUrl: '/projects/4/docs' },
            'RK': { id: 46, firstName: 'Rajesh', initials: 'RK', bg: 'bg-[#db2777]', name: 'Rajesh Kumar', roleBadge: 'Admin', jobTitle: 'Project Lead at Ajath Infotech Pvt Ltd', avatar: null, docsUrl: '/projects/4/docs' },
            'SK': { id: 60, firstName: 'Shachish', initials: 'SS', bg: 'bg-[#4f46e5]', name: 'Shachish Sneh', roleBadge: 'Admin', jobTitle: 'Solution Architect & Technology Master at Ajath Infotech Pvt Ltd', location: 'New Delhi', bio: 'Pain is temporary Pride is forever', avatar: '/uploads/avatars/25036340.png', docsUrl: '/projects/4/docs' },
            'SS': { id: 60, firstName: 'Shachish', initials: 'SS', bg: 'bg-[#4f46e5]', name: 'Shachish Sneh', roleBadge: 'Admin', jobTitle: 'Solution Architect & Technology Master at Ajath Infotech Pvt Ltd', location: 'New Delhi', bio: 'Pain is temporary Pride is forever', avatar: '/uploads/avatars/25036340.png', docsUrl: '/projects/4/docs' },
            'AI': { id: 5, firstName: 'Ajath', initials: 'AI', bg: 'bg-[#0d9488]', name: 'Ajath Infotech', roleBadge: 'Admin', jobTitle: 'Ajath Infotech Pvt Ltd', avatar: null, docsUrl: '/projects/4/docs' },
            'M': { id: 1, isSelf: true, firstName: 'Manjot', initials: 'M', bg: 'bg-[#1d4ed8]', name: 'Manjot Singh', roleBadge: 'Owner', jobTitle: 'Global VP & Partner at Ajath Infotech Pvt Ltd', avatar: '/uploads/avatars/22369668.png', docsUrl: '/projects/4/docs' },
            'MS': { id: 1, isSelf: true, firstName: 'Manjot', initials: 'MS', bg: 'bg-[#1d4ed8]', name: 'Manjot Singh', roleBadge: 'Owner', jobTitle: 'Global VP & Partner at Ajath Infotech Pvt Ltd', avatar: '/uploads/avatars/22369668.png', docsUrl: '/projects/4/docs' },
            'DMP': { id: 43, firstName: 'Dr. Muaad', initials: 'DMP', bg: 'bg-[#1e293b]', name: 'Dr. Muaad Alahmadi', roleBadge: 'Client', jobTitle: 'Product Owner', avatar: null, docsUrl: '/projects/8/docs' },
            'DMON': { id: 23, firstName: 'Doug', initials: 'DMON', bg: 'bg-[#ea580c]', name: 'Doug Morneau', roleBadge: 'Client', jobTitle: 'Product Owner', avatar: null, docsUrl: '/projects/4/docs' },
            'SR': { id: 66, firstName: 'Srijita', initials: 'SR', bg: 'bg-[#7c3aed]', name: 'Srijita Roy', roleBadge: '', jobTitle: 'AI Consultant', avatar: null, docsUrl: '/projects/4/docs' },
            'A': { id: 4, firstName: 'AJ', initials: 'A', bg: 'bg-[#e11d48]', name: 'AJ', roleBadge: 'Client', jobTitle: 'Product Owner', avatar: null, docsUrl: '/projects/4/docs' },
            'SN': { id: 62, firstName: 'Shiv', initials: 'SN', bg: 'bg-[#9333ea]', name: 'Shiv Narayan', roleBadge: '', jobTitle: 'Full Stack Developer at Ajath Infotech Pvt Ltd', avatar: null, docsUrl: '/projects/4/docs' },
            'NV': { id: 148, firstName: 'Nirdesh', initials: 'NV', bg: 'bg-[#0d9488]', name: 'Nirdesh Verma', roleBadge: '', jobTitle: 'QA Engineer at Borgfy', avatar: '/uploads/avatars/48060513.png', docsUrl: '/projects/4/docs' },
            'NA': { id: 147, firstName: 'Nikhil', initials: 'NA', bg: 'bg-[#0284c7]', name: 'Nikhil Aneja', roleBadge: '', jobTitle: 'Recruitment Specialist at ITG', avatar: '/uploads/avatars/48443395.png', docsUrl: '/projects/4/docs' },
            'P': { id: 48, firstName: 'Prashant', initials: 'P', bg: 'bg-[#2563eb]', name: 'Prashant', roleBadge: '', jobTitle: 'Analyst at Ajath Infotech Pvt Ltd', avatar: '/uploads/avatars/22369742.png', docsUrl: '/projects/26/docs' },
            'AG': { id: 146, firstName: 'Ankit', initials: 'AG', bg: 'bg-[#d946ef]', name: 'Ankit G.', roleBadge: '', jobTitle: 'Business Development at Ajath Infotech Pvt Ltd', avatar: null, docsUrl: '/projects/5/docs' },
            'S': { id: 45, firstName: 'Shubham', initials: 'S', bg: 'bg-[#2ebd85]', name: 'Shubham', roleBadge: '', jobTitle: 'Sn. Php developer at Ajath Infotech Pvt Ltd', avatar: null, docsUrl: '/projects/48/docs' },
            'RB': { id: 149, firstName: 'Rudra', initials: 'RB', bg: 'bg-[#92400e]', name: 'Rudra Banger', roleBadge: '', jobTitle: 'Team Member at Ajath Infotech Pvt Ltd', avatar: null, docsUrl: '/projects/4/docs' },
            'TS': { id: 153, firstName: 'Tanu', initials: 'TS', bg: 'bg-[#eab308]', name: 'Tanu Shree', roleBadge: '', jobTitle: 'HR Executive at Ajath Infotech Pvt Ltd', avatar: null, docsUrl: '/projects/4/docs' },
            'K': { id: 202, firstName: 'Kashish', initials: 'K', bg: 'bg-[#10b981]', name: 'Kashish', roleBadge: '', jobTitle: 'Business Analyst at Ajath Infotech Pvt Ltd', avatar: null, docsUrl: '/projects/4/docs' }
        };

        window.openUserProfileModal = function(u) {
            if (!u) return;
            const mapped = {
                id: u.id || 45,
                isSelf: !!u.isSelf || u.id == 1 || /manjot/i.test(u.name || ''),
                firstName: u.firstName || (u.name ? u.name.split(' ')[0] : 'Member'),
                initials: u.initials || 'TM',
                bg: u.bg || 'bg-[#00897b]',
                name: u.name || 'Team Member',
                roleBadge: u.roleBadge || (u.role === 'Owner' || u.role === 'Admin' || u.role === 'Client' ? u.role : ''),
                jobTitle: u.jobTitle || u.title || 'Team Member at Ajath Infotech Pvt Ltd',
                location: u.location || '',
                bio: u.bio || '',
                avatar: u.avatar || null,
                docsUrl: u.docsUrl || '/projects/4/docs'
            };
            window.dispatchEvent(new CustomEvent('open-user-profile', { detail: mapped }));
        };

        window.resolveBcUserFromElement = function(el) {
            if (!el) return null;
            const img = el.tagName === 'IMG' ? el : el.querySelector('img');
            if (img) {
                const src = (img.getAttribute('src') || '');
                const alt = (img.getAttribute('alt') || img.getAttribute('title') || el.getAttribute('title') || '').trim();
                if (src.includes('22369668') || src.includes('avatar_1_') || /manjot/i.test(alt) || alt === 'Me') {
                    return window.BC_USERS_BY_INITIALS['M'];
                }
                if (src.includes('25036340') || src.includes('22369672') || src.includes('22369669') || /shachish/i.test(alt)) {
                    return window.BC_USERS_BY_INITIALS['SK'];
                }
                if (src.includes('22369742') || src.includes('22369670') || /prashant/i.test(alt)) {
                    return window.BC_USERS_BY_INITIALS['P'];
                }
                if (src.includes('52829137') || src.includes('49471926') || /edward/i.test(alt)) {
                    return window.BC_USERS_BY_INITIALS['E'];
                }
                if (src.includes('53122994') || src.includes('39059742') || /gaurav/i.test(alt)) {
                    return window.BC_USERS_BY_INITIALS['GK'];
                }
                if (src.includes('23053551') || /pawandeep/i.test(alt)) {
                    return window.BC_USERS_BY_INITIALS['PK'];
                }
                if (src.includes('35685578') || src.includes('48563099') || /rajesh/i.test(alt)) {
                    return window.BC_USERS_BY_INITIALS['RK'];
                }
                if (src.includes('51410494') || /rishabh/i.test(alt)) {
                    return window.BC_USERS_BY_INITIALS['RO'];
                }
                if (src.includes('52873395') || /shiv/i.test(alt)) {
                    return window.BC_USERS_BY_INITIALS['SN'];
                }
                if (src.includes('47630095') || /harshvardhan/i.test(alt)) {
                    return window.BC_USERS_BY_INITIALS['HK'];
                }
                if (src.includes('48060513') || /nirdesh/i.test(alt)) {
                    return window.BC_USERS_BY_INITIALS['NV'];
                }
                if (src.includes('48443395') || /nikhil/i.test(alt)) {
                    return window.BC_USERS_BY_INITIALS['NA'];
                }
                // Match by alt name against known users
                for (const key in window.BC_USERS_BY_INITIALS) {
                    const u = window.BC_USERS_BY_INITIALS[key];
                    if (alt && (u.name.toLowerCase() === alt.toLowerCase() || alt.toLowerCase().includes(u.firstName.toLowerCase()))) {
                        return u;
                    }
                }
                if (src.includes('/uploads/avatars/') || alt) {
                    const displayName = (alt && alt !== 'Member' && alt !== 'Avatar') ? alt : 'Team Member';
                    const first = displayName.split(' ')[0] || 'Member';
                    const init = displayName.split(' ').map(p => p[0]).join('').toUpperCase().slice(0, 2) || 'TM';
                    return { id: 45, firstName: first, initials: init, bg: 'bg-[#00897b]', name: displayName, roleBadge: '', jobTitle: 'Team Member at Ajath Infotech Pvt Ltd', avatar: src || null, docsUrl: '/projects/4/docs' };
                }
            }

            // Otherwise check initials text
            if (el.querySelector('svg')) return null;
            const rawText = (el.textContent || '').replace(/OUT$/i, '').trim().toUpperCase();
            if (window.BC_USERS_BY_INITIALS[rawText]) {
                return window.BC_USERS_BY_INITIALS[rawText];
            }
            if (/^[A-Z]{1,4}$/.test(rawText)) {
                const titleAttr = (el.getAttribute('title') || '').trim();
                const displayName = titleAttr || rawText;
                const first = displayName.split(' ')[0] || rawText;
                return { id: 45, firstName: first, initials: rawText, bg: 'bg-[#00897b]', name: displayName, roleBadge: '', jobTitle: 'Team Member at Ajath Infotech Pvt Ltd', avatar: null, docsUrl: '/projects/4/docs' };
            }
            return null;
        };

        // Ensure Manjot's avatar always uses 22369668.png and displays the yellow OUT badge when Out of Office is active
        window.decorateManjotOooAvatars = function() {
            const oooActive = localStorage.getItem('bc_ooo_active') !== '0';
            document.querySelectorAll('img').forEach(function(img) {
                const src = img.getAttribute('src') || '';
                if (src.includes('avatar_1_1793286396')) {
                    img.setAttribute('src', '/uploads/avatars/22369668.png');
                }
                const updatedSrc = img.getAttribute('src') || '';
                const isManjot = updatedSrc.includes('22369668.png');
                if (!isManjot) return;
                img.style.cursor = 'pointer';
                // Skip large 104px profile modal avatar
                if (img.classList.contains('w-[104px]')) return;
                const parent = img.parentElement;
                if (!parent) return;
                if (parent.classList.contains('rounded-full') && parent.classList.contains('overflow-hidden')) {
                    if (getComputedStyle(parent).position === 'static') {
                        parent.classList.add('relative');
                    }
                    let badge = parent.querySelector('.bc-ooo-badge');
                    if (oooActive && !badge && !parent.querySelector('span.bg-\\[\\#facc15\\]')) {
                        const rect = parent.getBoundingClientRect();
                        const fontSize = (rect.width && rect.width <= 20) ? '4.8px' : ((rect.width && rect.width <= 28) ? '5.8px' : '6.8px');
                        badge = document.createElement('span');
                        badge.className = 'bc-ooo-badge absolute bottom-0 inset-x-0 h-[36%] bg-[#facc15] flex items-center justify-center pointer-events-none';
                        badge.innerHTML = '<span style="font-size:' + fontSize + '" class="font-black text-[#1d1c1b] leading-none tracking-tight">OUT</span>';
                        parent.appendChild(badge);
                    } else if (!oooActive && badge) {
                        badge.remove();
                    }
                } else if (img.classList.contains('rounded-full') && !parent.classList.contains('bc-avatar-ooo-wrap')) {
                    if (!oooActive) return;
                    const wrap = document.createElement('span');
                    wrap.className = 'bc-avatar-ooo-wrap relative inline-flex rounded-full overflow-hidden flex-shrink-0 cursor-pointer';
                    const sizeClasses = ['w-4','h-4','w-5','h-5','w-6','h-6','w-7','h-7','w-8','h-8','w-9','h-9','w-10','h-10','w-[16px]','h-[16px]','w-[18px]','h-[18px]','w-[20px]','h-[20px]','w-[22px]','h-[22px]','w-[24px]','h-[24px]','w-[26px]','h-[26px]','w-[28px]','h-[28px]','w-[30px]','h-[30px]','w-[32px]','h-[32px]','w-[34px]','h-[34px]','w-[36px]','h-[36px]','w-[40px]','h-[40px]','w-[42px]','h-[42px]','w-[44px]','h-[44px]','mt-0.5','-ml-1','border','border-white'];
                    sizeClasses.forEach(c => {
                        if (img.classList.contains(c)) {
                            wrap.classList.add(c);
                        }
                    });
                    img.parentNode.insertBefore(wrap, img);
                    wrap.appendChild(img);
                    img.className = 'w-full h-full object-cover';
                    const rect = wrap.getBoundingClientRect();
                    const fontSize = (rect.width && rect.width <= 20) ? '4.8px' : ((rect.width && rect.width <= 28) ? '5.8px' : '6.8px');
                    const badge = document.createElement('span');
                    badge.className = 'bc-ooo-badge absolute bottom-0 inset-x-0 h-[36%] bg-[#facc15] flex items-center justify-center pointer-events-none';
                    badge.innerHTML = '<span style="font-size:' + fontSize + '" class="font-black text-[#1d1c1b] leading-none tracking-tight">OUT</span>';
                    wrap.appendChild(badge);
                }
            });
        };

        document.addEventListener('DOMContentLoaded', function() {
            window.decorateManjotOooAvatars();
            setTimeout(window.decorateManjotOooAvatars, 250);
        });

        // Global delegate so tapping ANY DP/avatar badge across any page opens the User Profile Modal
        document.addEventListener('click', function(e) {
            const target = e.target;
            if (!target || !target.closest) return;
            // Skip bottom-left My Account dock button, project people setup bar, Pings & Notifications drawer, Pings pages, Boost pills, and Profile Modal itself
            if (
                target.closest('[title="My Account"]') ||
                target.closest('[title="Set up who\'s on this project"]') ||
                target.closest('a[href*="/people"]') ||
                target.closest('[role="dialog"]') ||
                target.closest('[aria-label="Pings and Notifications"]') ||
                target.closest('aside[role="complementary"]') ||
                target.closest('[title="Click to remove boost"]') ||
                target.closest('button[data-user-badge]') ||
                window.location.pathname.startsWith('/my/pings') ||
                window.location.pathname.startsWith('/pings')
            ) return;

            const avatarEl = target.closest('img.rounded-full, img[src*="/uploads/avatars/"], span.rounded-full, div.rounded-full');
            if (!avatarEl) return;

            const resolvedUser = window.resolveBcUserFromElement(avatarEl);
            if (resolvedUser) {
                e.preventDefault();
                e.stopPropagation();
                window.dispatchEvent(new CustomEvent('open-user-profile', { detail: resolvedUser }));
            }
        }, true);

        // Global Basecamp Bookmark Toggle & State Sync
        window.applyBookmarkVisual = function(btn, isBookmarked) {
            if (!btn) return;
            const labelEl = btn.querySelector('[data-bookmark-label]') || btn.querySelector('span');
            if (labelEl) {
                labelEl.textContent = isBookmarked ? 'Bookmarked' : 'Bookmark';
            }
            const svgEl = btn.querySelector('svg');
            if (svgEl) {
                svgEl.setAttribute('fill', isBookmarked ? 'currentColor' : 'none');
            }
        };

        window.syncBookmarkButtons = function() {
            let list = [];
            try {
                list = JSON.parse(localStorage.getItem('bc_bookmarks') || '[]');
            } catch (e) {}
            const currentUrl = window.location.pathname + window.location.search;
            const isBookmarked = list.some(function(b) { return b && b.url === currentUrl; });
            document.querySelectorAll('[data-bookmark-label]').forEach(function(span) {
                window.applyBookmarkVisual(span.closest('button'), isBookmarked);
            });
        };

        window.toggleBookmark = function(btn) {
            const currentUrl = window.location.pathname + window.location.search;
            const h1 = document.querySelector('main h1');
            const title = (h1 ? h1.innerText : document.title.split(' - ')[0]).trim();
            const projLink = document.querySelector('main a[href*="/projects/"]');
            const project = (projLink ? projLink.innerText : 'Tender').trim();
            const author = (currentUrl.includes('/projects/29') || title.includes('GEM/')) ? 'Pawandeep Kaur' : 'Manjot Singh';
            const now = new Date();
            let hours = now.getHours();
            const mins = String(now.getMinutes()).padStart(2, '0');
            const ampm = hours >= 12 ? 'pm' : 'am';
            hours = hours % 12 || 12;
            const savedAt = 'Saved today ' + hours + ':' + mins + ampm;

            let list = [];
            try {
                list = JSON.parse(localStorage.getItem('bc_bookmarks') || '[]');
            } catch (e) {}
            const existingIdx = list.findIndex(function(b) { return b && b.url === currentUrl; });
            let isNowBookmarked = false;
            if (existingIdx !== -1) {
                list.splice(existingIdx, 1);
                isNowBookmarked = false;
            } else {
                list.unshift({
                    url: currentUrl,
                    title: title,
                    project: project,
                    author: author,
                    type: 'folder',
                    savedAt: savedAt,
                    timestamp: Date.now()
                });
                isNowBookmarked = true;
            }
            try {
                localStorage.setItem('bc_bookmarks', JSON.stringify(list));
            } catch (e) {}

            window.applyBookmarkVisual(btn, isNowBookmarked);

            const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
            fetch('/my/bookmarks/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': token
                },
                body: new URLSearchParams({
                    url: currentUrl,
                    title: title,
                    project: project,
                    author: author,
                    type: 'folder',
                    savedAt: savedAt,
                    _token: token
                })
            }).catch(function() {});
        };

        document.addEventListener('DOMContentLoaded', window.syncBookmarkButtons);
        window.syncBookmarkButtons();

        // =========================================================================
        // Universal Basecamp @Mention Autocomplete Engine (Matches media_1793346439836_4b9bbafa.jpg 1:1)
        // =========================================================================
        (function () {
            const MENTION_PEOPLE = [
                { id: 'group_dm', name: 'Delivery Managers (1)', insertName: 'Delivery Managers', isGroup: true, initials: 'DM', bg: '#1d2d35', avatar: null, ooo: null },
                { id: 4, name: 'Gaurav Khulbey', insertName: 'Gaurav Khulbey', initials: 'GK', bg: '#e879f9', avatar: null, ooo: null },
                { id: 1, name: 'Manjot Singh', insertName: 'Manjot Singh', initials: 'MS', bg: '#1d4ed8', avatar: '/uploads/avatars/22369668.png', ooo: 'OUT (back on Nov 6)' },
                { id: 2, name: 'Pawandeep Kaur', insertName: 'Pawandeep Kaur', initials: 'PK', bg: '#00897b', avatar: null, ooo: null },
                { id: 3, name: 'Prashant', insertName: 'Prashant', initials: 'P', bg: '#0284c7', avatar: '/uploads/avatars/22369670.png', ooo: null },
                { id: 5, name: 'Rajesh Kumar', insertName: 'Rajesh Kumar', initials: 'RK', bg: '#e879f9', avatar: null, ooo: null },
                { id: 6, name: 'Rishabh Ojha', insertName: 'Rishabh Ojha', initials: 'RO', bg: '#00897b', avatar: null, ooo: null },
                { id: 60, name: 'Shachish Sneh', insertName: 'Shachish Sneh', initials: 'SS', bg: '#00a884', avatar: '/uploads/avatars/25036340.png', ooo: null },
                { id: 62, name: 'Shiv Narayan', insertName: 'Shiv Narayan', initials: 'SN', bg: '#9333ea', avatar: null, ooo: null },
                { id: 47, name: 'Harshvardhan Singh', insertName: 'Harshvardhan Singh', initials: 'HS', bg: '#c026d3', avatar: '/uploads/avatars/47630095.png', ooo: null },
                { id: 148, name: 'Nirdesh Verma', insertName: 'Nirdesh Verma', initials: 'NV', bg: '#0d9488', avatar: '/uploads/avatars/48060513.png', ooo: null },
                { id: 147, name: 'Nikhil Aneja', insertName: 'Nikhil Aneja', initials: 'NA', bg: '#0284c7', avatar: '/uploads/avatars/48443395.png', ooo: null }
            ];

            let menuEl = null;
            let activeField = null;
            let mentionStartIdx = -1;
            let filteredList = [];
            let selectedIdx = 0;

            function ensureMenu() {
                if (menuEl) return menuEl;
                menuEl = document.createElement('div');
                menuEl.id = 'bc-global-mention-menu';
                menuEl.style.cssText = 'position:fixed;z-index:9999;display:none;width:248px;max-height:218px;overflow-y:auto;background:#ffffff;border:1px solid #d8d4cc;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.14);padding:4px;font-family:inherit;';
                document.body.appendChild(menuEl);
                return menuEl;
            }

            function hideMenu() {
                if (menuEl) menuEl.style.display = 'none';
                activeField = null;
                mentionStartIdx = -1;
                filteredList = [];
                selectedIdx = 0;
            }

            function renderMenu() {
                const m = ensureMenu();
                if (!filteredList.length) {
                    hideMenu();
                    return;
                }
                let html = '';
                filteredList.forEach(function (p, idx) {
                    const isSel = idx === selectedIdx;
                    const rowBg = isSel ? '#1b6ac9' : 'transparent';
                    const nameColor = isSel ? '#ffffff' : '#1d2d35';
                    const oooColor = isSel ? 'rgba(255,255,255,0.85)' : '#8c8881';

                    let avatarHtml = '';
                    if (p.isGroup) {
                        avatarHtml = '<span style="width:24px;height:24px;border-radius:9999px;background:#1d2d35;color:#fff;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid rgba(255,255,255,0.35);">'
                            + '<svg style="width:14px;height:14px;" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>'
                            + '</span>';
                    } else if (p.avatar) {
                        const outBadge = p.ooo
                            ? '<span style="position:absolute;bottom:-2px;left:50%;transform:translateX(-50%);background:#ffd500;color:#1d2d35;font-size:6.5px;font-weight:900;padding:0 3px;border-radius:2px;line-height:1.2;">OUT</span>'
                            : '';
                        avatarHtml = '<span style="position:relative;width:24px;height:24px;display:inline-flex;flex-shrink:0;">'
                            + '<img src="' + p.avatar + '" alt="" style="width:24px;height:24px;border-radius:9999px;object-fit:cover;">'
                            + outBadge
                            + '</span>';
                    } else {
                        avatarHtml = '<span style="width:24px;height:24px;border-radius:9999px;background:' + p.bg + ';color:#fff;font-weight:700;font-size:10px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">' + p.initials + '</span>';
                    }

                    const oooText = p.ooo
                        ? '<span style="font-size:11px;font-weight:400;color:' + oooColor + ';margin-left:5px;white-space:nowrap;">' + p.ooo + '</span>'
                        : '';

                    html += '<button type="button" data-mention-idx="' + idx + '" style="width:100%;display:flex;align-items:center;gap:7px;padding:5px 8px;border:0;border-radius:5px;background:' + rowBg + ';text-align:left;cursor:pointer;">'
                        + avatarHtml
                        + '<div style="min-width:0;flex:1;display:flex;align-items:baseline;overflow:hidden;">'
                        + '<span style="font-size:13.5px;font-weight:500;color:' + nameColor + ';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + p.name + '</span>'
                        + oooText
                        + '</div>'
                        + '</button>';
                });
                m.innerHTML = html;
                m.querySelectorAll('button[data-mention-idx]').forEach(function (btn) {
                    btn.addEventListener('mouseenter', function () {
                        const i = parseInt(btn.getAttribute('data-mention-idx'), 10);
                        if (!isNaN(i) && i !== selectedIdx) {
                            selectedIdx = i;
                            renderMenu();
                        }
                    });
                    btn.addEventListener('mousedown', function (ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        const i = parseInt(btn.getAttribute('data-mention-idx'), 10);
                        if (filteredList[i]) {
                            insertMention(filteredList[i]);
                        }
                    });
                });
            }

            function positionMenu(el) {
                const m = ensureMenu();
                m.style.visibility = 'hidden';
                m.style.display = 'block';
                const menuH = m.offsetHeight || 218;
                const rect = el.getBoundingClientRect();
                let top = rect.top + 34;
                let left = rect.left + 8;
                if (rect.bottom + menuH + 16 > window.innerHeight || el.getAttribute('name') === 'content') {
                    top = Math.max(12, rect.top - menuH - 8);
                    left = Math.max(12, rect.left + 8);
                }
                if (left + 260 > window.innerWidth) {
                    left = Math.max(12, window.innerWidth - 265);
                }
                m.style.top = top + 'px';
                m.style.left = left + 'px';
                m.style.visibility = 'visible';
                m.style.display = 'block';
            }

            function insertMention(person) {
                if (!activeField || !person) return;
                if (activeField.tagName === 'TEXTAREA' || activeField.tagName === 'INPUT') {
                    const val = activeField.value || '';
                    const caret = typeof activeField.selectionStart === 'number' ? activeField.selectionStart : val.length;
                    const before = val.slice(0, mentionStartIdx);
                    const after = val.slice(caret);
                    const replacement = '@' + (person.insertName || person.name) + ' ';
                    activeField.value = before + replacement + after;
                    const newCaret = before.length + replacement.length;
                    activeField.focus();
                    try {
                        activeField.setSelectionRange(newCaret, newCaret);
                    } catch (e) {}
                    activeField.dispatchEvent(new Event('input', { bubbles: true }));
                }
                hideMenu();
            }

            function handleFieldInput(el) {
                if (!el || (el.tagName !== 'TEXTAREA' && el.tagName !== 'INPUT')) return;
                const val = el.value || '';
                const caret = typeof el.selectionStart === 'number' ? el.selectionStart : val.length;
                const textBeforeCaret = val.slice(0, caret);
                const match = textBeforeCaret.match(/(?:^|\s)@([a-zA-Z0-9_]{0,20})$/);
                if (!match) {
                    hideMenu();
                    return;
                }
                const query = (match[1] || '').toLowerCase();
                mentionStartIdx = textBeforeCaret.lastIndexOf('@');
                activeField = el;
                filteredList = MENTION_PEOPLE.filter(function (p) {
                    return p.name.toLowerCase().includes(query);
                });
                if (!filteredList.length) {
                    hideMenu();
                    return;
                }
                selectedIdx = 0;
                renderMenu();
                positionMenu(el);
            }

            document.addEventListener('input', function (e) {
                const t = e.target;
                if (t && (t.tagName === 'TEXTAREA' || (t.tagName === 'INPUT' && t.type === 'text'))) {
                    handleFieldInput(t);
                }
            }, true);

            document.addEventListener('focusin', function (e) {
                const t = e.target;
                if (t && (t.tagName === 'TEXTAREA' || (t.tagName === 'INPUT' && t.type === 'text'))) {
                    if ((t.value || '').includes('@')) {
                        handleFieldInput(t);
                    }
                }
            }, true);

            document.addEventListener('keyup', function (e) {
                if (['ArrowDown', 'ArrowUp', 'Enter', 'Tab', 'Escape'].includes(e.key)) return;
                const t = e.target;
                if (t && (t.tagName === 'TEXTAREA' || (t.tagName === 'INPUT' && t.type === 'text'))) {
                    handleFieldInput(t);
                }
            }, true);

            document.addEventListener('keydown', function (e) {
                if (!menuEl || menuEl.style.display === 'none' || !filteredList.length) return;
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    e.stopPropagation();
                    selectedIdx = (selectedIdx + 1) % filteredList.length;
                    renderMenu();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    e.stopPropagation();
                    selectedIdx = (selectedIdx - 1 + filteredList.length) % filteredList.length;
                    renderMenu();
                } else if (e.key === 'Enter' || e.key === 'Tab') {
                    e.preventDefault();
                    e.stopPropagation();
                    insertMention(filteredList[selectedIdx]);
                } else if (e.key === 'Escape') {
                    hideMenu();
                }
            }, true);

            document.addEventListener('click', function (e) {
                if (menuEl && menuEl.style.display !== 'none' && !menuEl.contains(e.target) && e.target !== activeField) {
                    hideMenu();
                }
            });
        })();
    </script>
</body>
</html>
