<?php

namespace App\Models\Task\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

class VideoStatisticDTO
{
    public function __construct(
        #[SerializedName('task_id')]
        public readonly int $taskId,

        public readonly string $status,

        public readonly string $type,

        #[SerializedName('created_at')]
        public readonly \DateTimeImmutable $createdAt,

        #[SerializedName('attempt_number')]
        public readonly int $attemptNumber,

        #[SerializedName('prev_attempt_at')]
        public readonly ?\DateTimeImmutable $prevAttemptAt,

        #[SerializedName('retry_delay')]
        public readonly ?string $retryDelay,

        #[SerializedName('total_tasks')]
        public readonly int $totalTasks,

        #[SerializedName('success_count')]
        public readonly int $successCount,

        #[SerializedName('failed_count')]
        public readonly int $failedCount,
    ) {}
}
