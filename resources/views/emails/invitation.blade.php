<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <p>Hello,</p>

    <p>You have been invited to join <strong>Bigfinch</strong>. Click the link below to create your account:</p>

    <p>
        <a href="{{ url('/public/register/' . $invitation->token) }}">
            Accept Invitation
        </a>
    </p>

    <p>This invitation is valid until <strong>{{ $invitation->valid_until->format('d M Y H:i') }}</strong>.</p>

    <p>If you were not expecting this invitation, you may ignore this email.</p>
</body>
</html>
