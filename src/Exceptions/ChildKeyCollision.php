<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Exceptions;

use RuntimeException;

/**
 * Thrown when a child node is being added with a key that would overwrite another child with the same key.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class ChildKeyCollision extends RuntimeException implements FailsIntegrity, AcceptsDebugContext
{
    use AcceptDebugContext;
}
