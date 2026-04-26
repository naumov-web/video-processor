<?php

namespace App\Models\Common\Filter;

abstract class BaseFilter
{
    public const DEFAULT_LIMIT = 20;

    public int $limit;
    public int $offset;
    public string $sortBy;
    public string $direction;

    public function __construct(
        ?int $limit = null,
        ?int $offset = null,
        ?string $sortBy = null,
        ?string $direction = null
    ) {
        $this->limit = $limit ?? static::DEFAULT_LIMIT;
        $this->offset = $offset ?? 0;
        $this->sortBy = $sortBy ?? $this->getDefaultSort();
        $this->direction = $direction ?? 'desc';

        $this->normalize();
    }

    abstract protected function getAllowedSorts(): array;

    protected function getDefaultSort(): string
    {
        return 'id';
    }

    protected function normalize(): void
    {
        if ($this->limit <= 0) {
            $this->limit = static::DEFAULT_LIMIT;
        }

        if ($this->offset < 0) {
            $this->offset = 0;
        }

        if (!in_array($this->sortBy, $this->getAllowedSorts(), true)) {
            $this->sortBy = $this->getDefaultSort();
        }

        $this->direction = strtolower($this->direction) === 'asc' ? 'asc' : 'desc';
    }
}
