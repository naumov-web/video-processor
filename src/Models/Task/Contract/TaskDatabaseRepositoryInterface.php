<?php

namespace App\Models\Task\Contract;

use App\Models\Common\DTO\PaginatedResultDTO;
use App\Models\Task\Enum\TaskType;
use App\Models\Task\Filter\TaskFilter;
use App\Models\Task\Task;

interface TaskDatabaseRepositoryInterface
{
    public function getById(int $id): ?Task;

    public function save(Task $task, bool $flush = false): void;

    public function findById(int $id): ?Task;

    public function findRetryableTasks(int $limit = 10): array;

    public function existsActiveTaskForVideo(int $videoId, TaskType $type): bool;

    public function findPaginated(TaskFilter $filter): PaginatedResultDTO;

    public function updateHeartbeat(int $taskId): void;

    public function findStaleRunningTasks(\DateTimeImmutable $threshold, int $limit): array;

    public function getVideoStatistic(int $videoId): array;
}
