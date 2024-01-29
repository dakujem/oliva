<?php

declare(strict_types=1);

namespace Dakujem\Test;

use Dakujem\Oliva\Iterator\Traversal;
use Tester\Assert;

require_once __DIR__ . '/setup.php';

(function () {
    $root = Preset::wikiTree();
    Assert::same('FBADCEGIH', TreeTesterTool::chain(Traversal::preOrder($root)));
    Assert::same('ACEDBHIGF', TreeTesterTool::chain(Traversal::postOrder($root)));
    Assert::same('FBGADICEH', TreeTesterTool::chain(Traversal::levelOrder($root)));
})();

