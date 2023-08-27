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

$foo = $_SERVER['foo'] ?? null;

$a = (static fn (): string => mt_rand(0, 1) ? 'a' : 'b')();
$b = $_GET[$a] ?? null;

function bar(string $c): ?string
{
    return $_SERVER[$c] ?? null;
}
