<?php

namespace App\Support;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ReadOnlyModeException;

/**
 * While enabled, reads keep working and anything that writes to the database
 * gets a 503. Used while the database is frozen for a migration.
 */
final class ReadOnlyMode
{
    public const string MESSAGE = 'We are doing some maintenance, so changes are paused for a few minutes. Please try again shortly.';

    public const int RETRY_AFTER_SECONDS = 900;

    private const string WRITE_STATEMENT = '/^\s*(insert|update|delete|replace|create|alter|drop|truncate|rename)\b/i';

    public static function isEnabled(): bool
    {
        return (bool) config('app.read_only');
    }

    /**
     * Refuses every write on the connection while read only mode is enabled,
     * whatever triggered it. Blocked statements are logged so unexpected
     * write paths show up when rehearsing.
     */
    public static function guard(Connection $connection): void
    {
        $connection->beforeExecuting(function (string $query): void {
            if (! self::isEnabled() || ! preg_match(self::WRITE_STATEMENT, $query)) {
                return;
            }

            Log::warning('Read only mode blocked a database write.', ['query' => $query]);

            throw new ReadOnlyModeException;
        });
    }
}
