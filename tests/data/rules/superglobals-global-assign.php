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

namespace CodeIgniter\PHPStan\Tests;

function test_superglobals_global_assign(): void
{
    $_SERVER  = [];
    $_GET     = [];
    $_POST    = [];
    $_COOKIE  = [];
    $_FILES   = [];
    $_REQUEST = [];
}

function test_superglobals_global_assign_non_array(): void
{
    $_SERVER  = 12;
    $_GET     = 'not an array';
    $_POST    = null;
    $_COOKIE  = 3.14;
    $_FILES   = true;
    $_REQUEST = (object) [];
}
