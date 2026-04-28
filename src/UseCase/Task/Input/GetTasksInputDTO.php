<?php

namespace App\UseCase\Task\Input;

class GetTasksInputDTO
{
    public function __construct(
        public ?int $offset = 0,
        public ?int $limit = 20,
        public ?string $sortBy = 'createdAt',
        public ?string $direction = 'desc',
        public ?string $status = null,
        public ?string $type = null,
    ) {}
}
