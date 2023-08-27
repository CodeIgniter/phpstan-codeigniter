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

namespace SuperglobalAssign;

$_SERVER['HTTP_HOST'] = 'https://localhost';

$_GET['first_name'] = 'John Doe';

$_SERVER[0] = 'hello';

function bar(string $key, string $value): void
{
    $_SERVER[$key] = $value;

    $_GET[$key] = $value;
}

$_GET = 'sss';
$_GET = 12_500;

$_GET = ['first' => 'John', 'last' => 'Doe'];
