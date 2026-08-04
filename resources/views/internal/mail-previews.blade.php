<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mail previews</title>
    <style>
        :root { color-scheme: dark; }
        body {
            background: #010101;
            color: #E0E1E2;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 48px 24px 96px;
        }
        .shell { margin: 0 auto; max-width: 720px; }
        h1 { color: #fff; font-size: 24px; margin: 0 0 8px; }
        .lede { color: #7D8488; font-size: 14px; margin: 0 0 40px; }
        h2 {
            border-bottom: 1px solid #29353A;
            color: #E7A26B;
            font-size: 13px;
            letter-spacing: 0.07em;
            margin: 40px 0 0;
            padding-bottom: 8px;
            text-transform: uppercase;
        }
        ul { list-style: none; margin: 0; padding: 0; }
        li { border-bottom: 1px solid #1B272D; }
        a {
            color: #E0E1E2;
            display: block;
            padding: 14px 4px;
            text-decoration: none;
        }
        a:hover { background: #0D151A; color: #fff; }
        .key { color: #7D8488; font-size: 12px; }
    </style>
</head>
<body>
<div class="shell">
    <h1>Mail previews</h1>
    <p class="lede">
        Every mailable rendered with real data from this environment. Nothing is sent and nothing
        is written. Super users only.
    </p>

    @foreach ($groups as $group => $previews)
        <h2>{{ $group }}</h2>
        <ul>
            @foreach ($previews as $key => $label)
                <li>
                    <a href="{{ route('internal.mail-previews.show', $key) }}">
                        {{ $label }}
                        <span class="key">{{ $key }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endforeach
</div>
</body>
</html>
