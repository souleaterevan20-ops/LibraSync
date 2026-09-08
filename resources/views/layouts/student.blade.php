<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('librasync_sidebar_collapsed') === 'true' }" x-init="$watch('sidebarCollapsed', value => localStorage.setItem('librasync_sidebar_collapsed', value))">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-theme-init />
    <title>{{ $title ?? 'Student/Teacher Dashboard' }} - {{ config('app.name', 'LibraSync') }}</title>
    <script src="https://unpkg.com/htmx.org@1.9.12" defer></script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
</head>
<body class="font-sans antialiased bg-parchment-100 dark:bg-bark-950 transition-colors">
<div id="nav-loading" class="htmx-indicator fixed top-0 left-0 right-0 h-0.5 bg-amber-400 z-[60]"></div>

    <div class="min-h-screen flex">

        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 bg-black/40 z-30 lg:hidden"></div>

        <!-- Sidebar -->
        <aside
            class="fixed z-40 inset-y-0 left-0 w-72 bg-gradient-to-b from-maroon-800 to-maroon-900 text-maroon-100 flex flex-col transform transition-all duration-200 ease-in-out lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen lg:z-auto"
            :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'lg:w-20' : 'lg:w-72']"
        >
            <div class="h-16 flex items-center gap-2 px-5 border-b border-white/10 shrink-0 overflow-hidden">
                <svg class="w-7 h-7 text-amber-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                <div class="leading-tight" x-show="!sidebarCollapsed">
                    <div class="font-bold text-white text-[15px]">Libra<span class="text-amber-300">Sync</span></div>
                    <div class="text-[10.5px] text-maroon-300 -mt-0.5">E-Library Management System</div>
                </div>
                <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden lg:flex ml-auto text-maroon-300 hover:text-white p-1" title="Collapse sidebar">
                    <svg class="w-4 h-4 transition-transform" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            <!-- Profile card -->
            <div class="px-5 py-4 border-b border-white/10 overflow-hidden">
                <div class="flex items-center gap-3">
                    <x-user-avatar size="w-11 h-11" text="text-base" class="bg-white/10 text-amber-200 shrink-0" />
                    <div class="min-w-0" x-show="!sidebarCollapsed">
                        <div class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] inline-flex items-center gap-1 text-emerald-300">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                            {{ (isset($user) ? $user : auth()->user())?->roleLabel() }}
                        </div>
                    </div>
                </div>
                @if(auth()->user()->bio)
                    <div class="mt-2 text-xs text-maroon-300 leading-snug italic" x-show="!sidebarCollapsed">
                        "{{ auth()->user()->bio }}"
                    </div>
                @endif
            </div>

            <nav id="sidebar-nav" hx-swap-oob="true" hx-boost="true" hx-target="#main-content" hx-select="#main-content" hx-swap="innerHTML show:window:top" hx-push-url="true" hx-indicator="#nav-loading" class="flex-1 overflow-y-auto sidebar-scroll px-3 py-3 space-y-0.5">
                <!-- 1. Dashboard -->
                <x-admin-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></x-slot>
                    Dashboard
                </x-admin-nav-link>

                <!-- 2. Browse Books -->
                <x-admin-nav-link :href="route('catalog.index')" :active="request()->routeIs('catalog.index')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></x-slot>
                    Browse Books
                </x-admin-nav-link>

                <!-- 3. My Borrowings -->
                <x-admin-nav-link :href="route('my-borrowings.index')" :active="request()->routeIs('my-borrowings.index')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" /></x-slot>
                    My Borrowings
                </x-admin-nav-link>

                <!-- 4. My Fines & Payments -->
                <x-admin-nav-link :href="route('fines.index')" :active="request()->routeIs('fines.index')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" /></x-slot>
                    My Fines &amp; Payments
                </x-admin-nav-link>

                <!-- 5. My Profile -->
                <x-admin-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></x-slot>
                    My Profile
                </x-admin-nav-link>

                <!-- 6. Help & Support -->
                <x-admin-nav-link :href="route('help.index')" :active="request()->routeIs('help.index')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></x-slot>
                    Help &amp; Support
                </x-admin-nav-link>

                <!-- 7. About System -->
                <x-admin-nav-link :href="route('about.index')" :active="request()->routeIs('about.index')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></x-slot>
                    About System
                </x-admin-nav-link>
            </nav>

            <!-- Penalty balance card -->
            <div class="p-4 border-t border-white/10 shrink-0 space-y-3">
                <a href="{{ route('fines.index') }}" class="block bg-white/5 hover:bg-white/10 rounded-xl p-3.5 transition-colors">
                    <div class="text-xs font-semibold text-amber-200 mb-1.5">Outstanding Penalty</div>
                    <div class="flex items-center justify-between text-xs text-maroon-200">
                        <span>Balance</span>
                        @if((float) auth()->user()->penalty_balance > 0)
                            <span class="text-rose-300 font-semibold">₱{{ number_format(auth()->user()->penalty_balance, 2) }}</span>
                        @else
                            <span class="text-emerald-300 font-semibold">₱0.00</span>
                        @endif
                    </div>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center gap-2 text-sm text-maroon-200 hover:text-white px-2 py-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" /></svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-white dark:bg-bark-900 border-b border-bark-200 dark:border-bark-800 flex items-center gap-4 px-4 sm:px-6 sticky top-0 z-20 transition-colors">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-bark-500 dark:text-bark-300 hover:text-bark-700 dark:hover:text-white">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                </button>

                <div>
                    <h1 class="text-lg sm:text-xl font-semibold text-bark-800 dark:text-parchment-100">{{ $title ?? 'Student/Teacher Dashboard' }}</h1>
                </div>

                <form action="{{ route('catalog.index') }}" method="GET" class="hidden md:flex ml-6 flex-1 max-w-md">
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-bark-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                        </span>
                        <input type="text" name="search" placeholder="Search books by title, author, subject..." class="pl-9 w-full text-sm rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500">
                    </div>
                </form>

                <div class="ml-auto flex items-center gap-3 sm:gap-4">
                    @isset($unreadNotifications)
                        <a href="{{ route('notifications.index') }}" class="relative text-bark-400 dark:text-bark-300 hover:text-bark-600 dark:hover:text-white" title="Notifications">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                            @if($unreadNotifications > 0)
                                <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] leading-4 text-center font-semibold">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
                            @endif
                        </a>
                    @endisset

                    <x-dark-mode-toggle />

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-2">
                                <x-user-avatar size="w-8 h-8" text="text-xs" />
                                <span class="hidden sm:block text-left leading-tight">
                                    <span class="block text-sm font-semibold text-bark-800 dark:text-parchment-100">{{ auth()->user()->name }}</span>
                                    <span class="block text-xs text-bark-500 dark:text-bark-400">{{ auth()->user()?->roleLabel() }}</span>
                                </span>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </header>

            <main id="main-content" class="flex-1 p-4 sm:p-6">
                {{ $slot }}
            </main>

            <footer class="text-center text-xs text-bark-400 dark:text-bark-600 py-4">
                &copy; {{ date('Y') }} LibraSync E-Library Management System.
            </footer>
        </div>
    </div>

    <script>
        // Safety net for boosted sidebar navigation: if a boosted request fails
        // (session expired, network error, or the response has no #main-content
        // to swap — e.g. it got redirected to the login page), fall back to a
        // normal full-page navigation instead of leaving the user stuck.
        document.body.addEventListener('htmx:responseError', function (e) {
            if (e.target.closest('#sidebar-nav')) window.location.href = e.detail.pathInfo.requestPath;
        });
        document.body.addEventListener('htmx:sendError', function (e) {
            if (e.target.closest('#sidebar-nav')) window.location.href = e.detail.pathInfo.requestPath;
        });
    </script>
</body>
</html>
