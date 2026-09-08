<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('librasync_sidebar_collapsed') === 'true' }" x-init="$watch('sidebarCollapsed', value => localStorage.setItem('librasync_sidebar_collapsed', value))">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-theme-init />
    <title>{{ $title ?? 'Library Staff Dashboard' }} - {{ config('app.name', 'LibraSync') }}</title>
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
            class="fixed z-40 inset-y-0 left-0 w-64 bg-gradient-to-b from-maroon-800 to-maroon-900 text-maroon-100 flex flex-col transform transition-all duration-200 ease-in-out lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen lg:z-auto"
            :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'lg:w-20' : 'lg:w-64']"
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

            <div class="px-5 pt-4 pb-1 text-[11px] font-semibold tracking-wider text-parchment-200 uppercase" x-show="!sidebarCollapsed">
                Library Staff
            </div>

            <nav id="sidebar-nav" hx-swap-oob="true" hx-boost="true" hx-target="#main-content" hx-select="#main-content" hx-swap="innerHTML show:window:top" hx-push-url="true" hx-indicator="#nav-loading" class="flex-1 overflow-y-auto sidebar-scroll px-3 py-3 space-y-0.5">
                <!-- 1. Dashboard -->
                <x-admin-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></x-slot>
                    Dashboard
                </x-admin-nav-link>

                <!-- 2. Pending Approvals -->
                <x-admin-nav-link :href="route('admin.pending-users')" :active="request()->routeIs('admin.pending-users')" :badge="$pendingRegistrations ?? null">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></x-slot>
                    Pending Approvals
                </x-admin-nav-link>

                <!-- 3. All Users -->
                <x-admin-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></x-slot>
                    All Users
                </x-admin-nav-link>

                <!-- 4. Borrow Requests -->
                <x-admin-nav-link :href="route('admin.borrows.index')" :active="request()->routeIs('admin.borrows.index')" :badge="$pendingBorrowRequests ?? null">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></x-slot>
                    Borrow Requests
                </x-admin-nav-link>

                <!-- 5. Returns -->
                <x-admin-nav-link :href="route('admin.returns.index')" :active="request()->routeIs('admin.returns.index')" :badge="$overdueReturns ?? null">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></x-slot>
                    Returns
                </x-admin-nav-link>

                <!-- 6. Book Management -->
                <x-admin-nav-link :href="route('admin.books.index')" :active="request()->routeIs('admin.books.*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></x-slot>
                    Book Management
                </x-admin-nav-link>

                <!-- 7. Penalty Management -->
                <x-admin-nav-link :href="route('admin.penalties.index')" :active="request()->routeIs('admin.penalties.*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></x-slot>
                    Penalty Management
                </x-admin-nav-link>

                <!-- 8. System Settings -->
                <x-admin-nav-link :href="route('admin.settings.index')" :active="request()->routeIs('admin.settings.*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.241.437-.613.43-.991a7.665 7.665 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /></x-slot>
                    System Settings
                </x-admin-nav-link>

                <!-- 9. About System -->
                <x-admin-nav-link :href="route('about.index')" :active="request()->routeIs('about.index')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></x-slot>
                    About System
                </x-admin-nav-link>
            </nav>

            <div class="p-4 border-t border-white/10 shrink-0 space-y-3">
                <div class="bg-white/5 rounded-xl p-3.5">
                    <div class="text-xs font-semibold text-amber-200 mb-1">Library Session</div>
                    <div class="text-[11px] text-maroon-300">{{ now()->format('l, M j, Y') }}</div>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-[11px] text-maroon-300">{{ now()->format('g:i A') }}</span>
                        <span class="text-[10px] font-semibold text-emerald-300 bg-emerald-400/10 px-2 py-0.5 rounded-full">Active</span>
                    </div>
                </div>
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
                <button @click="sidebarOpen = !sidebarOpen" class="hidden lg:block text-bark-400 hover:text-bark-600 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                </button>

                <h1 class="text-lg sm:text-xl font-semibold text-bark-800 dark:text-parchment-100">{{ $title ?? 'Library Staff Dashboard' }}</h1>

                <div class="ml-auto flex items-center gap-3 sm:gap-4">
                    <a href="{{ route('chat.index') }}" class="relative text-bark-400 dark:text-bark-300 hover:text-bark-600 dark:hover:text-white" title="Staff Chat">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                        </svg>
                        @if(($unreadMessages ?? 0) > 0)
                            <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] leading-4 text-center font-semibold">{{ $unreadMessages > 9 ? '9+' : $unreadMessages }}</span>
                        @endif
                    </a>

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
                                    <span class="block text-xs text-emerald-600 dark:text-emerald-400">Online</span>
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
