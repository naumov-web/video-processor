<?php

namespace App\UseCase\User;

use App\Models\User\Contract\UserDatabaseRepositoryInterface;
use App\Models\User\Exception\InvalidCredentialsException;
use App\Models\User\Exception\UserNotFoundException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginUserUseCase
{
    public function __construct(
        private readonly UserDatabaseRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly JWTTokenManagerInterface $jwt,
    ) {}

    public function execute(string $email, string $password): string
    {
        $user = $this->userRepository->findOneByEmail($email);

        if (!$user) {
            throw new UserNotFoundException('User not found');
        }

        if (!$this->hasher->isPasswordValid($user, $password)) {
            throw new InvalidCredentialsException('Invalid credentials');
        }

        return $this->jwt->create($user);
    }
}
