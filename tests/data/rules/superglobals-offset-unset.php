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

function test_superglobals_offset_unset(string $name): void
{
    unset(
        $_SERVER[$name],
        $_SERVER['key'],
        $_GET['key'],
        $_POST['key'],
        $_COOKIE['key'],
        $_REQUEST['key'],
        $_FILES['key'], // unset to $_FILES should be ignored
    );

    $key = (static fn (): string => mt_rand(0, 1) ? 'key1' : 'key2')();
    unset($_SERVER[$key]);
}
