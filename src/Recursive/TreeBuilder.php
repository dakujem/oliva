<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Recursive;

use Dakujem\Oliva\MovableNodeContract;
use Dakujem\Oliva\TreeNodeContract;
use InvalidArgumentException;
use LogicException;

/**
 * Recursive tree builder.
 * Builds trees from flat data collections.
 * Each item of a collection must contain self reference (an ID) and a reference to its parent.
 *
 * Example for collections containing items with `id` and `parent` props, the root being the node with `null` parent:
 * ```
 * $builder = new TreeBuilder(
 *     fn(MyItem $item) => new Node($item),
 *     fn(MyItem $item) => $item->id,
 *     fn(MyItem $item) => $item->parent,
 * );
 * $root = $builder->build( $myItemCollection );
 * ```
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class TreeBuilder
{
    /**
     * Node factory,
     * signature `fn(mixed $data): MovableNodeContract`.
     * @var callable
     */
    private $node;

    /**
     * Extractor of the self reference,
     * signature `fn(mixed $data, mixed $inputIndex, TreeNodeContract $node): string|int`.
     * @var callable
     */
    private $self;

    /**
     * Extractor of the parent reference,
     * signature `fn(mixed $data, mixed $inputIndex, TreeNodeContract $node): string|int|null`.
     * @var callable
     */
    private $parent;

    /**
     * Callable that detects the root node.
     * Signature `fn(mixed $data, mixed $inputIndex, TreeNodeContract $node, string|int|null $parentRef, string|int $selfRef): bool`.
     * @var callable
     */
    private $root;

    public function __construct(
        callable $node,
        callable $self,
        callable $parent,
        string|int|callable|null $root = null,
    ) {
        $this->node = $node;
        $this->self = $self;
        $this->parent = $parent;
        if (null === $root || is_string($root) || is_int($root)) {
            // By default, the root node is detected by having the parent ref equal to `null`.
            // By passing in a string or an integer, the root node will be detected by comparing that value to the node's parent value.
            // For custom "is root" detector, use a callable.
            $this->root = fn($data, $inputIndex, $node, $parentRef, $selfRef): bool => $parentRef === $root;
        } elseif (is_callable($root)) {
            $this->root = $root;
        } else {
            throw new InvalidArgumentException();
        }
    }

    public function build(iterable $input): TreeNodeContract
    {
        [$root] = $this->processData(
            input: $input,
            nodeFactory: $this->node,
            selfRefExtractor: $this->self,
            parentRefExtractor: $this->parent,
            isRoot: $this->root,
        );
        return $root;
    }

    private function processData(
        iterable $input,
        callable $nodeFactory,
        callable $selfRefExtractor,
        callable $parentRefExtractor,
        callable $isRoot,
    ): array {
        //
        // This algo works in two passes.
        //
        // The first pass indexes the data and builds a map of nodes and their children.
        // The second pass recursively connects all that indexed data starting from the root node.
        //

        /** @var array<string|int, array<int, string|int>> $childRegister */
        $childRegister = [];
        /** @var array<string|int, MovableNodeContract> $nodeRegister */
        $nodeRegister = [];
        $rootFound = false;
        $rootRef = null;

        // The data indexing pass.
        foreach ($input as $inputIndex => $data) {
            // Create a node using the provided factory.
            $node = $nodeFactory($data, $inputIndex);

            // Check for consistency.
            if (!$node instanceof MovableNodeContract) {
                // TODO improve exceptions
                throw new LogicException('The node factory must return a movable node instance.');
            }

            $self = $selfRefExtractor($data, $inputIndex, $node);
            $parent = $parentRefExtractor($data, $inputIndex, $node);

            if (isset($nodeRegister[$self])) {
                // TODO improve exceptions
                throw new LogicException('Duplicate node reference: ' . $self);
            }
            $nodeRegister[$self] = $node;

            // When this node is the root, it has no parent.
            if (!$rootFound && $isRoot($data, $inputIndex, $node, $parent, $self)) {
                $rootRef = $self;
                $rootFound = true;
                continue;
            }

            if (!isset($childRegister[$parent])) {
                $childRegister[$parent] = [];
            }
            $childRegister[$parent][] = $self;
        }

        if (!$rootFound) {
            // TODO improve exceptions
            throw new LogicException('No root node found.');
        }

        // The tree reconstruction pass.
        $this->connectNode(
            $nodeRegister,
            $childRegister,
            $rootRef,
        );

        return [
            $nodeRegister[$rootRef],
            $nodeRegister,
            $childRegister,
        ];
    }

    /**
     * @param array<string|int, MovableNodeContract> $nodeRegister
     * @param array<string|int, array<int, string|int>> $childRegister
     */
    private function connectNode(
        array $nodeRegister,
        array $childRegister,
        string|int|null $ref,
    ): void {
        $parent = $nodeRegister[$ref];
        foreach ($childRegister[$ref] ?? [] as $childRef) {
            $child = $nodeRegister[$childRef];
            $child->setParent($parent);
            $parent->addChild($child);
            $this->connectNode(
                $nodeRegister,
                $childRegister,
                $childRef,
            );
        }
    }
}
