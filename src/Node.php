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
class Node implements TreeNodeContract, DataNodeContract, MovableNodeContract, IteratorAggregate, JsonSerializable
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
