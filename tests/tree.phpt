<?php

declare(strict_types=1);

namespace Dakujem\Test;

use Dakujem\Oliva\Exceptions\NodeNotMovable;
use Dakujem\Oliva\Node;
use Dakujem\Oliva\Simple\NodeBuilder;
use Dakujem\Oliva\Tree;
use Tester\Assert;

require_once __DIR__ . '/setup.php';

// Test tree manipulation edge cases
(function () {
    $nodeWithUnmovableChild = new Node('A', ['one' => new Node('B'), 'two' => new NotMovable('impostor')]);
    $nodeWithUnmovableParent = new Node('A', parent: new NotMovable('impostor'));

    Assert::throws(function () use ($nodeWithUnmovableChild) {
        Tree::unlinkChildren($nodeWithUnmovableChild);
    }, NodeNotMovable::class, 'Encountered a non-movable node while manipulating a tree.');

    Assert::throws(function () use ($nodeWithUnmovableParent) {
        Tree::unlink($nodeWithUnmovableParent);
    }, NodeNotMovable::class, 'Encountered a non-movable node while manipulating a tree.');

    Assert::throws(function () use ($nodeWithUnmovableParent) {
        Tree::link($nodeWithUnmovableParent, new Node(null));
    }, NodeNotMovable::class, 'Encountered a non-movable node while manipulating a tree.');

    Assert::throws(function () use ($nodeWithUnmovableChild) {
        Tree::reindexTree($nodeWithUnmovableChild, null, null);
    }, NodeNotMovable::class, 'Encountered a non-movable node while manipulating a tree.');
})();

(function () {
    $proxy = new NodeBuilder(fn(mixed $data) => new Node($data));

    $one = $proxy->node('A', $childrenOfOne = [
        $b = $proxy->node('B'),
        $c = $proxy->node('C'),
    ]);
    $two = $proxy->node('X', $childrenOfTwo = [
        $y = $proxy->node('Y'),
        $z = $proxy->node('Z'),
    ]);

    $shouldBeTwo = Tree::link($y, $one);
    $shouldBeOne = Tree::link($y, $two);
    Assert::same($two, $shouldBeTwo);
    Assert::same($one, $shouldBeOne);

    $counter = 0;
    Tree::linkChildren($one, $childrenOfTwo, function ($parent) use (&$counter, $two) {
        Assert::same($two, $parent);
        $counter += 1;
    });
    Assert::same(2, $counter);
})();

(function () {
    $node = new Node(null, parent: $parent = new Node(null));

    Assert::same($parent, $node->parent());
    Assert::same([], $parent->children());

    Tree::link($node, $parent);
    Assert::same($parent, $node->parent());
    Assert::same([$node], $parent->children());
})();

(function () {
    $parent = new Node(null, children: [
        $node = new Node(null),
    ]);

    Assert::same(null, $node->parent());
    Assert::same([$node], $parent->children());

    Tree::link($node, $parent);
    Assert::same($parent, $node->parent());
    Assert::same([$node], $parent->children());
})();

(function () {
    $proxy = new NodeBuilder(fn(mixed $data) => new Node($data));

    $parent = $proxy->node(null, [
        'original' => $node = new Node(null),
    ]);

    Assert::same($parent, $node->parent());
    Assert::same(['original' => $node], $parent->children());

    Tree::link($node, $parent, 'new-key');
    Assert::same($parent, $node->parent());
    Assert::same(['new-key' => $node], $parent->children());
})();

(function () {
    $proxy = new NodeBuilder(fn(mixed $data) => new Node($data));

    $parent = $proxy->node(null, [
        'original' => $node = new Node(null),
    ]);

    Assert::same($parent, $node->parent());
    Assert::same(['original' => $node], $parent->children());

    Tree::link($node, $parent, 'original');
    Assert::same($parent, $node->parent());
    Assert::same(['original' => $node], $parent->children());

    Tree::link($node, $parent);
    Assert::same($parent, $node->parent());
    Assert::same(['original' => $node], $parent->children());
})();

