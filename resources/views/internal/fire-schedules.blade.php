<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fire schedule testing</title>
    <style>
        :root { color-scheme: dark; }
        body {
            background: #010101;
            color: #E0E1E2;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 48px 24px 96px;
        }
        .shell { margin: 0 auto; max-width: 960px; }
        h1 { color: #fff; font-size: 24px; margin: 0 0 8px; }
        h2 { color: #fff; font-size: 18px; margin: 0; }
        .lede, .muted { color: #7D8488; }
        .lede { margin: 0 0 24px; }
        a { color: #E7A26B; }
        .status {
            background: #12301F;
            border: 1px solid #2F7A4E;
            border-radius: 6px;
            margin: 0 0 24px;
            padding: 12px 16px;
        }
        .errors { background: #3A1414; border-color: #8A2E2E; }
        form.search { display: flex; gap: 8px; margin: 0 0 32px; }
        input[type=search], select {
            background: #0D151A;
            border: 1px solid #29353A;
            border-radius: 6px;
            color: #E0E1E2;
            font: inherit;
            padding: 8px 10px;
        }
        input[type=search] { flex: 1; }
        button {
            background: #29353A;
            border: 0;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            font: inherit;
            padding: 8px 12px;
        }
        button.primary { background: #B4602A; }
        .fire { border-top: 1px solid #29353A; padding: 24px 0; }
        .meta { display: grid; gap: 4px 24px; grid-template-columns: 160px 1fr; margin: 16px 0; }
        .meta dt { color: #7D8488; }
        .meta dd { margin: 0; }
        .badge { border-radius: 999px; font-size: 12px; margin-left: 8px; padding: 2px 8px; }
        .published { background: #12301F; color: #7BD69C; }
        .unpublished { background: #3A1414; color: #F29B9B; }
        ul { margin: 0; padding-left: 18px; }
        .actions { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; }
        .actions form { align-items: center; display: flex; gap: 8px; margin: 0; }
        .presets { color: #7D8488; margin: 0 0 32px; }
        .presets dt { color: #E0E1E2; margin-top: 8px; }
        .presets dd { margin: 0; }
    </style>
</head>
<body>
<div class="shell">
    <h1>Fire schedule testing</h1>
    <p class="lede">
        Move a scheduled Fire's dates, then run the daily schedule check for that Fire alone. Emails go to the
        <a href="{{ route('internal.captured-emails.index') }}">captured emails</a> page and are never delivered.
        Moving dates changes this environment's real data, so only use Fires made for testing. Not available in production.
    </p>

    @if (session('status'))
        <div class="status">
            {{ session('status') }}
            @if (session('showCapturedEmailsLink'))
                <a href="{{ route('internal.captured-emails.index') }}">Open captured emails</a>
            @endif
        </div>
    @endif

    @if ($errors->any())
        <div class="status errors">{{ $errors->first() }}</div>
    @endif

    <dl class="presets">
        @foreach ($presets as $preset)
            <dt>Last date {{ $preset->label() }}</dt>
            <dd>{{ $preset->expectation() }}</dd>
        @endforeach
    </dl>

    <form class="search" method="get" action="{{ route('internal.fire-schedules.index') }}">
        <input type="search" name="search" value="{{ $search }}" placeholder="Fire name, Fire ID or host email">
        <button type="submit">Search</button>
    </form>

    @forelse ($rows as $row)
        <section class="fire">
            <h2>
                {{ $row->name }}
                @if ($row->isPublished)
                    <span class="badge published">Published</span>
                @else
                    <span class="badge unpublished">{{ $row->wasUnpublishedBySchedule ? 'Unpublished by the schedule' : 'Unpublished' }}</span>
                @endif
            </h2>
            <div class="muted">{{ $row->eventType }} · {{ $row->id }}</div>

            <dl class="meta">
                <dt>Host</dt>
                <dd>
                    {{ $row->ownerEmail }}
                    @if ($row->coHostEmails !== [])
                        <span class="muted">(co-hosts {{ implode(', ', $row->coHostEmails) }})</span>
                    @endif
                </dd>
                <dt>Schedule</dt>
                <dd>{{ $row->frequency }}@if ($row->pattern), {{ $row->pattern }}@endif</dd>
                <dt>Timezone</dt>
                <dd>{{ $row->timezone }}, today there is {{ $row->localToday }}</dd>
                <dt>Last date</dt>
                <dd>
                    @if ($row->lastDate)
                        {{ $row->lastDate }}
                        <span class="muted">
                            ({{ $row->daysUntilLastDate >= 0 ? "in {$row->daysUntilLastDate} days" : abs($row->daysUntilLastDate) . ' days ago' }})
                        </span>
                    @else
                        None
                    @endif
                </dd>
                <dt>Dates</dt>
                <dd>
                    <ul>
                        @foreach ($row->dates as $date)
                            <li>{{ $date }}</li>
                        @endforeach
                    </ul>
                </dd>
                <dt>Schedule emails</dt>
                <dd>
                    @if ($row->sentEmails === [])
                        None sent
                    @else
                        <ul>
                            @foreach ($row->sentEmails as $sentEmail)
                                <li>{{ $sentEmail }}</li>
                            @endforeach
                        </ul>
                    @endif
                </dd>
            </dl>

            <div class="actions">
                <form method="post" action="{{ route('internal.fire-schedules.moves.store', $row->id) }}">
                    @csrf
                    <select name="preset" aria-label="Move the last date to">
                        @foreach ($presets as $preset)
                            <option value="{{ $preset->value }}">Last date {{ $preset->label() }}</option>
                        @endforeach
                    </select>
                    <button type="submit">Move dates</button>
                </form>

                <form method="post" action="{{ route('internal.fire-schedules.checks.store', $row->id) }}">
                    @csrf
                    <button type="submit" class="primary">Run schedule check</button>
                </form>

                <form method="post" action="{{ route('internal.fire-schedules.notifications.destroy', $row->id) }}">
                    @csrf
                    @method('delete')
                    <button type="submit">Reset reminder state</button>
                </form>
            </div>
        </section>
    @empty
        <p class="muted">No scheduled Fires{{ $search ? ' match that search' : '' }}. Fires get a schedule when they're saved with the new host form.</p>
    @endforelse

    {{ $fires->links() }}
</div>
</body>
</html>
