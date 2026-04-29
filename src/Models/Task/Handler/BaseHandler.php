<?php

namespace App\Models\Task\Handler;

use App\Models\Task\Contract\TaskDatabaseRepositoryInterface;
use Psr\Log\LoggerInterface;

abstract class BaseHandler
{
    protected int $heartbeatInterval = 3;

    public function __construct(
        protected LoggerInterface $logger,
        protected TaskDatabaseRepositoryInterface $taskDatabaseRepository,
    ) {}

    protected function updateHeartbeat(int $taskId): int
    {
        $this->taskDatabaseRepository->updateHeartbeat($taskId);
        $this->logger->debug("Heartbeat updated for task {$taskId}");

        return time();
    }
}
