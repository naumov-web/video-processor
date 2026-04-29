<?php

namespace App\Models\Task\Strategy;

class ExponentialBackoffRetryStrategy implements RetryStrategyInterface
{
    private int $baseDelay;
    private int $maxDelay;
    private int $jitter;

    public function __construct(
        int $baseDelay = 10,
        int $maxDelay = 300,
        int $jitter = 5
    ) {
        $this->baseDelay = $baseDelay;
        $this->maxDelay = $maxDelay;
        $this->jitter = $jitter;
    }

    public function getNextRetryAt(int $attemptsCount): \DateTimeImmutable
    {
        // delay = base * 2^attempt
        $delay = $this->baseDelay * (2 ** max(0, $attemptsCount - 1));
        // cap
        $delay = min($delay, $this->maxDelay);
        // jitter
        if ($this->jitter > 0) {
            $delay += random_int(0, $this->jitter);
        }

        return (new \DateTimeImmutable())->modify("+{$delay} seconds");
    }
}
