<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Exceptions;

use LogicException;

/**
 * A problem with return value of an extractor.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class ExtractorReturnValueIssue extends LogicException implements FailsIntegrity, AcceptsDebugContext
{
    use AcceptDebugContext;
}
