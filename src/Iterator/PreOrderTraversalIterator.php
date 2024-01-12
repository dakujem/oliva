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
    private $index;

    public function __construct(
        private TreeNodeContract $node,
        ?callable $index = null,
        private ?array $startingVector = null,
    ) {
        $this->index = $index ?? fn(TreeNodeContract $node, array $vector, int $seq, int $counter): int => $counter;
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
        // The yielded index is calculated based on the index function.
        // By default, it returns an incrementing sequence to prevent issues with `iterator_to_array` casts.
        yield ($this->index)($node, $vector, $nodeSeq, $counter->touch()) => $node;

        // $seq is the child sequence number, within the given parent node.
        $seq = 0;
        foreach ($node->children() as $index => $child) {
            yield from $this->generate($child, array_merge($vector, [$index]), $seq, $counter);
            $seq += 1;
        }
    }
}
