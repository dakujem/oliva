<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Exceptions;

use LogicException;

/**
 * Sorry.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class InternalLogicException extends LogicException implements IndicatesInternalFault
{
}
