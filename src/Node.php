<?php

declare(strict_types=1);

namespace Dakujem\Oliva;

use Dakujem\Oliva\Iterator\PreOrderTraversalIterator;
use Exception;
use IteratorAggregate;
use JsonSerializable;

/**
 * Basic data node implementation.
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
            $key = $child;
            $child = $this->child($key);
        } else {
            $key = $this->childKey($child);
        }
        // Note: Important to check both conditions.
        return null !== $child && null !== $key;
    }

    public function child(int|string $key): ?TreeNodeContract
    {
        return $this->children[$key] ?? null;
    }

    public function childKey(TreeNodeContract $node): string|int|null
    {
        foreach ($this->children as $key => $child) {
            if ($child === $node) {
                return $key;
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

    public function addChild(TreeNodeContract $child, string|int|null $key = null): self
    {
        if (null === $key) {
            $this->children[] = $child;
        } elseif (!isset($this->children[$key])) {
            $this->children[$key] = $child;
        } else {
            throw new Exception('Collision not allowed.');
        }
        return $this;
    }

    public function removeChild(TreeNodeContract|string|int $child): self
    {
        $key = is_scalar($child) ? $child : $this->childKey($child);
        if (null !== $key) {
            unset($this->children[$key]);
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

    public function jsonSerialize(): array
    {
        return [
            'data' => $this->data(),
            'children' => $this->children(),
        ];
    }
}
