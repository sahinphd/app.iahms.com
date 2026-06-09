<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'LMS') }} - Secure Learning Portal</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Modern backdrop/scroll behavior styling */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.5);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(139, 92, 246, 0.4);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(139, 92, 246, 0.6);
        }
    </style>
</head>
<body class="h-full text-slate-100 flex flex-col md:flex-row overflow-hidden">

    <!-- Mobile Header -->
    <div class="md:hidden flex items-center justify-between bg-slate-950 px-4 py-3 border-b border-slate-800 w-full z-20">
        <div class="flex items-center space-x-2">
            <div class="p-1.5 rounded-lg bg-gradient-to-tr from-brand-600 to-indigo-500 text-white font-bold shadow-md shadow-brand-500/20">
                LMS
            </div>
            <span class="font-semibold text-lg tracking-wider bg-clip-text text-transparent bg-gradient-to-r from-brand-400 to-indigo-300">
                IAHMS Portal
            </span>
        </div>
        <button id="mobile-menu-toggle" class="text-slate-400 hover:text-slate-200 focus:outline-none">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <!-- Sidebar Navigation -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 ease-in-out bg-slate-950 w-64 flex-shrink-0 flex flex-col border-r border-slate-800/80 z-30 md:z-10 h-full">
        <!-- Sidebar Brand Info -->
        <div class="px-6 py-5 border-b border-slate-800/80 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="p-2 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 text-white font-bold shadow-lg shadow-brand-500/20 text-center w-10">
                    L
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-md tracking-wider text-slate-200 leading-tight">IAHMS LMS</span>
                    <span class="text-[10px] text-slate-400 font-medium tracking-wide">SECURE LEARNING</span>
                </div>
            </div>
            <button id="mobile-menu-close" class="md:hidden text-slate-500 hover:text-slate-300">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        @auth
        <!-- Authenticated User Profile Summary -->
        <div class="px-6 py-4 border-b border-slate-800 bg-slate-900/30 flex items-center space-x-3">
            <div class="h-10 w-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-semibold text-brand-400">
                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
            </div>
            <div class="flex flex-col min-w-0">
                <p class="text-sm font-semibold text-slate-200 truncate">{{ Auth::user()->name }}</p>
                <div class="flex items-center space-x-1.5 mt-0.5">
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider 
                        @if(Auth::user()->isAdmin()) bg-rose-500/20 text-rose-400 border border-rose-500/30
                        @elseif(Auth::user()->isTeacher()) bg-amber-500/20 text-amber-400 border border-amber-500/30
                        @else bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 @endif">
                        {{ Auth::user()->role }}
                    </span>
                </div>
            </div>
        </div>
        @endauth

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-4 space-y-1.5 overflow-y-auto">
            @auth
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 group {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-brand-600/30 to-brand-700/10 text-brand-400 border-l-4 border-brand-500 pl-3' : 'text-slate-400 hover:bg-slate-900 hover:text-slate-100' }}">
                    <svg class="h-5 w-5 {{ request()->routeIs('dashboard') ? 'text-brand-400' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('courses.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 group {{ request()->routeIs('courses.*') ? 'bg-gradient-to-r from-brand-600/30 to-brand-700/10 text-brand-400 border-l-4 border-brand-500 pl-3' : 'text-slate-400 hover:bg-slate-900 hover:text-slate-100' }}">
                    <svg class="h-5 w-5 {{ request()->routeIs('courses.*') ? 'text-brand-400' : 'text-slate-400 group-hover:text-slate-200' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Courses</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-400 hover:bg-slate-900 hover:text-slate-100 transition-all duration-200">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 01-3-3h5a3 3 0 013 3v1" />
                    </svg>
                    <span>Log In</span>
                </a>
                <a href="{{ route('register') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-400 hover:bg-slate-900 hover:text-slate-100 transition-all duration-200">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    <span>Register</span>
                </a>
            @endauth
        </nav>

        <!-- Sidebar Footer Action (Logout) -->
        @auth
        <div class="p-4 border-t border-slate-800 bg-slate-900/10">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-400 bg-slate-900 hover:bg-rose-500/10 hover:text-rose-400 border border-slate-800 hover:border-rose-500/20 transition-all duration-200">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
        @endauth
    </aside>

    <!-- Overlay for mobile sidebar -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-25 hidden transition-opacity duration-300 md:hidden"></div>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 overflow-y-auto relative bg-slate-900">
        
        <!-- Header -->
        <header class="hidden md:flex items-center justify-between px-8 py-4 border-b border-slate-800 bg-slate-950/40 backdrop-blur sticky top-0 z-10">
            <div>
                <h1 class="text-lg font-semibold text-slate-200">@yield('page_title', 'Learning Portal')</h1>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-xs text-slate-400">Current Local Time: 2026-06-09</span>
            </div>
        </header>

        <!-- Dynamic Flash Alerts -->
        <div class="px-4 md:px-8 mt-4">
            @if(session('success'))
                <div class="flex items-center justify-between p-4 mb-4 text-sm bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-2xl shadow-lg shadow-emerald-500/5 transition-all duration-300 animate-pulse">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="flex items-center justify-between p-4 mb-4 text-sm bg-rose-500/10 border border-rose-500/30 text-rose-400 rounded-2xl shadow-lg shadow-rose-500/5 transition-all duration-300">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 mb-4 bg-rose-500/10 border border-rose-500/30 text-rose-400 rounded-2xl shadow-lg shadow-rose-500/5">
                    <div class="flex items-center space-x-2 mb-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span class="font-bold text-sm">Please correct the following errors:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Inner Content -->
        <div class="p-4 md:p-8 flex-1">
            @yield('content')
        </div>
    </main>

    <!-- Sidebar Toggler Script -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const menuToggle = document.getElementById('mobile-menu-toggle');
        const menuClose = document.getElementById('mobile-menu-close');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        if (menuToggle) menuToggle.addEventListener('click', toggleSidebar);
        if (menuClose) menuClose.addEventListener('click', toggleSidebar);
        if (overlay) overlay.addEventListener('click', toggleSidebar);
    </script>
</body>
</html>
