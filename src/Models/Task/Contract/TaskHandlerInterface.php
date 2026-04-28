<?php

namespace App\Models\Task\Contract;

use App\Models\Task\Task;

interface TaskHandlerInterface
{
    public function supports(string $type): bool;

    public function handle(Task $task): void;
}
