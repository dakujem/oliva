<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Exceptions;

/**
 * Indicates internal logic fault.
 * Implementors should never see these being thrown.
 *
 * @internal
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
interface IndicatesInternalFault extends IndicatesTreeIssue
{

}
