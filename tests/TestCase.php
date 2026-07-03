<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // RefreshDatabase resets the database between tests but not the
        // cache store; without this, cached home-page data from an earlier
        // test (e.g. an older column shape) can leak into a later one.
        Cache::flush();
    }
}
