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

namespace CodeIgniter\PHPStan\Tests\Type;

use function PHPStan\Testing\assertType;

/**
 * @param array{a: int, b: array{c: string, d: array{e: bool}}, 0: float, n: null, empty: array{}} $shape
 * @param array{a?: int, b: array{c: string}|null}                                                 $optional
 * @param array<string, mixed>                                                                     $generic
 * @param 'a'|'b.c'                                                                                $literals
 */
function dotArraySearch(array $shape, array $optional, array $generic, string $index, string $literals): void
{
    assertType('int', dot_array_search('a', $shape));
    assertType('string', dot_array_search('b.c', $shape));
    assertType('bool', dot_array_search('b.d.e', $shape));
    assertType('array{e: bool}', dot_array_search('b.d', $shape));
    assertType('float', dot_array_search('0', $shape));
    assertType('null', dot_array_search('missing', $shape));
    assertType('null', dot_array_search('b.missing', $shape));
    assertType('null', dot_array_search('a.b', $shape));
    assertType('null', dot_array_search('n', $shape));
    assertType('null', dot_array_search('empty.x', $shape));
    assertType('string', dot_array_search('b.c.', $shape));
    assertType('array{c: string, d: array{e: bool}}', dot_array_search('b.*', $shape));

    assertType('int|null', dot_array_search('a', $optional));
    assertType('string|null', dot_array_search('b.c', $optional));

    assertType('int|string', dot_array_search($literals, $shape));
    assertType('mixed', dot_array_search('b.*.e', $shape));
    assertType('mixed', dot_array_search('a', $generic));
    assertType('mixed', dot_array_search($index, $shape));
}
