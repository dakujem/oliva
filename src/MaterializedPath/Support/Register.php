<?php

declare(strict_types=1);

namespace Dakujem\Oliva\MaterializedPath\Support;

/**
 * @internal
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class Register
{
    private array $nodes = [];

    public function contains(array $vector): bool
    {
        return isset($this->nodes[$this->makeIndex($vector)]);
    }

    public function push(array $vector, ShadowNode $node): void
    {
        $this->nodes[$this->makeIndex($vector)] = $node;
    }

    public function pull(array $vector): ?ShadowNode
    {
        return $this->nodes[$this->makeIndex($vector)] ?? null;
    }

    private function makeIndex(array $vector): string
    {
        return count($vector) . chr(22) . implode(chr(7), $vector);
    }
}
