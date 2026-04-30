<?php

namespace App\UseCase\Task;

use App\Models\Task\Processor\OutboxProcessor;

class ProcessOutboxUseCase
{
    public function __construct(
        private readonly OutboxProcessor $processor,
    ) {}

    public function execute(int $limit = 100): void
    {
        $this->processor->processBatch($limit);
    }
}
