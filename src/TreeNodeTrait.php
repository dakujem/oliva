<?php

declare(strict_types=1);

namespace Dakujem\Oliva;

/**
 * Base trait implementing TreeNodeContract functionality.
 * @see TreeNodeContract
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
trait TreeNodeTrait
{
    protected array $children = [];
    protected ?TreeNodeContract $parent = null;

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
     * Returns `true` if the node has no parent, i.e., it is a root node.
     */
    public function isRoot(): bool
    {
        return null === $this->parent;
    }

    /**
     * Get the root node.
     * The returned node may be the node itself when it already is the root node.
     */
    public function root(): TreeNodeContract
    {
        $root = $this;
        while (!$root->isRoot()) {
            $root = $root->parent();
        }
        return $root;
    }
}
