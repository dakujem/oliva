<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Simple;

use Dakujem\Oliva\MovableNodeContract;
use Dakujem\Oliva\TreeNodeContract;
use LogicException;

/**
 * Simple tree builder.
 * Wraps data that is already structured as a tree into tree node classes.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class TreeWrapper
{
    /**
     * Node factory,
     * signature `fn(mixed $data): MovableNodeContract`.
     * @var callable
     */
    private $factory;

    /**
     * Extractor of children iterable,
     * signature `fn(mixed $data, TreeNodeContract $node): ?iterable`.
     * @var callable
     */
    private $childrenExtractor;

    public function __construct(
        callable $node,
        callable $children,
    ) {
        $this->factory = $node;
        $this->childrenExtractor = $children;
    }

    public function wrap(mixed $data): TreeNodeContract
    {
        return $this->wrapNode(
            data: $data,
            nodeFactory: $this->factory,
            childrenExtractor: $this->childrenExtractor,
        );
    }

    private function wrapNode(
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
            $child = $this->wrapNode(
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
