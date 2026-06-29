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

use CodeIgniter\HTTP\URI;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

/**
 * Resolves the `string|URI` url helpers `current_url()` and `previous_url()` from the constant
 * `$returnObject` flag: `true` returns a `URI`, `false` or absent a `string`.
 */
final class UrlHelperFunctionReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
    private const SUPPORTED_FUNCTIONS = ['current_url', 'previous_url'];

    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return in_array($functionReflection->getName(), self::SUPPORTED_FUNCTIONS, true);
    }

    public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): ?Type
    {
        $args = $functionCall->getArgs();

        $returnObject = isset($args[0])
            ? $scope->getType($args[0]->value)
            : new ConstantBooleanType(false);

        if ($returnObject->isTrue()->yes()) {
            return new ObjectType(URI::class);
        }

        if ($returnObject->isFalse()->yes()) {
            return new StringType();
        }

        return null;
    }
}
