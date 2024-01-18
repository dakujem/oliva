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

Oliva trees consist of node instances implementing `TreeNodeContract`.
```php
use Dakujem\Oliva\TreeNodeContract;
```


## Builders

Oliva provides _tree builders_, classes that construct structured trees from unstructured data.
The data is usually in form of iterable collections, typically a result of SQL queries, API calls, and such.

```php
$tree = (new TreeBuilder)->build(
    input: $anyDataCollection,
    node: fn(mixed $data) => new Node($data),    // This is a node factory.
);
```

The builders are flexible and allow to create any node instances via the _node factory_ parameter.  
The simplest factory may look like this
```php
fn(mixed $data) => new \Dakujem\Oliva\Node($data);
```

... but it's really up to the integrator to provide a node factory according to his needs:
```php
fn(mixed $anyItem) => new MyNode(MyTransformer::transform($anyItem)),
```

Anything _callable_ may be a node factory, as long as it returns a class instance implementing `\Dakujem\Oliva\MovableNodeContract`.
```php
use Dakujem\Oliva\MovableNodeContract;
```

>
> 💡
>
> The full signature of the node factory callable is `fn(mixed $data, mixed $dataIndex): MovableNodeContract`.  
> That means the keys (indexes) of the input collection may also be used for node construction.
>

Each of the builders also requires _extractors_, described below,
callables that provide the builder with information about the tree structure.


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

The following tree will be used in the examples below:
```
[0]  (the root)
|
+--[1]
|  |
|  +--[4]
|  |
|  +--[7]
|
+--[6]
|  |
|  +--[5]
|
+--[2]
|
+--[3]
```

Simple example of a fixed-length MPT:
```php
use Any\Item;
use Dakujem\Oliva\MaterializedPath\TreeBuilder;
use Dakujem\Oliva\Node;

// The input data may be a result of an SQL query or any other iterable collection.
$collection = [
    new Item(id: 0, path: ''), // the root
    new Item(id: 1, path: '000'),
    new Item(id: 2, path: '001'),
    new Item(id: 3, path: '003'),
    new Item(id: 4, path: '000000'),
    new Item(id: 5, path: '002000'),
    new Item(id: 6, path: '002'),
    new Item(id: 7, path: '000001'),
];

$builder = new TreeBuilder();
$root = $builder->build(
    input: $collection,
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

$collection = [
    new Item(id: 0, path: null), // the root
    new Item(id: 1, path: '.0'),
    new Item(id: 2, path: '.1'),
    new Item(id: 3, path: '.3'),
    new Item(id: 4, path: '.0.0'),
    new Item(id: 5, path: '.2.0'),
    new Item(id: 6, path: '.2'),
    new Item(id: 7, path: '.0.1'),
];

$builder = new TreeBuilder();
$root = $builder->build(
    input: $collection,
    node: fn(Item $item) => new Node($item),      // How to create a node.
    vector: TreeBuilder::delimited(               // How to extract path vector.
        delimiter: '.',
        accessor: fn(Item $item) => $item->path,
    ),
);
```

>
> 💡
>
> Since child nodes are added to parents in the order they appear in the source data,
> sorting the source collection by path _prior_ to building the tree may be a good idea.
>
> If sorting of siblings is needed _after_ a tree has been built,
> one of the provided iterators can be used to traverse and modify the tree.
>


## Recursive trees

By far the most common and trivial way of persisting trees. Each node has a reference to its parent.
The tree is reconstructed recursively.

```php
use Any\Item;
use Dakujem\Oliva\Recursive\TreeBuilder;
use Dakujem\Oliva\Node;

$collection = [
    new Item(id: 0, parent: null),
    new Item(id: 1, parent: 0),
    new Item(id: 2, parent: 0),
    new Item(id: 3, parent: 0),
    new Item(id: 4, parent: 1),
    new Item(id: 5, parent: 6),
    new Item(id: 6, parent: 0),
    new Item(id: 7, parent: 1),
];

$builder = new TreeBuilder();
$root = $builder->build(
    input: $collection,
    node: fn(Item $item) => new Node($item),      // How to create a node.
    self: fn(Item $item) => $item->id,            // How to get ID of self.
    parent: fn(Item $item) => $item->parent,      // How to get parent ID.
    root: null,                                   // The root node's parent value.
);
```

