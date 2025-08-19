<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Prometheus\CollectorRegistry;

class PrometheusMiddleware
{
    private CollectorRegistry $registry;
    private \Prometheus\Counter $requestCounter;
    private \Prometheus\Gauge $activeRequestsGauge;
    private \Prometheus\Gauge $memoryGauge;
    private \Prometheus\Histogram $requestsDurationHistogram;

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
        $this->activeRequestsGauge->inc();
        $start = microtime(true);
        /** @var Response $response */
        $response = $next($request);
        $duration = microtime(true) - $start;

        $this->requestCounter->inc([
            'status' => (string) $response->getStatusCode(),
            'path'   => '/' . ltrim($request->path(), '/'),
            'method' => $request->getMethod(),
        ]);
        $this->requestsDurationHistogram->observe(
            $duration,
            [
                'status' => $response->getStatusCode(),
                'path' => $request->path(),
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
}
