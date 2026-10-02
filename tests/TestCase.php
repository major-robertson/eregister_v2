<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected string $seeder = \Database\Seeders\TestReferenceSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();

        // Livewire deletes temporary uploads older than a day, measured with
        // now(). Tests that travel forward (the waiver suites pin the 15th of
        // the month) made every fake upload look stale, so it was deleted
        // before the component read it and the suite failed from the 1st to
        // the 14th of each month. The cleanup has no value in tests.
        config(['livewire.temporary_file_upload.cleanup' => false]);
    }
}
