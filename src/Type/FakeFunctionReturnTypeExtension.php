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
use CodeIgniter\PHPStan\Helpers\FactoriesReturnTypeHelper;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\Type;

/**
 * Types `fake()` as a single fabricated record of the given model's return type.
 */
final class FakeFunctionReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
    public function __construct(
        private readonly ModelFetchedReturnTypeHelper $modelFetchedReturnTypeHelper,
        private readonly FactoriesReturnTypeHelper $factoriesReturnTypeHelper,
    ) {}

    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return $functionReflection->getName() === 'fake';
    }

    public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): ?Type
    {
        $args = $functionCall->getArgs();

        if (! isset($args[0])) {
            return null;
        }

        $argType   = $scope->getType($args[0]->value);
        $modelType = $argType->isObject()->yes() ? $argType : $this->factoriesReturnTypeHelper->check($argType, 'model');

        $classReflections = $modelType->getObjectClassReflections();

        if (count($classReflections) !== 1) {
            return null;
        }

        $classReflection = current($classReflections);

        if (! $classReflection->is(Model::class)) {
            return null;
        }

        return $this->modelFetchedReturnTypeHelper->getFetchedReturnType($classReflection, null, $scope);
    }
}
