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

use CodeIgniter\HTTP\CLIRequest;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;

/**
 * Sharpens the `CLIRequest` input accessors, which never read a real superglobal: a null or array index
 * yields an empty array and a string index yields null, mirroring `CLIRequest::returnNullOrEmptyArray()`.
 */
final class CLIRequestMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    private const SUPPORTED_METHODS = [
        'getGet',
        'getPost',
        'getCookie',
        'getPostGet',
        'getGetPost',
    ];

    public function __construct(
        private readonly TypeStringResolver $typeStringResolver,
    ) {}

    public function getClass(): string
    {
        return CLIRequest::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), self::SUPPORTED_METHODS, true);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();

        if (! isset($args[0])) {
            return $this->typeStringResolver->resolve('array{}');
        }

        $indexType = $scope->getType($args[0]->value);

        if ($indexType->isNull()->yes() || $indexType->isArray()->yes()) {
            return $this->typeStringResolver->resolve('array{}');
        }

        if ($indexType->isString()->yes()) {
            return new NullType();
        }

        return null;
    }
}
