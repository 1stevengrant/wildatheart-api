<?php

namespace Tests;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

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

    public static function tearDownAfterClass(): void
    {
        $parityDatabase = env('DB_DATABASE_PARITY', 'wahlocal_parity');

        try {
            $pdo = new \PDO(
                sprintf('mysql:host=%s;port=%s', env('DB_HOST', '127.0.0.1'), env('DB_PORT', '3306')),
                env('DB_USERNAME', 'root'),
                env('DB_PASSWORD', ''),
            );
            $pdo->exec("DROP DATABASE IF EXISTS `{$parityDatabase}`");
        } catch (\Exception) {
            // Silently ignore if we can't drop the database
        }

        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $parityDatabase = env('DB_DATABASE_PARITY', 'wahlocal_parity');

        if (! static::$databaseSynced) {
            static::$databaseSynced = true;

            if (empty(config('dbsync.host')) || empty(config('dbsync.username'))) {
                static::$syncError = 'REMOTE_DATABASE_* env vars not configured';
            } else {
                try {
                    DB::statement("CREATE DATABASE IF NOT EXISTS `{$parityDatabase}`");
                } catch (\Exception $e) {
                    static::$syncError = "Failed to create parity database: {$e->getMessage()}";
                }

                if (static::$syncError === null) {
                    config(['database.connections.mysql.database' => $parityDatabase]);
                    DB::purge('mysql');
                    DB::reconnect('mysql');

                    $exitCode = Artisan::call('db:production-sync');

                    if ($exitCode !== 0) {
                        static::$syncError = "db:production-sync failed with exit code {$exitCode}";
                    }
                }
            }
        }

        config(['database.connections.mysql.database' => $parityDatabase]);
        DB::purge('mysql');
        DB::reconnect('mysql');

        if (static::$syncError) {
            $this->markTestSkipped('Database sync unavailable: ' . static::$syncError);
        }
    }
}
