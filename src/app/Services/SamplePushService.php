<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Prometheus\CollectorRegistry;
use PrometheusPushGateway\PushGateway;
use Prometheus\Storage\InMemory;

class SamplePushService
{
    private CollectorRegistry $registry;
    private PushGateway $pushGateway;
    private \Prometheus\Counter $requestCounter;

    public function __construct(CollectorRegistry $registry)
    {
        $this->registry = $registry;
        $this->pushGateway = new PushGateway(env('PUSHGATEWAY_URL'));

        $this->requestCounter = $this->registry->getOrRegisterCounter(
            'app',
            'push_http_requests_total',
            'Total HTTP requests',
            ['status']
        );
    }

    public function getPosts()
    {
        $status = '200';

        try {
            $response = Http::get('https://jsonplaceholder.typicode.com/posts');
            $response->throw();
            $status = (string) $response->status();
            return $response->json();
        } finally {
            // Record metric
            $this->requestCounter->inc(['status' => $status]);
            $this->requestCounter->inc(['status' => $status]);

            // Push metrics to Pushgateway
            $this->pushGateway->pushAdd($this->registry, 'laravel_app_job');
        }
    }
}
