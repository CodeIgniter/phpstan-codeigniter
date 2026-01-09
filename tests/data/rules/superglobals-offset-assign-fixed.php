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

namespace CodeIgniter\PHPStan\Tests\Rules;

function test_superglobals_offset_assign(string $name): void
{
    service('superglobals')->setServer($name, 'value');
    service('superglobals')->setGet('key', 'value');
    service('superglobals')->setPost('key', 'value');
    service('superglobals')->setCookie('key', 'value');
    service('superglobals')->setRequest('key', 'value');

    $_FILES['key'] = 'value'; // should not error

    $key = (static fn (): string => mt_rand(0, 1) ? 'key1' : 'key2')();

    service('superglobals')->setServer($key, 'value');
}
