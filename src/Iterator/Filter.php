<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Iterator;

use CallbackFilterIterator;

/**
 * Filter tree iterator.
 * To be used with tree traversal iterators or for filtering input data.
 *
 * Wraps the native callback filter iterator with improved interface (supporting arrays).
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class Filter extends CallbackFilterIterator
{
    /**
     * @param callable $accept Callable predicate returning `true`/truthy for accepted nodes; signature: `fn(TreeNodeContract|mixed):bool`
     */
    public function __construct(
        iterable $input,
        callable $accept,
    ) {
        parent::__construct(
            Data::iterator($input),
            $accept,
        );
    }
}
