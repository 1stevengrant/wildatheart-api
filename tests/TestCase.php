<?php

namespace Tests;

use Saloon\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\WithCachedConfig;
use Illuminate\Foundation\Testing\WithCachedRoutes;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use LazilyRefreshDatabase;
    use WithCachedConfig;
    use WithCachedRoutes;

    /**
     * Seed once during migrate:fresh (per process) instead of re-running
     * the DatabaseSeeder inside every test's transaction.
     */
    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Http::preventStrayRequests();
        Config::preventStrayRequests();

    }
}
