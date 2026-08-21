<?php

namespace App\Services;

use App\Models\User;
use Prometheus\CollectorRegistry;

class DbSampleService
{
    private \Prometheus\Histogram $queryDurationHistogram;

    public function __construct(CollectorRegistry $registry)
    {
        $this->queryDurationHistogram = $registry->getOrRegisterHistogram(
            'app',
            'db_query_duration_seconds',
            'Duration of database queries',
            ['query'],
            [0.005, 0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1]
        );
    }

    public function listUsers()
    {
        $start = microtime(true);

        try {
            return User::query()->orderBy('id')->limit(20)->get(['id', 'name', 'email']);
        } finally {
            $this->queryDurationHistogram->observe(microtime(true) - $start, ['query' => 'users.list']);
        }
    }
}
