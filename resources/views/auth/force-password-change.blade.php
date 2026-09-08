<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-theme-init />
    <title>Set a New Password - {{ config('app.name', 'LibraSync') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-parchment-100 dark:bg-bark-950 transition-colors flex items-center justify-center min-h-screen">
    <div class="max-w-sm w-full mx-4 bg-white dark:bg-bark-900 rounded-2xl shadow-lg p-8">
        <h1 class="text-xl font-bold text-bark-800 dark:text-parchment-100 mb-1">Set a New Password</h1>
        <p class="text-sm text-bark-500 dark:text-bark-400 mb-6">
            Your password was reset by a library administrator. Please choose a new password to continue.
        </p>

        <form method="POST" action="{{ route('password.force-change.update') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">New Password</label>
                <input type="password" name="password" required autofocus
                    class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div>
                <label class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">Confirm New Password</label>
                <input type="password" name="password_confirmation" required
                    class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
            </div>

            <button type="submit" class="w-full bg-maroon-700 hover:bg-maroon-800 text-white text-sm font-semibold px-4 py-2.5 rounded-lg">
                Set Password &amp; Continue
            </button>
        </form>
    </div>
</body>
</html>