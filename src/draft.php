<?php

declare(strict_types=1);

$data = [
    new Item(1, '000'),
    new Item(2, '001'),
    new Item(3, '003'),
    new Item(4, '000000'),
    new Item(5, '002000'),
    new Item(6, '002'),
];

/** @var MptBuilder $builder */
$root = $builder->build(
    data: $data,
    pathFunc: fn(Item $item) => $item->path,
    keyFunc: fn(Item $item) => $item->id,
);
new BuilderContext(
    $vector,
    $seq,
//    $parentNode, // NO!
);

$item = $root->data();


// rekalkulacia / presuny ?


// propagacia zmeny (hore/dole) (eventy?)




