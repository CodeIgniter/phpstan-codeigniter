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

use Closure;
use Config\App;
use stdClass;

// config()
config('App'); // valid
config('bar'); // invalid class string
config('Foo\Bar'); // invalid class string
config(App::class); // valid
config(stdClass::class); // valid class string but not a BaseConfig

// model()
model('foo'); // invalid class string
model(stdClass::class); // valid class string but not a Model
model(Closure::class); // valid class string but not a Model
