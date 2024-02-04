<?php

declare(strict_types=1);

namespace Dakujem\Oliva;

use Dakujem\Oliva\Exceptions\NodeNotMovable;

/**
 * A helper class for high-level tree operations.
 *
 * This contrasts with the low-level interface of movable nodes:
 * @see MovableNodeContract
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class Tree
{
    /**
     * Attaches a node to a parent,
     * establishing both the child-to-parent link and adding the child to the parent's children list.
     *
     * If a key is given, the node will be added under that key.
     * If already added, it will be re-added under the new key.
     *
     * If the node already has a parent at the moment of the call,
     * that link will be broken and the original parent will be returned.
     *
     * Null is returned in all other cases.
     */
    public static function link(
        MovableNodeContract $node,
        MovableNodeContract $parent,
        string|int|null $key = null,
    ): ?MovableNodeContract {
        // If the current parent is different, first detach the node.
        $currentParent = $node->parent();
        if ($currentParent === $parent) {
            // Already linked, but check the link the other way around.
            self::adoptChild($parent, $node, $key);
            return null;
        }
        if (null !== $currentParent) {
            $originalParent = self::unlink($node);
        }
        $node->setParent($parent);

        self::adoptChild($parent, $node, $key);
        return $originalParent ?? null;
    }

    /**
     * Detaches a node from its parent,
     * both resetting the child-to-parent link and removing the node from among its parent's children list.
     *
     * Returns the original parent, if any.
     */
    public static function unlink(
        MovableNodeContract $node,
    ): ?MovableNodeContract {
        $parent = $node->parent();
        if (null === $parent) {
            // The parent is already null, terminate.
            return null;
        }
        if (!$parent instanceof MovableNodeContract) {
            throw new NodeNotMovable($parent);
        }
        $node->setParent(null);
        $parent->removeChild($node);
        return $parent;
    }

    /**
     * Attaches a bunch of nodes to a parent,
     * establishing both the child-to-parent link and adding the child to the parent's children list.
     *
     * Does NOT remove the original children of the parent node ($parent).
     *
     * The callable $onParentUnlinked may be used to process cases where the original node's parent is unlinked.
     *
     * The call does not use the keys in the given list of children.
     * However, those may be used via the $key callable, in fact, any valid keys may be returned.
     */
    public static function linkChildren(
        MovableNodeContract $parent,
        iterable $children,
        ?callable $onParentUnlinked = null,
        ?callable $key = null,
    ): MovableNodeContract {
        foreach ($children as $index => $child) {
            if (!$child instanceof MovableNodeContract) {
                throw new NodeNotMovable($child);
            }
            $originalParent = self::link(
                node: $child,
                parent: $parent,
                key: null !== $key ? $key($child, $index) : null,
            );
            if (null !== $originalParent && null !== $onParentUnlinked) {
                $onParentUnlinked($originalParent, $child);
            }
        }
        return $parent;
    }

    /**
     * Unlinks all children of a node,
     * both resetting the child-to-parent links and removing the nodes the children list.
     */
    public static function unlinkChildren(
        MovableNodeContract $parent,
    ): void {
        foreach ($parent->children() as $key => $child) {
            if (!$child instanceof MovableNodeContract) {
                throw new NodeNotMovable($child);
            }
            $child->setParent(null);
        }
        $parent->removeChildren();
    }

    /**
     * Sorts children and recalculates their child keys of all tree nodes recursively.
     *
     * Both operations are optional:
     * - If the key-calculating function is not passed in, the child keys will not be altered.
     * - If the sorting function is not passed in, the order of the nodes will not change.
     *
     * Example usage of <=> (spaceship) operator to sort children based on path props:
     * `fn(Node $a, Node $b) => '!'.$a->data()->path <=> '!'.$b->data()->path`
     * (Note the `!` prefix above is to prevent issues with "000" <=> "000000" being 0, incorrect, while "!000" <=> "!000000" being -1, correct.)
     */
    public static function reindexTree(
        MovableNodeContract $node,
        ?callable $key,
        ?callable $sort,
    ): void {
        $children = Seed::array($node->children());
        $node->removeChildren();
        if (null !== $sort) {
            uasort($children, $sort);
        }
        $seq = 0;
        foreach ($children as $childKey => $child) {
            if (!$child instanceof MovableNodeContract) {
                throw new NodeNotMovable($child);
            }
            $newKey = null !== $key ? $key($child, $childKey, $seq) : $childKey;
            $node->addChild($child, $newKey);
            self::reindexTree($child, $key, $sort);
            $seq += 1;
        }
    }

    /**
     * @internal
     */
    private static function adoptChild(
        MovableNodeContract $parent,
        MovableNodeContract $child,
        string|int|null $key = null
    ): void {
        $existing = $parent->childKey($child);
        if (null !== $existing && $parent->child($existing) === $child) {
            // Already linked.
            return;
        }
        $parent->addChild($child, $key);
        if (null !== $existing) {
            // The child has already been linked to the same parent but under a different key. Remove that link.
            // Note:
            //   Important to keep this _after_ adding the node
            //   to prevent inconsistent state if adding failed due to a conflicting key.
            $parent->removeChild($existing);
        }
    }
}
