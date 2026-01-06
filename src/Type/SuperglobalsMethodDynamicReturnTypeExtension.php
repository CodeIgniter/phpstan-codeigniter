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

use CodeIgniter\PHPStan\Helpers\SuperglobalsHelper;
use CodeIgniter\Superglobals;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

final class SuperglobalsMethodDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function __construct(
        private readonly SuperglobalsHelper $superglobalsHelper,
        private readonly TypeStringResolver $typeStringResolver,
    ) {}

    public function getClass(): string
    {
        return Superglobals::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), [
            'server',
            'get',
            'post',
            'cookie',
            'request',
            'getGlobalArray',
        ], true);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $arguments = $methodCall->getArgs();

        if ($arguments === []) {
            return null;
        }

        $methodName = $methodReflection->getName();

        if ($methodName === 'getGlobalArray') {
            return $this->getTypeForGetGlobalArrayCall($methodReflection, $methodCall, $scope);
        }

        if ($methodName === 'server') {
            $resolvedType = $this->getTypeForServerCall($methodReflection, $methodCall, $scope);
        } else {
            $resolvedType = ParametersAcceptorSelector::selectFromArgs(
                $scope,
                $methodCall->getArgs(),
                $methodReflection->getVariants(),
            )->getReturnType();
        }

        if (! isset($arguments[1]) || ! $scope->getType($arguments[1]->value)->isNull()->no()) {
            return TypeCombinator::addNull($resolvedType);
        }

        return TypeCombinator::removeNull($resolvedType);
    }

    private function getTypeForGetGlobalArrayCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $arguments = $methodCall->getArgs();
        $nameTypes = $scope->getType($arguments[0]->value)->getConstantStrings();

        if ($nameTypes === []) {
            return null;
        }

        $types = [];

        foreach ($nameTypes as $nameType) {
            $name = array_search($nameType->getValue(), SuperglobalsHelper::HANDLED_SUPERGLOBALS, true);

            if (! is_string($name)) {
                continue;
            }

            $methodGlobalGetter    = $this->superglobalsHelper->getMethodGlobalGetter($name);
            $innerMethodReflection = $methodReflection->getDeclaringClass()->getMethod($methodGlobalGetter, $scope);

            $types[] = ParametersAcceptorSelector::selectFromArgs(
                $scope,
                $methodCall->getArgs(),
                $innerMethodReflection->getVariants(),
                $innerMethodReflection->getNamedArgumentsVariants(),
            )->getReturnType();
        }

        return TypeCombinator::union(...$types);
    }

    private function getTypeForServerCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        $arguments = $methodCall->getArgs();
        $nameTypes = $scope->getType($arguments[0]->value)->getConstantStrings();

        if ($nameTypes === []) {
            return ParametersAcceptorSelector::selectFromArgs(
                $scope,
                $methodCall->getArgs(),
                $methodReflection->getVariants(),
            )->getReturnType();
        }

        $types = [];

        foreach ($nameTypes as $nameType) {
            $name = $nameType->getValue();

            if (array_key_exists($name, SuperglobalsHelper::SERVER_ITEMS_WITH_NON_STRING_TYPE)) {
                $types[] = $this->typeStringResolver->resolve(SuperglobalsHelper::SERVER_ITEMS_WITH_NON_STRING_TYPE[$name]);
            } else {
                $types[] = new StringType();
            }
        }

        return TypeCombinator::union(...$types);
    }
}
