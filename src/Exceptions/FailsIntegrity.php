<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Exceptions;

/**
 * Base interface for exceptions indicating a problem on the integration side,
 * be it either a configuration issue or invalid source data.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
interface FailsIntegrity extends IndicatesTreeIssue
{
}
