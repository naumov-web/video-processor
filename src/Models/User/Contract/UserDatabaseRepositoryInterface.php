<?php

namespace App\Models\User\Contract;

use App\Models\User\User;

interface UserDatabaseRepositoryInterface
{
    public function findOneByEmail(string $email): ?User;

    public function save(User $user): void;
}
