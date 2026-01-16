<?php

declare(strict_types=1);

namespace Dakujem\Oliva\MaterializedPath;

use RuntimeException;
use Throwable;

/**
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class InvalidTreePath extends RuntimeException
{
    public function __construct($message = null, $code = null, Throwable $previous = null)
    {
        parent::__construct($message ?? 'The given string is not a valid tree path.', $code, $previous);
    }
}
