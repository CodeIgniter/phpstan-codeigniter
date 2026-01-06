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

function test_superglobals_offset_access(string $name): void
{
    $_SERVER[$name];
    $_GET['key'];
    $_POST['key'];
    $_COOKIE['key'];
    $_FILES['key']; // should not error
    $_REQUEST['key'];

    $key = (static fn (): string => mt_rand(0, 1) ? 'key1' : 'key2')();
    $_SERVER[$key];
}
