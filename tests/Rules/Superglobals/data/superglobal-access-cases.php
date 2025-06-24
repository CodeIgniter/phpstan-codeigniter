<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) 2023 CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace SuperglobalAccess;

/**
 * @return list<mixed>
 */
function access(): array
{
    $foo = $_SERVER['foo'] ?? null;

    $a = (static fn (): string => mt_rand(0, 1) ? 'a' : 'b')();
    $b = $_GET[$a] ?? null;

    return [$foo, $b];
}

function bar(string $c): ?string
{
    return $_SERVER[$c] ?? null;
}

/**
 * @return array{list<string>, int, int, float}
 */
function allowed_offset_access(): array
{
    return [
        $_SERVER['argv'] ?? [],
        $_SERVER['argc'] ?? 0,
        $_SERVER['REQUEST_TIME'] ?? 0,
        $_SERVER['REQUEST_TIME_FLOAT'] ?? 0.0,
    ];
}
