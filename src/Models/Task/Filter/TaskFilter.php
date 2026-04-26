<?php

namespace App\Models\Task\Filter;

use App\Models\Common\Filter\BaseFilter;

class TaskFilter extends BaseFilter
{
    public const ALLOWED_SORTS = [
        'id',
        'videoId'
    ];
    public ?string $status = null;
    public ?string $type = null;

    protected function getAllowedSorts(): array
    {
        return self::ALLOWED_SORTS;
    }
}
