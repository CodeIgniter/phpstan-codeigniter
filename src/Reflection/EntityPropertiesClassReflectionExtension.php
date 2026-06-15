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

namespace CodeIgniter\PHPStan\Reflection;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;
use CodeIgniter\PHPStan\Database\ModelTableMapProvider;
use CodeIgniter\PHPStan\Database\Schema\CastFieldTypeResolver;
use CodeIgniter\PHPStan\Database\Schema\Column;
use CodeIgniter\PHPStan\Database\Schema\ColumnTypeResolver;
use CodeIgniter\PHPStan\Database\SchemaProvider;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

/**
 * Types virtual properties on `CodeIgniter\Entity\Entity` subclasses, layering `$dates` and `$casts`
 * over the raw type of the backing database column, and reflecting custom `$castHandlers`.
 */
final class EntityPropertiesClassReflectionExtension implements PropertiesClassReflectionExtension
{
    public function __construct(
        private readonly CastFieldTypeResolver $castFieldTypeResolver,
        private readonly SchemaProvider $schemaProvider,
        private readonly ModelTableMapProvider $modelTableMapProvider,
        private readonly ColumnTypeResolver $columnTypeResolver,
    ) {}

    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        return $classReflection->is(Entity::class);
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        return new EntityPropertyReflection(
            $classReflection,
            $this->resolveType($classReflection, $propertyName) ?? new MixedType(),
        );
    }

    private function resolveType(ClassReflection $classReflection, string $propertyName): ?Type
    {
        $column = $this->mapColumn($classReflection, $propertyName);
        $casts  = $this->readStringMap($classReflection, 'casts');
        $schema = $this->lookupColumn($classReflection, $column);

        // `__get()` mutates date fields to Time before any cast. Only claim real columns so that
        // the framework's default `$dates` don't fabricate Time properties on unrelated entities.
        if ($schema !== null && in_array($column, $this->readStringList($classReflection, 'dates'), true)) {
            return new ObjectType(Time::class);
        }

        if (isset($casts[$column])) {
            return $this->castFieldTypeResolver->resolve($casts[$column], $this->readStringMap($classReflection, 'castHandlers'));
        }

        if ($schema !== null && ! $this->hasGetter($classReflection, $column)) {
            return $this->columnTypeResolver->resolve($schema);
        }

        return null;
    }

    private function lookupColumn(ClassReflection $classReflection, string $column): ?Column
    {
        $table = $this->modelTableMapProvider->getTableForEntity($classReflection->getName());

        if ($table === null) {
            return null;
        }

        return $this->schemaProvider->get()->getTable($table)?->getColumn($column);
    }

    private function hasGetter(ClassReflection $classReflection, string $column): bool
    {
        $method = 'get' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $column)));

        return $classReflection->hasNativeMethod($method) || $classReflection->hasNativeMethod('_' . $method);
    }

    private function mapColumn(ClassReflection $classReflection, string $propertyName): string
    {
        $mapped = $this->readStringMap($classReflection, 'datamap')[$propertyName] ?? '';

        return $mapped !== '' ? $mapped : $propertyName;
    }

    /**
     * @return array<string, string>
     */
    private function readStringMap(ClassReflection $classReflection, string $property): array
    {
        $value = $classReflection->getNativeReflection()->getDefaultProperties()[$property] ?? [];

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
     * @return list<string>
     */
    private function readStringList(ClassReflection $classReflection, string $property): array
    {
        $value = $classReflection->getNativeReflection()->getDefaultProperties()[$property] ?? [];

        if (! is_array($value)) {
            return [];
        }

        $list = [];

        foreach ($value as $item) {
            if (is_string($item)) {
                $list[] = $item;
            }
        }

        return $list;
    }
}
