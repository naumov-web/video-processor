<?php

namespace App\Models\Task\Validator;

use App\Models\Common\Validator\BaseListValidator;
use App\Models\Task\Enum\TaskStatus;
use App\Models\Task\Enum\TaskType;
use App\Models\Task\Filter\TaskFilter;
use Symfony\Component\Validator\Constraints as Assert;

class GetTasksValidator extends BaseListValidator
{

    protected function getAllowedSortFields(): array
    {
        return TaskFilter::ALLOWED_SORTS;
    }

    protected function getAdditionalConstraints(): array
    {
        return [
            'status' => new Assert\Optional([
                new Assert\Choice(
                    array_map(
                        function (TaskStatus $status) {
                            return $status->value;
                        },
                        TaskStatus::cases()
                    )
                ),
            ]),
            'type' => new Assert\Optional([
                new Assert\Choice(
                    array_map(
                        function (TaskType $type) {
                            return $type->value;
                        },
                        TaskType::cases()
                    )
                ),
            ]),
        ];
    }
}
