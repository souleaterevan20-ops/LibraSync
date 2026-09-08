<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-theme-init />
    <title>Privacy Policy - {{ config('app.name', 'LibraSync') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-parchment-100 dark:bg-bark-950 transition-colors">
    <div class="max-w-2xl mx-auto py-10 px-4">
        <h1 class="text-2xl font-bold text-bark-800 dark:text-parchment-100 mb-1">Privacy Policy</h1>
        <p class="text-xs text-bark-400 mb-6">Last updated {{ date('F Y') }}</p>

        <div class="space-y-4 text-sm text-bark-600 dark:text-bark-300 leading-relaxed">
            <p>LibraSync collects only the information needed to operate the library's circulation system:
            your name, email address, role (Student, Teacher, Library Staff, or Super Admin), department
            or course where applicable, and your borrowing, penalty, and leaderboard activity.</p>

            <p><strong>Who can see your information.</strong> Your borrowing history, outstanding penalties,
            and leaderboard points are visible to library staff (Library Staffs and Super Admins) for the
            purpose of managing circulation and settling penalties. Your leaderboard ranking may be visible
            to other users depending on the leaderboard display.</p>

            <p><strong>How your information is used.</strong> Data is used to process borrow requests,
            calculate overdue penalties, send you notifications about your account, and generate internal
            reports and semester archives. We do not sell or share your information with third parties.</p>

            <p><strong>Data retention.</strong> Borrowing and penalty records are archived rather than
            deleted at the end of each semester, so historical records remain available for reporting.
            Deleted accounts and records are retained in recoverable form as part of routine system backups.</p>

            <p><strong>Your choices.</strong> You can view your own borrowing history, penalties, and
            notifications at any time from your dashboard. For questions about your data or to request
            corrections, contact the library circulation desk.</p>
        </div>

        <a href="{{ url()->previous() }}" class="inline-block mt-8 text-sm font-semibold text-maroon-700 dark:text-amber-300 hover:underline">&larr; Back</a>
    </div>
</body>
</html>
