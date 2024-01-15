<?php

declare(strict_types=1);

namespace Dakujem\Oliva;

use ArrayIterator;
use Generator;
use Iterator;
use IteratorIterator;

/**
 * A static tool to manage iterable data collections and iterators.
 *
 * Contains:
 * - methods that produce or adapt iterable data and iterators
 * - methods that produce filtering callables to be used with the Filter iterator
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class Seed
{
    /**
     * Create a merged iterable.
     * Can be used to prepend or append data from multiple source collections.
     * The data is not actually merged, but a generator is produced.
     */
    public static function merged(iterable ...$input): Generator
    {
        foreach ($input as $iterable) {
            yield from $iterable;
        }
    }

    /**
     * Returns the first element of an iterable collection.
     */
    public static function first(iterable $input): mixed
    {
        foreach ($input as $item) {
            return $item;
        }
        return null;
    }

    /**
     * Prepend `null` value at the beginning of the data collection.
     * Use when missing root.
     * Remember, the data accessor and the node factory must be aware that a null may be passed to them.
     */
    public static function nullFirst(iterable $input): Generator
    {
        yield null;
        yield from $input;
    }

    /**
     * Create a callable that omits the data nodes with `null` data.
     * To be used with `Filter` iterator as the predicate:
     * @see Filter
     */
    public static function omitNull(): callable
    {
        return fn(DataNodeContract $node): bool => $node->data() !== null;
    }

    /**
     * Create a filtering callable that omits the root node.
     * To be used with `Filter` iterator as the predicate:
     * @see Filter
     */
    public static function omitRoot(): callable
    {
        return fn(TreeNodeContract $node): bool => !$node->isRoot();
    }

    /**
     * Accepts any iterable and returns an iterator.
     * Useful where en iterator is required, but any iterable or array is provided.
     */
    public static function iterator(iterable $input): Iterator
    {
        return is_array($input) ? new ArrayIterator($input) : new IteratorIterator($input);
    }
}
