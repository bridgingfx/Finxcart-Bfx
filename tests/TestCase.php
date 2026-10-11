<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        // Durable guard: NEVER let the test suite migrate a non-SQLite database.
        // phpunit.xml sets DB_CONNECTION=sqlite, but Collision's `artisan test
        // --env=...` lets Laravel's .env file override phpunit's <env> vars, which
        // once wiped the local MySQL database via RefreshDatabase. So we boot a
        // throwaway app instance here and verify the RESOLVED default connection
        // before parent::setUp() can run RefreshDatabase migrations.
        $probe = $this->createApplication();
        $connection = $probe['config']->get('database.default');
        if ($connection !== 'sqlite') {
            fwrite(
                STDERR,
                "\nREFUSING TO RUN TESTS: resolved database.default is [{$connection}], expected [sqlite]. " .
                "The suite only ever runs against SQLite. Aborting before any migration.\n"
            );
            exit(1);
        }

        parent::setUp();
    }
}
