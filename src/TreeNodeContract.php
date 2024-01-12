<?php

declare(strict_types=1);

namespace Dakujem\Oliva;

/**
 * Base contract supporting tree traversals.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
interface TreeNodeContract
{
    /**
     * Get the node's parent, if any.
     */
    public function parent(): ?TreeNodeContract;

    /**
     * Get the node's children.
     *
     * @return iterable<int|string,TreeNodeContract>
     */
    public function children(): iterable;

    /**
     * Get a specific child, if possible.
     * Returns `null` when there is no such a child.
     */
    public function child(string|int $index): ?TreeNodeContract;

    /**
     * Get a child's index, if possible.
     * Returns `null` when the node is not a child.
     */
    public function childIndex(TreeNodeContract $node): string|int|null;

    /**
     * Discover whether the given node is one or the given index points to one of this node's children.
     */
    public function hasChild(TreeNodeContract|string|int $child): bool;

    /**
     * Returns `true` if the node has no children, i.e. it is a leaf node.
     */
    public function isLeaf(): bool;

    /**
     * Returns `true` if the node has no parent, i.e. it is a root node.
     */
    public function isRoot(): bool;

    /**
     * Get the root node.
     * May be self.
     */
    public function root(): TreeNodeContract;
}
