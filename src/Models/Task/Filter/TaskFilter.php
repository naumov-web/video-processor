<?php

namespace App\Models\Task\Filter;

use App\Models\Common\Filter\BaseFilter;

class TaskFilter extends BaseFilter
{
    public ?string $status = null;
    public ?string $type = null;

    protected function getAllowedSorts(): array
    {
        return [
            'id',
            'createdAt',
            'status',
            'type',
            'videoId'
        ];
    }
}
