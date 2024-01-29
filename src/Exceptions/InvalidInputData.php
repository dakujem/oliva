<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Exceptions;

use RuntimeException;

/**
 * Indicates invalid input data or argument.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class InvalidInputData extends RuntimeException implements FailsIntegrity, AcceptsDebugContext
{
    use AcceptDebugContext;
}
