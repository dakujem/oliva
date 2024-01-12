<?php

declare(strict_types=1);

namespace Dakujem\Oliva;

use Dakujem\Oliva\Iterator\PreOrderTraversalIterator;
use Exception;
use IteratorAggregate;
use JsonSerializable;

/**
 * Base data node implementation.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
class Node implements TreeNodeContract, DataNodeContract, MovableNodeContract, AttachableNodeContract, IteratorAggregate, JsonSerializable
{
    public function __construct(
        protected mixed $data,
        protected ?TreeNodeContract $parent = null,
        protected array $children = [],
    ) {
    }

    public function parent(): ?TreeNodeContract
    {
        return $this->parent;
    }

    public function children(): array
    {
        return $this->children;
    }

    public function hasChild(TreeNodeContract|string|int $child): bool
    {
        if (is_scalar($child)) {
            $index = $child;
            $child = $this->child($child);
        } else {
            $index = $this->childIndex($child);
        }
        // Note: Important to check both conditions.
        return null !== $child && null !== $index;
    }

    public function child(int|string $index): ?TreeNodeContract
    {
        return $this->children[$index] ?? null;
    }

    public function childIndex(TreeNodeContract $node): string|int|null
    {
        foreach ($this->children as $index => $child) {
            if ($child === $node) {
                return $index;
            }
        }
        return null;
    }

    public function isLeaf(): bool
    {
        return count($this->children) === 0;
    }

    public function isRoot(): bool
    {
        return null === $this->parent;
    }

    public function root(): TreeNodeContract
    {
        $root = $this;
        while (!$root->isRoot()) {
            $root = $root->parent();
        }
        return $root;
    }

    public function data(): mixed
    {
        return $this->data;
    }

    public function fill(mixed $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function setParent(?TreeNodeContract $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    public function addChild(TreeNodeContract $child, string|int|null $index = null): self
    {
        if (null === $index) {
            $this->children[] = $child;
        } elseif (!isset($this->children[$index])) {
            $this->children[$index] = $child;
        } else {
            throw new Exception('Collision not allowed.');
        }
        return $this;
    }

    public function removeChild(TreeNodeContract|string|int $child): self
    {
        $index = is_scalar($child) ? $child : $this->childIndex($child);
        if (null !== $index) {
            unset($this->children[$index]);
        }
        return $this;
    }

    public function removeChildren(): self
    {
        $this->children = [];
        return $this;
    }

    /**
     * @experimental TODO might contain design problems...
     */
    public function attach(TreeNodeContract $parent, string|int|null $index = null): self
    {
        // If the current parent is different, first detach the node.
        if (null !== $this->parent) {
            if ($this->parent === $parent) {
                // Already attached.
                return $this;
            }
            $this->detach();
        }
        $this->setParent($parent);
        $parent->attachChild($this, $index);
        // Possible attached event handling here.
        return $this;
    }

    /**
     * @experimental TODO might contain design problems...
     */
    public function detach(): self
    {
        $parent = $this->parent;
        if (null !== $parent) {
            $this->setParent(null);
            $parent->removeChild($this);
            // Possible detached event handling here.
        }
        return $this;
    }

    /**
     * @experimental TODO might contain design problems...
     */
    public function attachChild(TreeNodeContract $child, string|int|null $index = null): self
    {
        $existing = $this->childIndex($child);
        if (null !== $existing && $this->child($existing) === $child) {
            // Already added.
            return $this;
        }
        $this->addChild($child, $index);
        if (null !== $existing) {
            // The child has already been added under different index. Remove it.
            // Note:
            //   Important to keep this _after_ adding the node
            //   to prevent inconsistent state if adding failed due to a conflicting index.
            $this->removeChild($existing);
        }
        $child->attach($this, $index);
        // Possible child-attached event handling here.
        return $this;
    }

    /**
     * @experimental TODO might contain design problems...
     */
    public function detachChild(TreeNodeContract|string|int $child): self
    {
        if (is_scalar($child)) {
            $index = $child;
            $child = $this->child($child);
        } else {
            $index = $this->childIndex($child);
        }
        // Note: Important to check both conditions.
        if (null !== $child && null !== $index) {
            $this->removeChild($index);
            $child->setParent(null);
            $child->detach(); // Also call detach in case someone implements attached/detached events.
            // Possible child-detached event handling here.
        }
        return $this;
    }

    public function getIterator(): PreOrderTraversalIterator
    {
        return new PreOrderTraversalIterator($this);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'data' => $this->data(),
            'children' => $this->children(),
        ];
    }
}
