<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Iterator\Support;

/**
 * @internal
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class Counter
{
    private int $i;

    public function __construct(
        int $start = 0,
    ) {
        $this->i = $start;
    }

    /**
     * Return the current value.
     */
    public function current(): int
    {
        return $this->i;
    }

    /**
     * Return the current value and increment the counter afterward.
     */
    public function touch(): int
    {
        return $this->i++;
    }

    /**
     * Increment the counter, then return the value.
     */
    public function next(): int
    {
        return ++$this->i;
    }
}
