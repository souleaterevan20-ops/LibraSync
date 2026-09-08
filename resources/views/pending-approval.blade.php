<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-theme-init />
    <title>Pending Approval - {{ config('app.name', 'LibraSync') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-parchment-100 dark:bg-bark-950 min-h-screen flex flex-col items-center justify-center px-6 transition-colors">

    <x-dark-mode-toggle class="!fixed top-6 right-6" />

    <div class="w-full max-w-md bg-white dark:bg-bark-900 border border-bark-200 dark:border-bark-800 rounded-2xl shadow-sm p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-300 flex items-center justify-center mx-auto mb-5">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
        @if(isset($user) && $user->rejected_at)
            <h1 class="text-xl font-bold text-bark-800 dark:text-parchment-100 mb-2">Registration Rejected</h1>
            <p class="text-sm text-bark-500 dark:text-bark-400 mb-2">
                Your registration for LibraSync was not approved.
            </p>
            <p class="text-sm text-bark-500 dark:text-bark-400 mb-6">
                Reason: {{ $user->rejection_reason ?? 'No reason was provided.' }}<br>
                Please coordinate with a Super Admin or Library Staff member if you believe this was a mistake.
            </p>
        @else
            <h1 class="text-xl font-bold text-bark-800 dark:text-parchment-100 mb-2">Pending Verification</h1>
            <p class="text-sm text-bark-500 dark:text-bark-400 mb-2">
                Welcome to LibraSync! Your account has been successfully created, but it is currently pending administrative verification.
            </p>
            <p class="text-sm text-bark-500 dark:text-bark-400 mb-6">
                Please coordinate with a Super Admin or Library Instructor to have your account manually vetted and approved.
            </p>
        @endif
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 border border-bark-200 dark:border-bark-700 text-bark-700 dark:text-parchment-100 font-medium px-5 py-2.5 rounded-lg hover:bg-parchment-200 dark:hover:bg-bark-800 transition-colors">
                Log Out
            </button>
        </form>
    </div>
</body>
</html>
