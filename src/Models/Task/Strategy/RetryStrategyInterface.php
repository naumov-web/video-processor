<?php

namespace App\Models\Task\Strategy;

interface RetryStrategyInterface
{
    public function getNextRetryAt(int $attemptsCount): \DateTimeImmutable;
}
