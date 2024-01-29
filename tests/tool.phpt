<?php

declare(strict_types=1);

namespace Dakujem\Test;

use ArrayIterator;
use Dakujem\Oliva\Iterator\LevelOrderTraversal;
use Dakujem\Oliva\Iterator\PostOrderTraversal;
use Dakujem\Oliva\Iterator\PreOrderTraversal;
use Dakujem\Oliva\Node;
use Dakujem\Oliva\Seed;
use Tester\Assert;

require_once __DIR__ . '/setup.php';


(function () {
    $a = new Node('A');
    $b = new Node('B');
    Manipulator::edge($a, $b);
    Assert::same($a, $b->parent());
    Assert::same($b, $a->child(0));
})();

(function () {
    Assert::same('', TreeTesterTool::chain([]));
    Assert::same('ABC', TreeTesterTool::chain([
        new Node('A'),
        new Node('B'),
        new Node('C'),
    ]));
    Assert::same('', TreeTesterTool::chain([], '.'));
    Assert::same('.A.B.C', TreeTesterTool::chain([
        new Node('A'),
        new Node('B'),
        new Node('C'),
    ], '.'));
})();


(function () {
    $tree = Preset::wikiTree();
    Assert::same('FBADCEGIH', TreeTesterTool::flatten($tree));
    Assert::same('FBADCEGIH', TreeTesterTool::flatten($tree, PreOrderTraversal::class));
    Assert::same('ACEDBHIGF', TreeTesterTool::flatten($tree, PostOrderTraversal::class));
    Assert::same('FBGADICEH', TreeTesterTool::flatten($tree, LevelOrderTraversal::class));
})();

(function () {
    Assert::same(null, Seed::firstOf([]));
    Assert::same(1, Seed::firstOf([1]));
    Assert::same(1, Seed::firstOf([1, 2, 3]));
    Assert::same(1, Seed::firstOf(new ArrayIterator([1, 2, 3])));
    Assert::same(null, Seed::firstOf(new ArrayIterator([])));
    Assert::same(42, Seed::firstOf(new ArrayIterator([42])));
})();

(function () {
    Assert::same([], iterator_to_array(Seed::merged()));
    Assert::same([], iterator_to_array(Seed::merged([], [], new ArrayIterator([]))));
    Assert::same([1, 2, 3, 4], iterator_to_array(Seed::merged([1, 2], new ArrayIterator([2 => 3, 4]))));
    Assert::same([1, 2, 3, 4], iterator_to_array(Seed::merged(new ArrayIterator([1, 2]), new ArrayIterator([2 => 3, 4]))));
    Assert::same([1, 2, 3, 4], iterator_to_array(Seed::merged(new ArrayIterator([1, 2]), [2 => 3, 4])));
})();
