<?php

declare(strict_types=1);

namespace Dakujem\Test;

use Dakujem\Oliva\Iterator\Traversal;
use Error;
use Tester\Assert;

require_once __DIR__ . '/setup.php';

(function () {
    // This test exists for code coverage purposes only... ¯\_(ツ)_/¯
    // We might remove the private constructor constraint as well.
    Assert::throws(function () {
        /** @noinspection */
        new Traversal();
    }, Error::class, 'Call to private Dakujem\Oliva\Iterator\Traversal::__construct() from global scope');
})();
