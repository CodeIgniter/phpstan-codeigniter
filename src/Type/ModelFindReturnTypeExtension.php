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
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\UnionType;

final class ModelFindReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @readonly
     */
    private ModelFetchedReturnTypeHelper $modelFetchedReturnTypeHelper;

    public function __construct(ModelFetchedReturnTypeHelper $modelFetchedReturnTypeHelper)
    {
        $this->modelFetchedReturnTypeHelper = $modelFetchedReturnTypeHelper;
    }

    public function getClass(): string
    {
        return Model::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), ['find', 'findAll', 'first'], true);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        $methodName = $methodReflection->getName();

        if ($methodName === 'find') {
            return $this->getTypeFromFind($methodReflection, $methodCall, $scope);
        }

        if ($methodName === 'findAll') {
            return $this->getTypeFromFindAll($methodReflection, $methodCall, $scope);
        }

        $classReflection = $this->getClassReflection($methodCall, $scope);

        return TypeCombinator::addNull($this->modelFetchedReturnTypeHelper->getFetchedReturnType($classReflection, $methodCall, $scope));
    }

    private function getClassReflection(MethodCall $methodCall, Scope $scope): ClassReflection
    {
        $classTypes = $scope->getType($methodCall->var)->getObjectClassReflections();
        assert(count($classTypes) === 1);

        return current($classTypes);
    }

    private function getTypeFromFind(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        $args = $methodCall->getArgs();

        if (! isset($args[0])) {
            return $this->getTypeFromFindAll($methodReflection, $methodCall, $scope);
        }

        return TypeTraverser::map(
            $scope->getType($args[0]->value),
            function (Type $idType, callable $traverse) use ($methodReflection, $methodCall, $scope): Type {
                if ($idType instanceof UnionType || $idType instanceof IntersectionType) {
                    return $traverse($idType);
                }

                if ($idType->isNull()->yes()) {
                    return $this->getTypeFromFindAll($methodReflection, $methodCall, $scope);
                }

                if ($idType->isInteger()->yes() || $idType->isString()->yes()) {
                    $classReflection = $this->getClassReflection($methodCall, $scope);

                    return TypeCombinator::addNull($this->modelFetchedReturnTypeHelper->getFetchedReturnType($classReflection, $methodCall, $scope));
                }

                return $this->getTypeFromFindAll($methodReflection, $methodCall, $scope);
            }
        );
    }

    private function getTypeFromFindAll(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        $classReflection = $this->getClassReflection($methodCall, $scope);

        return AccessoryArrayListType::intersectWith(
            new ArrayType(
                new IntegerType(),
                $this->modelFetchedReturnTypeHelper->getFetchedReturnType($classReflection, $methodCall, $scope)
            )
        );
    }
}
