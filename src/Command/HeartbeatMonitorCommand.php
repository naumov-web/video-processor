<?php

namespace App\Command;

use App\UseCase\Task\HeartbeatMonitorUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:task:heartbeat-monitor',
    description: 'Detect and retry stale tasks'
)]
class HeartbeatMonitorCommand extends Command
{
    private const DELAY = 5;

    public function __construct(
        private readonly HeartbeatMonitorUseCase $monitorUseCase,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        while (true) {
            $this->monitorUseCase->execute();

            sleep(self::DELAY);
        }

        return Command::SUCCESS;
    }
}
