<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Prometheus\CollectorRegistry;

class SampleService
{
    private $registry;
    private $summary;

    public function __construct(CollectorRegistry $registry)
    {
        $this->registry = $registry;
        $this->summary = $registry->getOrRegisterSummary(
            'app',
            'external_api_request_duration_seconds',
            'Duration of external API requests',
            ['endpoint']
        );
    }

    public function getPosts()
    {
        $start = microtime(true);

        try {
            $response = Http::get('https://jsonplaceholder.typicode.com/posts');
            $response->throw();
            return $response->json();
        } finally {
            $duration = microtime(true) - $start;
            $this->summary->observe($duration, ['endpoint' => 'posts']);
        }
    }
}
