<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\PrometheusMiddleware::class);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Simulates the Pushgateway's real use case: a short-lived job that
        // runs, pushes its own metrics, and exits — nothing to scrape between
        // runs. Requires `php artisan schedule:work` (or a cron entry calling
        // `schedule:run` every minute) to actually fire.
        $schedule->command('metrics:push-sample')->everyMinute();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
