<?php

declare(strict_types=1);

namespace Dakujem\Oliva;

use Dakujem\Oliva\Iterator\Traversal;
use Generator;
use IteratorAggregate;
use JsonSerializable;

/**
 * Flexible data node implementation.
 *
 * Note: Iterating over a node will iterate over the whole subtree in pre-order DFS.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
class Node implements TreeNodeContract, DataNodeContract, MovableNodeContract, IteratorAggregate, JsonSerializable
{
    use MovableNodeTrait;

    /**
     * Create a data node.
     *
     * Note that passing children or the parent here does NOT make a link the other way around. Use the Tree utility for that.
     * @see Tree::link()
     * @see Tree::linkChildren()
     */
    public function __construct(
        protected mixed $data,
        array $children = [],
        ?TreeNodeContract $parent = null,
    ) {
        $this->children = $children;
        $this->parent = $parent;
    }

    /**
     * Get the node's assigned data.
     */
    public function data(): mixed
    {
        return $this->data;
    }

    /**
     * Set/assign new data to the data node.
     */
    public function fill(mixed $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Returns an iterator that iterates over this node and all its descendants,
     * in pre-order depth-first search order.
     * @return Generator
     */
    public function getIterator(): Generator
    {
        return Traversal::preOrder($this);
    }

    /**
     * @return mixed Intentionally returns mixed and not an array, so that overriding implementations may expand it to whatever value desired.
     */
    public function jsonSerialize(): mixed
    {
        return [
            'data' => $this->data(),
            'children' => $this->children(),
        ];
    }
}
