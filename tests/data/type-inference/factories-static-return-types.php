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

namespace CodeIgniter\PHPStan\Tests\Type;

use CodeIgniter\Config\Factories;
use CodeIgniter\PHPStan\Tests\Fixtures\BarModel;
use Config\App;

use function PHPStan\Testing\assertType;

// Factories::config(): the component is the method name, the alias is the first argument.
assertType('Config\App', Factories::config('App'));
assertType('Config\App', Factories::config(App::class));
assertType('null', Factories::config('bar'));

// Factories::models() resolves through the app and additional model namespaces, or a class string.
assertType('CodeIgniter\PHPStan\Tests\Fixtures\BarModel', Factories::models('BarModel'));
assertType('CodeIgniter\PHPStan\Tests\Fixtures\BarModel', Factories::models(BarModel::class));

// Factories::get($component, $alias): the component is the first argument.
assertType('Config\App', Factories::get('config', 'App'));
assertType('Config\App', Factories::get('config', App::class));
assertType('CodeIgniter\PHPStan\Tests\Fixtures\BarModel', Factories::get('models', BarModel::class));

// A class-string alias resolves regardless of the component.
assertType('CodeIgniter\PHPStan\Tests\Fixtures\BarModel', Factories::get('filters', BarModel::class));

// A non-class-string alias under an unmapped component is left as the framework's declared type.
assertType('object|null', Factories::get('filters', 'toolbar'));

// A non-constant alias is unresolvable, so the declared type is left in place rather than narrowed to null.
function staticFactoriesDynamic(string $name): void
{
    assertType('CodeIgniter\Config\BaseConfig|null', Factories::config($name));
    assertType('CodeIgniter\Model|null', Factories::models($name));
    assertType('object|null', Factories::get('config', $name));
}
