<?php

namespace App\Command;

use App\Infrastructure\Kafka\KafkaConsumer;
use App\Models\Task\Enum\OutboxEventType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:kafka:consume',
    description: 'Consume Kafka messages',
)]
class KafkaConsumeCommand extends Command
{
    public function __construct(
        private KafkaConsumer $consumer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->consumer->subscribe(OutboxEventType::taskCreated->value);
        $this->consumer->consume();

        return Command::SUCCESS;
    }
}
