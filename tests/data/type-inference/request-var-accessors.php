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

use CodeIgniter\HTTP\IncomingRequest;

use function PHPStan\Testing\assertType;

/**
 * @param list<string> $keys
 */
function test_incoming_request(IncomingRequest $request, string $key, array $keys, ?string $maybe): void
{
    // A null or absent index returns the whole global as a keyed array.
    assertType('array<string, mixed>', $request->getGet());
    assertType('array<string, mixed>', $request->getPost(null));
    assertType('array<string, mixed>', $request->getCookie());
    assertType('array<string, mixed>', $request->getPostGet());
    assertType('array<string, mixed>', $request->getGetPost());
    assertType('array<string, mixed>', $request->getRawInputVar());

    // An array of keys returns an array keyed by those names.
    assertType('array<string, mixed>', $request->getGet(['a', 'b']));
    assertType('array<string, mixed>', $request->getPost($keys));
    assertType('array<string, mixed>', $request->getRawInputVar(['x']));

    // A string index returns the scalar value, a nested array, or null.
    assertType('array<mixed>|string|null', $request->getGet('q'));
    assertType('array<mixed>|string|null', $request->getPost($key));
    assertType('array<mixed>|string|null', $request->getCookie('session'));
    assertType('array<mixed>|string|null', $request->getPostGet('id'));
    assertType('array<mixed>|string|null', $request->getGetPost('id'));

    // getRawInputVar's string leaf is left as the declared union.
    assertType('mixed', $request->getRawInputVar('field'));

    // A filter argument re-types the value, so the declared union is left in place.
    assertType('mixed', $request->getGet('q', FILTER_VALIDATE_INT));

    // An ambiguous index (string or null) cannot be resolved.
    assertType('mixed', $request->getGet($maybe));
}
