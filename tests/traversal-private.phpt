<?php

declare(strict_types=1);

namespace Dakujem\Test;

use Dakujem\Oliva\Iterator\Traversal;
use LogicException;
use Tester\Assert;

require_once __DIR__ . '/setup.php';

(function () {
    // This test exists for code coverage purposes only... ¯\_(ツ)_/¯
    // We might remove the instantiation constraint as well.
    Assert::throws(function () {
        /** @noinspection */
        new Traversal();
    }, LogicException::class, 'The `Dakujem\Oliva\Iterator\Traversal` class is static.');
})();
