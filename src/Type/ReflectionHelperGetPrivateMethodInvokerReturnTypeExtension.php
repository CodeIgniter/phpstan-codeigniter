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

use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ClosureType;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\UnionType;

final class ReflectionHelperGetPrivateMethodInvokerReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    /**
     * @param class-string $class
     */
    public function __construct(
        private readonly string $class,
    ) {}

    public function getClass(): string
    {
        return $this->class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getPrivateMethodInvoker';
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();

        if (count($args) !== 2) {
            return null;
        }

        $objectType = $scope->getType($args[0]->value)->getObjectTypeOrClassStringObjectType();
        $methodType = $scope->getType($args[1]->value);

        if (! $objectType->isObject()->yes()) {
            return new NeverType(true);
        }

        return TypeTraverser::map($objectType, static function (Type $type, callable $traverse) use ($methodType, $scope, $args, $methodReflection): Type {
            if ($type instanceof UnionType || $type instanceof IntersectionType) {
                return $traverse($type);
            }

            $closures = [];

            foreach ($type->getObjectClassReflections() as $classReflection) {
                foreach ($methodType->getConstantStrings() as $methodStringType) {
                    $methodName = $methodStringType->getValue();

                    if (! $classReflection->hasMethod($methodName)) {
                        $closures[] = new NeverType(true);

                        continue;
                    }

                    $invokedMethodReflection = $classReflection->getMethod($methodName, $scope);

                    $parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
                        $scope,
                        [],
                        $invokedMethodReflection->getVariants(),
                        $invokedMethodReflection->getNamedArgumentsVariants(),
                    );

                    $returnType = strtolower($methodName) === '__construct' ? $type : $parametersAcceptor->getReturnType();

                    $closures[] = new ClosureType(
                        $parametersAcceptor->getParameters(),
                        $returnType,
                        $parametersAcceptor->isVariadic(),
                        $parametersAcceptor->getTemplateTypeMap(),
                        $parametersAcceptor->getResolvedTemplateTypeMap(),
                    );
                }
            }

            if ($closures === []) {
                return ParametersAcceptorSelector::selectFromArgs(
                    $scope,
                    $args,
                    $methodReflection->getVariants(),
                )->getReturnType();
            }

            return TypeCombinator::union(...$closures);
        });
    }
}
