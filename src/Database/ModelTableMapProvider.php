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

namespace CodeIgniter\PHPStan\Database;

use CodeIgniter\Model;
use ReflectionClass;

/**
 * Maps an entity class to the table of the model that returns it, discovered by reflecting every
 * model found under the registered `Models/` namespaces.
 */
final class ModelTableMapProvider
{
    /**
     * @var array<class-string, string>|null
     */
    private ?array $map = null;

    public function getTableForEntity(string $entityClass): ?string
    {
        return $this->map()[$entityClass] ?? null;
    }

    /**
     * @return array<class-string, string>
     */
    private function map(): array
    {
        if ($this->map !== null) {
            return $this->map;
        }

        $map = [];

        foreach ($this->discoverModels() as $model) {
            $defaults   = (new ReflectionClass($model))->getDefaultProperties();
            $returnType = $defaults['returnType'] ?? null;
            $table      = $defaults['table'] ?? null;

            if (! is_string($returnType) || ! is_string($table) || $table === '' || ! class_exists($returnType)) {
                continue;
            }

            $map[$returnType] = $table;
        }

        $this->map = $map;

        return $this->map;
    }

    /**
     * @return list<class-string<Model>>
     */
    private function discoverModels(): array
    {
        $locator = service('locator');
        $models  = [];

        foreach ($locator->listFiles('Models/') as $file) {
            if ($file === '') {
                continue;
            }

            $class = $locator->getClassname($file);

            if ($class === '' || ! class_exists($class) || ! is_subclass_of($class, Model::class)) {
                continue;
            }

            $models[] = $class;
        }

        return $models;
    }
}
