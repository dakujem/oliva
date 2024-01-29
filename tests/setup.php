<?php

declare(strict_types=1);

namespace Dakujem\Test;

use Dakujem\Oliva\DataNodeContract;
use Dakujem\Oliva\Iterator\PreOrderTraversal;
use Dakujem\Oliva\MovableNodeContract;
use Dakujem\Oliva\Node;
use Dakujem\Oliva\TreeNodeContract;
use Tester\Environment;


require_once __DIR__ . '/../vendor/autoload.php';
Environment::setup();

final class TreeTesterTool
{
    public static function flatten(
        TreeNodeContract $node,
        string $traversalClass = PreOrderTraversal::class,
        string $glue = '',
    ): string {
        return self::chain(
            new $traversalClass($node),
            $glue,
        );
    }

    public static function chain(
        iterable $traversal,
        string $glue = '',
        ?callable $extractor = null,
    ): string {
        $extractor ??= fn(DataNodeContract $item) => $item->data();
        return self::reduce(
            $traversal,
            fn(string $carry, DataNodeContract $item) => $carry . $glue . $extractor($item),
        );
    }

    public static function reduce(
        iterable $traversal,
        callable $reducer,
        string $carry = '',
    ): string {
        foreach ($traversal as $node) {
            $carry = $reducer($carry, $node);
        }
        return $carry;
    }
}

final class Manipulator
{
    public static function edge(MovableNodeContract $parent, MovableNodeContract $child): void
    {
        $parent->addChild($child);
        $child->setParent($parent);
    }
}

final class Preset
{
    /**
     * Returns manually built tree from Wikipedia:
     * @link https://en.wikipedia.org/wiki/Tree_traversal
     *
     *                 F
     *                 |
     *         B ------+------ G
     *         |               |
     *     A --+-- D           I
     *             |           |
     *         C --+-- E       H
     */
    public static function wikiTree(): Node
    {
        $a = new Node('A');
        $b = new Node('B');
        $c = new Node('C');
        $d = new Node('D');
        $e = new Node('E');
        $f = new Node('F');
        $g = new Node('G');
        $h = new Node('H');
        $i = new Node('I');


        $root = $f;
        Manipulator::edge($f, $b);
        Manipulator::edge($b, $a);
        Manipulator::edge($b, $d);
        Manipulator::edge($d, $c);
        Manipulator::edge($d, $e);
        Manipulator::edge($f, $g);
        Manipulator::edge($g, $i);
        Manipulator::edge($i, $h);

        return $root;
    }
}
