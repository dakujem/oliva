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

//    public function contains(array $vector): bool
//    {
//        return isset($this->nodes[$this->makeIndex($vector)]);
//    }

    public function push(array $vector, ShadowNode $node): void
    {
        $this->nodes[$this->makeIndex($vector)] = $node;
    }

    public function pull(array $vector): ?ShadowNode
    {
        return $this->nodes[$this->makeIndex($vector)] ?? null;
    }

    /**
     * "Serializes" a path into a string usable as an index within a native PHP array.
     *
     * Note that if the tree paths relied upon `chr(7)` (ASCII #7) unprintable character
     * (which is very unlikely), then this indexing might cause the tree building to fail for certain cases.
     *
     * To demonstrate using an example, imagine that instead of `chr(7)` and `chr(22)` a dot `.` delimiter and semicolon `:`
     * were used in this method and the vectors contained only dots,
     * then index produced by this method `2:....` would be the same for vectors `['..', '.']` and `['.', '..']`,
     * the vectors would be indistinguishable and would cause the building algorithm to fail.
     *
     * This case is very unlikely, however, if not academic only.
     */
    private function makeIndex(array $vector): string
    {
        return count($vector) . chr(22) . implode(chr(7), $vector);
    }
}
