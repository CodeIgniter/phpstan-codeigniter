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
use CodeIgniter\PHPStan\Database\Schema\CastTypeResolver;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * Types virtual properties on `CodeIgniter\Entity\Entity` subclasses from their `$casts` entries,
 * reflecting custom `$castHandlers` for casts the framework does not define.
 */
final class EntityPropertiesClassReflectionExtension implements PropertiesClassReflectionExtension
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly CastTypeResolver $castTypeResolver,
    ) {}

    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        if (! $classReflection->is(Entity::class)) {
            return false;
        }

        $casts  = $this->readStringMap($classReflection, 'casts');
        $column = $this->mapColumn($classReflection, $propertyName);

        return isset($casts[$column]);
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        $casts  = $this->readStringMap($classReflection, 'casts');
        $column = $this->mapColumn($classReflection, $propertyName);
        $cast   = $casts[$column] ?? '';

        return new EntityPropertyReflection($classReflection, $this->resolveCastType($classReflection, $cast));
    }

    private function resolveCastType(ClassReflection $classReflection, string $cast): Type
    {
        return $this->castTypeResolver->resolve($cast) ?? $this->resolveCustomHandlerType($classReflection, $cast);
    }

    private function resolveCustomHandlerType(ClassReflection $classReflection, string $cast): Type
    {
        $nullable = str_starts_with($cast, '?');

        if ($nullable) {
            $cast = substr($cast, 1);
        }

        $handler = $this->readStringMap($classReflection, 'castHandlers')[$this->castName($cast)] ?? null;

        if ($handler === null || ! $this->reflectionProvider->hasClass($handler)) {
            return new MixedType();
        }

        $handlerReflection = $this->reflectionProvider->getClass($handler);

        if (! $handlerReflection->hasNativeMethod('get')) {
            return new MixedType();
        }

        $type = ParametersAcceptorSelector::combineAcceptors($handlerReflection->getNativeMethod('get')->getVariants())->getReturnType();

        return $nullable ? TypeCombinator::addNull($type) : $type;
    }

    private function castName(string $cast): string
    {
        if (preg_match('/\A(.+)\[.+\]\z/', $cast, $matches) === 1) {
            return $matches[1];
        }

        return $cast;
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
}
