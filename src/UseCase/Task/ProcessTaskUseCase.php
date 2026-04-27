<?php

namespace App\UseCase\Task;

use App\Models\Task\Contract\TaskDatabaseRepositoryInterface;
use App\Models\Task\Enum\TaskStatus;
use App\Models\Task\Exception\InvalidTaskStatusException;
use App\Models\Task\Task;
use Psr\Log\LoggerInterface;

class ProcessTaskUseCase
{
    public function __construct(
        private readonly TaskDatabaseRepositoryInterface $taskDatabaseRepository,
        private readonly LoggerInterface $logger
    ) {}

    public function execute(int $taskId): void
    {
        /** @var Task|null $task */
        $task = $this->taskDatabaseRepository->find($taskId);

        if (!$task) {
            $this->logger->warning("Task {$taskId} not found");
            return;
        }

        if ($task->getStatus() !== TaskStatus::pending) {
            $this->logger->info("Task {$taskId} already processed");
            return;
        }

        try {
            $this->markAsRunning($task);
            $this->logger->info("Task {$taskId} taken into work");
        } catch (\Throwable $e) {
            $this->logger->warning("Task {$taskId} race condition, skipped");
        }
    }

    /**
     * @throws InvalidTaskStatusException
     */
    private function markAsRunning(Task $task): void
    {
        $task->markRunning();
        $this->taskDatabaseRepository->save($task);
    }
}
