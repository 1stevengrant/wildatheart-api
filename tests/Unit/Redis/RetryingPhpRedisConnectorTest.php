<?php

use App\Support\Redis\RetryingPhpRedisConnector;

function establishConnection(Redis $client, array $config): void
{
    $connector = new class extends RetryingPhpRedisConnector
    {
        public function connectClient(Redis $client, array $config): void
        {
            $this->establishConnection($client, $config);
        }
    };

    $connector->connectClient($client, $config);
}

it('retries the connect after a timeout', function (): void {
    $client = Mockery::mock(Redis::class);

    $attempts = 0;

    $client->shouldReceive('connect')
        ->twice()
        ->andReturnUsing(function () use (&$attempts): bool {
            $attempts++;

            if ($attempts === 1) {
                throw new RedisException('Connection timed out');
            }

            return true;
        });

    establishConnection($client, [
        'host' => '127.0.0.1',
        'port' => 6379,
        'connect_attempts' => 2,
        'connect_retry_delay' => 0,
    ]);
});

it('rethrows once the connect attempts are used up', function (): void {
    $client = Mockery::mock(Redis::class);

    $client->shouldReceive('connect')
        ->twice()
        ->andThrow(new RedisException('Connection timed out'));

    establishConnection($client, [
        'host' => '127.0.0.1',
        'port' => 6379,
        'connect_attempts' => 2,
        'connect_retry_delay' => 0,
    ]);
})->throws(RedisException::class, 'Connection timed out');

it('passes the configured connect and read timeouts to phpredis', function (): void {
    $client = Mockery::mock(Redis::class);

    $client->shouldReceive('connect')
        ->once()
        ->withArgs(fn (string $host, int $port, float $timeout, $persistentId, int $retryInterval, float $readTimeout): bool => $timeout === 2.0 && $readTimeout === 5.0)
        ->andReturn(true);

    establishConnection($client, [
        'host' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 2.0,
        'read_timeout' => 5.0,
    ]);
});
