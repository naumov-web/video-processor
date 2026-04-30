<?php

namespace App\Models\Task\Processor;

use App\Infrastructure\Metrics\MetricsService;
use App\Models\Task\Contract\OutboxEventDatabaseRepositoryInterface;
use App\Models\Task\Contract\TaskHandlerInterface;
use App\Models\Task\Enum\OutboxEventType;
use App\Models\Task\OutboxEvent;
use App\Models\Task\Strategy\RetryStrategyInterface;
use App\Models\Task\Task;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class TaskProcessor
{
    /**
     * @param iterable<TaskHandlerInterface> $handlers
     */
    public function __construct(
        private readonly iterable $handlers,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
        private readonly RetryStrategyInterface $retryStrategy,
        private readonly MetricsService $metricsService,
        private readonly OutboxEventDatabaseRepositoryInterface $outboxEventDatabaseRepository
    ) {}

    public function process(Task $task): void
    {
        $this->logger->info("Processing task {$task->getId()}");
        $start = microtime(true);

        try {
            $handler = $this->resolveHandler($task->getType()->value);
            $handler->handle($task);

            $this->em->beginTransaction();
            $task->markCompleted();
            $event = new OutboxEvent(
                OutboxEventType::taskCompleted->value,
                $task->getId(),
                [
                    'task_id' => $task->getId(),
                    'output' => $task->getOutputData()
                ]
            );
            $this->outboxEventDatabaseRepository->save($event);
            $this->em->flush();
            $this->em->commit();

            $this->logger->info("Task {$task->getId()} completed");
            $this->metricsService->incrementProcessed();
        } catch (\Throwable $e) {
            $this->em->beginTransaction();
            $attempts = $task->getAttemptsCount() + 1;
            $task->setAttemptsCount($attempts);
            $task->setLastError([
                'message' => $e->getMessage(),
            ]);

            if ($attempts >= $task->getMaxAttempts()) {
                $task->markFailed();
                $task->setFinishedAt(new \DateTimeImmutable());
                $event = new OutboxEvent(
                    OutboxEventType::taskFailed->value,
                    $task->getId(),
                    [
                        'task_id' => $task->getId(),
                        'input' => $task->getInputData(),
                        'last_error' => $task->getLastError()
                    ]
                );
                $this->outboxEventDatabaseRepository->save($event);

                $this->logger->error("Task {$task->getId()} failed completely!");
                $this->metricsService->incrementFailed();
            } else {
                $nextRetryAt = $this->retryStrategy->getNextRetryAt($attempts);
                $task->markPending();
                $task->setNextRetryAt($nextRetryAt);

                $this->logger->warning(sprintf(
                    "Task %d retry #%d scheduled at %s",
                    $task->getId(),
                    $attempts,
                    $nextRetryAt->format('Y-m-d H:i:s')
                ));
            }

            $this->em->flush();
            $this->em->commit();
        } finally {
            $duration = microtime(true) - $start;
            $this->metricsService->observeDuration($duration);
        }
    }

    private function resolveHandler(string $type): TaskHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($type)) {
                return $handler;
            }
        }

        throw new \RuntimeException("No handler for type {$type}");
    }
}
