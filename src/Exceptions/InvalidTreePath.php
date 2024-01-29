<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Exceptions;

use RuntimeException;
use Throwable;

/**
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class InvalidTreePath extends RuntimeException implements FailsIntegrity, AcceptsDebugContext
{
    use AcceptDebugContext;

    public function __construct($message = null, $code = null, Throwable $previous = null)
    {
        parent::__construct($message ?? 'The given value is not a valid tree path.', $code ?? 0, $previous);
    }
}
