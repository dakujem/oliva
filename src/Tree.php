<?php

declare(strict_types=1);

namespace Dakujem\Oliva;

use Exception;

/**
 * A helper class for high-level tree operations.
 *
 * This contrasts with the low-level interface.
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
            // TODO improve exceptions
            throw new Exception('Parent not movable.');
        }
        $node->setParent(null);
        $parent->removeChild($node);
        return $parent;
    }

    /**
     * Attaches a bunch of nodes to a parent,
     * establishing both the child-to-parent link and adding the child to the parent's children list.
     *
     * Does NOT remove the original children, collisions may occur.
     *
     * The callable $onParentUnlinked may be used to process cases where the original node's parent is unlinked.
     */
    public static function linkChildren(
        MovableNodeContract $parent,
        iterable $children,
        ?callable $onParentUnlinked = null,
    ): MovableNodeContract {
        foreach ($children as $key => $child) {
            if (!$child instanceof MovableNodeContract) {
                // TODO improve exceptions
                throw new Exception('Child not movable.');
            }
            $originalParent = self::link($child, $parent, $key);
            if (null !== $parent && null !== $onParentUnlinked) {
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
                // TODO improve exceptions
                throw new Exception('Child not movable.');
            }
            $child->setParent(null);
        }
        $parent->removeChildren();
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
