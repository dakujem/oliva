<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Iterator;

use Dakujem\Oliva\TreeNodeContract;
use Generator;

/**
 * This class creates generators to iterate over all tree nodes in different order.
 * Use these generators if you only need to iterate over the nodes without control over the keys.
 *
 * This implementation is more efficient than the iterator traversal implementations
 * because it does not allow to modify the keys in any way.
 * It is also less flexible for the same reason.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class Traversal
{
    /**
     * Depth-first search pre-order traversal.
     *
     * Equivalent to the iterator:
     * @see PreOrderTraversal
     */
    public static function preOrder(TreeNodeContract $node): Generator
    {
        // First, yield the current node,
        // then do the same for all the children.
        yield $node;
        foreach ($node->children() as $child) {
            yield from self::preOrder($child);
        }
    }

    /**
     * Depth-first search post-order traversal.
     *
     * Equivalent to the iterator:
     * @see PostOrderTraversal
     */
    public static function postOrder(TreeNodeContract $node): Generator
    {
        // Yield the current node last,
        // after recursively calling this for all it's children.
        foreach ($node->children() as $child) {
            yield from self::postOrder($child);
        }
        yield $node;
    }

    /**
     * Breadth-first search (level-order) traversal.
     *
     * Equivalent to the iterator:
     * @see LevelOrderTraversal
     */
    public static function levelOrder(TreeNodeContract $node): Generator
    {
        // In BFS traversal a queue has to be used instead of recursion
        // (recursion uses a stack - the call stack).
        $queue = [$node];

        // The first node in the queue is taken and yielded,
        // then all of its children are added to the queue.
        // This continues until there are no more nodes in the queue.
        while ($node = array_shift($queue)) {
            yield $node;
            foreach ($node->children() as $child) {
                $queue[] = $child;
            }
        }
    }

    // No not instantiate this class.
    // This is enforced to avoid confusion with the traversal iterators.
    private function __construct()
    {
    }
}
