# Oliva

Flexible tree structures, materialized path trees, tree traversal iterators.

>
> 💿 `composer require dakujem/oliva`
>


This package is a modern reimplementation of [`dakujem/oliva-tree`](https://github.com/dakujem/oliva-tree).


## Tree

A tree is a form of a graph, specifically a directed acyclic graph (DAG).  
Each node in a tree can have at most one parent and any number of children.  
The node without a parent is called the root.  
A node without children is called a leaf.  

Read more on Wikipedia: [Tree (data structure)](https://en.wikipedia.org/wiki/Tree_(data_structure)).


## Builders

Oliva provides _tree builders_, classes that construct structured trees from unstructured data.
The data is usually in form of iterable collections, typically a result of SQL queries and such.

```php
$tree = (new TreeBuilder)->build(
    $anyDataCollection,
    fn(mixed $data) => new Node($data),
);
```

The builders are flexible and allow to create any node instances via the _node factory_ callable.  
The simplest one may look like this
```php
fn(mixed $data) => new \Dakujem\Oliva\Node($data);
```

... but it's really up to the integrator to provide a node factory according to his needs:
```php
fn(mixed $anyItem) => new MyNode(MyTransformer::transform($anyItem)),
```


## Materialized path trees

MPT refers to the technique of storing the position of nodes relative to the root node (a.k.a. "path") in the data.

There are multiple ways to actually do that and Oliva is agnostic of them.  
A typical _path_ of a node may look like the following:
- `"1.33.2"`, `"/1/33/2"`, also known as the **delimited** variant
- `"001033002"`, known as the **fixed** variant with 3 characters per level (here `001`, `033` and `002`)
- `[1,33,2]`, as an array of integers, here also referred to as a _vector_

Also, the individual references may either mean the position relative to other siblings on a given level, or be direct node references (ancestor IDs).
That is, a path `1.2.3` may mean "the third child of the second child of the first child of the root",
or it may mean that a node's ancestor IDs are `[1,2,3]`, `3` being the parent's ID.

To enable all the different techniques, Oliva MPT `TreeBuilder` requires the user to pass a vector extractor function, e.g. `fn($item) => explode('.', $item->path)`.
Oliva comes with two common-case extractor factories: `TreeBuilder::fixed()` and `TreeBuilder::delimited()`.

Simple example of a fixed-length MPT:
```php
use Any\Item;
use Dakujem\Oliva\MaterializedPath\TreeBuilder;
use Dakujem\Oliva\Node;

// The input data may be a result of an SQL query or ny other iterable collection.
$data = [
    new Item(id: 0, path: ''), // the root
    new Item(id: 1, path: '000'),
    new Item(id: 2, path: '001'),
    new Item(id: 3, path: '003'),
    new Item(id: 4, path: '000000'),
    new Item(id: 5, path: '002000'),
    new Item(id: 6, path: '002'),
];

$builder = new TreeBuilder();
$root = $builder->build(
    input: $data,
    node: fn(Item $item) => new Node($item),      // How to create a node.
    vector: TreeBuilder::fixed(                   // How to extract path vector.
        levelWidth: 3,
        accessor: fn(Item $item) => $item->path,
    ),
);
```

Same example with an equivalent delimited MPT:
```php
use Any\Item;
use Dakujem\Oliva\MaterializedPath\TreeBuilder;
use Dakujem\Oliva\Node;

$data = [
    new Item(id: 0, path: null), // the root
    new Item(id: 1, path: '.0'),
    new Item(id: 2, path: '.1'),
    new Item(id: 3, path: '.3'),
    new Item(id: 4, path: '.0.0'),
    new Item(id: 5, path: '.2.0'),
    new Item(id: 6, path: '.2'),
];

$builder = new TreeBuilder();
$root = $builder->build(
    input: $data,
    node: fn(Item $item) => new Node($item),      // How to create a node.
    vector: TreeBuilder::delimited(               // How to extract path vector.
        delimiter: '.',
        accessor: fn(Item $item) => $item->path,
    ),
);
```

> 💡
>
> Since child nodes are be added to parents in the order they appear in the source data,
> sorting the source collection by path prior to building the tree may be a good idea. 


## Recursive trees

By far the most common and trivial way of persisting trees. Each node has a reference to its parent.
The tree is reconstructed recursively.

```php
use Any\Item;
use Dakujem\Oliva\Recursive\TreeBuilder;
use Dakujem\Oliva\Node;

$data = [
    new Item(id: 0, parent: null),
    new Item(id: 1, parent: 0),
    new Item(id: 2, parent: 0),
    new Item(id: 3, parent: 0),
    new Item(id: 4, parent: 1),
    new Item(id: 5, parent: 6),
    new Item(id: 6, parent: 0),
];

$builder = new TreeBuilder();
$root = $builder->build(
    input: $data,
    node: fn(Item $item) => new Node($item), // How to create a node.
    self: fn(Item $item) => $item->id,       // How to get ID of self.
    parent: fn(Item $item) => $item->parent, // How to get parent ID.
    root: null,                              // How to tell the root node (parent is `null`)
);
```


## Wrapping JSON or arrays

In case the data is already structured as tree data, a simple wrapper may be used to build the tree structure.

```php
use Any\Item;
use Dakujem\Oliva\Simple\TreeBuilder;
use Dakujem\Oliva\Node;

// $json = (new External\ApiConnector())->call('getJsonData');
// $data = json_decode($json);

$data = [
    [
        'attributes' => [ ... ],
        'children' => [
            [
                'attributes' => [ ... ],
                'children' => [],
            ]
        ],
    ],
    [
        'attributes' => [ ... ],
        'children' => [ ... ],
    ],
];

$builder = new TreeBuilder();
$root = $builder->build(
    input: $data,
    node: fn(array $item) => new Node($item),                   // How to create a node.
    children: fn(array $item):array => $item['children'] ?? [], // How to extract children.
);
```


## Manual tree building

Using the low-level interface for building a tree:
```php
use Dakujem\Oliva\Node;

$root = new Node('root');
$root->addChild($child1 = new Node('first child'));
$root->addChild($child2 = new Node('second child'));
$child1->setParent($root);
$child2->setParent($root);
$leaf1 = new Node('leaf of the first child node');
$child1->addChild($leaf1);
$leaf1->setParent($child1);
$leaf2 = new Node('another leaf of the first child node');
$child1->addChild($leaf2);
$leaf2->setParent($child1);
```
... yeah, this is not the most optimal way to build a tree.

Using high-level interface for doing the same:
```php
use Dakujem\Oliva\Node;
use Dakujem\Oliva\Tree;

$root = new Node('root');
Tree::link(node: $child1 = new Node('first child'), parent: $root);
Tree::link(node: $child2 = new Node('second child'), parent: $root);
Tree::link(node: new Node('leaf of the first child node'), parent: $child1);
Tree::link(node: new Node('another leaf of the first child node'), parent: $child1);
```


🚧 TODO fluent?
```php
use Dakujem\Oliva\Node;
use Dakujem\Oliva\Fluent\Proxy;

$proxy = new Proxy(fn(mixed $item) => new Node($item));

$proxy
    ->node('root')
        ->node('first child')
            ->node('leaf of the first child node')->end()
            ->leaf('another leaf of the first child node') // same as calling ->node(...)->end() above
        ->node('second child');

$root = $proxy->root();
```


## Caveats

🚧 TODO

Caveats:
- no root in data
- null data rows

