<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Prometheus\Counter;
use Prometheus\Gauge;
use Prometheus\Histogram;
use Symfony\Component\HttpFoundation\Response;
use Prometheus\CollectorRegistry;

class PrometheusMiddleware
{
    private CollectorRegistry $registry;
    private Counter $requestCounter;
    private Gauge $activeRequestsGauge;
    private Gauge $memoryGauge;
    private Histogram $requestsDurationHistogram;

    public function __construct(CollectorRegistry $registry)
    {
        $this->registry = $registry;

        $this->requestCounter = $registry->getOrRegisterCounter(
            'app',
            'http_requests_total',
            'Total number of HTTP requests',
            ['status', 'path', 'method']
        );

        $this->activeRequestsGauge = $registry->getOrRegisterGauge(
            'app',
            'http_active_requests',
            'Number of active HTTP requests'
        );

        $this->memoryGauge = $this->registry->getOrRegisterGauge(
            'app',
            'memory_usage_bytes',
            'Current memory usage in bytes',
            ['type']
        );
        $this->requestsDurationHistogram = $registry->getOrRegisterHistogram(
            'app',
            'http_request_duration_seconds',
            'HTTP request duration in seconds',
            ['status', 'path', 'method'],
            [0.1, 0.25, 0.5, 1, 2.5, 5]
        );
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->path() === 'metrics') {
            return $next($request);
        }

        $this->activeRequestsGauge->inc();
        $start = microtime(true);
        /** @var Response $response */
        $response = $next($request);
        $duration = microtime(true) - $start;

        $path = $this->routePathLabel($request);

        $this->requestCounter->inc([
            'status' => (string) $response->getStatusCode(),
            'path'   => $path,
            'method' => $request->getMethod(),
        ]);
        $this->requestsDurationHistogram->observe(
            $duration,
            [
                'status' => $response->getStatusCode(),
                'path' => $path,
                'method' => $request->method()
            ]
        );
        $this->activeRequestsGauge->dec();

        $this->memoryGauge->set(
            memory_get_usage(true),
            ['real']
        );
        $this->memoryGauge->set(
            memory_get_usage(false),
            ['emalloc']
        );

        return $response;
    }

    private function routePathLabel(Request $request): string
    {
        $route = $request->route();

        return $route ? '/' . ltrim($route->uri(), '/') : '/' . ltrim($request->path(), '/');
    }
}
