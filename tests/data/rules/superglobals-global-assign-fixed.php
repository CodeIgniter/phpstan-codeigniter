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
    service('superglobals')->setServerArray([]);
    service('superglobals')->setGetArray([]);
    service('superglobals')->setPostArray([]);
    service('superglobals')->setCookieArray([]);
    service('superglobals')->setFilesArray([]);
    service('superglobals')->setRequestArray([]);
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

function test_superglobals_global_assign_multiple_not_fixable(): void
{
    $_GET = $_POST = $_REQUEST = [];
}
