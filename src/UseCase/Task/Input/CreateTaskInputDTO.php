<?php

namespace App\UseCase\Task\Input;

use App\Models\Task\Enum\TaskType;
use Symfony\Component\Validator\Constraints as Assert;

class CreateTaskInputDTO
{
    public function __construct(
        public readonly int $videoId,
        public readonly TaskType $type,
        public readonly array $inputData,
        public readonly string $source
    ) {}
}
