<?php

namespace App\Models\Task\Processor;

use App\Models\Task\Contract\TaskHandlerInterface;
use App\Models\Task\Task;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class TaskProcessor
{
    /**
     * @param iterable<TaskHandlerInterface> $handlers
     */
    public function __construct(
        private iterable $handlers,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {}

    public function process(Task $task): void
    {
        $this->logger->info("Processing task {$task->getId()}");

        try {
            $handler = $this->resolveHandler($task->getType()->value);
            $handler->handle($task);

            $task->markCompleted();
            $this->em->flush();

            $this->logger->info("Task {$task->getId()} completed");

        } catch (\Throwable $e) {
            $task->markFailed();
            $this->em->flush();

            $this->logger->error("Task {$task->getId()} failed: " . $e->getMessage());
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
