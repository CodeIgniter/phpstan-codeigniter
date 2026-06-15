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

namespace CodeIgniter\PHPStan\Type;

use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\Type\CallbackUnresolvedPropertyPrototypeReflection;
use PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

/**
 * An entity object type whose listed properties are retyped by the `$casts` of the model that produced it.
 * CodeIgniter applies the producing model's casts before hydrating the entity, so an entity fetched through
 * a model's `asObject()`/`asArray()` picks up that model's casts instead of the bare column types.
 */
final class ModelCastEntityType extends ObjectType
{
    /**
     * @param array<string, Type>   $castedProperties Field name => cast-resolved type
     * @param array<string, string> $datamap          Property name => field name
     */
    public function __construct(
        string $className,
        private readonly array $castedProperties,
        private readonly array $datamap = [],
    ) {
        parent::__construct($className);
    }

    public function getUnresolvedInstancePropertyPrototype(string $propertyName, ClassMemberAccessAnswerer $scope): UnresolvedPropertyPrototypeReflection
    {
        return $this->withCastOverride($propertyName, parent::getUnresolvedInstancePropertyPrototype($propertyName, $scope));
    }

    public function getUnresolvedPropertyPrototype(string $propertyName, ClassMemberAccessAnswerer $scope): UnresolvedPropertyPrototypeReflection
    {
        return $this->withCastOverride($propertyName, parent::getUnresolvedPropertyPrototype($propertyName, $scope));
    }

    private function withCastOverride(string $propertyName, UnresolvedPropertyPrototypeReflection $prototype): UnresolvedPropertyPrototypeReflection
    {
        $field = $this->datamap[$propertyName] ?? $propertyName;

        if (! isset($this->castedProperties[$field])) {
            return $prototype;
        }

        $naked        = $prototype->getNakedProperty();
        $overrideType = $this->castedProperties[$field];

        return new CallbackUnresolvedPropertyPrototypeReflection(
            $naked,
            $naked->getDeclaringClass(),
            false,
            static fn (Type $type): Type => $overrideType,
        );
    }
}
