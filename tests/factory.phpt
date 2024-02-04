<?php

declare(strict_types=1);

namespace Dakujem\Test;

use Dakujem\Oliva\Exceptions\InvalidNodeFactoryReturnValue;
use Dakujem\Oliva\MaterializedPath;
use Dakujem\Oliva\Recursive;
use Dakujem\Oliva\Simple\NodeBuilder;
use Dakujem\Oliva\Simple\TreeWrapper;
use Tester\Assert;

require_once __DIR__ . '/setup.php';

// Test some edge cases
(function () {
    $testFactory = function (callable $badNodeFactory): void {
        Assert::throws(function () use ($badNodeFactory) {
            (new TreeWrapper($badNodeFactory, fn() => []))->wrap(['item']);
        }, InvalidNodeFactoryReturnValue::class);

        Assert::throws(function () use ($badNodeFactory) {
            (new NodeBuilder($badNodeFactory))->node('item');
        }, InvalidNodeFactoryReturnValue::class);

        Assert::throws(function () use ($badNodeFactory) {
            (new MaterializedPath\TreeBuilder($badNodeFactory, fn() => []))->build(['item']);
        }, InvalidNodeFactoryReturnValue::class);

        Assert::throws(function () use ($badNodeFactory) {
            (new Recursive\TreeBuilder($badNodeFactory, fn() => 1234, fn() => null))->build(['item']);
        }, InvalidNodeFactoryReturnValue::class);
    };

    // These factories do not return a valid Node instance (node contract).
    $testFactory(fn() => null);
    $testFactory(fn() => 'foo');
    $testFactory(fn($data) => new NotNodeAtAll($data));
    $testFactory(fn($data) => new NotMovable($data));

    // The following can be uncommented to test that the failing tests actually do not fail... lol.
//    $testFactory(fn($data) => new Node($data));
})();


