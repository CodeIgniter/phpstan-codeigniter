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

use CodeIgniter\Model;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\UnionType;

final class ModelFindReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function __construct(
        private readonly ModelFetchedReturnTypeHelper $modelFetchedReturnTypeHelper,
    ) {}

    public function getClass(): string
    {
        return Model::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), ['find', 'findAll', 'first', 'findColumn'], true);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $classReflections = $scope->getType($methodCall->var)->getObjectClassReflections();

        if ($classReflections === []) {
            return null;
        }

        $types = [];

        foreach ($classReflections as $classReflection) {
            if (! $classReflection->is(Model::class)) {
                return null;
            }

            $type = $this->getTypeForModel($classReflection, $methodReflection->getName(), $methodCall, $scope);

            if ($type === null) {
                return null;
            }

            $types[] = $type;
        }

        return TypeCombinator::union(...$types);
    }

    private function getTypeForModel(ClassReflection $classReflection, string $methodName, MethodCall $methodCall, Scope $scope): ?Type
    {
        if ($methodName === 'find') {
            return $this->getTypeFromFind($classReflection, $methodCall, $scope);
        }

        if ($methodName === 'findAll') {
            return $this->getTypeFromFindAll($classReflection, $methodCall, $scope);
        }

        if ($methodName === 'findColumn') {
            return $this->getTypeFromFindColumn($classReflection, $methodCall, $scope);
        }

        return TypeCombinator::addNull($this->modelFetchedReturnTypeHelper->getFetchedReturnType($classReflection, $methodCall, $scope));
    }

    private function getTypeFromFindColumn(ClassReflection $classReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();

        if (! isset($args[0])) {
            return null;
        }

        $strings = $scope->getType($args[0]->value)->getConstantStrings();

        if (count($strings) !== 1) {
            return null;
        }

        $fieldType = $this->modelFetchedReturnTypeHelper->getColumnFieldType($classReflection, $strings[0]->getValue());

        if ($fieldType === null) {
            return null;
        }

        return TypeCombinator::addNull(TypeCombinator::intersect(
            new ArrayType(new IntegerType(), $fieldType),
            new AccessoryArrayListType(),
        ));
    }

    private function getTypeFromFind(ClassReflection $classReflection, MethodCall $methodCall, Scope $scope): Type
    {
        $args = $methodCall->getArgs();

        if (! isset($args[0])) {
            return $this->getTypeFromFindAll($classReflection, $methodCall, $scope);
        }

        return TypeTraverser::map(
            $scope->getType($args[0]->value),
            function (Type $idType, callable $traverse) use ($classReflection, $methodCall, $scope): Type {
                if ($idType instanceof UnionType || $idType instanceof IntersectionType) {
                    return $traverse($idType);
                }

                if ($idType->isArray()->yes() && ! $idType->isIterableAtLeastOnce()->yes()) {
                    return new ConstantArrayType([], []);
                }

                if ($idType->isInteger()->yes() || $idType->isString()->yes()) {
                    return TypeCombinator::addNull($this->modelFetchedReturnTypeHelper->getFetchedReturnType($classReflection, $methodCall, $scope));
                }

                return $this->getTypeFromFindAll($classReflection, $methodCall, $scope);
            },
        );
    }

    private function getTypeFromFindAll(ClassReflection $classReflection, MethodCall $methodCall, Scope $scope): Type
    {
        return TypeCombinator::intersect(
            new ArrayType(
                new IntegerType(),
                $this->modelFetchedReturnTypeHelper->getFetchedReturnType($classReflection, $methodCall, $scope),
            ),
            new AccessoryArrayListType(),
        );
    }
}
