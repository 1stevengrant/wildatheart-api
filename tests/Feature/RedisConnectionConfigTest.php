<?php

use Illuminate\Redis\RedisManager;
use App\Support\Redis\RetryingPhpRedisConnector;

it('uses the retrying connector for phpredis', function (): void {
    $manager = app(RedisManager::class);

    $connector = (fn () => $this->connector())->call($manager);

    expect($connector)->toBeInstanceOf(RetryingPhpRedisConnector::class);
});

it('bounds connects and reads and retries the connect on every redis connection', function (string $connection): void {
    expect(config("database.redis.{$connection}"))
        ->timeout->toEqual(2)
        ->read_timeout->toEqual(5)
        ->connect_attempts->toEqual(2);
})->with(['default', 'cache']);
