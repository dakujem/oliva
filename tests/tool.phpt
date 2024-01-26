<?php

declare(strict_types=1);

namespace Dakujem\Test;

use Dakujem\Oliva\Iterator\LevelOrderTraversal;
use Dakujem\Oliva\Iterator\PostOrderTraversal;
use Dakujem\Oliva\Iterator\PreOrderTraversal;
use Dakujem\Oliva\Node;
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
    Assert::same('', TreeTesterTool::append([]));
    Assert::same('ABC', TreeTesterTool::append([
        new Node('A'),
        new Node('B'),
        new Node('C'),
    ]));
    Assert::same('', TreeTesterTool::append([], '.'));
    Assert::same('.A.B.C', TreeTesterTool::append([
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