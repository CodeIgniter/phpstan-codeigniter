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

/**
 * @param list<string> $keys
 */
function test_incoming_request_json_var(IncomingRequest $request, string $key, array $keys, bool $assoc, ?string $maybe): void
{
    assertType('array<mixed>|stdClass|null', $request->getJsonVar());
    assertType('array<mixed>|stdClass|null', $request->getJsonVar(null));
    assertType('array<mixed>|null', $request->getJsonVar(null, true));
    assertType('array<mixed>|stdClass|null', $request->getJsonVar(null, false));
    assertType('array<mixed>|stdClass|null', $request->getJsonVar(null, $assoc));

    assertType('array<mixed>|null', $request->getJsonVar(['a', 'b']));
    assertType('array<mixed>|null', $request->getJsonVar($keys, true));

    // A string index returns a leaf of the decoded body, which stays the declared union.
    assertType('array<mixed>|bool|float|int|stdClass|string|null', $request->getJsonVar('a.b'));
    assertType('array<mixed>|bool|float|int|string|null', $request->getJsonVar($key, true));

    // A filter or flags argument re-types the value, and an ambiguous index cannot be resolved.
    assertType('array<mixed>|bool|float|int|stdClass|string|null', $request->getJsonVar(null, false, FILTER_VALIDATE_INT));
    assertType('array<mixed>|bool|float|int|stdClass|string|null', $request->getJsonVar($maybe));
}
