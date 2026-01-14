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
    Tree::linkChildren($one, $childrenOfTwo, onParentUnlinked: function ($parent) use (&$counter, $two) {
        Assert::same($two, $parent);
        $counter += 1;
    });
    Assert::same(2, $counter);

    // note: the parent has changed to "one"
    Tree::linkChildren($one, $childrenOfTwo, key: fn(Node $node) => $node->data()); // key by data
    Assert::same([
        0 => $b,
        1 => $c,
        'Y' => $y,
        'Z' => $z,
    ], $one->children());
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

(function () {
    $parent = new Node(null);
    $node = new NotMovable(null);

    Assert::throws(function () use ($parent, $node) {
        Tree::linkChildren($parent, [$node]);
    }, NodeNotMovable::class, 'Encountered a non-movable node while manipulating a tree.');
})();


(function () {
    //
    // Duplicate linking of the same node without specifying a key should have no effect.
    //

    $parent = new Node(null);
    $node = new Node(null);

    Tree::link($node, $parent); // default index `0`
    Tree::link($node, $parent); // has no effect
    Assert::same([0 => $node], $parent->children());

    Tree::link($node, $parent, 'foo');
    Tree::link($node, $parent); // has no effect, preserves the previously assigned key
    Assert::same(['foo' => $node], $parent->children());
})();


(function () {
    //
    // Duplicate linking of the same node with the same key should have no effect.
    //

    $node1 = new Node(null);
    $node2 = new Node(null);
    $children = ['one' => $node1, 'two' => $node2];
    $parent = new Node(null, children: $children);
    Assert::same($children, $parent->children()); // sanity check

    // Calling Tree::link here has no effect and does not change the order of the child nodes.
    Tree::link($node1, $parent, 'one');
    Assert::same($children, $parent->children());
})();


(function () {
    //
    // Duplicate linking of the same node with a specific key should change the node's key (re-link the node with a different child key).
    //

    $node1 = new Node(null);
    $node2 = new Node(null);
    $children = ['one' => $node1, 'two' => $node2];
    $parent = new Node(null, children: $children);
    Assert::same($children, $parent->children()); // sanity check

    // This call removes the child and re-links it under a different key.
    Tree::link($node1, $parent, 'three');
    Assert::same(['two' => $node2, 'three' => $node1], $parent->children());
})();

