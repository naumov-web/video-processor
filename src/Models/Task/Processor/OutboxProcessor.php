<?php

namespace App\Models\Task\Processor;

use App\Infrastructure\Kafka\KafkaProducer;
use App\Models\Task\Contract\OutboxEventDatabaseRepositoryInterface;
use App\Models\Task\OutboxEvent;
use Doctrine\ORM\EntityManagerInterface;

class OutboxProcessor
{
    public function __construct(
        private OutboxEventDatabaseRepositoryInterface $outboxEventDatabaseRepository,
        private KafkaProducer $kafkaProducer,
        private EntityManagerInterface $em,
    ) {}

    public function processBatch(int $limit = 100): void
    {
        $events = $this->outboxEventDatabaseRepository->findPending($limit);

        foreach ($events as $event) {
            try {
                $this->handleEvent($event);
                $event->markSent();
            } catch (\Throwable $e) {
                $event->markFailed($e->getMessage());
            }
        }

        $this->em->flush();
    }

    private function handleEvent(OutboxEvent $event): void
    {
        $this->kafkaProducer->publish(
            $event->getEventType(),
            $event->getPayload(),
            $event->getAggregateId()
        );
    }
}
