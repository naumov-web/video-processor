<?php

namespace App\Infrastructure\Kafka;

use RdKafka\Conf;
use RdKafka\Producer;

class KafkaProducer
{
    private Producer $producer;

    public function __construct(string $brokers)
    {
        $conf = new Conf();
        $conf->set('bootstrap.servers', $brokers);
        $this->producer = new Producer($conf);
        $this->producer->addBrokers($brokers);
    }

    public function publish(string $topicName, array $payload, string $key): void
    {
        $topic = $this->producer->newTopic($topicName);

        $topic->produce(
            RD_KAFKA_PARTITION_UA,
            0,
            json_encode($payload),
            $key
        );

        // важно!
        $this->producer->flush(1000);
    }
}
