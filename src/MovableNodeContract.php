<?php

declare(strict_types=1);

namespace Dakujem\Oliva;


/**
 * Low-level contract for node manipulation.
 *
 * These methods are used for tree reconstruction
 * and for performance reasons SHOULD NOT alter other nodes nor contain side effects.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
interface MovableNodeContract extends TreeNodeContract
{
    /**
     * Set the parent node.
     *
     * The implementation SHOULD NOT alter the parent node,
     * namely, the call SHOULD NOT add the node among the children.
     */
    public function setParent(?TreeNodeContract $parent): self;

    /**
     * Add a node to the children list,
     * optionally specifying its key (index).
     *
     * The implementation SHOULD NOT alter the child node,
     * namely, the call SHOULD NOT set the parent on the node.
     */
    public function addChild(TreeNodeContract $child, string|int|null $key = null): self;

    /**
     * Remove a specific child from the list of children.
     *
     * The implementation SHOULD NOT alter the child node,
     * namely, the call SHOULD NOT unset the parent on the child node.
     */
    public function removeChild(TreeNodeContract|string|int $child): self;

    /**
     * Remove all children.
     *
     * The implementation SHOULD NOT alter the children nodes,
     * namely, the call SHOULD NOT unset the parent on the nodes.
     */
    public function removeChildren(): self;
}