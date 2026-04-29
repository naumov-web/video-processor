<?php

namespace App\UseCase\Task;

use App\Infrastructure\Contract\DeduplicationServiceInterface;
use App\Models\Task\Contract\OutboxEventDatabaseRepositoryInterface;
use App\Models\Task\Contract\TaskDatabaseRepositoryInterface;
use App\Models\Task\Enum\OutboxEventType;
use App\Models\Task\Enum\TaskStatus;
use App\Models\Task\Exception\InvalidTaskStatusException;
use App\Models\Task\Exception\TaskNotFoundException;
use App\Models\Task\OutboxEvent;
use App\Models\Task\Task;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class RetrySingleTaskUseCase
{
    public function __construct(
        private readonly TaskDatabaseRepositoryInterface $taskDatabaseRepository,
        private readonly OutboxEventDatabaseRepositoryInterface $outboxEventDatabaseRepository,
        private readonly DeduplicationServiceInterface $deduplicationService,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
    ) {}

    public function execute(int $taskId): void
    {
        /** @var Task|null $task */
        $task = $this->taskDatabaseRepository->find($taskId);

        if (!$task) {
            throw new TaskNotFoundException("Task not found");
        }

        if ($task->getStatus()->value === TaskStatus::running->value) {
            throw new InvalidTaskStatusException("Task is currently running");
        }

        $this->em->wrapInTransaction(function () use ($task, $taskId) {
            $this->deduplicationService->release($taskId);
            $event = new OutboxEvent(
                eventType: OutboxEventType::taskCreated->value,
                aggregateId: $taskId,
                payload: [
                    'task_id' => $taskId,
                ]
            );
            $this->outboxEventDatabaseRepository->save($event);
            $task->setNextRetryAt(null);
            $task->markPending();
            $this->taskDatabaseRepository->save($task);
            $this->em->flush();
        });

        $this->logger->info("Manual retry triggered for task {$taskId}");
    }
}
