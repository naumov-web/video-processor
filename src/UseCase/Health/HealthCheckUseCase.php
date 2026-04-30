<?php

namespace App\UseCase\Health;

use App\Infrastructure\Kafka\KafkaConsumer;
use App\UseCase\Health\Output\HealthCheckDTO;
use Doctrine\ORM\EntityManagerInterface;
use Redis;

class HealthCheckUseCase
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Redis $redis,
        private readonly KafkaConsumer $kafkaConsumer,
    ) {}

    public function execute(): HealthCheckDTO
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'kafka' => $this->checkKafka(),
        ];

        $success = !in_array(false, $checks, true);

        return new HealthCheckDTO(
            success: $success,
            checks: $checks,
        );
    }

    private function checkDatabase(): bool
    {
        try {
            $this->em->getConnection()->executeQuery('SELECT 1');
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function checkRedis(): bool
    {
        try {
            return $this->redis->ping();
        } catch (\Throwable) {
            return false;
        }
    }

    private function checkKafka(): bool
    {
        return $this->kafkaConsumer->check();
    }
}
