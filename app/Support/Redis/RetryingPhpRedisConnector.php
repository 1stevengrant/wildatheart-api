<?php

namespace App\Support\Redis;

use Redis;
use Throwable;
use RedisException;
use Illuminate\Redis\Connectors\PhpRedisConnector;

/**
 * Retries the initial connect, which phpredis' max_retries and backoff options do not cover.
 */
class RetryingPhpRedisConnector extends PhpRedisConnector
{
    /**
     * @param  Redis  $client
     * @param  array<string, mixed>  $config
     */
    protected function establishConnection($client, array $config): void
    {
        retry(
            times: max(1, (int) ($config['connect_attempts'] ?? 1)),
            callback: fn () => parent::establishConnection($client, $config),
            sleepMilliseconds: (int) ($config['connect_retry_delay'] ?? 100),
            when: fn (Throwable $exception): bool => $exception instanceof RedisException,
        );
    }
}
