<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Builder;

use Dakujem\Oliva\MovableNodeContract;
use Dakujem\Oliva\Node;
use Dakujem\Oliva\TreeNodeContract;
use LogicException;

/**
 * ShadowNode
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class ShadowNode extends Node implements MovableNodeContract
{
    public function __construct(
        ?TreeNodeContract $realNode,
        ShadowNode $parent,
        string $path, // alebo vector?
        string|int $key,
    ) {
        parent::__construct(
            data: $realNode,
            parent: $parent,
        );
    }

    public function reconstructRealTree(): ?TreeNodeContract
    {
        $realNode = $this->realNode();
        /** @var self $child */
        foreach ($this->children() as $index => $child) {
            $realChild = $child->realNode();
            if (null !== $realNode && null !== $realChild) {
                $realNode->addChild($realChild, $index);
                $realChild->setParent($realNode);
            }
            $child->reconstructRealTree();
        }
        return $realNode;
    }

    private function realNode(): ?TreeNodeContract
    {
        return $this->data();
    }

    public function addChild(TreeNodeContract $child, string|int|null $index = null): self
    {
        if (!$child instanceof self) {
            throw new LogicException('Invalid use of a shadow node. Only shadow nodes can be children of shadow nodes.');
        }
        return parent::addChild($child, $index);
    }

    public function setParent(?TreeNodeContract $parent): self
    {
        if (!$parent instanceof self) {
            throw new LogicException('Invalid use of a shadow node. Only shadow nodes can be parents of shadow nodes.');
        }
        return parent::setParent($parent);
    }
}
