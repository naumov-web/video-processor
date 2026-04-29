<?php

namespace App\Models\Task\Handler;

use App\Models\Task\Contract\TaskDatabaseRepositoryInterface;
use App\Models\Task\Contract\TaskHandlerInterface;
use App\Models\Task\Enum\TaskType;
use App\Models\Task\Task;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class TranscodingHandler extends BaseHandler implements TaskHandlerInterface
{
    public function supports(string $type): bool
    {
        return $type === TaskType::transcoding->value;
    }

    public function handle(Task $task): void
    {
        $taskId = $task->getId();
        $this->logger->info("Start transcoding for task {$taskId}");
        $process = new Process(['sleep', '30']);
        $process->start();
        $lastHeartbeat = $this->updateHeartbeat($taskId);

        while ($process->isRunning()) {
            if (time() - $lastHeartbeat >= $this->heartbeatInterval) {
                $lastHeartbeat = $this->updateHeartbeat($taskId);
            }

            usleep(1_000_000);
        }

        $this->updateHeartbeat($taskId);

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                "Transcoding failed for task {$taskId}: " . $process->getErrorOutput()
            );
        }

        $this->logger->info("Finished transcoding for task {$taskId}");
    }
}
