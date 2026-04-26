<?php

namespace App\UseCase\Task;

use App\Models\Common\DTO\PaginatedResultDTO;
use App\Models\Task\Contract\TaskDatabaseRepositoryInterface;
use App\Models\Task\Filter\TaskFilter;
use App\UseCase\Task\Input\GetTasksInputDTO;

class GetTasksUseCase
{
    public function __construct(
        private TaskDatabaseRepositoryInterface $repository
    ){}

    public function execute(GetTasksInputDTO $input): PaginatedResultDTO
    {
        $filter = new TaskFilter(
            limit: $input->limit,
            offset: $input->offset,
            sortBy: $input->sortBy,
            direction: $input->direction
        );
        $filter->type = $input->type;
        $filter->status = $input->status;

        return $this->repository->findPaginated(
            $filter
        );
    }
}
