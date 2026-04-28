<?php

namespace App\Models\Task\Handler;

use App\Models\Task\Contract\TaskHandlerInterface;
use App\Models\Task\Enum\TaskType;
use App\Models\Task\Task;
use Psr\Log\LoggerInterface;

class TranscodingHandler implements TaskHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function supports(string $type): bool
    {
        return $type === TaskType::transcoding->value;
    }

    public function handle(Task $task): void
    {
        $this->logger->info("Transcoding video for task {$task->getId()}");
        sleep(3);
    }
}
