<?php

namespace App\Models\Common\Validator;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Exception\ValidationException;

abstract class BaseListValidator
{
    protected const ALLOWED_DIRECTIONS = ['asc', 'desc'];

    public function __construct(
        protected ValidatorInterface $validator
    ) {}

    abstract protected function getAllowedSortFields(): array;

    abstract protected function getAdditionalConstraints(): array;

    public function validate(array $data): void
    {
        $constraints = new Assert\Collection(array_merge(
            $this->getBaseConstraints(),
            $this->getAdditionalConstraints()
        ));

        $errors = $this->validator->validate($data, $constraints);

        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }
    }

    protected function getBaseConstraints(): array
    {
        return [
            'limit' => [
                new Assert\Optional([
                    new Assert\Type('integer'),
                    new Assert\Range(min: 1),
                ]),
            ],
            'offset' => [
                new Assert\Optional([
                    new Assert\Type('integer'),
                    new Assert\GreaterThanOrEqual(0),
                ]),
            ],
            'sortBy' => [
                new Assert\Optional([
                    new Assert\Choice($this->getAllowedSortFields()),
                ]),
            ],
            'direction' => [
                new Assert\Optional([
                    new Assert\Choice(self::ALLOWED_DIRECTIONS),
                ]),
            ],
        ];
    }
}
