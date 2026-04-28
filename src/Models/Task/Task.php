<?php

namespace App\Models\Task;

use App\Models\Task\Enum\TaskStatus;
use App\Models\Task\Enum\TaskType;
use App\Models\Task\Exception\InvalidTaskStatusException;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tasks')]
#[ORM\HasLifecycleCallbacks]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\Column(type: 'bigint')]
    private int $videoId;

    #[ORM\Column(enumType: TaskType::class)]
    private TaskType $type;

    #[ORM\Column(enumType: TaskStatus::class)]
    private TaskStatus $status;

    #[ORM\Column(type: 'integer')]
    private int $priority = 0;

    #[ORM\Column(type: 'integer')]
    private int $attemptsCount = 0;

    #[ORM\Column(type: 'integer')]
    private int $maxAttempts = 3;

    #[ORM\Column(type: 'json')]
    private array $inputData;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $outputData = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $lastError = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $nextRetryAt = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $processingKey = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $source;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $finishedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Version]
    #[ORM\Column(type: 'integer')]
    private int $version = 1;

    public function __construct(
        int $videoId,
        TaskType $type,
        array $inputData,
        string $source,
        ?string $processingKey = null
    )
    {
        $this->videoId = $videoId;
        $this->type = $type;
        $this->inputData = $inputData;
        $this->source = $source;
        $this->processingKey = $processingKey;
        $this->status = TaskStatus::pending;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markCompleted(): void
    {
        if ($this->status !== TaskStatus::running) {
            throw new InvalidTaskStatusException('Task must be running to complete.');
        }

        $this->status = TaskStatus::completed;
        $this->finishedAt = new \DateTimeImmutable();
    }

    public function markRunning(): void
    {
        if ($this->status !== TaskStatus::pending) {
            throw new InvalidTaskStatusException('Task must be pending to start');
        }

        $this->status = TaskStatus::running;
        $this->startedAt = new \DateTimeImmutable();
    }

    public function markFailed(): void
    {
        if ($this->status !== TaskStatus::running) {
            throw new InvalidTaskStatusException('Task must be running to move to the failed status');
        }

        $this->status = TaskStatus::failed;
    }

    public function markPending(): void
    {
        $this->status = TaskStatus::pending;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVideoId(): int
    {
        return $this->videoId;
    }

    public function getType(): TaskType
    {
        return $this->type;
    }

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function getStatusValue(): string
    {
        return $this->status->value;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getAttemptsCount(): int
    {
        return $this->attemptsCount;
    }

    public function setAttemptsCount(int $attemptsCount): void
    {
        $this->attemptsCount = $attemptsCount;
    }

    public function setLastError(?array $lastError): void
    {
        $this->lastError = $lastError;
    }

    public function setMaxAttempts(int $maxAttempts): void
    {
        $this->maxAttempts = $maxAttempts;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function setFinishedAt(?\DateTimeImmutable $finishedAt): void
    {
        $this->finishedAt = $finishedAt;
    }

    public function setNextRetryAt(?\DateTimeImmutable $nextRetryAt): void
    {
        $this->nextRetryAt = $nextRetryAt;
    }
}
