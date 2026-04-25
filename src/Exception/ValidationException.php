<?php

namespace App\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends \RuntimeException
{
    public function __construct(
        private ConstraintViolationListInterface $errors
    ) {
        parent::__construct('Validation failed');
    }

    public function getErrors(): ConstraintViolationListInterface
    {
        return $this->errors;
    }
}
