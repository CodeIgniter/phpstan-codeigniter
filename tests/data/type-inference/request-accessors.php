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

function test_get_server(IncomingRequest $request, string $name): void
{
    // A null/absent index returns the whole server array.
    assertType('array<string, mixed>', $request->getServer());
    assertType('array<string, mixed>', $request->getServer(null));

    // Constant keys in the server map resolve to their mapped type, others to a string.
    assertType('array<mixed>|null', $request->getServer('argv'));
    assertType('int|null', $request->getServer('argc'));
    assertType('int|null', $request->getServer('REQUEST_TIME'));
    assertType('float|null', $request->getServer('REQUEST_TIME_FLOAT'));
    assertType('string|null', $request->getServer('HTTP_HOST'));

    // A filter argument re-types the value, so the declared type is left in place.
    assertType('mixed', $request->getServer('HTTP_HOST', FILTER_SANITIZE_SPECIAL_CHARS));

    // A non-constant index cannot be resolved.
    assertType('mixed', $request->getServer($name));
}

function test_get_json(IncomingRequest $request, bool $assoc): void
{
    // A constant true decodes objects as associative arrays, so stdClass is dropped.
    assertType('array|bool|float|int|null', $request->getJSON(true));

    // A false, absent, or non-constant $assoc leaves the declared union.
    assertType('array|bool|float|int|stdClass|null', $request->getJSON());
    assertType('array|bool|float|int|stdClass|null', $request->getJSON(false));
    assertType('array|bool|float|int|stdClass|null', $request->getJSON($assoc));
}
