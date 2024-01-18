<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Simple;

use Dakujem\Oliva\MovableNodeContract;
use Dakujem\Oliva\TreeNodeContract;
use LogicException;

/**
 * A trivial builder that ensures the children are bound to the respective parent.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class NodeBuilder
{
    /**
     * Node factory,
     * signature `fn(mixed $data): MovableNodeContract`.
     * @var callable
     */
    private $factory;

    public function __construct(
        callable $node,
    ) {
        $this->factory = $node;
    }

    public function node(mixed $data, iterable $children = []): TreeNodeContract
    {
        // Build the node.
        $node = ($this->factory)($data);

        // Check for consistency.
        if (!$node instanceof MovableNodeContract) {
            // TODO improve exceptions
            throw new LogicException('The node factory must return a movable node instance.');
        }

        // Bind the children.
        foreach ($children as $key => $child) {
            // Check for consistency.
            if (!$child instanceof MovableNodeContract) {
                // TODO improve exceptions
                throw new LogicException('The children must be movable node instances.');
            }
            $child->setParent($node);
            $node->addChild($child, $key);
        }

        return $node;
    }
}
