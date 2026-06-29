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

use CodeIgniter\Database\ResultInterface;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use stdClass;

/**
 * Sharpens the row-set accessors of `CodeIgniter\Database\ResultInterface`, which the framework declares
 * as a bare `array` or a loose object union. `getCustomResultObject()` is typed from its class-string argument.
 */
final class ResultMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {}

    public function getClass(): string
    {
        return ResultInterface::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), [
            'getResultArray',
            'getResultObject',
            'getRowArray',
            'getRowObject',
            'getCustomResultObject',
        ], true);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        return match ($methodReflection->getName()) {
            'getResultArray'        => $this->listOf(new ArrayType(new StringType(), new MixedType())),
            'getResultObject'       => $this->listOf(new ObjectType(stdClass::class)),
            'getRowArray'           => TypeCombinator::addNull(new ArrayType(new StringType(), new MixedType())),
            'getRowObject'          => TypeCombinator::addNull(new ObjectType(stdClass::class)),
            'getCustomResultObject' => $this->listOf($this->customObjectType($methodCall, $scope)),
            default                 => null,
        };
    }

    private function customObjectType(MethodCall $methodCall, Scope $scope): Type
    {
        $args = $methodCall->getArgs();

        if (! isset($args[0])) {
            return new ObjectWithoutClassType();
        }

        foreach ($scope->getType($args[0]->value)->getConstantStrings() as $className) {
            if ($this->reflectionProvider->hasClass($className->getValue())) {
                return new ObjectType($className->getValue());
            }
        }

        return new ObjectWithoutClassType();
    }

    private function listOf(Type $itemType): Type
    {
        return TypeCombinator::intersect(
            new ArrayType(new IntegerType(), $itemType),
            new AccessoryArrayListType(),
        );
    }
}
