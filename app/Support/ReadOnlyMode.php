<?php

namespace App\Support;

/**
 * While enabled, reads keep working and anything that writes to the database
 * gets a 503. Used while the database is frozen for a migration.
 */
final class ReadOnlyMode
{
    public const string MESSAGE = 'We are doing some maintenance, so changes are paused for a few minutes. Please try again shortly.';

    public const int RETRY_AFTER_SECONDS = 900;

    public static function isEnabled(): bool
    {
        return (bool) config('app.read_only');
    }
}
