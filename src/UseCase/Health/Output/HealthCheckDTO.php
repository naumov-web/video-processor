<?php

namespace App\UseCase\Health\Output;

class HealthCheckDTO
{
    public function __construct(
        public readonly bool $success,
        public readonly array $checks,
    ) {}
}
