<?php

namespace App\Command;

use App\Models\User\Contract\UserDatabaseRepositoryInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:jwt:create',
    description: 'Create a new JWT token',
)]
class CreateJwtCommand  extends Command
{
    public function __construct(
        private UserDatabaseRepositoryInterface $userDatabaseRepository,
        private JWTTokenManagerInterface $jwtManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'User email');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (string) $input->getArgument('email');
        $user = $this->userDatabaseRepository->findOneByEmail($email);

        if (!$user) {
            $output->writeln('<error>User not found</error>');
            return Command::FAILURE;
        }

        $token = $this->jwtManager->create($user);

        $output->writeln('<info>JWT Token:</info>');
        $output->writeln($token);

        return Command::SUCCESS;
    }
}
