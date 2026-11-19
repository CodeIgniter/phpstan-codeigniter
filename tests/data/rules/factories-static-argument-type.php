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

use CodeIgniter\Config\Factories;
use Config\App;
use stdClass;

// Factories::config()
Factories::config('App'); // valid
Factories::config('bar'); // invalid class string
Factories::config(App::class); // valid
Factories::config(stdClass::class); // valid class string but not a BaseConfig

// Factories::models()
Factories::models('foo'); // invalid class string
Factories::models(stdClass::class); // valid class string but not a Model

// Factories::get()
Factories::get('config', 'App'); // valid
Factories::get('config', 'bar'); // invalid class string
Factories::get('models', 'foo'); // invalid class string
Factories::get('models', stdClass::class); // valid class string but not a Model
Factories::get('filters', 'foo'); // other components are not checked
Factories::cells('foo'); // other methods are not checked

function test_factories_static_dynamic(string $alias, string $component): void
{
    Factories::config($alias);
    Factories::get($component, 'foo');
}
