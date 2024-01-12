<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Iterator;

use Dakujem\Oliva\TreeNodeContract;
use Generator;
use IteratorAggregate;

/**
 * Breadth-first search (level-order) traversal iterator.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class LevelOrderTraversalIterator implements IteratorAggregate
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
        );
    }

    private function generate(TreeNodeContract $node, array $vector): Generator
    {
        // In BFS traversal a queue has to be used instead of recursion.
        $queue = [
            [$node, $vector, 0],
        ];
        $counter = 0;
        while ($tuple = array_shift($queue)) {
            [$node, $vector, $seq] = $tuple;

            // The yielded index is calculated based on the index function.
            // By default, it returns an incrementing sequence to prevent issues with `iterator_to_array` casts.
            yield ($this->index)($node, $vector, $seq, $counter) => $node;
            $counter += 1;

            $seq = 0;
            foreach ($node->children() as $index => $child) {
                $queue[] = [$child, array_merge($vector, [$index]), $seq];
                $seq += 1;
            }
        }
    }
}
