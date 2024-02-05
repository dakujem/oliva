<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Recursive;

use Dakujem\Oliva\Exceptions\ExtractorReturnValueIssue;
use Dakujem\Oliva\Exceptions\InvalidInputData;
use Dakujem\Oliva\Exceptions\InvalidNodeFactoryReturnValue;
use Dakujem\Oliva\MovableNodeContract;
use Dakujem\Oliva\TreeNodeContract;

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
    private $factory;

    /**
     * Extractor of the self reference,
     * signature `fn(mixed $data, mixed $inputIndex, TreeNodeContract $node): string|int`.
     * @var callable
     */
    private $selfRef;

    /**
     * Extractor of the parent reference,
     * signature `fn(mixed $data, mixed $inputIndex, TreeNodeContract $node): string|int|null`.
     * @var callable
     */
    private $parentRef;

    /**
     * Callable that detects the root node.
     * Signature `fn(mixed $data, mixed $inputIndex, TreeNodeContract $node, string|int|null $parentRef, string|int $selfRef): bool`.
     * @var callable
     */
    private $root;

    public function __construct(
        callable $node,
        callable $selfRef,
        callable $parentRef,
        string|int|callable|null $root = null,
    ) {
        $this->factory = $node;
        $this->selfRef = $selfRef;
        $this->parentRef = $parentRef;
        if (null === $root || is_string($root) || is_int($root)) {
            // By default, the root node is detected by having the parent ref equal to `null`.
            // By passing in a string or an integer, the root node will be detected by comparing that value to the node's parent value.
            // For custom "is root" detector, use a callable.
            $this->root = fn(
                mixed $data,
                mixed $inputIndex,
                TreeNodeContract $node,
                int|string|null $parentReference,
                int|string|null $selfReference,
            ): bool => $parentReference === $root;
        } else {
            $this->root = $root;
        }
    }

    public function build(iterable $input): TreeNodeContract
    {
        [$root] = $this->processData(
            input: $input,
            nodeFactory: $this->factory,
            selfRefExtractor: $this->selfRef,
            parentRefExtractor: $this->parentRef,
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
                throw (new InvalidNodeFactoryReturnValue())->tag('node', $node)->tag('data', $data)->tag('index', $inputIndex);
            }

            $selfRef = $selfRefExtractor($data, $inputIndex, $node);
            $parentRef = $parentRefExtractor($data, $inputIndex, $node);

            if (!is_string($selfRef) && !is_int($selfRef)) {
                throw (new ExtractorReturnValueIssue('Invalid "self reference" returned by the extractor. Requires a int|string unique to the given node.'))
                    ->tag('ref', $selfRef)
                    ->tag('node', $node)
                    ->tag('data', $data)
                    ->tag('index', $inputIndex);
            }
            if (null !== $parentRef && !is_string($parentRef) && !is_int($parentRef)) {
                throw (new ExtractorReturnValueIssue('Invalid "parent reference" returned by the extractor. Requires a int|string uniquely pointing to "self reference" of another node, or `null`.'))
                    ->tag('parent', $parentRef)
                    ->tag('self', $selfRef)
                    ->tag('node', $node)
                    ->tag('data', $data)
                    ->tag('index', $inputIndex);
            }

            if (isset($nodeRegister[$selfRef])) {
                throw (new ExtractorReturnValueIssue('Duplicate node reference: ' . $selfRef))
                    ->tag('ref', $selfRef)
                    ->tag('node', $node)
                    ->tag('data', $data)
                    ->tag('index', $inputIndex);
            }
            $nodeRegister[$selfRef] = $node;

            // When this node is the root, it has no parent.
            if (!$rootFound && $isRoot($data, $inputIndex, $node, $parentRef, $selfRef)) {
                $rootRef = $selfRef;
                $rootFound = true;
                continue;
            }

            if (!isset($childRegister[$parentRef])) {
                $childRegister[$parentRef] = [];
            }
            $childRegister[$parentRef][] = $selfRef;
        }

        if (!$rootFound) {
            throw (new InvalidInputData('No root node found in the input collection.'))
                ->tag('nodes', $nodeRegister);
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
     * Recursive.
     *
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
