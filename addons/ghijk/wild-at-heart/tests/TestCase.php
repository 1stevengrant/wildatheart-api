<?php

namespace Ghijk\WildAtHeart\Tests;

use Statamic\Testing\AddonTestCase;
use Ghijk\WildAtHeart\ServiceProvider;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
