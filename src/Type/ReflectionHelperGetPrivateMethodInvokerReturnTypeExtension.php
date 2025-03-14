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
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

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

        if ($objectType->getObjectClassReflections() === [] && ! $objectType->isObject()->yes()) {
            return new NeverType(true);
        }

        $closures = [];

        foreach ($objectType->getObjectClassReflections() as $classReflection) {
            foreach ($methodType->getConstantStrings() as $methodStringType) {
                $methodName = $methodStringType->getValue();

                if (! $classReflection->hasMethod($methodName)) {
                    $closures[] = new NeverType(true);

                    continue;
                }

                $methodReflection   = $classReflection->getMethod($methodName, $scope);
                $parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
                    $scope,
                    $args,
                    $methodReflection->getVariants(),
                    $methodReflection->getNamedArgumentsVariants(),
                );

                $closures[] = new ClosureType(
                    $parametersAcceptor->getParameters(),
                    $parametersAcceptor->getReturnType(),
                    $parametersAcceptor->isVariadic(),
                    $parametersAcceptor->getTemplateTypeMap(),
                    $parametersAcceptor->getResolvedTemplateTypeMap(),
                );
            }
        }

        if ($closures === []) {
            return null;
        }

        return TypeCombinator::union(...$closures);
    }
}
