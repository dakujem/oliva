<?php

declare(strict_types=1);

namespace Dakujem\Oliva\MaterializedPath;

/**
 * A calculus for fixed-length materialized path trees.
 *
 * It can be used to transform paths to vectors and vice versa.
 *
 * Note 💡:
 *   This calculator is OPINIONATED and assumes a trivial MPT
 *   where sibling enumerators (sequence numbers) are used to describe a node's position within the tree.
 *
 *
 * The most important setting is the number of characters to store a single position within a path,
 * a.k.a. the level width.
 *
 * Recommended values: 3 and above.
 * - 3 characters means a maximum of    46_656 siblings per node (36^3)
 * - 4 characters means a maximum of 1_679_616 siblings per node (36^4)
 * - 5 characters ~ over 60 million nodes (36^5)
 * - 6 characters ~ over  2 billion nodes (36^6)
 *
 * The number of levels is not limited in theory, but in practice it will be limited by storage restrictions, like column width.
 * Higher number means that each node can have more children, but takes up more space.
 *
 * If IDs are going to be used in the paths, then the length must be used accordingly.
 * In such a case the delimited variant may be a better choice.
 * But remember that the delimited variant either requires a storage that supports natural sort (MySQL/MariaDB do not)
 * and a more convoluted sorting conditions using splits, or requires the use of a second column to store sequence/priority.
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class FixedPathCalculator
{
    /**
     * Number of characters to store a single position within a path,
     * with one position for each level of depth.
     *
     * If this value is changed, the tree paths must be recalculated.
     */
    private int $charsPerPosition;

    private bool $base36;

    public function __construct(
        int $charsPerPosition,
        bool $base36 = true,
    ) {
        $this->charsPerPosition = $charsPerPosition;
        $this->base36 = $base36;
    }

    /**
     * Recalculate a path string into a numeric vector.
     * A vector is a sequence of indexes from the tree's root to a node.
     * ```
     * ""        -->       []
     * "000"     -->      [0]
     * "00c001"  -->  [12, 1]
     * ```
     */
    public function pathToVector(string $path): array
    {
        if ('' === $path) {
            return [];
        }
        return array_map(
            fn(string $v) => self::posToNum($v),
            str_split($path, $this->charsPerPosition),
        );
    }

    /**
     * Recalculate a numeric vector into a path string.
     * ```
     *      []  -->  ""
     *     [0]  -->  "000"
     * [12, 1]  -->  "00c001"
     * ```
     */
    public function vectorToPath(array $vector): string
    {
        return implode(
            '',
            array_map(fn(int $v) => self::numToPos($v), $vector),
        );
    }

    /**
     * Convert a string of a single position into a vector element.
     * ```
     * 000 -->  0
     * 001 -->  1
     * 00c --> 12
     * ```
     */
    public function posToNum(string $position): int
    {
        if (!$this->base36) {
            return (int)$position;
        }
        return (int)base_convert($position, 36, 10);
    }

    /**
     * Convert a numeric vector element to a position string.
     * ```
     *  0 --> 000
     *  1 --> 001
     * 12 --> 00c
     * ```
     */
    public function numToPos(int $element): string
    {
        if (!$this->base36) {
            return (string)$element;
        }
        $x = base_convert((string)$element, 10, 36);
        return str_pad($x, $this->charsPerPosition, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate the depth of a node with the given path.
     */
    public function pathToDepth(string $path): int
    {
        $depth = strlen($path) / $this->charsPerPosition;
        if (!is_int($depth)) {
            throw new InvalidTreePath('The given tree path "' . $path . '" is invalid. The path`s length is expected to be a multiple of ' . $this->charsPerPosition . '.');
        }
        return $depth;
    }

    /**
     * Calculate the path length for a given depth.
     *
     * Note: This method has no equivalent for the delimited MPT variant where the path length is not fixed.
     */
    public function depthToPathLength(int $depth): int
    {
        return $depth * $this->charsPerPosition;
    }
}
