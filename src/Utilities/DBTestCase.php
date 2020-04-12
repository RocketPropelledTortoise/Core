<?php

/**
 * Simplifies the creation of tests creating a full application, running migrations and loading multiple Service Providers
 */
namespace Rocket\Utilities;

/**
 * Class TestCase
 * @codeCoverageIgnore
 */
class DBTestCase extends TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $artisan = $this->app->make('Illuminate\Contracts\Console\Kernel');
        $artisan->call('migrate');
        //echo $artisan->output();
    }

    public function debugSQL() {
        echo "\n";
        $i = 0;
        $this->app['db']->listen(function($sql) use (&$i) {
            if (strpos($sql->sql, "drop") === 0 || strpos($sql->sql, "create") === 0 || strpos($sql->sql, "migrations") !== false || strpos($sql->sql, "sqlite_master") !== false) {
                return;
            }

            echo "-> (" . ++$i . ") " . $sql->sql . ": " . print_r(implode(", ", $sql->bindings), true) . "\n";
        });
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    public function tearDown(): void
    {
        $artisan = $this->app->make('Illuminate\Contracts\Console\Kernel');
        $artisan->call('migrate:reset');

        parent::tearDown();
    }
}
