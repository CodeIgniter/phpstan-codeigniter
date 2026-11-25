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

namespace CodeIgniter\PHPStan\Helpers;

use CodeIgniter\Config\BaseConfig;
use PHPStan\DependencyInjection\AutowiredService;

/**
 * Resolver of a config class that survives PHPStan's autoload trap marking its file as included.
 */
#[AutowiredService(name: 'configResolver')]
final class ConfigResolver
{
    /**
     * @template T of BaseConfig
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function resolve(string $class): BaseConfig
    {
        $config = config($class);

        if ($config instanceof $class) {
            return $config;
        }

        $file = service('locator')->locateFile($class);

        if ($file !== false) {
            require $file;
        }

        $config = config($class);

        return $config instanceof $class ? $config : new $class();
    }
}
