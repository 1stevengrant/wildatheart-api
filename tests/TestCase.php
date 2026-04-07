<?php

namespace Tests;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use LazilyRefreshDatabase;

    protected function afterRefreshingDatabase(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }
}
