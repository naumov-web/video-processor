<?php

namespace App\UseCase\User\Input;

class CreateUserInputDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public array $roles = ['ROLE_ADMIN'],
    ) {}
}
