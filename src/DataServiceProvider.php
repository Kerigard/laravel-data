<?php

namespace Kerigard\LaravelData;

use Illuminate\Support\ServiceProvider;
use Kerigard\LaravelData\Commands\FillDatabaseData;

class DataServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                FillDatabaseData::class,
            ]);
        }
    }
}
