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

use CodeIgniter\Config\Factories;
use CodeIgniter\PHPStan\Helpers\FactoriesReturnTypeHelper;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Type;

/**
 * Resolves the static factory entry points `Factories::config()`, `Factories::models()`, and
 * `Factories::get()` to the concrete class named by the call, mirroring the `config()`/`model()` functions.
 */
final class FactoriesStaticMethodReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    public function __construct(
        private readonly FactoriesReturnTypeHelper $factoriesReturnTypeHelper,
    ) {}

    public function getClass(): string
    {
        return Factories::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), ['get', 'config', 'models'], true);
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();

        if ($methodReflection->getName() === 'get') {
            if (! isset($args[0], $args[1])) {
                return null;
            }

            $components = $scope->getType($args[0]->value)->getConstantStrings();
            $aliasType  = $scope->getType($args[1]->value);

            if (count($components) !== 1) {
                return $this->resolveClassString($aliasType);
            }

            return $this->resolve($components[0]->getValue(), $aliasType);
        }

        if (! isset($args[0])) {
            return null;
        }

        return $this->resolve($methodReflection->getName(), $scope->getType($args[0]->value));
    }

    private function resolve(string $component, Type $aliasType): ?Type
    {
        $key = match (strtolower($component)) {
            'config'          => 'config',
            'models', 'model' => 'model',
            default           => null,
        };

        if ($key === null) {
            return $this->resolveClassString($aliasType);
        }

        $resolved = $this->factoriesReturnTypeHelper->check($aliasType, $key);

        // A non-constant alias has no constant class name to resolve, so a null result is unproven:
        // leave the declared type rather than narrowing the call to null.
        if ($resolved->isNull()->yes() && $aliasType->getConstantStrings() === []) {
            return null;
        }

        return $resolved;
    }

    /**
     * The class-string of the alias resolves regardless of the component. A non-class-string alias under
     * an unmapped component cannot be resolved, so the declared type is left in place.
     */
    private function resolveClassString(Type $aliasType): ?Type
    {
        $resolved = $this->factoriesReturnTypeHelper->check($aliasType, '');

        return $resolved->isNull()->yes() ? null : $resolved;
    }
}
