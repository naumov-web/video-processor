<?php

namespace App\Models\Task\Validator;

use App\Exception\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CreateVideoTaskValidator
{
    public function __construct(
        private ValidatorInterface $validator
    ) {}

    public function validate(array $data): void
    {
        $constraints = new Assert\Collection([
            'videoId' => [
                new Assert\NotBlank(),
                new Assert\Type('integer'),
            ],
            'type' => [
                new Assert\NotBlank(),
                new Assert\Choice([
                    'choices' => ['transcode', 'thumbnail', 'ai_tagging'],
                ]),
            ],
            'inputData' => [
                new Assert\NotNull(),
                new Assert\Type('array'),
            ],
            'source' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 50]),
            ],
        ]);

        $errors = $this->validator->validate($data, $constraints);

        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }
    }
}
