<?php

declare(strict_types=1);

namespace Dakujem\Test;

use Dakujem\Oliva\DataNodeContract;
use Dakujem\Oliva\Iterator\PreOrderTraversal;
use Dakujem\Oliva\TreeNodeContract;
use Tester\Environment;


require_once __DIR__ . '/../vendor/autoload.php';
Environment::setup();

final class TreeTesterTool
{
    public static function concatTree(
        TreeNodeContract $node,
        string $traversalClass = PreOrderTraversal::class,
        string $delimiter = '',
    ): string {
        return self::concat(
            new $traversalClass($node),
            $delimiter,
        );
    }

    public static function concat(iterable $traversal, string $delimiter = ''): string
    {
        return self::reduce(
            $traversal,
            fn(string $carry, DataNodeContract $item) => $carry . $delimiter . $item->data(),
        );
    }

    public static function reduce(iterable $traversal, callable $reducer, string $carry = ''): string
    {
        foreach ($traversal as $node) {
            $carry = $reducer($carry, $node);
        }
        return $carry;
    }
}

