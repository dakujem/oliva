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
class LevelOrderTraversalIterator implements IteratorAggregate
{
    public function __construct(
        private TreeNodeContract $node,
    ) {
    }

    public function getIterator(): Generator
    {
        return $this->generate($this->node, null);
    }

    private function generate(TreeNodeContract $node, $index): Generator
    {
        $q = [
            // TODO what indices to yield? vectors?
            [$node, $index],
        ];
        while ($tuple = array_shift($q)) {
            [$node, $i] = $tuple;
            yield $i => $node;
            foreach ($node->children() as $childIndex => $child) {
                $q[] = [$child, $childIndex]; // TODO what indices to yield? vectors?
            }
        }
    }
}
