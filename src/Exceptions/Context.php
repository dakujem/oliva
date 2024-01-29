<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Exceptions;

/**
 * Context meant for debugging issues.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class Context
{
    public array $bag = [];

    public function tag(string $key, mixed $value): self
    {
        $this->bag[$key] = $value;
        return $this;
    }

    public function push(string $key, mixed $value): self
    {
        if (!isset($this->bag[$key])) {
            $this->bag[$key] = [];
        }
        $this->bag[$key][] = $value;
        return $this;
    }
}
