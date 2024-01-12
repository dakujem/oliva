<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Iterator;

use Dakujem\Oliva\TreeNodeContract;
use Generator;
use IteratorAggregate;

/**
 * Depth-first search pre-order traversal iterator.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class PreOrderTraversalIterator implements IteratorAggregate
{
    /** @var callable */
    private $key;

    public function __construct(
        private TreeNodeContract $node,
        ?callable $key = null,
        private ?array $startingVector = null,
    ) {
        $this->key = $key ?? fn(TreeNodeContract $node, array $vector, int $seq, int $counter): int => $counter;
    }

    public function getIterator(): Generator
    {
        return $this->generate(
            $this->node,
            $this->startingVector ?? [],
            0,
            new Counter(),
        );
    }

    private function generate(TreeNodeContract $node, array $vector, int $nodeSeq, Counter $counter)
    {
        // The yielded key is calculated by the key function.
        // By default, it returns an incrementing sequence to prevent issues with `iterator_to_array` casts.
        yield ($this->key)($node, $vector, $nodeSeq, $counter->touch()) => $node;

        // $seq is the child sequence number, within the given parent node.
        $seq = 0;
        foreach ($node->children() as $index => $child) {
            yield from $this->generate($child, array_merge($vector, [$index]), $seq, $counter);
            $seq += 1;
        }
    }
}
