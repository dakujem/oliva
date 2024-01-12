# Oliva

Flexible tree structures, materialized path trees, tree iterators.

This package is a modern reimplementation of `dakujem/oliva-tree`.

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