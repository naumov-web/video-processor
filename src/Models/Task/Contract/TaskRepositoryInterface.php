<?php

namespace App\Models\Task\Contract;

use App\Models\Task\Enum\TaskType;
use App\Models\Task\Task;

interface TaskRepositoryInterface
{
    public function save(Task $task): void;

    public function findById(int $id): ?Task;

    public function findNextForProcessing(): ?Task;

    public function findRetryableTasks(int $limit = 10): array;

    public function existsActiveTaskForVideo(int $videoId, TaskType $type): bool;
}
