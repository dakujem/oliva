<?php

declare(strict_types=1);

namespace Dakujem\Oliva;


/**
 * Low-level contract for node manipulation.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
interface MovableNodeContract extends TreeNodeContract
{
    /**
     * Link the parent node.
     */
    public function setParent(?TreeNodeContract $parent): self;

    /**
     * Add a child,
     * optionally specifying a key (index).
     */
    public function addChild(TreeNodeContract $child, string|int|null $key = null): self;

    /**
     * Remove a specific child.
     */
    public function removeChild(TreeNodeContract|string|int $child): self;

    /**
     * Remove all children.
     */
    public function removeChildren(): self;
}