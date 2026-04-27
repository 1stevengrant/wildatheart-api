<?php

namespace WildAtHeart\CommandCenter\Tests;

use Statamic\Testing\AddonTestCase;
use WildAtHeart\CommandCenter\ServiceProvider;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
