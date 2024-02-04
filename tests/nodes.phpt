<?php

declare(strict_types=1);

namespace Dakujem\Test;

use Dakujem\Oliva\Exceptions\AcceptsDebugContext;
use Dakujem\Oliva\Exceptions\Context;
use Dakujem\Oliva\Node;
use Dakujem\Oliva\Simple\NodeBuilder;
use Dakujem\Oliva\Simple\TreeWrapper;
use Dakujem\Oliva\Tree;
use Dakujem\Oliva\TreeNodeContract;
use Exception;
use Tester\Assert;

require_once __DIR__ . '/setup.php';

(function () {
    $builder = new NodeBuilder(fn(mixed $data) => new Node($data));

    // Tree from here:
    // https://en.wikipedia.org/wiki/Tree_traversal
    $root = $builder->node('F', [
        $builder->node('B', [
            $builder->node('A'),
            $builder->node('D', [
                $builder->node('C'),
                $builder->node('E'),
            ]),
        ]),
        $builder->node('G', [
            $builder->node('I', [
                $builder->node('H'),
            ]),
        ]),
    ]);

    // pre-order
    Assert::same('FBADCEGIH', TreeTesterTool::flatten($root));
})();

(function () {
    // Tree from here:
    // https://en.wikipedia.org/wiki/Tree_traversal
    $data = [
        'data' => 'F',
        'children' => [
            [
                'data' => 'B',
                'children' => [
                    [
                        'data' => 'A',
                    ],
                    [
                        'data' => 'D',
                        'children' => [
                            [
                                'data' => 'C',
                            ],
                            [
                                'data' => 'E',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'data' => 'G',
                'children' => [
                    [
                        'data' => 'I',
                        'children' => [
                            [
                                'data' => 'H',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];
    $wrapper = new TreeWrapper(
        fn(array $raw) => new Node($raw['data']),
        fn(array $raw): ?iterable => $raw['children'] ?? null,
    );
    $root = $wrapper->wrap($data);

    // pre-order
    Assert::same('FBADCEGIH', TreeTesterTool::flatten($root));

    $faulty = $data;
    $thrown = false;
    try {
        $faulty['children'][1]['children'][] = ['data' => 'this will cause type error inside the extractor', 'children' => 42];
        $wrapper->wrap($faulty);
    } catch (AcceptsDebugContext $e) {
        /** @var Context $context */
        $context = $e->context;
        Assert::count(3, $context->bag['nodes'] ?? []);
        Assert::same(
            ['this will cause type error inside the extractor', 'G', 'F'],
            array_map(fn(Node $node) => $node->data(), $context->bag['nodes']),
        );
        $thrown = true;
    }
    Assert::true($thrown, 'The catch block has to run');

    $faultyWrapper = new TreeWrapper(
        fn(array $raw) => new Node($raw['data']),
        function (array $raw): mixed {
            if (isset($raw['children']) && !is_iterable($raw['children'] ?? null)) {
                return 'fault';
            }
            return $raw['children'] ?? null;
        },
    );

    $thrown = false;
    try {
        $faultyWrapper->wrap($faulty);
    } catch (AcceptsDebugContext $e) {
        /** @var Context $context */
        $context = $e->context;
        Assert::count(3, $context->bag['nodes'] ?? []);
        Assert::same(
            ['this will cause type error inside the extractor', 'G', 'F'],
            array_map(fn(Node $node) => $node->data(), $context->bag['nodes']),
        );
        // the "children" extracted by the extractor
        Assert::same('fault', $context->bag['children'] ?? null);
        // the data that was present
        Assert::same([
            'data' => 'this will cause type error inside the extractor',
            'children' => 42,
        ], $context->bag['data'] ?? null);
        // ref to the parent node
        Assert::type(Node::class, $context->bag['parent'] ?? null);
        $thrown = true;
    }
    Assert::true($thrown, 'The catch block has to run');
})();


// Test linking/unlinking of nodes via Tree utility.
(function () {
    $a = new Node('A');
    $b = new Node('B');
    $c = new Node('C');
    $d = new Node('D');
    $e = new Node('E');

    Tree::linkChildren($a, [$b, $c]);
    Tree::linkChildren($b, [$d, $e]);

    Assert::same('ABDEC', TreeTesterTool::flatten($a));
    assert::same($a, $b->root());
    assert::same($a, $c->root());
    assert::same($a, $d->root());
    assert::same($a, $e->root());

    Assert::true($a->hasChild(0));
    Assert::true($a->hasChild(1));
    Assert::true($a->hasChild($b));
    Assert::true($a->hasChild($c));
    Assert::false($a->hasChild($d));
    Assert::false($a->hasChild(2));

    $hasRun = false;
    Assert::same($b, $e->parent());
    Assert::same([$d, $e], $b->children());
    Assert::same([], $d->children());
    Tree::linkChildren($d, $e, function (TreeNodeContract $originalParent) use (&$hasRun, $b) {
        $hasRun = true;
        // B is the original parent of E
        Assert::same($b, $originalParent);
    });
    Assert::true($hasRun);
    Assert::same('ABDEC', TreeTesterTool::flatten($a));
    Assert::same($d, $e->parent());
    Assert::same([$d], $b->children());
    Assert::same([$e], $d->children());
    assert::same($a, $e->root());

    Tree::unlinkChildren($a);
    Assert::same('A', TreeTesterTool::flatten($a));
    Assert::true($a->isRoot());
    Assert::true($a->isLeaf());
    Assert::same([], $a->children());
    Assert::true($b->isRoot());
    Assert::true($c->isRoot());

    // When unlinking node that is already a root, null is returned.
    // Otherwise, the original parent is returned.
    Assert::same(null, Tree::unlink($b));
    Tree::link($b, $a, 'my-key');
    Assert::false($a->isLeaf());
    Assert::false($b->isRoot());
    Assert::true($a->hasChild($b));
    Assert::true($a->hasChild('my-key'));
    Assert::false($a->hasChild(0));
    Assert::same($a, Tree::unlink($b));
    Assert::true($b->isRoot());
})();


// A data node can be filled with any data.
(function () {
    $a = new Node('A');
    Assert::same('A', $a->data());
    $a->fill('foo');
    Assert::same('foo', $a->data());
    $a->fill(null);
    Assert::same(null, $a->data());
    $a->fill($object = new Exception());
    Assert::same($object, $a->data());
})();



(function () {
    $tree = Preset::wikiTree();
    $raw = json_decode(json_encode($tree), true);
    Assert::same([
        'data' => 'F',
        'children' => [
            [
                'data' => 'B',
                'children' => [
                    [
                        'data' => 'A',
                        'children' => [],
                    ],
                    [
                        'data' => 'D',
                        'children' => [
                            [
                                'data' => 'C',
                                'children' => [],
                            ],
                            [
                                'data' => 'E',
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'data' => 'G',
                'children' => [
                    [
                        'data' => 'I',
                        'children' => [
                            [
                                'data' => 'H',
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ], $raw);
})();



