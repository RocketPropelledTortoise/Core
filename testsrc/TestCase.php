<?php namespace Tests;

/**
 * Simplifies the creation of tests creating a full application, running migrations and loading multiple Service Providers
 */

/**
 * Class TestCase
 * @codeCoverageIgnore
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        if (!$this->app) {
            $this->refreshApplication();
        }

        $this->artisan('vendor:publish', [ '--all' => true ]);

        //refresh configuration values
        $this->refreshApplication();
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // reset base path to point to our package's src directory
        $app['path.base'] = realpath(__DIR__ . '/..');

        $app['config']->set(
            'database.connections',
            [
                'default' => [
                    'driver' => 'sqlite',
                    'database' => ':memory:',
                    'prefix' => '',
                ],
                'mysql' => [
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => 'test_db',
                    'username'  => 'test',
                    'password'  => 'test',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ],
                'pgsql' => [
                    'driver'   => 'pgsql',
                    'host'     => 'localhost',
                    'database' => 'test_db',
                    'username' => 'postgres',
                    'password' => '',
                    'charset'  => 'utf8',
                    'prefix'   => '',
                    'schema'   => 'public',
                ],
            ]
        );

        $app['config']->set('database.default', getenv('DB') ?: 'default');
    }
}
