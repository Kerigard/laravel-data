<?php

namespace Kerigard\LaravelData\Tests;

use Illuminate\Support\Facades\File;
use Kerigard\LaravelData\DataServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [DataServiceProvider::class];
    }

    public function setUp(): void
    {
        parent::setUp();

        File::copy(__DIR__ . '/Models/Country.php', app_path('/Models/Country.php'));
        require_once app_path('/Models/Country.php');
        File::copy(__DIR__ . '/Models/User.php', app_path('/Models/User.php'));
        require_once app_path('/Models/User.php');

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