Above, `self` and `parent` parameters expect extractors with signature
`fn(mixed $data, mixed $dataIndex, TreeNodeContract $node): string|int`,  
while `root` expects a _value_ of the root node's parent.
The node with this parent value will be returned.

>
> The most natural case for `root` is `null` because nodes without a parent are considered to be root nodes.
> The value can be changed to `0`, `""` or whatever else is suitable for the particular dataset.  
> This is useful when building a subtree.
>


## Wrapping JSON or arrays

In case the data is already structured as tree data, a simple wrapper may be used to build the tree structure.

```php
use Any\Item;
use Dakujem\Oliva\Simple\TreeBuilder;
use Dakujem\Oliva\Node;

// $json = (new External\ApiConnector())->call('getJsonData');
// $data = json_decode($json);

$rawData = [
    'children' => [
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
    ],
];

$builder = new TreeBuilder();
$root = $builder->build(
    input: $rawData,
    node: function(array $item) {                                // How to create a node.
        unset($item['children']);
        return new Node($item);
    },                   
    children: fn(array $item):array => $item['children'] ?? [],  // How to extract children.
);
```

Above, `children` expects an extractor with signature `fn(mixed $data, TreeNodeContract $node): ?iterable`.

>
> 💡
> 
> Remember, it is up to the integrator to construct the tree nodes.
> Any transformation can be done with the data, as long as a node implementing the `MovableNodeContract` is returned.
>


## Manual tree building


Using a manual builder:
```php
use Dakujem\Oliva\Node;
use Dakujem\Oliva\Simple\NodeBuilder;

$proxy = new NodeBuilder(
    node: fn(mixed $item) => new Node($item),
);

$root = 
    $proxy->node('root', [
        $proxy->node('first child', [
            $proxy->node('leaf of the first child node'),
            $proxy->node('another leaf of the first child node'),
        ]),
        $proxy->node('second child'), 
    ]);
```

Using the low-level node interface (`MovableNodeContract`) for building a tree:
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

Using high-level manipulator (`Tree`) for doing the same:
```php
use Dakujem\Oliva\Node;
use Dakujem\Oliva\Tree;

$root = new Node('root');
Tree::link(node: $child1 = new Node('first child'), parent: $root);
Tree::link(node: $child2 = new Node('second child'), parent: $root);
Tree::link(node: new Node('leaf of the first child node'), parent: $child1);
Tree::link(node: new Node('another leaf of the first child node'), parent: $child1);
```

... or a more concise way:
```php
use Dakujem\Oliva\Node;
use Dakujem\Oliva\Tree;

Tree::linkChildren(node: $root = new Node('root'), children: [
    Tree::linkChildren(node: new Node('first child'), children: [
        new Node('leaf of the first child node'),
        new Node('another leaf of the first child node'),
    ]),
    new Node('second child'),
]);
```


## Iterators

Oliva provides iterators for tree traversal.
The iterators will iterate over all the tree's nodes, including the root, in a specific order.

**Depth-first search**
- `Iterator\PreOrderTraversal` pre-order traversal
- `Iterator\PostOrderTraversal` post-order traversal

**Breadth-first search**
- `Iterator\LevelOrderTraversal` level-order traversal

If unsure what the above means, read more about [Tree traversal](https://en.wikipedia.org/wiki/Tree_traversal).

If the order of traversal, is not important, a `Node` instance can be iterated over:

```php
use Dakujem\Oliva\Node;

$root = new Node( ... );

foreach ($root as $node) {
    // do something useful with the nodes
}
```

Finally, `Iterator\Filter` may be used for filtering either the input data or tree nodes.
```php
use Dakujem\Oliva\Iterator\Filter;
use Dakujem\Oliva\Node;

// Filter the input before building a tree.
$filteredCollection = new Filter($sourceCollection, fn(Item $item): bool => $item->id > 5);
$root = (new TreeBuilder( ... ))->build(
    $filteredCollection,
);

// Iterate ober leafs only.
$filter = new Filter($root, fn(Node $node): bool => $node->isLeaf());
foreach($filter as $node){
    // ...
}
```


## Caveats

🚧 TODO

Caveats:
- no root in data
- null data rows

