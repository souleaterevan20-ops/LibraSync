<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-theme-init />
    <title>Sign In - {{ config('app.name', 'LibraSync') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-parchment-100 dark:bg-bark-950 transition-colors">

    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">

        <!-- Brand panel -->
        <div class="hidden lg:flex flex-col justify-center relative overflow-hidden bg-gradient-to-br from-maroon-900 via-maroon-800 to-bark-900 text-parchment-100 px-14 py-16">
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_20%_20%,white,transparent_35%)]"></div>

            <div class="relative flex items-center gap-3 mb-10">
                <svg class="w-12 h-12 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                <div>
                    <div class="text-2xl font-bold">Libra<span class="text-amber-300">Sync</span></div>
                    <div class="text-[11px] tracking-widest text-maroon-300 uppercase -mt-1">E-Library Management System</div>
                </div>
            </div>

            <h1 class="relative text-3xl font-bold leading-snug mb-2">Smart Library. Stronger Community.</h1>
            <p class="relative text-amber-200 font-medium mb-4">Knowledge for today, success for tomorrow.</p>
            <p class="relative text-maroon-200/90 max-w-md mb-10 leading-relaxed">
                LibraSync is a hybrid online/offline e-library system designed to manage books, users, and library transactions with ease and efficiency.
            </p>

            <div class="relative grid grid-cols-3 gap-4 mb-10 max-w-lg">
                <div class="bg-white/5 rounded-xl p-4">
                    <div class="w-8 h-8 rounded-lg bg-sky-500/20 text-sky-300 flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                    </div>
                    <div class="text-sm font-semibold">Easy Borrowing</div>
                    <div class="text-xs text-maroon-300 mt-0.5">Borrow and return books in a few clicks.</div>
                </div>
                <div class="bg-white/5 rounded-xl p-4">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                    </div>
                    <div class="text-sm font-semibold">Notifications</div>
                    <div class="text-xs text-maroon-300 mt-0.5">Get alerts for due dates and updates.</div>
                </div>
                <div class="bg-white/5 rounded-xl p-4">
                    <div class="w-8 h-8 rounded-lg bg-violet-500/20 text-violet-300 flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728" /></svg>
                    </div>
                    <div class="text-sm font-semibold">Leaderboard</div>
                    <div class="text-xs text-maroon-300 mt-0.5">Earn points and be a top reader.</div>
                </div>
            </div>

            <div class="relative bg-white/5 border border-white/10 rounded-xl p-4 flex gap-3 max-w-lg">
                <svg class="w-6 h-6 text-sky-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                <div>
                    <div class="font-semibold text-sm">Secure. Reliable. Accessible.</div>
                    <div class="text-xs text-maroon-300 mt-0.5">Built for students, teachers, and library staff.</div>
                </div>
            </div>

            <p class="relative mt-10 text-sm italic text-maroon-300">&ldquo;The best weapon in the world is knowledge.&rdquo; &mdash; Nelson Mandela</p>
        </div>

        <!-- Form panel -->
        <div class="flex flex-col items-center justify-center px-6 py-12 relative">

            <x-dark-mode-toggle class="!absolute top-6 right-6" />

            <div class="w-full max-w-sm">

                <div class="flex flex-col items-center text-center mb-6 lg:hidden">
                    <svg class="w-10 h-10 text-maroon-700 dark:text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                    <div class="text-xl font-bold text-bark-800 dark:text-parchment-100 mt-2">Libra<span class="text-maroon-700 dark:text-amber-300">Sync</span></div>
                </div>

                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-bark-800 dark:text-parchment-100">Welcome Back!</h2>
                    <p class="text-bark-500 dark:text-bark-400 text-sm mt-1">Sign in to continue to LibraSync</p>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">Email or Username</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-bark-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                            </span>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                class="pl-10 block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm"
                                placeholder="Enter your email or username">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">Password</label>
                        <div class="relative" x-data="{ show: false }">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-bark-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                            </span>
                            <input :type="show ? 'text' : 'password'" id="password" name="password" required autocomplete="current-password"
                                class="pl-10 pr-10 block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm"
                                placeholder="Enter your password">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-bark-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                        <div class="flex justify-end mt-1">
                            @if (Route::has('password.request'))
                                <a class="text-xs font-medium text-maroon-600 dark:text-maroon-400 hover:underline" href="{{ route('password.request') }}">Forgot Password?</a>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">Select Role <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-bark-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z" /></svg>
                            </span>
                            <select id="role" name="role" required class="pl-10 block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm">
                                <option value="" disabled @selected(old('role') === null)>-- Select your role --</option>
                                <option value="student" @selected(old('role') === 'student')>Student</option>
                                <option value="teacher" @selected(old('role') === 'teacher')>Teacher</option>
                                <option value="student_assistant" @selected(old('role') === 'student_assistant')>Library Staff</option>
                                <option value="super_admin" @selected(old('role') === 'super_admin')>Librarian</option>
                            </select>
                        </div>
                        <x-input-error :messages="$errors->get('role')" class="mt-1" />
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-maroon-700 hover:bg-maroon-800 text-white font-semibold py-2.5 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                        Sign In
                    </button>

                    <div class="relative text-center text-xs text-bark-400 my-2">
                        <span class="bg-parchment-100 dark:bg-bark-950 px-2 relative z-10">or</span>
                        <div class="absolute inset-x-0 top-1/2 border-t border-bark-200 dark:border-bark-800"></div>
                    </div>

                    <a href="{{ route('register') }}" class="w-full inline-flex items-center justify-center gap-2 border border-bark-200 dark:border-bark-700 text-bark-700 dark:text-parchment-100 font-medium py-2.5 rounded-lg hover:bg-parchment-200 dark:hover:bg-bark-800 transition-colors">
                        <svg class="w-4 h-4 text-maroon-600 dark:text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
                        Self-Registration
                    </a>
                </form>

                <p class="text-center text-xs text-bark-400 mt-6">
                    By logging in, you agree to our
                    <a href="{{ route('legal.terms') }}" target="_blank" class="text-maroon-600 dark:text-maroon-400 hover:underline">Terms of Use</a> and
                    <a href="{{ route('legal.privacy') }}" target="_blank" class="text-maroon-600 dark:text-maroon-400 hover:underline">Privacy Policy</a>.
                </p>
            </div>

            <p class="text-center text-[11px] text-bark-400 mt-10">
                &copy; {{ date('Y') }} LibraSync E-Library Management System.
            </p>
        </div>
    </div>
</body>
</html>
