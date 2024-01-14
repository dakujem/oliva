<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Recursive;

use Dakujem\Oliva\MovableNodeContract;
use Dakujem\Oliva\TreeNodeContract;
use LogicException;

/**
 * Recursive tree builder.
 * Builds trees from flat data collections.
 * Each item of a collection must contain self reference (an ID) and a reference to its parent.
 *
 * Example for collections containing items with `id` and `parent` props, the root being the node with `null` parent:
 * ```
 * $root = (new TreeBuilder())->build(
 *     $myItemCollection,
 *     fn(MyItem $item) => new Node($item),
 *     TreeBuilder::prop('id'),
 *     TreeBuilder::prop('parent'),
 * );
 * ```
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class TreeBuilder
{
    public static function prop(string $name): callable
    {
        return fn(object $item) => $item->{$name} ?? null;
    }

    public static function attr(string $name): callable
    {
        return fn(array $item) => $item[$name] ?? null;
    }

    public function build(
        iterable $input,
        callable $node,
        callable $self,
        callable $parent,
        string|int|null $root = null,
    ): TreeNodeContract {
        [$root] = $this->processData(
            input: $input,
            nodeFactory: $node,
            selfRefExtractor: $self,
            parentRefExtractor: $parent,
            rootRef: $root,
        );
        return $root;
    }

    private function processData(
        iterable $input,
        callable $nodeFactory,
        callable $selfRefExtractor,
        callable $parentRefExtractor,
        string|int|null $rootRef = null,
    ): array {
        /** @var array<string|int, array<int, string|int>> $childRegister */
        $childRegister = [];
        /** @var array<string|int, MovableNodeContract> $nodeRegister */
        $nodeRegister = [];
//        $root = null;
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

            // No parent, when this node is the root.
            if ($rootRef === $self) {
                continue;
            }

            if (!isset($childRegister[$parent])) {
                $childRegister[$parent] = [];
            }
            $childRegister[$parent][] = $self;
        }
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
    private function connectNode(array $nodeRegister, array $childRegister, string|int|null $ref): void
    {
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
