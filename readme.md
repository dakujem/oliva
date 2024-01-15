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

```php
use Dakujem\Oliva\Node;

$tree = new Node('root');
$tree->addChild($child1 = new Node('first child'));
$tree->addChild($child2 = new Node('second child'));
$child1->setParent($tree);
$child2->setParent($tree);
$leaf = new Node('leaf od first node');
$child1->addChild($leaf);
$leaf->setParent($child1);
```
... yeah, this is not the most optimal way to build a tree.


🚧 TODO


## Builders

Oliva provides _tree builders_, classes that construct structured trees from unstructured data.
The data is usually in form of iterable collections, typically a result of SQL queries and such.

```php
$tree = (new TreeBuilder)->build(
    $anyDataCollection,
    fn(mixed $data) => new Node($data),
);
```

The builders are flexible and allow to create any node classes via the _node factory_ callable.  
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
    new Item(0, ''), // the root
    new Item(1, '000'),
    new Item(2, '001'),
    new Item(3, '003'),
    new Item(4, '000000'),
    new Item(5, '002000'),
    new Item(6, '002'),
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
    new Item(0, null), // the root
    new Item(1, '.0'),
    new Item(2, '.1'),
    new Item(3, '.3'),
    new Item(4, '.0.0'),
    new Item(5, '.2.0'),
    new Item(6, '.2'),
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
    // self_id, parent_id
    new Item(0, null),
    new Item(1, 0),
    new Item(2, 0),
    new Item(3, 0),
    new Item(4, 1),
    new Item(5, 6),
    new Item(6, 0),
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



## Caveats

🚧 TODO

Caveats:
- no root in data
- null data rows

