<?php

declare(strict_types=1);

namespace Dakujem\Oliva\MaterializedPath;

use Dakujem\Oliva\MaterializedPath\Support\Register;
use Dakujem\Oliva\MaterializedPath\Support\ShadowNode;
use Dakujem\Oliva\MaterializedPath\Support\Tree;
use Dakujem\Oliva\TreeNodeContract;
use Generator;
use LogicException;
use RuntimeException;

/**
 * Materialized path tree builder. Builds trees from flat data collections.
 *
 * The builder needs to be provided an iterable data collection, a node factory
 * and a vector extractor that returns the node's vector based on the data.
 * The extractor will typically be a simple function that takes a path prop/attribute from the data item
 * and splits or explodes it into a vector.
 * Two common-case extractors can be created using the `fixed` and `delimited` methods.
 *
 * Fixed path variant example:
 * ```
 * $root = (new MaterializedPathTreeBuilder())->build(
 *     $myItemCollection,
 *     fn(MyItem $item) => new Node($item),
 *     MaterializedPathTreeBuilder::fixed(3, fn(MyItem $item) => $item->path),
 * );
 * ```
 *
 * Delimited path variant example:
 * ```
 * $root = (new MaterializedPathTreeBuilder())->build(
 *     $myItemCollection,
 *     fn(MyItem $item) => new Node($item),
 *     MaterializedPathTreeBuilder::delimited('.', fn(MyItem $item) => $item->path),
 * );
 * ```
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class TreeBuilder
{
    public static function fixed(int $levelWidth, callable $accessor): callable
    {
        return function (mixed $data) use ($levelWidth, $accessor) {
            $path = $accessor($data);
            if (null === $path) {
                return [];
            }
            if (!is_string($path)) {
                // TODO improve exceptions (index/path etc)
                throw new LogicException('Invalid path returned.');
            }
            return str_split($path, $levelWidth);
        };
    }

    public static function delimited(string $delimiter, callable $accessor): callable
    {
        return function (mixed $data) use ($delimiter, $accessor) {
            $path = $accessor($data);
            if (null === $path) {
                return [];
            }
            if (!is_string($path)) {
                // TODO improve exceptions (index/path etc)
                throw new LogicException('Invalid path returned.');
            }
            return explode($delimiter, $path);
        };
    }

    /**
     * Prepend `null` value at the beginning of the data collection.
     * Use when missing root.
     * Remember, the data accessor and the node factory must be aware that a null may be passed to them.
     */
    public static function rootNull(iterable $input): Generator
    {
        yield null;
        yield from $input;
    }

    /**
     * Create a merged iterable.
     * Can be used to prepend or append data from multiple source collections.
     * The data is not actually merged, but a generator is used.
     */
    public static function merged(iterable ...$input): Generator
    {
        foreach ($input as $iterable) {
            yield from $iterable;
        }
    }

    public function build(
        iterable $input,
        callable $node,
        callable $vector,
    ): TreeNodeContract {
        $root = $this->buildTree($input, $node, $vector)->root();
        if (null === $root) {
            throw new RuntimeException('Corrupted input, no tree created.');
        }
        return $root;
    }

    public function buildTree(
        iterable $input,
        callable $node,
        callable $vector,
    ): Tree {
        $shadowRoot = $this->buildShadowTree(
            $input,
            $node,
            $vector,
        );

        // The actual tree nodes are not yet connected.
        // Reconstruct the tree using the shadow tree's structure.
        $root = $shadowRoot->reconstructRealTree();

        // For edge case handling, return a structure containing the shadow root as well as the actual tree root.
        return new Tree(
            root: $root,
            shadowRoot: $shadowRoot,
        );
    }

    private function buildShadowTree(
        iterable $input,
        callable $nodeFactory,
        callable $vectorExtractor,
    ): ShadowNode {
        $register = new Register();
        foreach ($input as $inputIndex => $data) {
            // Create a node using the provided factory.
            $node = $nodeFactory($data, $inputIndex);

            // Enable skipping particular data.
            if (null === $node) {
                continue;
            }

            // Check for consistency.
            if (!$node instanceof TreeNodeContract) {
                // TODO improve exceptions
                throw new LogicException('The node factory must return a node instance.');
            }

            // Calculate the node's vector.
            $vector = $vectorExtractor($data, $inputIndex, $node);
            if (!is_array($vector)) {
                // TODO improve exceptions
                throw new LogicException('The vector calculator must return an array.');
            }
            foreach ($vector as $i) {
                if (!is_string($i) && !is_integer($i)) {
                    // TODO improve exceptions
                    throw new LogicException('The vector may only consist of strings or integers.');
                }
            }

            // Finally, connect the newly created shadow node to the shadow tree.
            // Make sure all the shadow nodes exist all the way to the root.
            $this->connectNode(
                new ShadowNode($node),
                $vector,
                $register,
            );
        }

        // Pull the shadow root from the register.
        return $register->pull([]);
    }

    /**
     * Recursion.
     */
    private function connectNode(ShadowNode $node, array $vector, Register $register): void
    {
        $existingNode = $register->pull($vector);

        // If the node is already in the registry, replace the real node and return.
        if (null !== $existingNode) {
            // Check for node collisions.
            if (null !== $node->realNode() && null !== $existingNode->realNode()) {
                // TODO improve exceptions
                throw new LogicException('Duplicate node vector: ' . implode('.', $vector));
            }
            $existingNode->fill($node->realNode());
            return;
        }

        // Register the node.
        $register->push($vector, $node);

        // Recursively connect ancestry all the way up to the root.
        $this->connectAncestry($node, $vector, $register);
    }

    private function connectAncestry(ShadowNode $node, array $vector, Register $register): void
    {
        // When the node is a root itself, abort recursion.
        if (count($vector) === 0) {
            return;
        }

        // Attempt to pull the parent node from the registry.
        array_pop($vector);
        $parent = $register->pull($vector);

        // If the parent is already in the registry, only bind the node to the parent and abort.
        if (null !== $parent) {
            // Establish parent-child relationship.
            $node->setParent($parent);
            $parent->addChild($node);
            return;
        }

        // Otherwise create a bridging node, push it to the registry, link them and continue recursively,
        // hoping other iterations will fill the parent.
        $parent = new ShadowNode(null);
        $register->push($vector, $parent);

        // Establish parent-child relationship.
        $node->setParent($parent);
        $parent->addChild($node);

        // Continue with the next ancestor.
        $this->connectAncestry($parent, $vector, $register);
    }
}