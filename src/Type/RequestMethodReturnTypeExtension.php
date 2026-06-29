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

use CodeIgniter\HTTP\Request;
use CodeIgniter\PHPStan\Helpers\SuperglobalsHelper;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use stdClass;

/**
 * Sharpens the request accessors declared as bare `mixed` or a loose JSON union: `getServer()` is typed
 * from the server-key map, and `getJSON($assoc)` drops `stdClass` when the constant `$assoc` is `true`.
 */
final class RequestMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function __construct(
        private readonly TypeStringResolver $typeStringResolver,
    ) {}

    public function getClass(): string
    {
        return Request::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), ['getServer', 'getJSON'], true);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        return match ($methodReflection->getName()) {
            'getServer' => $this->resolveServer($methodCall, $scope),
            'getJSON'   => $this->resolveJson($methodReflection, $methodCall, $scope),
            default     => null,
        };
    }

    /**
     * Types `getServer($index, $filter)`: a null index returns the whole array, a constant key in the
     * server map returns its mapped type, any other constant key a `string`. A filter argument or a
     * non-constant key leaves the declared `mixed`.
     */
    private function resolveServer(MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();

        // A filter argument re-types the value through filter_var, so defer to the declared type.
        if (isset($args[1]) && ! $scope->getType($args[1]->value)->isNull()->yes()) {
            return null;
        }

        if (! isset($args[0]) || $scope->getType($args[0]->value)->isNull()->yes()) {
            return new ArrayType(new StringType(), new MixedType());
        }

        $strings = $scope->getType($args[0]->value)->getConstantStrings();

        if ($strings === []) {
            return null;
        }

        $types = [];

        foreach ($strings as $string) {
            $name    = $string->getValue();
            $types[] = array_key_exists($name, SuperglobalsHelper::SERVER_ITEMS_WITH_NON_STRING_TYPE)
                ? $this->typeStringResolver->resolve(SuperglobalsHelper::SERVER_ITEMS_WITH_NON_STRING_TYPE[$name])
                : new StringType();
        }

        return TypeCombinator::addNull(TypeCombinator::union(...$types));
    }

    /**
     * Types `getJSON($assoc)`: a constant `true` decodes objects as associative arrays, so `stdClass` is
     * dropped from the declared union. A `false`, absent, or non-constant `$assoc` leaves it in place.
     */
    private function resolveJson(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();

        if (! isset($args[0]) || ! $scope->getType($args[0]->value)->isTrue()->yes()) {
            return null;
        }

        $declared = ParametersAcceptorSelector::selectFromArgs(
            $scope,
            $args,
            $methodReflection->getVariants(),
        )->getReturnType();

        return TypeCombinator::remove($declared, new ObjectType(stdClass::class));
    }
}
