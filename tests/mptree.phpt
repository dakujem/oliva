<?php

declare(strict_types=1);

use Dakujem\Oliva\Iterator\Filter;
use Dakujem\Oliva\Iterator\PreOrderTraversal;
use Dakujem\Oliva\MaterializedPath\Support\AlmostThere;
use Dakujem\Oliva\MaterializedPath\TreeBuilder;
use Dakujem\Oliva\Node;
use Dakujem\Oliva\Seed;
use Dakujem\Oliva\TreeNodeContract;
use Tester\Assert;
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

$builder = new TreeBuilder(
    node: fn(?Item $item) => new Node($item),
    vector: TreeBuilder::fixed(
        3,
        fn(?Item $item) => $item?->path,
    ),
);
$tree = $builder->processInput(
    input: Seed::nullFirst($data),
);

$item = $tree->root()?->data();

Assert::type(AlmostThere::class, $tree);
Assert::type(Node::class, $tree->root());
Assert::null($tree->root()?->data());
Assert::type(Item::class, Seed::first($tree->root()?->children())?->data());





$vectorExtractor = TreeBuilder::fixed(
    3,
    fn(mixed $path) => $path,
);
Assert::same(['000', '000'], $vectorExtractor('000000'));
Assert::same(['foo', 'bar'], $vectorExtractor('foobar'));
Assert::same([], $vectorExtractor(''));
Assert::same([], $vectorExtractor(null));
Assert::throws(function () use ($vectorExtractor) {
    $vectorExtractor(4.2);
}, \RuntimeException::class); // TODO improve


// an empty input can not result in any tree
Assert::throws(function () use ($builder) {
    $builder->build([]);
}, RuntimeException::class, 'Corrupted input, no tree created.'); // TODO improve


$failingBuilder = new TreeBuilder(fn() => null, fn() => []);
Assert::throws(function () use ($failingBuilder) {
    $failingBuilder->build([null]);
}, LogicException::class, 'The node factory must return a movable node instance.'); // TODO improve

$invalidVector = new TreeBuilder(fn() => new Node(null), fn() => null);
Assert::throws(function () use ($invalidVector) {
    $invalidVector->build([null]);
}, LogicException::class, 'The vector calculator must return an array.'); // TODO improve


$invalidVectorContents = new TreeBuilder(fn() => new Node(null), fn() => ['a', null]);
Assert::throws(function () use ($invalidVectorContents) {
    $invalidVectorContents->build([null]);
}, LogicException::class, 'The vector may only consist of strings or integers.'); // TODO improve


$duplicateVector = new TreeBuilder(fn() => new Node(null), fn() => ['any']);
Assert::throws(function () use ($duplicateVector) {
    $duplicateVector->build([null, null]);
}, LogicException::class, 'Duplicate node vector: any'); // TODO improve



$collection = [
    new Item(id: 0, path: ''), // the root
    new Item(id: 1, path: '.0'),
    new Item(id: 2, path: '.1'),
    new Item(id: 3, path: '.3'),
    new Item(id: 4, path: '.0.0'),
    new Item(id: 5, path: '.2.0'),
    new Item(id: 6, path: '.2'),
    new Item(id: 7, path: '.0.1'),
];

$builder = new TreeBuilder(
    node: fn(Item $item) => new Node($item),
    vector: TreeBuilder::delimited(
        delimiter: '.',
        accessor: fn(Item $item) => $item->path,
    ),
);

$root = $builder->build(
    input: $collection,
);

$iterator = function(TreeNodeContract $root) {
    $it = new PreOrderTraversal($root, fn(
        TreeNodeContract $node,
        array $vector,
        int $seq,
        int $counter,
    ): string => '>' . implode('.', $vector));
    return array_map(function (Node $item): string {
        $data = $item->data();
        return "[$data->id]";
    }, iterator_to_array($it));
};


//new Filter($it, Seed::omitNull());
//new Filter($it, Seed::omitRoot());

Assert::same([
    '>' => '[0]',
    '>0' => '[1]',
    '>0.0' => '[4]',
    '>0.1' => '[7]',
    '>1' => '[2]',
    '>2' => '[3]',
    '>3' => '[6]',
    '>3.0' => '[5]',
], $iterator($root));

