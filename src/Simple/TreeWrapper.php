<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Simple;

use Dakujem\Oliva\Exceptions\AcceptsDebugContext;
use Dakujem\Oliva\Exceptions\ExtractorReturnValueIssue;
use Dakujem\Oliva\Exceptions\InvalidNodeFactoryReturnValue;
use Dakujem\Oliva\MovableNodeContract;
use Dakujem\Oliva\TreeNodeContract;

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

    public function wrap(
        mixed $data,
    ): TreeNodeContract {
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
            throw (new InvalidNodeFactoryReturnValue())
                ->tag('node', $node)
                ->tag('data', $data);
        }

        $childrenData = $childrenExtractor($data, $node);
        if (null !== $childrenData && !is_iterable($childrenData)) {
            throw (new ExtractorReturnValueIssue('Children data extractor must return an iterable collection containing children data.'))
                ->tag('children', $childrenData)
                ->tag('parent', $node)
                ->tag('data', $data);
        }
        foreach ($childrenData ?? [] as $key => $childData) {
            try {
                $child = $this->wrapNode(
                    data: $childData,
                    nodeFactory: $nodeFactory,
                    childrenExtractor: $childrenExtractor,
                );
            } catch (AcceptsDebugContext $e) {
                throw $e->push('nodes', $node);
            }
            $child->setParent($node);
            $node->addChild($child, $key);
        }
        return $node;
    }
}
