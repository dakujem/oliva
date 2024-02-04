<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Simple;

use Dakujem\Oliva\Exceptions\InvalidInputData;
use Dakujem\Oliva\Exceptions\InvalidNodeFactoryReturnValue;
use Dakujem\Oliva\MovableNodeContract;
use Dakujem\Oliva\TreeNodeContract;

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

    public function node(
        mixed $data,
        iterable $children = [],
    ): TreeNodeContract {
        // Build the node.
        $node = ($this->factory)($data);

        // Check for consistency.
        if (!$node instanceof MovableNodeContract) {
            throw (new InvalidNodeFactoryReturnValue())->tag('node', $node)->tag('data', $data);
        }

        // Bind the children.
        foreach ($children as $key => $child) {
            // Check for consistency.
            if (!$child instanceof MovableNodeContract) {
                throw (new InvalidInputData('Each child must be a movable node instance (' . MovableNodeContract::class . ').'))
                    ->tag('child', $child)
                    ->tag('key', $key)
                    ->tag('parent', $node)
                    ->tag('data', $data);
            }
            $child->setParent($node);
            $node->addChild($child, $key);
        }

        return $node;
    }
}
