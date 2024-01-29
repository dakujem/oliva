<?php

declare(strict_types=1);

namespace Dakujem\Test;

use Dakujem\Oliva\Exceptions\InvalidInputData;
use Dakujem\Oliva\Iterator\Filter;
use Dakujem\Oliva\Iterator\PreOrderTraversal;
use Dakujem\Oliva\Node;
use Dakujem\Oliva\Recursive\TreeBuilder;
use Dakujem\Oliva\Seed;
use Dakujem\Oliva\TreeNodeContract;
use Tester\Assert;

require_once __DIR__ . '/setup.php';

class Item
{
    public function __construct(
        public int $id,
        public ?int $parent,
    ) {
    }
}

(function () {
    $toArray = function (TreeNodeContract $root, callable $mod = null) {
        $it = new PreOrderTraversal($root, fn(
            TreeNodeContract $node,
            array $vector,
            int $seq,
            int $counter,
        ): string => '>' . implode('.', $vector));
        return array_map(function (Node $item): string {
            $data = $item->data();
            return null !== $data ? "[$data->id]" : 'root';
        }, iterator_to_array($mod ? $mod($it) : $it));
    };

    $data = [
        new Item(id: 0, parent: null),
    ];

    $builder = new TreeBuilder(
        node: fn(?Item $item) => new Node($item),
        selfRef: fn(?Item $item) => $item?->id,
        parentRef: fn(?Item $item) => $item?->parent,
    );

    $tree = $builder->build(
        input: $data,
    );

    Assert::same([
        '>' => '[0]',
    ], $toArray($tree));


    $data = [
        new Item(id: 1, parent: 2),
        new Item(id: 2, parent: 4),
        new Item(id: 4, parent: null),
        new Item(id: 8, parent: 7),
        new Item(id: 77, parent: 42),
        new Item(id: 5, parent: 4),
        new Item(id: 6, parent: 5),
        new Item(id: 3, parent: 4),
    ];

    $builder = new TreeBuilder(
        node: fn(?Item $item) => new Node($item),
        selfRef: fn(?Item $item) => $item?->id,
        parentRef: fn(?Item $item) => $item?->parent,
    );

    $tree = $builder->build(
        input: $data,
    );

    Assert::type(Node::class, $tree);

    Assert::same([
        '>' => '[4]',
        '>0' => '[2]',
        '>0.0' => '[1]',
        '>1' => '[5]',
        '>1.0' => '[6]',
        '>2' => '[3]',
    ], $toArray($tree));


    //new Filter($it, Seed::omitNull());
    $withoutRoot = fn(iterable $iterator) => new Filter($iterator, Seed::omitRoot());

    Assert::same([
//        '>' => '[4]', is omitted by the Seed::omitRoot() call
        '>0' => '[2]',
        '>0.0' => '[1]',
        '>1' => '[5]',
        '>1.0' => '[6]',
        '>2' => '[3]',
    ], $toArray($tree, $withoutRoot));

    $filter = new Filter($collection = [
        new Node(null),
        new Node('ok'),
    ], Seed::omitNull());
    $shouldContainOneElement = iterator_to_array($filter);
    Assert::count(1, $shouldContainOneElement);
    Assert::same(null, Seed::firstOf($collection)?->data());
    Assert::same('ok', Seed::firstOf($filter)?->data());


    Assert::throws(
        fn() => $builder->build([]),
        InvalidInputData::class,
        'No root node found in the data.',
    );
    Assert::throws(
        fn() => $builder->build([new Item(id: 7, parent: 42)]),
        InvalidInputData::class,
        'No root node found in the data.',
    );
})();
