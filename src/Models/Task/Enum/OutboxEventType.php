<?php

namespace App\Models\Task\Enum;

enum OutboxEventType: string
{
    case taskCreated = 'task.created';
    case taskCompleted = 'task.completed';
    case taskFailed = 'task.failed';
}
