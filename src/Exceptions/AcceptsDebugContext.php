<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Exceptions;

/**
 * @internal
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
interface AcceptsDebugContext
{
//    public function context(callable $code): self;

    public function tag(string $key, mixed $value): self;

    public function push(string $key, mixed $value): self;
}
