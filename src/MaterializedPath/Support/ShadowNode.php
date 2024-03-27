<?php

declare(strict_types=1);

namespace Dakujem\Oliva\MaterializedPath\Support;

use Dakujem\Oliva\Exceptions\InternalLogicException;
use Dakujem\Oliva\MovableNodeContract;
use Dakujem\Oliva\Node;
use Dakujem\Oliva\TreeNodeContract;

/**
 * Shadow node used internally when building materialized path trees.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class ShadowNode extends Node implements MovableNodeContract
{
    public function __construct(
        ?MovableNodeContract $node
    ) {
        parent::__construct(data: $node);
    }

    /**
     * Reconstruct the real tree according to the connections of the shadow tree.
     * Reflect all the shadow tree's child-parent links to the actual tree
     * and return the root.
     *
     * Note:
     *   Should only be called on a root shadow node,
     *   otherwise a non-root node may be returned.
     */
    public function reconstructRealTree(): ?TreeNodeContract
    {
        $realNode = $this->realNode();
        $realNode?->removeChildren();
        /** @var self $child */
        foreach ($this->children() as $index => $child) {
            $realChild = $child->realNode();
            if (null !== $realChild) {
                $realNode?->addChild($realChild, $index);
                $realChild->setParent($realNode);
            }
            $child->reconstructRealTree();
        }
        return $realNode;
    }

    public function realNode(): ?MovableNodeContract
    {
        return $this->data();
    }

    public function addChild(TreeNodeContract $child, string|int|null $key = null): self
    {
        if (!$child instanceof self) {
            throw new InternalLogicException('Invalid use of a shadow node. Only shadow nodes can be children of shadow nodes.');
        }
        return parent::addChild($child, $key);
    }

    public function setParent(?TreeNodeContract $parent): self
    {
        if (!$parent instanceof self) {
            throw new InternalLogicException('Invalid use of a shadow node. Only shadow nodes can be parents of shadow nodes.');
        }
        return parent::setParent($parent);
    }
}
