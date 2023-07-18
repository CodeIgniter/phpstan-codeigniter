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

use Config\App;

use function PHPStan\Testing\assertType;

$class = (static fn (): string => mt_rand(0, 10) > 5 ? stdClass::class : 'Foo')();

assertType('null', config('bar'));
assertType('null', config('Foo\Bar'));
assertType('Config\App', config('App'));
assertType('Config\App', config(App::class));
assertType('stdClass|null', config($class));
