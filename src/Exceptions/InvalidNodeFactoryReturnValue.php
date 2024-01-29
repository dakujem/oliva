<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Exceptions;

use Dakujem\Oliva\MovableNodeContract;
use LogicException;
use Throwable;

/**
 * Every node factory callable must return a movable node implementation instance, otherwise the builders can not work.
 * @see MovableNodeContract
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class InvalidNodeFactoryReturnValue extends LogicException implements FailsIntegrity, AcceptsDebugContext
{
    use AcceptDebugContext;

    public function __construct($message = null, $code = null, Throwable $previous = null)
    {
        parent::__construct(
            $message ?? ('The node factory must return a movable node instance (' . MovableNodeContract::class . ').'),
            $code ?? 0,
            $previous,
        );
    }
}
