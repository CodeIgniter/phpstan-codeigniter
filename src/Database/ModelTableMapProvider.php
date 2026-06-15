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
 * Maps an entity class to the table and `$casts`/`$castHandlers` of the model that returns it,
 * discovered by reflecting every model found under the registered `Models/` namespaces.
 */
final class ModelTableMapProvider
{
    /**
     * @var array<class-string, array{table: string, casts: array<string, string>, handlers: array<string, string>}>|null
     */
    private ?array $map = null;

    public function getTableForEntity(string $entityClass): ?string
    {
        return $this->map()[$entityClass]['table'] ?? null;
    }

    /**
     * The `$casts` of the model returning this entity. CodeIgniter applies them before hydrating the
     * entity, so they describe the stored value of a property the entity itself does not cast.
     *
     * @return array<string, string>
     */
    public function getModelCastsForEntity(string $entityClass): array
    {
        return $this->map()[$entityClass]['casts'] ?? [];
    }

    /**
     * @return array<string, string>
     */
    public function getModelCastHandlersForEntity(string $entityClass): array
    {
        return $this->map()[$entityClass]['handlers'] ?? [];
    }

    /**
     * @return array<class-string, array{table: string, casts: array<string, string>, handlers: array<string, string>}>
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

            $map[$returnType] = [
                'table'    => $table,
                'casts'    => $this->stringMap($defaults['casts'] ?? null),
                'handlers' => $this->stringMap($defaults['castHandlers'] ?? null),
            ];
        }

        $this->map = $map;

        return $this->map;
    }

    /**
     * @return array<string, string>
     */
    private function stringMap(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $map = [];

        foreach ($value as $key => $cast) {
            if (is_string($key) && is_string($cast)) {
                $map[$key] = $cast;
            }
        }

        return $map;
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
