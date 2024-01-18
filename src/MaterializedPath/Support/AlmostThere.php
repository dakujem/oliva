<?php

declare(strict_types=1);

namespace Dakujem\Oliva\MaterializedPath\Support;

use Dakujem\Oliva\TreeNodeContract;

/**
 * A wrapper structure containing a tree built from flat data as well as its shadow tree.
 *
 * This structure allows for data inspection, data correction or debugging
 * and is not directly intended to be used in applications.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
class AlmostThere
{
    public function __construct(
        private ?TreeNodeContract $root,
        private ?ShadowNode $shadowRoot,
    ) {
    }

    /**
     * Return the actual tree root.
     */
    public function root(): ?TreeNodeContract
    {
        return $this->root;
    }

    /**
     * Return the shadow tree root.
     * This shadow tree may be used for edge case handling, data reconstruction, inspections and debugging,
     * because there may be nodes that are not connected to the root due to inconsistent input data.
     * These nodes are present and reachable within the shadow tree.
     */
    public function shadow(): ?ShadowNode
    {
        return $this->shadowRoot;
    }
}
