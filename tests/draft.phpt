<?php

declare(strict_types=1);

use Dakujem\Oliva\DataNodeContract;
use Dakujem\Oliva\Iterator\LevelOrderTraversalIterator;
use Dakujem\Oliva\Iterator\PostOrderTraversalIterator;
use Dakujem\Oliva\Iterator\PreOrderTraversalIterator;
use Dakujem\Oliva\Node;
use Dakujem\Oliva\TreeNodeContract;
use Tester\Assert;
use Tester\Environment;

require_once __DIR__ . '/../vendor/autoload.php';

// tester
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

$iterator = new PreOrderTraversalIterator($root);
$str = '';
foreach ($iterator as $node) {
    $str .= $node->data();
}
echo $str;
echo "\n";
Assert::same('FBADCEGIH', $str);

$iterator = new PostOrderTraversalIterator($root);
$str = '';
foreach ($iterator as $node) {
    $str .= $node->data();
}
echo $str;
echo "\n";
Assert::same('ACEDBHIGF', $str);

$iterator = new LevelOrderTraversalIterator($root);
$str = '';
foreach ($iterator as $i => $node) {
    $str .= $node->data();
}
echo $str;
echo "\n";
Assert::same('FBGADICEH', $str);

echo "\n";
foreach ($root as $i) {
    echo $i->data();
}
echo "\n";


$iterator = new PreOrderTraversalIterator(
    node: $root,
    index: null,
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


$iterator = new PreOrderTraversalIterator(
    node: $root,
    index: fn(TreeNodeContract $node, array $vector, int $seq, int $counter): int => $counter + 1,
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


$iterator = new PreOrderTraversalIterator(
    node: $root,
    index: fn(TreeNodeContract $node, array $vector, int $seq, int $counter): string => '.' . implode('.', $vector),
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


$iterator = new PreOrderTraversalIterator(
    node: $root,
    index: fn(TreeNodeContract $node, array $vector, int $seq, int $counter): string => implode('.', $vector),
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


$iterator = new PostOrderTraversalIterator($root);
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

//$root->addChild(new)
