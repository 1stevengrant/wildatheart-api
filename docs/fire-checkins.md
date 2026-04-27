# Fire Check-ins Email Flow

This document describes the automated check-in system that ensures fire events remain active and accurate.

## Overview

The system sends periodic emails to fire hosts asking them to confirm their event is still active. If hosts don't respond, the event is automatically unpublished to keep the map accurate.

## Timeline

```
Day 0:   Fire hasn't been updated in 90 days
         → Initial check-in email sent

Day 7:   No response to initial email (within 90-day window)
         → Reminder email sent

Day 10:  No response to reminder (within 90-day window)
         → Fire auto-unpublished

Day 90+: If no confirmation after 90 days from initial email
         → Fresh initial email cycle starts (not reminder/unpublish)
```

The jobs are **mutually exclusive** - a fire will only be processed by one job type at a time. If 90+ days pass without confirmation, the fire gets a fresh check-in cycle rather than being unpublished from a stale reminder.

## How It Works

### 1. Scheduled Command

The process starts with a scheduled command that runs daily:

```
php artisan fire:send-checkins
```

This command dispatches three jobs that handle different stages of the check-in process.

### 2. Initial Check-in Email

**Job:** `SendFireCheckinsEmailJob` → `MonthlyFireCleanupEmailToHostJob`

Finds fires that:
- Are published
- Have been inactive for 90+ days (no updates)
- Haven't received a check-in email in 90+ days
- Haven't been confirmed in 90+ days

For each matching fire, sends a single email to the primary host with all co-hosts CC'd. The email greeting addresses all hosts by name (e.g., "Hi John, Jane, and Bob,").

### 3. Reminder Email (7 Days Later)

**Job:** `SendFireCheckinsReminderEmailJob` → `SendSingleFireCheckinReminderEmailJob`

Finds fires that:
- Are published
- Received the initial check-in email 7-90 days ago (not 90+ days, which would trigger a fresh cycle)
- Haven't received a reminder yet
- Haven't been confirmed

For each matching fire, sends a single reminder email to the primary host with all co-hosts CC'd. The email greeting addresses all hosts by name.

### 4. Auto-Unpublish (3 Days After Reminder)

**Job:** `AutoUnpublishUnconfirmedFiresJob` → `UnpublishSingleUnconfirmedFireJob`

Finds fires that:
- Are published
- Received the initial check-in email within the last 90 days
- Received the reminder email 3+ days ago
- Still haven't been confirmed
- Haven't already been auto-unpublished

For each matching fire:
- Removes the `approved_at` date (unpublishes the fire)
- Marks it as `auto_unpublished`
- Sends a notification email to the primary host with all co-hosts CC'd

## Safeguards

### Mutual Exclusivity

The three job types use exclusive query conditions to ensure a fire is only processed by one workflow at a time:

| Initial Email Sent | Reminder Sent | Action |
|--------------------|---------------|--------|
| NULL or 90+ days ago | - | → Initial email |
| 7-90 days ago | NULL | → Reminder |
| 7-90 days ago | 3+ days ago | → Unpublish |

This prevents scenarios where a fire could receive multiple email types simultaneously.

### Preventing Duplicate Emails

- **Unique Parent Jobs:** Parent jobs implement `ShouldBeUnique` to prevent concurrent execution if the scheduler overlaps or the command runs twice.
- **Unique Child Jobs:** Child jobs implement `ShouldBeUnique` with the event ID as the unique key. This prevents duplicate jobs for the same event if the parent job completes before child jobs update the timestamp.
- **Timestamp-First Updates:** Child jobs update the database timestamp before sending emails. If the job fails mid-execution, retries won't send duplicate emails.
- **Single Email with CC:** Each notification sends one email to the primary host with co-hosts CC'd, ensuring atomic delivery instead of multiple separate emails.

### Retry Logic

All jobs retry up to 5 times with 300 seconds (5 minutes) between attempts.

### Memory Management

Parent jobs process events in chunks of 100 to prevent memory exhaustion with large datasets.

### Activity Logging

All actions are logged to the activity log for auditing:
- "Monthly email sent"
- "Check-in reminder email sent"
- "Auto-unpublished due to unconfirmed check-in"

## Database Fields

| Field                           | Purpose                                         |
|---------------------------------|-------------------------------------------------|
| `last_checkin_email_sent_at`    | When the initial check-in email was sent        |
| `last_checkin_reminder_sent_at` | When the reminder email was sent                |
| `last_checkin_confirmed_at`     | When the host confirmed the fire is active      |
| `auto_unpublished`              | Boolean flag indicating auto-unpublish occurred |
| `auto_unpublished_at`           | When the fire was auto-unpublished              |

## Testing & Demo

Use the demo command to simulate the check-in flow without waiting for actual time periods:

```bash
# Show current status of a fire
php artisan fire:demo-checkins {event_id}

# Simulate each stage (manipulates timestamps, dry-run by default)
php artisan fire:demo-checkins {event_id} --stage=initial    # Simulate 90+ days inactive
php artisan fire:demo-checkins {event_id} --stage=reminder   # Simulate 7+ days after initial
php artisan fire:demo-checkins {event_id} --stage=unpublish  # Simulate 3+ days after reminder

# Run full interactive flow
php artisan fire:demo-checkins {event_id} --stage=full

# Actually send emails (not just dry-run)
php artisan fire:demo-checkins {event_id} --stage=initial --send

# Reset timestamps to start fresh
php artisan fire:demo-checkins {event_id} --reset
```

## Related Files

### Commands & Jobs
- `app/Console/Commands/Fire/SendFiresCheckinsEmailCommand.php` - Main scheduled command
- `app/Console/Commands/Fire/DemoFireCheckinsCommand.php` - Demo/testing command
- `app/Jobs/Fire/SendFireCheckinsEmailJob.php`
- `app/Jobs/Fire/SendFireCheckinsReminderEmailJob.php`
- `app/Jobs/Fire/AutoUnpublishUnconfirmedFiresJob.php`
- `app/Jobs/Fire/MonthlyFireCleanupEmailToHostJob.php`
- `app/Jobs/Fire/SendSingleFireCheckinReminderEmailJob.php`
- `app/Jobs/Fire/UnpublishSingleUnconfirmedFireJob.php`

### Mailables
- `app/Mail/Fire/MonthlyFireCleanupEmailToHostMailable.php`
- `app/Mail/Fire/SevenDayFollowupMailable.php`
- `app/Mail/EventAutoUnpublishedMail.php`

### Shared Traits
- `app/Traits/FormatsEventHostNames.php` - Formats host names with natural language (e.g., "John, Jane, and Bob")
