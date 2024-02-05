<?php

declare(strict_types=1);

namespace Dakujem\Test;

use Dakujem\Oliva\Exceptions\ExtractorReturnValueIssue;
use Dakujem\Oliva\Exceptions\InvalidInputData;
use Dakujem\Oliva\Iterator\Filter;
use Dakujem\Oliva\Node;
use Dakujem\Oliva\Recursive\TreeBuilder;
use Dakujem\Oliva\Seed;
use Tester\Assert;

require_once __DIR__ . '/setup.php';

class Item
{
    public function __construct(
        public mixed $id,
        public mixed $parent,
    ) {
    }
}

(function () {
    $data = [
        new Item(id: 0, parent: null),
    ];

    $builder = new TreeBuilder(
        node: fn(?Item $item): Node => new Node($item),
        selfRef: fn(?Item $item): int => $item?->id,
        parentRef: fn(?Item $item): ?int => $item?->parent,
    );

    $tree = $builder->build(
        input: $data,
    );

    Assert::same([
        '>' => '[0]',
    ], TreeTesterTool::visualize($tree));


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
    ], TreeTesterTool::visualize($tree));


    //new Filter($it, Seed::omitNull());
    $withoutRoot = fn(iterable $iterator) => new Filter($iterator, Seed::omitRoot());

    Assert::same([
//        '>' => '[4]', is omitted by the Seed::omitRoot() call
        '>0' => '[2]',
        '>0.0' => '[1]',
        '>1' => '[5]',
        '>1.0' => '[6]',
        '>2' => '[3]',
    ], TreeTesterTool::visualize($tree, $withoutRoot));

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
        'No root node found in the input collection.',
    );
    Assert::throws(
        fn() => $builder->build([new Item(id: 7, parent: 42)]),
        InvalidInputData::class,
        'No root node found in the input collection.',
    );
})();


(function () {
    $builder = new TreeBuilder(
        node: fn(?Item $item): Node => new Node($item),
        selfRef: fn(?Item $item): mixed => $item?->id,
        parentRef: fn(?Item $item): mixed => $item?->parent,
        root: fn(?Item $item): bool => $item->id === 'unknown',
    );

    /** @var Node $root */
    $root = $builder->build([
        new Item(id: 'frodo', parent: 'unknown'),
        new Item(id: 'sam', parent: 'unknown'),
        new Item(id: 'gandalf', parent: 'unknown'),
        new Item(id: 'unknown', parent: 'unknown'),
    ]);

    Assert::same('unknown', $root->data()?->id);
    Assert::same('unknown', $root->data()?->parent);
    Assert::count(3, $root->children());

    Assert::same([
        '>' => '[unknown]',
        '>0' => '[frodo]',
        '>1' => '[sam]',
        '>2' => '[gandalf]',
    ], TreeTesterTool::visualize($root));
})();

(function () {
    $builder = new TreeBuilder(
        node: fn(?Item $item): Node => new Node($item),
        selfRef: fn(?Item $item): mixed => $item?->id,
        parentRef: fn(?Item $item): mixed => $item?->parent,
    );

    Assert::throws(
        function () use ($builder) {
            $builder->build([
                new Item(id: null, parent: null),
            ]);
        },
        ExtractorReturnValueIssue::class,
        'Invalid "self reference" returned by the extractor. Requires a int|string unique to the given node.',
    );

    Assert::throws(
        function () use ($builder) {
            $builder->build([
                new Item(id: 123, parent: 4.2),
            ]);
        },
        ExtractorReturnValueIssue::class,
        'Invalid "parent reference" returned by the extractor. Requires a int|string uniquely pointing to "self reference" of another node, or `null`.',
    );


    Assert::throws(
        function () use ($builder) {
            $builder->build([
                new Item(id: 123, parent: null),
                new Item(id: 42, parent: 123),
                new Item(id: 42, parent: 5),
            ]);
        },
        ExtractorReturnValueIssue::class,
        'Duplicate node reference: 42',
    );
})();
