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
    single_service('superglobals')->server($name);
    single_service('superglobals')->get('key');
    single_service('superglobals')->post('key');
    single_service('superglobals')->cookie('key');
    $_FILES['key']; // should not error
    single_service('superglobals')->request('key');

    $key = (static fn (): string => mt_rand(0, 1) ? 'key1' : 'key2')();
    single_service('superglobals')->server($key);

    single_service('superglobals')->get('a') !== null;
    isset(single_service('superglobals')->get('a')['b']);
    ! (single_service('superglobals')->get('h') !== null);
    isset($_GET['i'], $_POST['j']);
    $_GET['c'] .= 'x';
    $_GET['d']++;

    foreach ([1] as $_POST['e']) {
    }
    [$_POST['f']] = [1];
    $ref          = &$_COOKIE['g'];
}
