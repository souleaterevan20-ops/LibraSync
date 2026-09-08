<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-theme-init />
    <title>Terms of Use - {{ config('app.name', 'LibraSync') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-parchment-100 dark:bg-bark-950 transition-colors">
    <div class="max-w-2xl mx-auto py-10 px-4">
        <h1 class="text-2xl font-bold text-bark-800 dark:text-parchment-100 mb-1">Terms of Use</h1>
        <p class="text-xs text-bark-400 mb-6">Last updated {{ date('F Y') }}</p>

        <div class="space-y-4 text-sm text-bark-600 dark:text-bark-300 leading-relaxed">
            <p>By creating a LibraSync account you agree to use the system solely for legitimate library
            circulation purposes — browsing the catalog, requesting and returning books, and tracking your
            own borrowing activity, penalties, and leaderboard standing.</p>

            <p><strong>Borrowing responsibilities.</strong> Books must be returned by their due date. Late
            returns accrue a ₱10 penalty per overdue day and a corresponding leaderboard point deduction.
            Lost or damaged books are charged at the book's listed replacement cost. Outstanding penalties
            must be settled at the library circulation desk.</p>

            <p><strong>Account accuracy.</strong> You're responsible for keeping your registration details
            accurate and for the activity that occurs under your account. Do not share your login
            credentials with another person.</p>

            <p><strong>Account status.</strong> Accounts may be disabled by library staff for policy
            violations, unresolved penalties, or at the end of a semester as part of routine archiving.
            Disabled accounts can be reactivated by a Super Admin or Library Staff.</p>

            <p><strong>Fair use.</strong> Attempts to interfere with the system's normal operation,
            circumvent penalty tracking, or misuse borrowing privileges may result in account suspension.</p>

            <p>Questions about these terms can be directed to the library circulation desk.</p>
        </div>

        <a href="{{ url()->previous() }}" class="inline-block mt-8 text-sm font-semibold text-maroon-700 dark:text-amber-300 hover:underline">&larr; Back</a>
    </div>
</body>
</html>
