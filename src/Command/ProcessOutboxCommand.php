<?php

namespace App\Command;

use App\UseCase\Task\ProcessOutboxUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:outbox:process',
    description: 'Process outbox events and send them to Kafka',
)]
class ProcessOutboxCommand extends Command
{
    public function __construct(
        private ProcessOutboxUseCase $useCase,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Batch size', 100)
            ->addOption('loop', null, InputOption::VALUE_NONE, 'Run in loop mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');
        $loop = (bool) $input->getOption('loop');

        do {
            $this->useCase->execute($limit);

            if (!$loop) {
                break;
            }

            usleep(500_000);

        } while (true);

        return Command::SUCCESS;
    }
}
