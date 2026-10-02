<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Test tidak boleh bergantung pada aset hasil build (public/build
        // di-git-ignore dan tidak tersedia di CI) — stub manifest Vite.
        $this->withoutVite();
    }
}
