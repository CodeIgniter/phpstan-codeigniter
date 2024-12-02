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
use PHPStan\Type\Type;

final class ServicesFunctionReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
    /**
     * @readonly
     */
    private ServicesReturnTypeHelper $servicesReturnTypeHelper;

    public function __construct(ServicesReturnTypeHelper $servicesReturnTypeHelper)
    {
        $this->servicesReturnTypeHelper = $servicesReturnTypeHelper;
    }

    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return in_array($functionReflection->getName(), ['service', 'single_service'], true);
    }

    public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): ?Type
    {
        $arguments = $functionCall->getArgs();

        if ($arguments === []) {
            return null;
        }

        return $this->servicesReturnTypeHelper->check($scope->getType($arguments[0]->value), $scope);
    }
}
