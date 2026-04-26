<?php

namespace App\Models\Task\Collection;

use App\Models\Task\Task;

class TaskCollection implements \IteratorAggregate, \Countable
{
    /** @var Task[] */
    private array $items;

    /**
     * @param Task[] $items
     */
    public function __construct(array $items)
    {
        foreach ($items as $item) {
            if (!$item instanceof Task) {
                throw new \InvalidArgumentException('All items must be instances of Task');
            }
        }

        $this->items = array_values($items);
    }

    /**
     * @return Task[]
     */
    public function toArray(): array
    {
        return $this->items;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }
}
