<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;

class PrometheusServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(CollectorRegistry::class, function () {
            return new CollectorRegistry(new Redis([
                'host' => env('REDIS_HOST', '127.0.0.1'),
                'port' => (int) env('REDIS_PORT', 6379),
                'password' => env('REDIS_PASSWORD') ?: null,
            ]));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
