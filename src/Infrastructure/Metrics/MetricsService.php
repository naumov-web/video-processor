<?php

namespace App\Infrastructure\Metrics;

use Prometheus\CollectorRegistry;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\Storage\Redis;
use Prometheus\Counter;
use Prometheus\Histogram;

class MetricsService
{
    private CollectorRegistry $registry;

    private Counter $tasksProcessed;
    private Counter $tasksFailed;
    private Counter $tasksRetried;
    private Histogram $taskDuration;

    /**
     * @throws MetricsRegistrationException
     */
    public function __construct(string $redisHost, int $redisPort = 6379)
    {
        $adapter = new Redis([
            'host' => $redisHost,
            'port' => $redisPort,
        ]);

        $this->registry = new CollectorRegistry($adapter);

        // Counters
        $this->tasksProcessed = $this->registry->getOrRegisterCounter(
            namespace: 'app',
            name: 'tasks_processed_total',
            help: 'Total processed tasks'
        );

        $this->tasksFailed = $this->registry->getOrRegisterCounter(
            namespace: 'app',
            name: 'tasks_failed_total',
            help: 'Total failed tasks'
        );

        $this->tasksRetried = $this->registry->getOrRegisterCounter(
            namespace: 'app',
            name: 'tasks_retried_total',
            help: 'Total retried tasks'
        );

        // Histogram (processing time)
        $this->taskDuration = $this->registry->getOrRegisterHistogram(
            namespace: 'app',
            name: 'task_processing_duration_seconds',
            help: 'Task processing time',
            buckets: [0.1, 0.5, 1, 2, 5, 10]
        );
    }

    public function incrementProcessed(): void
    {
        $this->tasksProcessed->inc();
    }

    public function incrementFailed(): void
    {
        $this->tasksFailed->inc();
    }

    public function incrementRetried(): void
    {
        $this->tasksRetried->inc();
    }

    public function observeDuration(float $seconds): void
    {
        $this->taskDuration->observe($seconds);
    }

    public function getRegistry(): CollectorRegistry
    {
        return $this->registry;
    }
}
