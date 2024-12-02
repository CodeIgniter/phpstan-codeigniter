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

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\NonAcceptingNeverType;
use PHPStan\Type\Type;

final class FakeFunctionReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
    /**
     * @readonly
     */
    private ModelFetchedReturnTypeHelper $modelFetchedReturnTypeHelper;

    /**
     * @readonly
     */
    private FactoriesReturnTypeHelper $factoriesReturnTypeHelper;

    public function __construct(ModelFetchedReturnTypeHelper $modelFetchedReturnTypeHelper, FactoriesReturnTypeHelper $factoriesReturnTypeHelper)
    {
        $this->modelFetchedReturnTypeHelper = $modelFetchedReturnTypeHelper;
        $this->factoriesReturnTypeHelper    = $factoriesReturnTypeHelper;
    }

    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return $functionReflection->getName() === 'fake';
    }

    public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): ?Type
    {
        $arguments = $functionCall->getArgs();

        if ($arguments === []) {
            return null;
        }

        $modelType = $this->factoriesReturnTypeHelper->check($scope->getType($arguments[0]->value), 'model');

        if (! $modelType->isObject()->yes()) {
            return new NonAcceptingNeverType();
        }

        $classReflections = $modelType->getObjectClassReflections();

        if (count($classReflections) !== 1) {
            return $modelType; // ObjectWithoutClassType
        }

        $classReflection = current($classReflections);

        return $this->modelFetchedReturnTypeHelper->getFetchedReturnType($classReflection, null, $scope);
    }
}
