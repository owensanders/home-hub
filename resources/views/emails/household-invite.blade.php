<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $householdName }} on HouseHub</title>
</head>
<body style="margin:0; padding:0; background-color:#eeeae3; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eeeae3; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="max-width:480px; width:100%; background-color:#fcfaf7; border-radius:16px; overflow:hidden;">
                    <tr>
                        <td style="padding:32px 32px 0 32px;">
                            <div style="font-size:20px; font-weight:800; color:#ff4f40; letter-spacing:-0.02em;">HouseHub</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 32px 8px 32px; color:#0e1a2b; font-size:22px; font-weight:800; line-height:1.3;">
                            @if ($hasAccount)
                                You're invited to join {{ $householdName }}
                            @else
                                Join {{ $householdName }} on HouseHub
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 24px 32px; color:#0e1a2b; font-size:15px; line-height:1.6;">
                            @if ($hasAccount)
                                Someone has invited your HouseHub account to join the household
                                "{{ $householdName }}". Accept below to be added — you'll be able to
                                pick which household you want to view once you're in.
                            @else
                                You've been invited to join the household "{{ $householdName }}" on
                                HouseHub. Create your account below and you'll be added to the
                                household automatically.
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 32px 32px;">
                            <a href="{{ $actionUrl }}"
                               style="display:inline-block; background-color:#ff4f40; color:#fcfaf7; text-decoration:none; font-weight:700; font-size:15px; padding:12px 24px; border-radius:10px;">
                                @if ($hasAccount)
                                    Accept invite
                                @else
                                    Create your account
                                @endif
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 32px 32px; color:#0e1a2b; opacity:0.6; font-size:12px; line-height:1.6;">
                            This link expires in 7 days. If you weren't expecting this invite, you can
                            safely ignore this email.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
