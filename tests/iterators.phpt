<?php

declare(strict_types=1);

namespace Dakujem\Test;

use Dakujem\Oliva\DataNodeContract;
use Dakujem\Oliva\Iterator\LevelOrderTraversal;
use Dakujem\Oliva\Iterator\Native;
use Dakujem\Oliva\Iterator\PostOrderTraversal;
use Dakujem\Oliva\Iterator\PreOrderTraversal;
use Dakujem\Oliva\Iterator\Support\Counter;
use Dakujem\Oliva\TreeNodeContract;
use RecursiveIteratorIterator;
use Tester\Assert;

require_once __DIR__ . '/setup.php';

(function () {
    $counter = new Counter();
    Assert::same(0, $counter->current());
    Assert::same(0, $counter->touch());
    Assert::same(1, $counter->touch());
    Assert::same(2, $counter->current());
    Assert::same(3, $counter->next());
    Assert::same(3, $counter->current());
})();

(function () {
    $counter = new Counter(5);
    Assert::same(5, $counter->current());
})();

(function () {
    $root = Preset::wikiTree();

    $iterator = new PreOrderTraversal($root);
    $str = '';
    foreach ($iterator as $node) {
        $str .= $node->data();
    }
    Assert::same('FBADCEGIH', $str);
    Assert::same('FBADCEGIH', TreeTesterTool::chain($iterator));

    $iterator = new PostOrderTraversal($root);
    $str = '';
    foreach ($iterator as $node) {
        $str .= $node->data();
    }
    Assert::same('ACEDBHIGF', $str);
    Assert::same('ACEDBHIGF', TreeTesterTool::chain($iterator));

    $iterator = new LevelOrderTraversal($root);
    $str = '';
    foreach ($iterator as $node) {
        $str .= $node->data();
    }
    Assert::same('FBGADICEH', $str);
    Assert::same('FBGADICEH', TreeTesterTool::chain($iterator));

//    Assert::type(PreOrderTraversal::class, $root->getIterator());
    $str = '';
    foreach ($root as $node) {
        $str .= $node->data();
    }
    Assert::same('FBADCEGIH', $str);
    Assert::same('FBADCEGIH', TreeTesterTool::chain($root));
    Assert::same('FBADCEGIH', TreeTesterTool::chain($root->getIterator()));
})();

(function () {
    $root = Preset::wikiTree();

    $iterator = new PreOrderTraversal(
        node: $root,
        key: null,
    );
    $expected = [
        0 => 'F',
        'B',
        'A',
        'D',
        'C',
        'E',
        'G',
        'I',
        'H',
    ];
    Assert::same($expected, array_map(fn(DataNodeContract $node) => $node->data(), iterator_to_array($iterator)));


    $iterator = new PreOrderTraversal(
        node: $root,
        key: fn(TreeNodeContract $node, array $vector, int $seq, int $counter): int => $counter + 1,
    );
    $expected = [
        1 => 'F',
        'B',
        'A',
        'D',
        'C',
        'E',
        'G',
        'I',
        'H',
    ];
    Assert::same($expected, array_map(fn(DataNodeContract $node) => $node->data(), iterator_to_array($iterator)));


    $iterator = new PreOrderTraversal(
        node: $root,
        key: fn(TreeNodeContract $node, array $vector, int $seq, int $counter): string => '.' . implode('.', $vector),
    );
    $expected = [
        '.' => 'F',
        '.0' => 'B',
        '.0.0' => 'A',
        '.0.1' => 'D',
        '.0.1.0' => 'C',
        '.0.1.1' => 'E',
        '.1' => 'G',
        '.1.0' => 'I',
        '.1.0.0' => 'H',
    ];
    Assert::same($expected, array_map(fn(DataNodeContract $node) => $node->data(), iterator_to_array($iterator)));


    $iterator = new PreOrderTraversal(
        node: $root,
        key: fn(TreeNodeContract $node, array $vector, int $seq, int $counter): string => implode('.', $vector),
        startingVector: ['a', 'b'],
    );
    $expected = [
        'a.b' => 'F',
        'a.b.0' => 'B',
        'a.b.0.0' => 'A',
        'a.b.0.1' => 'D',
        'a.b.0.1.0' => 'C',
        'a.b.0.1.1' => 'E',
        'a.b.1' => 'G',
        'a.b.1.0' => 'I',
        'a.b.1.0.0' => 'H',
    ];
    Assert::same($expected, array_map(fn(DataNodeContract $node) => $node->data(), iterator_to_array($iterator)));
})();

