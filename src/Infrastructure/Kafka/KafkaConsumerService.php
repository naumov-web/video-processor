<?php

namespace App\Infrastructure\Kafka;

use RdKafka\Conf;
use RdKafka\KafkaConsumer;

class KafkaConsumerService
{
    private KafkaConsumer $consumer;

    public function __construct(string $brokers)
    {
        $conf = new Conf();

        $conf->set('bootstrap.servers', $brokers);
        $conf->set('group.id', 'task-consumers');
        $conf->set('auto.offset.reset', 'earliest');

        $this->consumer = new KafkaConsumer($conf);
    }

    public function subscribe(string $topic): void
    {
        $this->consumer->subscribe([$topic]);
    }

    public function consume(): void
    {
        while (true) {
            $message = $this->consumer->consume(1000);

            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    echo "Received: " . $message->payload . PHP_EOL;
                    break;

                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    break;

                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    break;

                default:
                    echo "Error: " . $message->errstr() . PHP_EOL;
                    break;
            }
        }
    }
}
