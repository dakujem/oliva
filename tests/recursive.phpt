<?php

declare(strict_types=1);

use Dakujem\Oliva\Node;
use Dakujem\Oliva\Recursive\TreeBuilder;
use Dakujem\Oliva\Seed;
use Tester\Assert;
use Tester\Environment;

require_once __DIR__ . '/../vendor/autoload.php';
Environment::setup();

class Item
{
    public function __construct(
        public int $id,
        public ?int $parent,
    ) {
    }
}

$data = [
    new Item(1, 2),
    new Item(2, 4),
    new Item(3, 4),
    new Item(4, null),
    new Item(5, 4),
    new Item(77, 42),
    new Item(8, 7),
    new Item(6, 5),
];

$builder = new TreeBuilder(
    node: fn(?Item $item) => new Node($item),
    self: fn(?Item $item) => $item?->id,
    parent: fn(?Item $item) => $item?->parent,
);

$tree = $builder->build(
    input: Seed::nullFirst($data),
);


Assert::type(Node::class, $tree);
