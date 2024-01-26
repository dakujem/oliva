<?php

declare(strict_types=1);

namespace Dakujem\Test;

use Dakujem\Oliva\Iterator\PreOrderTraversal;
use Dakujem\Oliva\MaterializedPath\Path;
use Dakujem\Oliva\MaterializedPath\Support\AlmostThere;
use Dakujem\Oliva\MaterializedPath\TreeBuilder;
use Dakujem\Oliva\Node;
use Dakujem\Oliva\Seed;
use Dakujem\Oliva\Tree;
use Dakujem\Oliva\TreeNodeContract;
use LogicException;
use RuntimeException;
use Tester\Assert;

require_once __DIR__ . '/setup.php';

class Item
{
    public function __construct(
        public int $id,
        public string $path,
    ) {
    }
}

(function () {
    $toArray = function (TreeNodeContract $root) {
        $it = new PreOrderTraversal($root, fn(
            TreeNodeContract $node,
            array $vector,
            int $seq,
            int $counter,
        ): string => '>' . implode('.', $vector));
        return array_map(function (Node $item): string {
            $data = $item->data();
            return null !== $data ? "[$data->id]" : 'root';
        }, iterator_to_array($it));
    };


    $data = [
        new Item(id: 1, path: '000'),
        new Item(id: 2, path: '001'),
        new Item(id: 3, path: '003'),
        new Item(id: 4, path: '000001'),
        new Item(id: 5, path: '002000'),
        new Item(id: 6, path: '002'),
        new Item(id: 7, path: '007007007'),
        new Item(id: 8, path: '000000'),
        new Item(id: 9, path: '009'),
    ];

    $builder = new TreeBuilder(
        node: fn(?Item $item) => new Node($item),
        vector: Path::fixed(
            3,
            fn(?Item $item) => $item?->path,
        ),
    );
    $almost = $builder->processInput(
        input: Seed::nullFirst($data),
    );

    Assert::type(AlmostThere::class, $almost);
    Assert::type(Node::class, $almost->root());
    Assert::null($almost->root()?->data());
    Assert::type(Item::class, Seed::firstOf($almost->root()?->children())?->data());

    Assert::same([
        '>' => 'root',
        '>0' => '[1]',
        '>0.0' => '[4]',
        '>0.1' => '[8]',
        '>1' => '[2]',
        '>2' => '[3]',
        '>3' => '[6]',
        '>3.0' => '[5]',
        '>5' => '[9]', // note the index `4` being skipped - this is expected, because the node with ID 7 is not connected to the root and is omitted during reconstruction by the shadow tree, but the index is not changed
    ], $toArray($almost->root()));


    $vectorExtractor = Path::fixed(
        3,
        fn(mixed $path) => $path,
    );
    Assert::same(['000', '000'], $vectorExtractor('000000'));
    Assert::same(['foo', 'bar'], $vectorExtractor('foobar'));
    Assert::same(['x'], $vectorExtractor('x')); // shorter than 3
    Assert::same([], $vectorExtractor(''));
    Assert::same([], $vectorExtractor(null));
    Assert::throws(function () use ($vectorExtractor) {
        $vectorExtractor(4.2);
    }, RuntimeException::class); // TODO improve


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
        new Item(id: 7, path: '.7.7.7'),
        new Item(id: 8, path: '.0.1'),
        new Item(id: 9, path: '.9'),
    ];

    $builder = new TreeBuilder(
        node: fn(Item $item) => new Node($item),
        vector: Path::delimited(
            delimiter: '.',
            accessor: fn(Item $item) => $item->path,
        ),
    );

    $root = $builder->build(
        input: $collection,
    );



    Assert::same([
        '>' => '[0]',
        '>0' => '[1]',
        '>0.0' => '[4]',
        '>0.1' => '[8]',
        '>1' => '[2]',
        '>2' => '[3]',
        '>3' => '[6]',
        '>3.0' => '[5]',
        '>5' => '[9]', // note the index `4` being skipped - this is expected
    ], $toArray($root));


    Tree::reindexTree($root, fn(Node $node) => $node->data()->id, null);
    Assert::same([
        '>' => '[0]',
        '>1' => '[1]',
        '>1.4' => '[4]',
        '>1.8' => '[8]',
        '>2' => '[2]',
        '>3' => '[3]',
        '>6' => '[6]',
        '>6.5' => '[5]',
        '>9' => '[9]',
    ], $toArray($root));

    Tree::reindexTree($root, null, fn(Node $a, Node $b) => $a->data()->path <=> $b->data()->path);
    Assert::same([
        '>' => '[0]',
        '>1' => '[1]', // .0
        '>1.4' => '[4]', // .0.0
        '>1.8' => '[8]', // .0.1
        '>2' => '[2]', // .1
        '>6' => '[6]', // .2
        '>6.5' => '[5]', // .2.0
        '>3' => '[3]', // .3
        '>9' => '[9]', // .9
    ], $toArray($root));
})();