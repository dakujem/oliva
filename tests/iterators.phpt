<?php

declare(strict_types=1);

use Dakujem\Oliva\DataNodeContract;
use Dakujem\Oliva\Iterator\LevelOrderTraversal;
use Dakujem\Oliva\Iterator\PostOrderTraversal;
use Dakujem\Oliva\Iterator\PreOrderTraversal;
use Dakujem\Oliva\Iterator\Support\Counter;
use Dakujem\Oliva\Node;
use Dakujem\Oliva\TreeNodeContract;
use Tester\Assert;
use Tester\Environment;

require_once __DIR__ . '/../vendor/autoload.php';
Environment::setup();

$a = new Node('A');
$b = new Node('B');
$c = new Node('C');
$d = new Node('D');
$e = new Node('E');
$f = new Node('F');
$g = new Node('G');
$h = new Node('H');
$i = new Node('I');

$edge = function (Node $from, Node $to): void {
    $from->addChild($to);
    $to->setParent($from);
};

$root = $f;
$edge($f, $b);
$edge($b, $a);
$edge($b, $d);
$edge($d, $c);
$edge($d, $e);
$edge($f, $g);
$edge($g, $i);
$edge($i, $h);

$iterator = new PreOrderTraversal($root);
$str = '';
foreach ($iterator as $node) {
    $str .= $node->data();
}
//echo $str;
//echo "\n";
Assert::same('FBADCEGIH', $str);

$iterator = new PostOrderTraversal($root);
$str = '';
foreach ($iterator as $node) {
    $str .= $node->data();
}
//echo $str;
//echo "\n";
Assert::same('ACEDBHIGF', $str);

$iterator = new LevelOrderTraversal($root);
$str = '';
foreach ($iterator as $i => $node) {
    $str .= $node->data();
}
//echo $str;
//echo "\n";
Assert::same('FBGADICEH', $str);

//echo "\n";
Assert::type(PreOrderTraversal::class, $root->getIterator());
$str = '';
foreach ($root as $node) {
    $str .= $node->data();
}
//echo $str;
Assert::same('FBADCEGIH', $str);
//echo "\n";


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


$counter = new Counter();
Assert::same(0, $counter->current());
Assert::same(0, $counter->touch());
Assert::same(1, $counter->touch());
Assert::same(2, $counter->current());
Assert::same(3, $counter->next());
Assert::same(3, $counter->current());

$counter = new Counter(5);
Assert::same(5, $counter->current());

//$root->addChild(new)
