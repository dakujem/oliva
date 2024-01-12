<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Iterator;

use Dakujem\Oliva\TreeNodeContract;
use Generator;
use IteratorAggregate;

/**
 * Depth-first search post-order traversal iterator.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
class PostOrderTraversalIterator implements IteratorAggregate
{
    public function __construct(
        private TreeNodeContract $node,
    ) {
    }

    public function getIterator(): Generator
    {
        return $this->generate($this->node, null);
    }

    private function generate(TreeNodeContract $node, $index)
    {
//        yield [] => $this->node; // TODO what indices to yield? vector?
        // TODO what index to yield for the root?
        foreach ($node->children() as $index => $child) {
            yield from $this->generate($child, $index); // TODO yield indices?
        }
        yield $index => $node; // TODO what indices to yield? vectors?
    }
}
