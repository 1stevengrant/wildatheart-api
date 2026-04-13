<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Test case for production parity tests.
 *
 * Syncs production database on first run, then uses that data
 * for all parity tests. Intentionally omits LazilyRefreshDatabase
 * so real content is available.
 *
 * Requires REMOTE_DATABASE_* env vars for the sync connection.
 */
abstract class ProductionParityTestCase extends BaseTestCase
{
    public static array $productionResponses = [];

    private static bool $databaseSynced = false;

    private static ?string $syncError = null;

    protected function setUp(): void
    {
        parent::setUp();

        $parityDatabase = env('DB_DATABASE_PARITY', 'wahlocal');
        config(['database.connections.mysql.database' => $parityDatabase]);
        DB::purge('mysql');
        DB::reconnect('mysql');

        if (! static::$databaseSynced) {
            static::$databaseSynced = true;

            if (empty(config('dbsync.host')) || empty(config('dbsync.username'))) {
                static::$syncError = 'REMOTE_DATABASE_* env vars not configured';

                return;
            }

            $exitCode = Artisan::call('db:production-sync');

            if ($exitCode !== 0) {
                static::$syncError = 'db:production-sync failed with exit code ' . $exitCode;
            }
        }

        if (static::$syncError) {
            $this->markTestSkipped("Database sync unavailable: " . static::$syncError);
        }
    }
}
