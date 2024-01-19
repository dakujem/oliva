<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Iterator;

use Dakujem\Oliva\Seed;
use Dakujem\Oliva\TreeNodeContract;
use Iterator;
use RecursiveIterator;

/**
 * An implementation of native `RecursiveIterator`, to be used with `RecursiveIteratorIterator`.
 * @see RecursiveIteratorIterator
 *
 * ```
 * new RecursiveIteratorIterator(new Native($root), RecursiveIteratorIterator::SELF_FIRST)
 * ```
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class Native implements RecursiveIterator
{
    private Iterator $iterator;

    public function __construct(
        TreeNodeContract|iterable $nodes,
    ) {
        $this->iterator = Seed::iterator(
            input: $nodes instanceof TreeNodeContract ? [$nodes] : $nodes,
        );
    }

    public function hasChildren(): bool
    {
        return !$this->current()->isLeaf();
    }

    public function getChildren(): ?self
    {
        return new self(
            nodes: $this->current()->children(),
        );
    }

    public function current(): TreeNodeContract
    {
        return $this->iterator->current();
    }

    public function next(): void
    {
        $this->iterator->next();
    }

    public function key(): mixed
    {
        return $this->iterator->key();
    }

    public function valid(): bool
    {
        return $this->iterator->valid();
    }

    public function rewind(): void
    {
        $this->iterator->rewind();
    }
}
