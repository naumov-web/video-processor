<?php

namespace App\Models\Common\DTO;

/**
 * @template T of object
 */
class PaginatedResultDTO
{
    /**
     * @param T $items
     */
    public function __construct(
        public $items,
        public int $total
    ) {}
}
