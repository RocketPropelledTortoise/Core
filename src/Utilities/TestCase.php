<?php namespace Rocket\Utilities;


class TestCase extends \Orchestra\Testbench\TestCase
{
    public function packagesToTest(array $items)
    {
        $artisan = $this->app->make('Illuminate\Contracts\Console\Kernel');

        $artisan->call('vendor:publish');

        if (in_array('translations', $items)) {
            $artisan->call('migrate', ['--database' => 'testbench', '--path' => "Translation/migrations"]);
        }

        if (in_array('entities', $items)) {
            $artisan->call('migrate', ['--database' => 'testbench', '--path' => "Entities/migrations"]);
        }

        if (in_array('taxonomy', $items)) {
            $artisan->call('migrate', ['--database' => 'testbench', '--path' => "Taxonomy/migrations"]);
        }
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

        $app['config']->set('database.default', 'testbench');
        $app['config']->set(
            'database.connections.testbench',
            array(
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            )
        );
    }

    /**
     * Get package providers. At a minimum this is the package being tested, but also
     * would include packages upon which our package depends, e.g. Cartalyst/Sentry
     * In a normal app environment these would be added to the 'providers' array in
     * the config/app.php file.
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return array(
            //'Cartalyst\Sentry\SentryServiceProvider',
            //'YourProject\YourPackage\YourPackageServiceProvider',
        );
    }

    /**
     * Get package aliases. In a normal app environment these would be added to
     * the 'aliases' array in the config/app.php file. If your package exposes an
     * aliased facade, you should add the alias here, along with aliases for
     * facades upon which your package depends, e.g. Cartalyst/Sentry
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return array(
            //'Sentry' => 'Cartalyst\Sentry\Facades\Laravel\Sentry',
            //'YourPackage' => 'YourProject\YourPackage\Facades\YourPackage',
        );
    }
}
