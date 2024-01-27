<?php

declare(strict_types=1);

namespace Dakujem\Test;

use Dakujem\Oliva\DataNodeContract;
use Dakujem\Oliva\TreeNodeContract;
use Tester\Assert;

require_once __DIR__ . '/setup.php';

// TODO test other implementations than `Node` (to test compliance)


class BareNode implements TreeNodeContract
{
    public function parent(): ?TreeNodeContract
    {
        // TODO: Implement parent() method.
    }

    public function children(): iterable
    {
        // TODO: Implement children() method.
    }

    public function hasChild(int|string|TreeNodeContract $child): bool
    {
        // TODO: Implement hasChild() method.
    }

    public function child(int|string $key): ?TreeNodeContract
    {
        // TODO: Implement child() method.
    }

    public function childKey(TreeNodeContract $node): string|int|null
    {
        // TODO: Implement childKey() method.
    }

    public function isLeaf(): bool
    {
        // TODO: Implement isLeaf() method.
    }

    public function isRoot(): bool
    {
        // TODO: Implement isRoot() method.
    }

    public function root(): TreeNodeContract
    {
        // TODO: Implement root() method.
    }

}

(function () {
    $a = new BareNode();
    Assert::type(TreeNodeContract::class, $a);
    Assert::false($a instanceof DataNodeContract);
})();

