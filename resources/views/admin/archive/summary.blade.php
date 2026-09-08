<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>LibraSync Archive Summary — {{ $semester }}</title>
<style>
    body { font-family: 'Helvetica', Arial, sans-serif; color: #2b2118; margin: 40px; }
    h1 { color: #6b1414; font-size: 22px; margin-bottom: 0; }
    .subtitle { color: #8a7f6f; font-size: 12px; margin-top: 4px; }
    table { width: 100%; border-collapse: collapse; margin-top: 24px; }
    td, th { padding: 10px 12px; border-bottom: 1px solid #e5ddd0; text-align: left; font-size: 13px; }
    th { background: #f4efe4; color: #6b1414; text-transform: uppercase; font-size: 11px; letter-spacing: .04em; }
    .footer { margin-top: 32px; font-size: 11px; color: #8a7f6f; }
    @media print { body { margin: 0; } }
</style>
</head>
<body>
    <h1>LibraSync — Archive Summary</h1>
    <p class="subtitle">{{ $semester }} &middot; Generated {{ $generatedAt->format('F d, Y g:i A') }} by {{ $creator->name }}</p>

    <table>
        <tr><th>Item</th><th>Count</th></tr>
        <tr><td>Borrow records archived</td><td>{{ $recordCount }}</td></tr>
        <tr><td>User accounts (all, including deleted)</td><td>{{ $userCount }}</td></tr>
        <tr><td>Penalties on record for this semester</td><td>{{ $penaltyCount }}</td></tr>
        <tr><td>Payments on record (all time)</td><td>{{ $paymentCount }}</td></tr>
    </table>

    <p class="footer">
        This package contains USERS.csv, BOOKS.csv, BORROW_RECORDS.csv, PENALTIES.csv, PAYMENTS.csv,
        AUDIT_LOGS.csv, NOTIFICATIONS.csv, and ANNOUNCEMENTS.csv alongside this summary. Generated automatically
        by LibraSync's End of Semester archive process.
    </p>
</body>
</html>
