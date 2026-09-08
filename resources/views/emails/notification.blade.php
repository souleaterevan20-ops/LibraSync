<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f5f0e8; font-family: Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding: 32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 480px; background:#ffffff; border-radius: 16px; overflow:hidden; border:1px solid #e5ddd0;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #5c1a1a, #3d1010); padding: 24px 28px;">
                            <span style="color:#ffffff; font-size:18px; font-weight:bold;">Libra<span style="color:#fbbf24;">Sync</span></span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 28px;">
                            <h1 style="margin:0 0 12px; font-size: 18px; color:#2b1d16;">{{ $notificationTitle }}</h1>
                            <p style="margin:0 0 20px; font-size: 14px; line-height:1.6; color:#5c4a3a;">{{ $notificationMessage }}</p>

                            @if($actionUrl)
                                <a href="{{ $actionUrl }}" style="display:inline-block; background:#5c1a1a; color:#ffffff; text-decoration:none; font-size:13px; font-weight:bold; padding:10px 20px; border-radius:8px;">{{ $actionLabel }}</a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 16px 28px; border-top:1px solid #f0e9dc;">
                            <p style="margin:0; font-size:11px; color:#a89a88;">This is an automated message from LibraSync. Manage your email/SMS notification preferences from your Profile page.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
