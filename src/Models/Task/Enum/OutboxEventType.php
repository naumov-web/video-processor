<?php

namespace App\Models\Task\Enum;

enum OutboxEventType: string
{
    case taskCreated = 'task.created';
}