(function () {
    $root = Preset::wikiTree();

    $iterator = new PostOrderTraversal($root);
    $expected = [
        0 => 'A',
        'C',
        'E',
        'D',
        'B',
        'H',
        'I',
        'G',
        'F',
    ];
    Assert::same($expected, array_map(fn(DataNodeContract $node) => $node->data(), iterator_to_array($iterator)));

    $iterator = new PostOrderTraversal(
        node: $root,
        key: fn(TreeNodeContract $node, array $vector, int $seq, int $counter): string => implode('.', $vector),
        startingVector: ['a', 'b'],
    );
    $expected = [
        'a.b.0.0' => 'A',
        'a.b.0.1.0' => 'C',
        'a.b.0.1.1' => 'E',
        'a.b.0.1' => 'D',
        'a.b.0' => 'B',
        'a.b.1.0.0' => 'H',
        'a.b.1.0' => 'I',
        'a.b.1' => 'G',
        'a.b' => 'F',
    ];
    Assert::same($expected, array_map(fn(DataNodeContract $node) => $node->data(), iterator_to_array($iterator)));
})();

(function () {
    $root = Preset::wikiTree();

    $iterator = new LevelOrderTraversal($root);
    $expected = [
        0 => 'F',
        'B',
        'G',
        'A',
        'D',
        'I',
        'C',
        'E',
        'H',
    ];
    Assert::same($expected, array_map(fn(DataNodeContract $node) => $node->data(), iterator_to_array($iterator)));

    $iterator = new LevelOrderTraversal(
        node: $root,
        key: fn(TreeNodeContract $node, array $vector, int $seq, int $counter): string => implode('.', $vector),
        startingVector: ['a', 'b'],
    );
    $expected = [
        'a.b' => 'F',
        'a.b.0' => 'B',
        'a.b.1' => 'G',
        'a.b.0.0' => 'A',
        'a.b.0.1' => 'D',
        'a.b.1.0' => 'I',
        'a.b.0.1.0' => 'C',
        'a.b.0.1.1' => 'E',
        'a.b.1.0.0' => 'H',
    ];
    Assert::same($expected, array_map(fn(DataNodeContract $node) => $node->data(), iterator_to_array($iterator)));
})();


(function () {
    $root = Preset::wikiTree();

    //
    // level-order (?), leaves only (the default)
    //

    $str = $keys = [];
    foreach (new RecursiveIteratorIterator(new Native($root)) as $key => $node) {
        $str[] = $node->data();
        $keys[] = $key;
    }
    Assert::same('A,C,E,H', implode(',', $str));
    Assert::same('0,0,1,0', implode(',', $keys));

    $str = $keys = [];
    foreach (new RecursiveIteratorIterator(new Native($root), RecursiveIteratorIterator::LEAVES_ONLY) as $key => $node) {
        $str[] = $node->data();
        $keys[] = $key;
    }
    Assert::same('A,C,E,H', implode(',', $str));
    Assert::same('0,0,1,0', implode(',', $keys));

    //
    // pre-order, all nodes
    //

    $str = $keys = [];
    foreach (new RecursiveIteratorIterator(new Native($root), RecursiveIteratorIterator::SELF_FIRST) as $key => $node) {
        $str[] = $node->data();
        $keys[] = $key;
    }
    // F,B,A,D,C,E,G,I,H is the iteration order of nodes, which corresponds to the following child indexes (iteration keys):
    // 0,0,0,1,0,1,1,0,0
    Assert::same('F,B,A,D,C,E,G,I,H', implode(',', $str));
    Assert::same('0,0,0,1,0,1,1,0,0', implode(',', $keys));

    //
    // post-order, all nodes
    //

    $str = $keys = [];
    foreach (new RecursiveIteratorIterator(new Native($root), RecursiveIteratorIterator::CHILD_FIRST) as $key => $node) {
        $str[] = $node->data();
        $keys[] = $key;
    }
    Assert::same('A,C,E,D,B,H,I,G,F', implode(',', $str));
    Assert::same('0,0,1,1,0,0,0,1,0', implode(',', $keys));
})();

