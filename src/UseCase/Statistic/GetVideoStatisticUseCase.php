<?php

namespace App\UseCase\Statistic;

use App\Models\Task\Contract\TaskDatabaseRepositoryInterface;

class GetVideoStatisticUseCase
{
    public function __construct(private readonly TaskDatabaseRepositoryInterface $taskDatabaseRepository) {}

    public function execute(int $videoId): array
    {
        return $this->taskDatabaseRepository->getVideoStatistic($videoId);
    }
}
