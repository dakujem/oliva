# Oliva

Flexible tree structures, materialized path trees, tree traversal iterators.

>
> 💿 `composer require dakujem/oliva`
>


This package is a modern reimplementation of `dakujem/oliva-tree`.


A tree is a form of a graph, specifically a directed acyclic graph (DAG).  
Each node in a tree can have at most one parent and any number of children.  
The node without a parent is called a root.  
A node without children is called a leaf.  



```php
use Any\Item;
use Dakujem\Oliva\MaterializedPath\TreeBuilder;
use Dakujem\Oliva\Node;

$data = [
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
    node: fn(Item $item) => new Node($item),
    vector: TreeBuilder::fixed(
        3,
        fn(Item $item) => $item->path,
    ),
);
```

Since child nodes are be added to parents in the order they appear in the source data, sorting the source collection by path prior to building the tree may be a good idea. 


## Builders

Caveats:
- no root in data
- null data rows

