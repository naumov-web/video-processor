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

    private bool $running = true;

    public function __construct(
        private readonly HeartbeatMonitorUseCase $monitorUseCase,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        pcntl_async_signals(true);

        pcntl_signal(SIGTERM, function () {
            $this->running = false;
        });

        pcntl_signal(SIGINT, function () {
            $this->running = false;
        });

        while ($this->running) {
            $this->monitorUseCase->execute();
            sleep(self::DELAY);
        }

        return Command::SUCCESS;
    }
}
