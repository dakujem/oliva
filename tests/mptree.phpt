<?php

declare(strict_types=1);

use Dakujem\Oliva\Iterator\PreOrderTraversal;
use Dakujem\Oliva\MaterializedPath\TreeBuilder;
use Dakujem\Oliva\Node;
use Dakujem\Oliva\TreeNodeContract;
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
    new Item(1, '000'),
    new Item(2, '001'),
    new Item(3, '003'),
    new Item(4, '000000'),
    new Item(5, '002000'),
    new Item(6, '002'),
    new Item(7, '007007007'),
    new Item(8, '008'),
];

$builder = new TreeBuilder();
$tree = $builder->buildTree(
    input: TreeBuilder::rootNull($data),
    node: fn(?Item $item) => new Node($item),
    vector: TreeBuilder::fixed(
        3,
        fn(?Item $item) => $item?->path,
    ),
);

$it = new PreOrderTraversal($tree->root(), fn(TreeNodeContract $node, array $vector, int $seq, int $counter): string => '>' . implode('.', $vector));
foreach($it as $key => $node){
    $item = $node->data();
    if(null === $item){
        echo '>root'."\n";
        continue;
    }
    $pad = str_pad($key, 10, ' ',STR_PAD_LEFT);
    echo "$pad {$item->id} {$item->path}\n";
}

//xdebug_break();

$item = $tree->root()?->data();


// rekalkulacia / presuny ?


// propagacia zmeny (hore/dole) (eventy?)



