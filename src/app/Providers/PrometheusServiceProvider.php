<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Arr;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;

class PrometheusServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
public function register (): void
    {

        $this->app->singleton( CollectorRegistry::class, function () {

            Redis::setDefaultOptions(
                Arr::only(config('database.redis.default' ), [ 'host', 'password', 'username' ] )
            );

            return CollectorRegistry::getDefault();

        } );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
