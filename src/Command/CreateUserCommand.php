<?php

namespace App\Command;

use App\UseCase\User\CreateUserUseCase;
use App\UseCase\User\Input\CreateUserInputDTO;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:user:create',
    description: 'Create a new user',
)]
class CreateUserCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('password', InputArgument::REQUIRED, 'User password')
            ->addOption(
                'role',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'User roles (can be multiple)',
                ['ROLE_ADMIN']
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (string) $input->getArgument('email');
        $password = (string) $input->getArgument('password');

        try {
            $request = new CreateUserInputDTO(
                email: $email,
                password: $password,
                roles: ['ROLE_USER']
            );

            $user = $this->useCase->execute($request);

            $output->writeln('<info>User created successfully:</info>');
            $output->writeln(sprintf('Email: %s', $user->getEmail()));

            return Command::SUCCESS;

        } catch (\DomainException $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $output->writeln('<error>Unexpected error: '.$e->getMessage().'</error>');
            return Command::FAILURE;
        }
    }
}
