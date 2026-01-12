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

use CodeIgniter\PHPStan\Helpers\ReflectionHelperPrivateInvokerHelper;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Type;

final class ReflectionHelperMethodInvokerStaticReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    /**
     * @param class-string $className
     */
    public function __construct(
        private readonly ReflectionHelperPrivateInvokerHelper $reflectionHelperPrivateInvokerHelper,
        private readonly string $className,
    ) {}

    public function getClass(): string
    {
        return $this->className;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getPrivateMethodInvoker';
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): ?Type
    {
        return $this->reflectionHelperPrivateInvokerHelper->checkMethodReturnType($methodReflection, $methodCall, $scope);
    }
}
