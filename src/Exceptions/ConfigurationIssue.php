<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Exceptions;

use LogicException;

/**
 * ConfigurationIssue
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class ConfigurationIssue extends LogicException implements FailsIntegrity
{
}
