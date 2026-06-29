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

use CodeIgniter\HTTP\IncomingRequest;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;

/**
 * Sharpens the `IncomingRequest` input accessors built on `RequestTrait::fetchGlobal()`, declared as the
 * loose union `array|bool|float|int|object|string|null`. The `$index` argument shape decides the result.
 */
final class IncomingRequestMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    private const SUPPORTED_METHODS = [
        'getGet',
        'getPost',
        'getCookie',
        'getPostGet',
        'getGetPost',
        'getRawInputVar',
    ];

    public function __construct(
        private readonly TypeStringResolver $typeStringResolver,
    ) {}

    public function getClass(): string
    {
        return IncomingRequest::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), self::SUPPORTED_METHODS, true);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();

        // A filter argument re-types the value through filter_var, so defer to the declared type.
        if (isset($args[1]) && ! $scope->getType($args[1]->value)->isNull()->yes()) {
            return null;
        }

        // A null or absent index returns the whole global as a keyed array.
        if (! isset($args[0]) || $scope->getType($args[0]->value)->isNull()->yes()) {
            return $this->typeStringResolver->resolve('array<string, mixed>');
        }

        $indexType = $scope->getType($args[0]->value);

        // An array of keys returns an array keyed by those names.
        if ($indexType->isArray()->yes()) {
            return $this->typeStringResolver->resolve('array<string, mixed>');
        }

        // A string index returns the scalar value, a nested array, or null when the key is absent.
        if ($indexType->isString()->yes()) {
            // getRawInputVar's leaf passes through dot_array_search, which does not narrow usefully.
            if ($methodReflection->getName() === 'getRawInputVar') {
                return null;
            }

            return $this->typeStringResolver->resolve('array<mixed>|string|null');
        }

        return null;
    }
}
