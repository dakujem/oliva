<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Simple;

use Dakujem\Oliva\MovableNodeContract;
use Dakujem\Oliva\TreeNodeContract;
use LogicException;

/**
 * Simple tree builder.
 * Wraps data that is already structured into tree node classes.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
class TreeBuilder
{
    public function build(
        mixed $data,
        callable $node,
        callable $children,
    ): TreeNodeContract {
        return $this->buildNode(
            data: $data,
            nodeFactory: $node,
            childrenExtractor: $children,
        );
    }

    public function buildNode(
        mixed $data,
        callable $nodeFactory,
        callable $childrenExtractor,
    ): MovableNodeContract {
        // Create a node using the provided factory.
        $node = $nodeFactory($data);

        // Check for consistency.
        if (!$node instanceof MovableNodeContract) {
            // TODO improve exceptions
            throw new LogicException('The node factory must return a movable node instance.');
        }

        $childrenData = $childrenExtractor($data, $node);
        if (null !== $childrenData && !is_iterable($childrenData)) {
            // TODO improve exceptions
            throw new LogicException('Children data extractor must return an iterable collection containing children data.');
        }
        foreach ($childrenData ?? [] as $key => $childData) {
            $child = $this->buildNode(
                data: $childData,
                nodeFactory: $nodeFactory,
                childrenExtractor: $childrenExtractor,
            );
            $child->setParent($node);
            $node->addChild($child, $key);
        }
        return $node;
    }
}
