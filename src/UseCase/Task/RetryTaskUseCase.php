<?php

namespace App\UseCase\Task;

use App\Infrastructure\Contract\DeduplicationServiceInterface;
use App\Models\Task\Contract\OutboxEventDatabaseRepositoryInterface;
use App\Models\Task\Contract\TaskDatabaseRepositoryInterface;
use App\Models\Task\Enum\OutboxEventType;
use App\Models\Task\OutboxEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class RetryTaskUseCase
{
    public function __construct(
        private readonly TaskDatabaseRepositoryInterface $taskDatabaseRepository,
        private readonly OutboxEventDatabaseRepositoryInterface $outboxEventDatabaseRepository,
        private DeduplicationServiceInterface $deduplicationService,
        private LoggerInterface $logger,
        private EntityManagerInterface $em,
    ) {}

    public function execute(int $limit = 100): void
    {
        $this->em->wrapInTransaction(function () use ($limit) {
            $tasks = $this->taskDatabaseRepository->findRetryableTasks($limit);

            foreach ($tasks as $task) {
                $taskId = $task->getId();

                try {
                    $this->deduplicationService->release($taskId);

                    $event = new OutboxEvent(
                        eventType: OutboxEventType::taskCreated->value,
                        aggregateId: $task->getId(),
                        payload: [
                            'task_id' => $task->getId(),
                        ]
                    );
                    $this->outboxEventDatabaseRepository->save($event);

                    $task->setNextRetryAt(null);
                    $this->taskDatabaseRepository->save($task);

                    $this->em->flush();

                    $this->logger->info("Task {$taskId} scheduled for retry");
                } catch (\Throwable $e) {
                    $this->logger->error("Retry failed for task {$taskId}: " . $e->getMessage());
                }
            }
        });
    }
}
