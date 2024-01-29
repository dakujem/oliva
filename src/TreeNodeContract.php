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
     * The implementations MUST ensure the keys are valid PHP array keys (only int or string).
     *
     * @return iterable<int|string,TreeNodeContract>
     */
    public function children(): iterable;

    /**
     * Discover whether the given node is one of this node's children (or the given key points to one of them).
     */
    public function hasChild(TreeNodeContract|string|int $child): bool;

    /**
     * Get a specific child, if possible.
     * Returns `null` when there is no such child.
     */
    public function child(string|int $key): ?TreeNodeContract;

    /**
     * Get a child's key (index), if possible.
     * Returns `null` when the node is not a child.
     */
    public function childKey(TreeNodeContract $node): string|int|null;

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
