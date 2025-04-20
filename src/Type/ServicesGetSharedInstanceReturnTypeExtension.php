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

use CodeIgniter\Config\BaseService;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Type;

class ServicesGetSharedInstanceReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    public function __construct(
        private readonly ServicesReturnTypeHelper $servicesReturnTypeHelper,
    ) {}

    public function getClass(): string
    {
        return BaseService::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getSharedInstance';
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): ?Type
    {
        $arguments = $methodCall->getArgs();

        if ($arguments === []) {
            return null;
        }

        return $this->servicesReturnTypeHelper->check($scope->getType($arguments[0]->value), $scope);
    }
}
