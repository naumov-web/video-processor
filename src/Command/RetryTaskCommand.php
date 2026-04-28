<?php

namespace App\Command;

use App\UseCase\Task\RetryTaskUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:task:retry',
    description: 'Process retryable tasks',
)]
class RetryTaskCommand extends Command
{
    public function __construct(
        private RetryTaskUseCase $useCase,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Batch size', 100)
            ->addOption('sleep', null, InputOption::VALUE_OPTIONAL, 'Sleep seconds between iterations', 5)
            ->addOption('once', null, InputOption::VALUE_NONE, 'Run only once and exit');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');
        $sleep = (int) $input->getOption('sleep');
        $once  = (bool) $input->getOption('once');

        do {
            $this->useCase->execute($limit);

            if ($once) {
                break;
            }

            sleep($sleep);

        } while (true);

        return Command::SUCCESS;
    }
}
