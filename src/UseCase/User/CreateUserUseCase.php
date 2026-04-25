<?php

namespace App\UseCase\User;

use App\Models\User\Contract\UserDatabaseRepositoryInterface;
use App\Models\User\Exception\UserAlreadyExistsException;
use App\Models\User\User;
use App\UseCase\User\Input\CreateUserInputDTO;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserUseCase
{
    public function __construct(
        private UserDatabaseRepositoryInterface $users,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function execute(CreateUserInputDTO $input): User
    {
        $existingUser = $this->users->findOneByEmail($input->email);

        if ($existingUser !== null) {
            throw new UserAlreadyExistsException('User already exists');
        }

        $user = new User();
        $user->setEmail($input->email);
        $user->setRoles($input->roles);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $input->password
        );

        $user->setPassword($hashedPassword);
        $this->users->save($user);

        return $user;
    }
}
