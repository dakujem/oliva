<?php

declare(strict_types=1);

use Dakujem\Oliva\MaterializedPath\TreeBuilder;
use Dakujem\Oliva\Node;
use Tester\Environment;

require_once __DIR__ . '/../vendor/autoload.php';
Environment::setup();

class Item
{
    public function __construct(
        public int $id,
        public string $path,
    ) {
    }
}

$data = [
    new Item(0, ''), // TODO get rid of this
    new Item(1, '000'),
    new Item(2, '001'),
//    new Item(3, '003'),
//    new Item(4, '000000'),
//    new Item(5, '002000'),
//    new Item(6, '002'),
//    new Item(7, '007007007'),
//    new Item(8, '008'),
];

$builder = new TreeBuilder();
$tree = $builder->buildTree(
    input: $data,
    node: fn(Item $item) => new Node($item),
    vector: TreeBuilder::fixed(
        3,
        fn(Item $item) => $item->path,
    ),
);

xdebug_break();

$item = $tree->root()?->data();


// rekalkulacia / presuny ?


// propagacia zmeny (hore/dole) (eventy?)




