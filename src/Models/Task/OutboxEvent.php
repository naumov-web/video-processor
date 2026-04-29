<?php

namespace App\Models\Task;

use App\Models\Task\Repository\OutboxEventDatabaseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OutboxEventDatabaseRepository::class)]
#[ORM\Table(name: 'outbox_events')]
class OutboxEvent
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $eventType;

    #[ORM\Column(type: 'bigint')]
    private int $aggregateId;

    #[ORM\Column(type: 'json')]
    private array $payload;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: 'integer')]
    private int $retriesCount = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $lastError = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $processedAt = null;

    public function __construct(
        string $eventType,
        int $aggregateId,
        array $payload
    ) {
        $this->eventType = $eventType;
        $this->aggregateId = $aggregateId;
        $this->payload = $payload;
        $this->createdAt = new \DateTimeImmutable();
        $this->status = self::STATUS_PENDING;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getAggregateId(): int
    {
        return $this->aggregateId;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function markSent(): void
    {
        $this->status = self::STATUS_SENT;
        $this->processedAt = new \DateTimeImmutable();
    }

    public function markFailed(string $error): void
    {
        $this->retriesCount++;
        $this->lastError = $error;

        if ($this->retriesCount >= 3) {
            $this->status = self::STATUS_FAILED;
        }
    }

    public function getProcessedAt(): ?\DateTimeImmutable
    {
        return $this->processedAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }
}
