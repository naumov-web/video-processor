<?php

namespace App\Models\Task\Enum;

enum TaskStatus: string
{
    case pending = 'pending';
    case running = 'running';
    case completed = 'completed';
    case failed = 'failed';
}
