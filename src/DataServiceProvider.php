<?php

namespace Kerigard\LaravelData;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Kerigard\LaravelData\Commands\FillDatabaseData;

class DataServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                FillDatabaseData::class,
            ]);
        }
    }

    public function provides(): array
    {
        return [
            FillDatabaseData::class,
        ];
    }
}
