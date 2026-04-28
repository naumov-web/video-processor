<?php

namespace App\Infrastructure\Kafka;

use App\UseCase\Task\ProcessTaskUseCase;
use RdKafka\Conf;
use RdKafka\KafkaConsumer as CoreKafkaConsumer;

class KafkaConsumer
{
    private CoreKafkaConsumer $consumer;

    public function __construct(
        string $brokers,
        private ProcessTaskUseCase $processTaskUseCase
    ) {
        $conf = new Conf();

        $conf->set('bootstrap.servers', $brokers);
        $conf->set('group.id', 'task-consumers');
        $conf->set('auto.offset.reset', 'earliest');

        $this->consumer = new CoreKafkaConsumer($conf);
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
                    $data = json_decode($message->payload, true);

                    if (!isset($data['task_id'])) {
                        return;
                    }
                    $this->processTaskUseCase->execute((int)$data['task_id']);

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
