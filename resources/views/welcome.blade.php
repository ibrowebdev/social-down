<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="Download videos from any social media platform in the highest quality. Free, fast, and secure video downloader for YouTube, Instagram, TikTok, Twitter, and more.">

    <title>{{ config('app.name', 'SocialDown') }} — Free Social Media Video Downloader</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-slate-950 text-white font-sans antialiased min-h-screen relative overflow-x-hidden">
    {{-- Background Gradient Effects --}}
    <div class="fixed inset-0 pointer-events-none">
        {{-- Main gradient orbs --}}
        <div
            class="absolute top-[-20%] left-[-10%] w-[60%] h-[60%] bg-blue-600/10 rounded-full blur-[120px] animate-float-slow">
        </div>
        <div
            class="absolute top-[10%] right-[-15%] w-[50%] h-[50%] bg-cyan-500/8 rounded-full blur-[100px] animate-float-slower">
        </div>
        <div
            class="absolute bottom-[-10%] left-[20%] w-[40%] h-[40%] bg-indigo-600/8 rounded-full blur-[100px] animate-float-slow">
        </div>

        {{-- Grid pattern overlay --}}
        <div
            class="absolute inset-0 bg-[linear-gradient(rgba(59,130,246,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(59,130,246,0.03)_1px,transparent_1px)] bg-[size:64px_64px]">
        </div>

        {{-- Radial gradient from center --}}
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(59,130,246,0.08)_0%,transparent_70%)]">
        </div>
    </div>

    {{-- Navigation --}}
    <header class="relative z-10">
        <nav class="max-w-6xl mx-auto px-6 py-5 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2.5 group" id="logo-link">
                <div
                    class="w-9 h-9 bg-gradient-to-br from-blue-500 to-cyan-400 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20 group-hover:shadow-blue-500/40 transition-shadow duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                </div>
                <span
                    class="text-xl font-bold bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent">SocialDown</span>
            </a>

            @if (Route::has('login'))
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="px-5 py-2 text-sm font-medium text-slate-300 hover:text-white transition-colors duration-200"
                            id="dashboard-link">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="px-5 py-2 text-sm font-medium text-slate-400 hover:text-white transition-colors duration-200"
                            id="login-link">
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="px-5 py-2 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 text-sm font-medium text-white rounded-lg transition-all duration-200"
                                id="register-link">
                                Sign up
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </nav>
    </header>

    {{-- Hero Section --}}
    <main class="relative z-10 flex flex-col items-center px-6 pt-12 sm:pt-20 pb-20">
        {{-- Badge --}}
        <div
            class="inline-flex items-center gap-2 px-4 py-1.5 bg-blue-500/10 border border-blue-500/20 rounded-full text-blue-400 text-xs font-medium mb-8 animate-fade-in-up">
            <span class="relative flex h-2 w-2">
                <span
                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
            </span>
            Free &bull; Fast &bull; No Registration Required
        </div>

        {{-- Heading --}}
        <h1
            class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-center max-w-3xl leading-tight mb-6 animate-fade-in-up animation-delay-100">
            Download Videos from
            <span class="bg-gradient-to-r from-blue-400 via-cyan-400 to-blue-500 bg-clip-text text-transparent">Any
                Platform</span>
        </h1>

        <p
            class="text-slate-400 text-center text-base sm:text-lg max-w-xl mb-12 leading-relaxed animate-fade-in-up animation-delay-200">
            Paste a link from YouTube, Instagram, TikTok, Twitter, or any supported platform and get the highest quality
            MP4 download.
        </p>

        {{-- Downloader Component --}}
        <div class="w-full max-w-2xl animate-fade-in-up animation-delay-300">
            @livewire('downloader')
        </div>

        {{-- Supported Platforms --}}
        <div class="mt-16 text-center animate-fade-in-up animation-delay-400">
            <p class="text-slate-500 text-xs font-medium uppercase tracking-wider mb-6">Supports 1000+ platforms
                including</p>
            <div class="flex flex-wrap items-center justify-center gap-6 sm:gap-10">
                {{-- YouTube --}}
                <div
                    class="flex items-center gap-2 text-slate-500 hover:text-red-500 transition-colors duration-300 group">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                    </svg>
                    <span class="text-sm font-medium hidden sm:inline">YouTube</span>
                </div>

                {{-- Instagram --}}
                <div
                    class="flex items-center gap-2 text-slate-500 hover:text-pink-500 transition-colors duration-300 group">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" />
                    </svg>
                    <span class="text-sm font-medium hidden sm:inline">Instagram</span>
                </div>

                {{-- TikTok --}}
                <div
                    class="flex items-center gap-2 text-slate-500 hover:text-white transition-colors duration-300 group">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z" />
                    </svg>
                    <span class="text-sm font-medium hidden sm:inline">TikTok</span>
                </div>

                {{-- Twitter / X --}}
                <div
                    class="flex items-center gap-2 text-slate-500 hover:text-white transition-colors duration-300 group">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                    </svg>
                    <span class="text-sm font-medium hidden sm:inline">X / Twitter</span>
                </div>

                {{-- Facebook --}}
                <div
                    class="flex items-center gap-2 text-slate-500 hover:text-blue-600 transition-colors duration-300 group">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                    </svg>
                    <span class="text-sm font-medium hidden sm:inline">Facebook</span>
                </div>
            </div>
        </div>

        {{-- Features Section --}}
        <div class="mt-24 w-full max-w-4xl">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                {{-- Feature 1 --}}
                <div class="group relative">
                    <div
                        class="absolute -inset-px bg-gradient-to-b from-blue-500/20 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>
                    <div
                        class="relative bg-slate-800/40 backdrop-blur-sm border border-white/5 rounded-2xl p-6 hover:border-white/10 transition-all duration-300">
                        <div
                            class="w-11 h-11 bg-blue-500/10 rounded-xl flex items-center justify-center mb-4 group-hover:bg-blue-500/20 transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold mb-2">Lightning Fast</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">Server-side processing means no strain on your
                            device. Downloads are fast and efficient.</p>
                    </div>
                </div>

                {{-- Feature 2 --}}
                <div class="group relative">
                    <div
                        class="absolute -inset-px bg-gradient-to-b from-cyan-500/20 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>
                    <div
                        class="relative bg-slate-800/40 backdrop-blur-sm border border-white/5 rounded-2xl p-6 hover:border-white/10 transition-all duration-300">
                        <div
                            class="w-11 h-11 bg-cyan-500/10 rounded-xl flex items-center justify-center mb-4 group-hover:bg-cyan-500/20 transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-cyan-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold mb-2">Private & Secure</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">Files are auto-deleted within 2 hours. No
                            tracking, no logs, complete privacy.</p>
                    </div>
                </div>

                {{-- Feature 3 --}}
                <div class="group relative">
                    <div
                        class="absolute -inset-px bg-gradient-to-b from-indigo-500/20 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>
                    <div
                        class="relative bg-slate-800/40 backdrop-blur-sm border border-white/5 rounded-2xl p-6 hover:border-white/10 transition-all duration-300">
                        <div
                            class="w-11 h-11 bg-indigo-500/10 rounded-xl flex items-center justify-center mb-4 group-hover:bg-indigo-500/20 transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold mb-2">Best Quality</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">Automatically picks the best video and audio
                            quality, merging them into a clean MP4.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="relative z-10 border-t border-white/5">
        <div class="max-w-6xl mx-auto px-6 py-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-slate-500 text-sm">&copy; {{ date('Y') }} SocialDown. All rights reserved.</p>
            <p class="text-slate-600 text-xs">Powered by yt-dlp &bull; Built with Laravel & Livewire</p>
        </div>
    </footer>

    @livewireScripts
</body>

</html>