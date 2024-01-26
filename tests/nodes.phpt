<?php

declare(strict_types=1);

namespace Dakujem\Test;

use Dakujem\Oliva\Node;
use Dakujem\Oliva\Simple\NodeBuilder;
use Dakujem\Oliva\Simple\TreeWrapper;
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
    $str = [];
    foreach ($root as $node) {
        $str[] = $node->data();
    }
    Assert::same('F,B,A,D,C,E,G,I,H', implode(',', $str));


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
    $wrapper = new TreeWrapper(fn(array $raw) => new Node($raw['data']), fn(array $raw) => $raw['children'] ?? null);
    $root = $wrapper->wrap($data);

    // pre-order
    $str = [];
    foreach ($root as $node) {
        $str[] = $node->data();
    }
    Assert::same('F,B,A,D,C,E,G,I,H', implode(',', $str));
})();
