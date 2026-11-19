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
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\DependencyInjection\AutowiredService;
use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\BooleanType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * Types `env()` as the found value, a `bool|string|null` for a constant key, joined with the default.
 */
#[AutowiredService]
final class EnvFunctionReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
    public function __construct(
        private readonly TypeStringResolver $typeStringResolver,
    ) {}

    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return $functionReflection->getName() === 'env';
    }

    public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): ?Type
    {
        $args = $functionCall->getArgs();

        if (! isset($args[0])) {
            return null;
        }

        $keyType = $scope->getType($args[0]->value);

        if (! $keyType->isString()->yes() || $keyType->getConstantStrings() === []) {
            return null;
        }

        $types = [
            isset($args[1]) ? $scope->getType($args[1]->value) : new NullType(),
            new BooleanType(),
            new StringType(),
            new NullType(),
        ];

        foreach ($keyType->getConstantStrings() as $key) {
            if (array_key_exists($key->getValue(), SuperglobalsHelper::SERVER_ITEMS_WITH_NON_STRING_TYPE)) {
                $types[] = $this->typeStringResolver->resolve(SuperglobalsHelper::SERVER_ITEMS_WITH_NON_STRING_TYPE[$key->getValue()]);
            }
        }

        return TypeCombinator::union(...$types);
    }
}
