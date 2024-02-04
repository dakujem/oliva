<?php

declare(strict_types=1);

namespace Dakujem\Test;

use Dakujem\Oliva\Exceptions\ConfigurationIssue;
use Dakujem\Oliva\Exceptions\Context;
use Dakujem\Oliva\Exceptions\InvalidTreePath;
use Dakujem\Oliva\MaterializedPath\Path;
use Dakujem\Oliva\Node;
use Tester\Assert;

require_once __DIR__ . '/setup.php';

// Test some edge cases of Path::delimited
(function () {
    Assert::throws(function () {
        Path::delimited('', fn() => null);
    }, ConfigurationIssue::class, 'The delimiter must be a single character.');
    Assert::throws(function () {
        Path::delimited('42', fn() => null);
    }, ConfigurationIssue::class, 'The delimiter must be a single character.');

    Assert::throws(function () {
        $extractor = Path::delimited('.', fn() => 42);
        $extractor(null);
    }, InvalidTreePath::class, 'Invalid tree path returned by the accessor. A string is required.');

    $extractor = Path::delimited('.', fn($data) => $data);
    Assert::same([], $extractor(null));
    Assert::same([], $extractor(''));
    Assert::same(['a string'], $extractor('a string'));
    Assert::same(['a', 'string'], $extractor('a.string'));
    Assert::same(['  a', '  string  '], $extractor('  a.  string  ')); // no trim implemented

    Assert::throws(function () use ($extractor) {
        $extractor(42);
    }, InvalidTreePath::class, 'Invalid tree path returned by the accessor. A string is required.');
    Assert::throws(function () use ($extractor) {
        $extractor([]);
    }, InvalidTreePath::class, 'Invalid tree path returned by the accessor. A string is required.');
    Assert::throws(function () use ($extractor) {
        $extractor(4.2);
    }, InvalidTreePath::class, 'Invalid tree path returned by the accessor. A string is required.');
    Assert::throws(function () use ($extractor) {
        $extractor(new Node(null));
    }, InvalidTreePath::class, 'Invalid tree path returned by the accessor. A string is required.');

    $extractor = Path::delimited('.', fn() => 42);
    $thrown = false;
    $node = new Node('empty');
    try {
        $extractor('foo', 'bar', $node);
    } catch (InvalidTreePath $e) {
        /** @var Context $context */
        $context = $e->context;
        Assert::type(Context::class, $context);
        Assert::same([
            'path' => 42,
            'data' => 'foo',
            'index' => 'bar',
            'node' => $node,
        ], $context->bag);
        $thrown = true;
    }
    Assert::true($thrown, 'The catch block has not been executed.');
})();

// Test some edge cases of Path::fixed
(function () {
    Assert::throws(function () {
        Path::fixed(0, fn() => null);
    }, ConfigurationIssue::class, 'The level width must be a positive integer.');
    Assert::throws(function () {
        Path::fixed(-1, fn() => null);
    }, ConfigurationIssue::class, 'The level width must be a positive integer.');

    $extractor = Path::fixed(1, fn($data) => $data);
    Assert::same(['a', 'b', 'c'], $extractor('abc'));
    Assert::same([], $extractor(''));

    $extractor = Path::fixed(2, fn($data) => $data);
    Assert::same(['ab', 'cd'], $extractor('abcd'));
    Assert::same(['  ', 'ab', 'cd', '  '], $extractor('  abcd  ')); // no trim implemented
    Assert::same([], $extractor(''));

    Assert::throws(function () use ($extractor) {
        $extractor(42);
    }, InvalidTreePath::class, 'Invalid tree path returned by the accessor. A string is required.');
    Assert::throws(function () use ($extractor) {
        $extractor([]);
    }, InvalidTreePath::class, 'Invalid tree path returned by the accessor. A string is required.');
    Assert::throws(function () use ($extractor) {
        $extractor(4.2);
    }, InvalidTreePath::class, 'Invalid tree path returned by the accessor. A string is required.');
    Assert::throws(function () use ($extractor) {
        $extractor(new Node(null));
    }, InvalidTreePath::class, 'Invalid tree path returned by the accessor. A string is required.');
})();

