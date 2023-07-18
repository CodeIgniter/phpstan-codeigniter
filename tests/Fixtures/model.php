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

use function PHPStan\Testing\assertType;

$class = (static fn (): string => mt_rand(0, 10) > 5 ? stdClass::class : 'Foo')();

assertType('null', model('foo'));
assertType('stdClass', model(stdClass::class));
assertType('Closure', model(Closure::class));
assertType('null', model('App'));
assertType('stdClass|null', model($class));
assertType('CodeIgniter\PHPStan\Tests\Fixtures\BarModel', model('BarModel'));
