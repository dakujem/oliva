<?php

declare(strict_types=1);

namespace Dakujem\Oliva;

use Dakujem\Oliva\Exceptions\ChildKeyCollision;

/**
 * Trait implementing MovableNodeContract functionality.
 * @see MovableNodeContract
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
trait MovableNodeTrait
{
    use TreeNodeTrait;

    /**
     * Set the parent node.
     *
     * Does NOT alter the new parent node, nor the original parent node.
     * Namely, the call does NOT alter the lists of children.
     */
    public function setParent(?TreeNodeContract $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Add a node to the children list,
     * optionally specifying its key (index).
     *
     * Does NOT set the parent on the child node.
     */
    public function addChild(TreeNodeContract $child, string|int|null $key = null): self
    {
        if (null === $key) {
            $this->children[] = $child;
        } elseif (!isset($this->children[$key])) {
            $this->children[$key] = $child;
        } elseif ($child !== $this->children[$key]) {
            throw (new ChildKeyCollision('Collision not allowed: ' . $key))
                ->tag('parent', $this)
                ->tag('child', $child)
                ->tag('key', $key);
        }
        return $this;
    }

    /**
     * Remove a specific child from the list of children.
     *
     * Does NOT unset the parent of the child being removed.
     */
    public function removeChild(TreeNodeContract|string|int $child): self
    {
        $key = is_scalar($child) ? $child : $this->childKey($child);
        if (null !== $key) {
            unset($this->children[$key]);
        }
        return $this;
    }

    /**
     * Remove all children.
     *
     * Does NOT unset the parent of the children nodes being removed.
     */
    public function removeChildren(): self
    {
        $this->children = [];
        return $this;
    }
}
