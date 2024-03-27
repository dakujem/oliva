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
 * Contains methods that:
 * - produce or adapt iterable data and iterators
 * - simplify working with iterables
 * - produce filtering callables to be used with the Filter iterator or input data
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class Seed
{
    /**
     * Chain multiple iterable collections into a single one.
     *
     * The input collections are not actually merged,
     * but a generator that iterates over all the elements of all the input collections is returned.
     *
     * Can be used to prepend or append data from multiple source collections.
     */
    public static function chain(iterable ...$input): Generator
    {
        foreach ($input as $iterable) {
            yield from $iterable;
        }
    }

    /**
     * An alias of `chain`.
     *
     * Creates a "merged" iterable from multiple collections.
     * The input collections are not actually merged, but a generator is produced.
     *
     * @deprecated Use `Seed::chain` method.
     */
    public static function merged(iterable ...$input): Generator
    {
        return self::chain(...$input);
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
     * Returns the first element of an iterable collection.
     */
    public static function firstOf(iterable $input): mixed
    {
        foreach ($input as $item) {
            return $item;
        }
        return null;
    }

    /**
     * Accepts any iterable and returns an iterator.
     * Useful where an iterator is required, but an iterable type is provided.
     */
    public static function iterator(iterable $input): Iterator
    {
        return is_array($input) ? new ArrayIterator($input) : new IteratorIterator($input);
    }

    /**
     * Accepts any iterable and returns an array.
     * Useful where an array is required, but an iterable type is provided.
     */
    public static function array(iterable $input): array
    {
        return is_array($input) ? $input : iterator_to_array($input);
    }
}
