<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Exceptions;

use Dakujem\Oliva\MovableNodeContract;
use LogicException;
use Throwable;

/**
 * Builders can only work with movable nodes:
 * @see MovableNodeContract
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class NodeNotMovable extends LogicException implements IndicatesTreeIssue
{
    public mixed $node;

    public function __construct(mixed $node, $message = null, $code = null, Throwable $previous = null)
    {
        $this->node = $node;
        parent::__construct($message ?? 'Encountered a non-movable node while manipulating a tree.', $code ?? 0, $previous);
    }
}
