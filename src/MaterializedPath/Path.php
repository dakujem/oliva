<?php

declare(strict_types=1);

namespace Dakujem\Oliva\MaterializedPath;

use Dakujem\Oliva\Exceptions\ConfigurationIssue;
use Dakujem\Oliva\Exceptions\InvalidTreePath;
use Dakujem\Oliva\TreeNodeContract;

/**
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class Path
{
    /**
     * Creates an extractor callable for tree builders that extracts vectors from materialized paths with delimiters.
     * These paths contain hierarchy information with variable-width levels delimited by a selected character.
     * The vectors are extracted by exploding the path string.
     *
     * @param string $delimiter The delimiter character.
     * @param callable $accessor An accessor callable that returns the raw path, signature `fn(mixed $data, mixed $inputIndex, TreeNodeContract $node): string`.
     * @return callable Vector extractor for the MPT builder.
     */
    public static function delimited(string $delimiter, callable $accessor): callable
    {
        if (strlen($delimiter) !== 1) {
            throw new ConfigurationIssue('The delimiter must be a single character.');
        }
        return function (mixed $data, mixed $inputIndex = null, ?TreeNodeContract $node = null) use (
            $delimiter,
            $accessor,
        ): array {
            $path = $accessor($data);
            if (null === $path) {
                return [];
            }
            if (!is_string($path)) {
                throw (new InvalidTreePath('Invalid tree path returned by the accessor. A string is required.'))
                    ->tag('path', $path)
                    ->tag('data', $data)
                    ->tag('index', $inputIndex)
                    ->tag('node', $node);
            }
            $path = trim($path, $delimiter);
            if ('' === $path) {
                return [];
            }
            return explode($delimiter, $path);
        };
    }

    /**
     * Creates an extractor callable for tree builders that extracts vectors from materialized paths without delimiters.
     * These paths contain hierarchy information with constant character count per level of depth.
     * The vectors are extracted by splitting the path string by the given number.
     *
     * @param int $levelWidth The number of characters per level.
     * @param callable $accessor An accessor callable that returns the raw path, signature `fn(mixed $data, mixed $inputIndex, TreeNodeContract $node): string`.
     * @return callable Vector extractor for the MPT builder.
     */
    public static function fixed(int $levelWidth, callable $accessor): callable
    {
        if ($levelWidth < 1) {
            throw new ConfigurationIssue('The level width must be a positive integer.');
        }
        return function (mixed $data, mixed $inputIndex = null, ?TreeNodeContract $node = null) use (
            $levelWidth,
            $accessor,
        ): array {
            $path = $accessor($data);
            if (null === $path || $path === '') {
                return [];
            }
            if (!is_string($path)) {
                throw (new InvalidTreePath('Invalid tree path returned by the accessor. A string is required.'))
                    ->tag('path', $path)
                    ->tag('data', $data)
                    ->tag('index', $inputIndex)
                    ->tag('node', $node);
            }
            return str_split($path, $levelWidth);
        };
    }
}
