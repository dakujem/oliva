<?php

declare(strict_types=1);

namespace Dakujem\Oliva;


/**
 * Low-level contract for node manipulation.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
interface MovableNodeContract extends TreeNodeContract
{
    public function setParent(?TreeNodeContract $parent): self;

    public function addChild(TreeNodeContract $child, string|int|null $index = null): self;

    public function removeChild(TreeNodeContract|string|int $child): self;

    public function removeChildren(): self;
}