<?php

declare(strict_types=1);

namespace Dakujem\Oliva;

use Dakujem\Oliva\Exceptions\ChildKeyCollision;
use Dakujem\Oliva\Iterator\Traversal;
use Generator;
use IteratorAggregate;
use JsonSerializable;

/**
 * TODO
 * The initial idea was to implement a node without implementing MovableNodeContract.
 * But the challenge is that when constructing the tree, one can not provide both the parent and the children via the constructor. A tree construction thus becomes tricky.
 *
 * TODO + refactor the internals
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
class ImmovableNode implements TreeNodeContract, DataNodeContract, IteratorAggregate, JsonSerializable
{
    /**
     * Create a data node.
     *
     * Note that passing children or parent here does NOT make a link the other way around. Use the Tree utility for that.
     * @see Tree::link()
     * @see Tree::linkChildren()
     */
    public function __construct(
        protected mixed $data,
        protected array $children = [],
        protected ?TreeNodeContract $parent = null,
    ) {
    }

    /**
     * Get the node's children.
     *
     * @return iterable<int|string,TreeNodeContract>
     */
    public function children(): array
    {
        return $this->children;
    }

    /**
     * Get the node's parent, if any.
     */
    public function parent(): ?TreeNodeContract
    {
        return $this->parent;
    }

    /**
     * Discover whether the given node is one of this node's children (or the given key points to one of them).
     */
    public function hasChild(TreeNodeContract|string|int $child): bool
    {
        if (is_scalar($child)) {
            $key = $child;
            $child = $this->child($key);
        } else {
            $key = $this->childKey($child);
        }
        // Note: Important to check both conditions.
        return null !== $child && null !== $key;
    }

    /**
     * Get a specific child, if possible.
     * Returns `null` when there is no such child.
     */
    public function child(int|string $key): ?TreeNodeContract
    {
        return $this->children[$key] ?? null;
    }

    /**
     * Get a child's key (index), if possible.
     * Returns `null` when the node is not a child.
     */
    public function childKey(TreeNodeContract $node): string|int|null
    {
        foreach ($this->children as $key => $child) {
            if ($child === $node) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Returns `true` if the node has no children, i.e. it is a leaf node.
     */
    public function isLeaf(): bool
    {
        return count($this->children) === 0;
    }

    /**
     * Returns `true` if the node has no parent, i.e. it is a root node.
     */
    public function isRoot(): bool
    {
        return null === $this->parent;
    }

    /**
     * Get the root node.
     * May be self.
     */
    public function root(): TreeNodeContract
    {
        $root = $this;
        while (!$root->isRoot()) {
            $root = $root->parent();
        }
        return $root;
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
