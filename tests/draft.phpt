<?php

declare(strict_types=1);

use Dakujem\Oliva\Iterator\LevelOrderTraversalIterator;
use Dakujem\Oliva\Iterator\PostOrderTraversalIterator;
use Dakujem\Oliva\Iterator\PreOrderTraversalIterator;
use Dakujem\Oliva\Node;
use Tester\Assert;
use Tester\Environment;

require_once __DIR__ . '/../../vendor/autoload.php';

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
    $str.= $node->data();
}
echo $str;
echo "\n";
Assert::same('FBADCEGIH', $str);

$iterator = new PostOrderTraversalIterator($root);
$str = '';
foreach ($iterator as $node) {
    $str.= $node->data();
}
echo $str;
echo "\n";
Assert::same('ACEDBHIGF', $str);

$iterator = new LevelOrderTraversalIterator($root);
$str = '';
foreach ($iterator as $i => $node) {
    $str.= $node->data();
}
echo $str;
echo "\n";
Assert::same('FBGADICEH', $str);

echo "\n";
foreach($root as $i){
    echo $i->data();
}
echo "\n";

//$root->addChild(new)
