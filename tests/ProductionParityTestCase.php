<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Test case for production parity tests.
 * Uses the local dev database and real Statamic content
 * instead of test database isolation.
 */
abstract class ProductionParityTestCase extends BaseTestCase
{
    // Intentionally omits LazilyRefreshDatabase
    // so we read from the real local database without transactions.
}
