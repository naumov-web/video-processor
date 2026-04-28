<?php

namespace App\Models\Task\Handler;

use App\Models\Task\Contract\TaskHandlerInterface;
use App\Models\Task\Enum\TaskType;
use App\Models\Task\Task;
use Psr\Log\LoggerInterface;

class AiTaggingHandler implements TaskHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function supports(string $type): bool
    {
        return $type === TaskType::ai_tagging->value;
    }

    public function handle(Task $task): void
    {
        $this->logger->info("AI tagging video for task {$task->getId()}");
        sleep(2);
    }
}
