<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Exceptions;

use LogicException;

/**
 * A problem caused by an exception thrown while running a callable.
 * This exception is provided to enable context for external exceptions.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class CallableIssue extends LogicException implements FailsIntegrity, AcceptsDebugContext
{
    use AcceptDebugContext;
}
