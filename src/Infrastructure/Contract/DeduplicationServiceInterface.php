<?php

namespace App\Infrastructure\Contract;

interface DeduplicationServiceInterface
{
    public function acquire(int $taskId): bool;
    public function release(int $taskId): void;
}
