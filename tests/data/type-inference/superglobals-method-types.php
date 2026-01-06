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

use CodeIgniter\Superglobals;

use function PHPStan\Testing\assertType;

function test_server_method(string $name): void
{
    $superglobals = new Superglobals();
    assertType('array<mixed>|float|int|string|null', $superglobals->server($name));
    assertType('array<mixed>|float|int|string', $superglobals->server($name, ''));

    assertType('array<mixed>|null', $superglobals->server('argv'));
    assertType('array<mixed>', $superglobals->server('argv', []));

    assertType('int|null', $superglobals->server('argc'));
    assertType('int', $superglobals->server('argc', 0));

    assertType('int|null', $superglobals->server('REQUEST_TIME'));
    assertType('int', $superglobals->server('REQUEST_TIME', 0));

    assertType('string|null', $superglobals->server('HTTP_HOST'));
    assertType('string', $superglobals->server('HTTP_HOST', ''));

    assertType('float|null', $superglobals->server('REQUEST_TIME_FLOAT'));
    assertType('float', $superglobals->server('REQUEST_TIME_FLOAT', 0.0));
}

function test_other_getter_methods(string $name): void
{
    $superglobals = new Superglobals();
    assertType('array<mixed>|string|null', $superglobals->get($name));
    assertType('array<mixed>|string', $superglobals->get($name, ''));

    assertType('array<mixed>|string|null', $superglobals->post($name));
    assertType('array<mixed>|string', $superglobals->post($name, ''));

    assertType('array<mixed>|string|null', $superglobals->cookie($name));
    assertType('array<mixed>|string', $superglobals->cookie($name, ''));

    assertType('array<mixed>|string|null', $superglobals->request($name));
    assertType('array<mixed>|string', $superglobals->request($name, ''));
}

function test_global_array_method(string $name): void
{
    $superglobals = new Superglobals();
    assertType('array<string, array<mixed>|float|int|string>', $superglobals->getGlobalArray($name));
    assertType('array<string, array<mixed>|float|int|string>', $superglobals->getGlobalArray('server'));
    assertType('array<string, array<mixed>|string>', $superglobals->getGlobalArray('get'));
    assertType('array<string, array<mixed>|string>', $superglobals->getGlobalArray('post'));
    assertType('array<string, array<mixed>|string>', $superglobals->getGlobalArray('cookie'));
    assertType('array<string, array<mixed>>', $superglobals->getGlobalArray('files'));
    assertType('array<string, array<mixed>|string>', $superglobals->getGlobalArray('request'));
}
