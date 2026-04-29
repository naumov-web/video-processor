<?php

namespace App\UseCase\Task;

use App\Models\Task\Contract\TaskDatabaseRepositoryInterface;
use App\Models\Task\Enum\TaskStatus;
use App\Models\Task\Task;
use Psr\Log\LoggerInterface;

class HeartbeatMonitorUseCase
{
    public function __construct(
        private readonly TaskDatabaseRepositoryInterface $taskRepository,
        private readonly LoggerInterface $logger,
    ) {}

    public function execute(int $thresholdSeconds = 30, int $limit = 100): void
    {
        $threshold = new \DateTimeImmutable("-{$thresholdSeconds} seconds");

        $tasks = $this->taskRepository->findStaleRunningTasks($threshold, $limit);

        foreach ($tasks as $task) {
            /** @var Task $task */
            $taskId = $task->getId();

            $this->logger->warning("Stale task detected: {$taskId}");

            $task->setAttemptsCount($task->getAttemptsCount() + 1);
            $task->markPending();
            $task->setNextRetryAt(new \DateTimeImmutable());

            $this->taskRepository->save($task, true);
        }
    }
}
