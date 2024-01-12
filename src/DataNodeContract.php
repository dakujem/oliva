<?php

declare(strict_types=1);

namespace Dakujem\Oliva;

/**
 * Contract for nodes operating with data.
 *
 * Most nodes SHOULD implement this contract, but do not have to.
 * Alternative approach to implementing this contract would be for nodes
 * to define their own public props, methods,
 * or to implement proxying, magic getters and setters, etc.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
interface DataNodeContract
{
    /**
     * Get the node's assigned data.
     */
    public function data(): mixed;

    /**
     * Set/assign new data to the node.
     */
    public function fill(mixed $data): self;
}
