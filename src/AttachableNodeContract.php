<?php

declare(strict_types=1);

namespace Dakujem\Oliva;


/**
 * @experimental TODO might contain design problems...
 *
 * High-level contract for manipulation with nodes.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
interface AttachableNodeContract extends MovableNodeContract
{
    public function attach(TreeNodeContract $parent, string|int|null $index = null): self;

    public function detach(): self;

    public function attachChild(TreeNodeContract $child, string|int|null $index = null): self;

    public function detachChild(TreeNodeContract|string|int $child): self;
}