<?php

declare(strict_types=1);

namespace Dakujem\Oliva\Exceptions;

/**
 * @see AcceptsDebugContext
 * @internal
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
trait AcceptDebugContext
{
    public ?Context $context = null;

//    public function context(callable $code): self
//    {
//        $this->context ??= new Context();
//        $code($this->context, $this);
//        return $this;
//    }

    public function tag(string $key, mixed $value): self
    {
        $this->context ??= new Context();
        $this->context->tag($key, $value);
        return $this;
    }

    public function push(string $key, mixed $value): self
    {
        $this->context ??= new Context();
        $this->context->push($key, $value);
        return $this;
    }
}
