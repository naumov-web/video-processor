<?php

namespace App\Models\Task\Contract;

use App\Models\Task\OutboxEvent;

interface OutboxEventDatabaseRepositoryInterface
{
    public function save(OutboxEvent $event): void;

    /**
     * @return OutboxEvent[]
     */
    public function findPending(int $limit = 100): array;
}
