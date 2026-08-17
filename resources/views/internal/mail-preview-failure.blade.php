<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mail preview unavailable</title>
    <style>
        :root { color-scheme: dark; }
        body {
            background: #010101;
            color: #E0E1E2;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 48px 24px;
        }
        .shell { margin: 0 auto; max-width: 640px; }
        h1 { color: #fff; font-size: 20px; margin: 0 0 4px; }
        .label { color: #E7A26B; font-size: 14px; margin: 0 0 32px; }
        .message {
            background: #0D151A;
            border-left: 4px solid #D65F00;
            line-height: 1.6;
            padding: 16px;
        }
        .exception { color: #7D8488; font-size: 12px; margin-top: 24px; }
        a { color: #E7A26B; }
    </style>
</head>
<body>
<div class="shell">
    <h1>This preview could not be rendered</h1>
    <p class="label">{{ $label }}</p>
    <div class="message">{{ $message }}</div>
    <p class="exception">{{ $exception }}</p>
    <p><a href="{{ route('internal.mail-previews.index') }}">Back to all previews</a></p>
</div>
</body>
</html>
