<?php

namespace App\Console\Commands;

use App\Services\SamplePushService;
use Illuminate\Console\Command;

/**
 * Pushgateway exists for short-lived batch jobs that die before Prometheus
 * could ever scrape them (cron jobs, one-off scripts, queue workers). It is
 * NOT a substitute for pulling /metrics from a long-running web process —
 * see https://github.com/prometheus/pushgateway#"who-should-use-the-pushgateway".
 * This command is that short-lived job: run it via cron/scheduler, it does
 * its work, pushes its metrics, and exits.
 */
class PushSampleMetrics extends Command
{
    protected $signature = 'metrics:push-sample';

    protected $description = 'Run a short-lived job and push its metrics to the Pushgateway';

    public function handle(SamplePushService $service): int
    {
        $service->getPosts();

        $this->info('Pushed sample job metrics to the Pushgateway.');

        return self::SUCCESS;
    }
}
