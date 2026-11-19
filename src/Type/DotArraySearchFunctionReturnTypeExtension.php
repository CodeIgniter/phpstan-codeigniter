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
use PHPStan\DependencyInjection\AutowiredService;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;

/**
 * Types `dot_array_search()` by walking a constant array shape along a constant, non-wildcard index.
 */
#[AutowiredService]
final class DotArraySearchFunctionReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return $functionReflection->getName() === 'dot_array_search';
    }

    public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): ?Type
    {
        $args = $functionCall->getArgs();

        if (count($args) < 2) {
            return null;
        }

        $indexType = $scope->getType($args[0]->value);

        if (! $indexType->isString()->yes() || $indexType->getConstantStrings() === []) {
            return null;
        }

        $arrayType = $scope->getType($args[1]->value);
        $types     = [];

        foreach ($indexType->getConstantStrings() as $index) {
            $segments = $this->segments($index->getValue());

            if ($segments === [] || in_array('*', $segments, true)) {
                return null;
            }

            $type = $this->search($arrayType, $segments);

            if ($type === null) {
                return null;
            }

            $types[] = $type;
        }

        return TypeCombinator::union(...$types);
    }

    /**
     * @return list<string>
     */
    private function segments(string $index): array
    {
        $segments = preg_split('/(?<!\\\\)\./', rtrim($index, '* '), 0, PREG_SPLIT_NO_EMPTY);

        if ($segments === false) {
            return [];
        }

        return array_map(static fn (string $segment): string => str_replace('\.', '.', $segment), $segments);
    }

    /**
     * @param list<string> $segments
     */
    private function search(Type $arrayType, array $segments): ?Type
    {
        if ($segments === []) {
            return new NullType();
        }

        $types = [];

        foreach (TypeUtils::flattenTypes($arrayType) as $member) {
            $constantArrays = $member->getConstantArrays();

            if (count($constantArrays) !== 1) {
                return null;
            }

            $array   = $constantArrays[0];
            $segment = new ConstantStringType($segments[0]);
            $exists  = $array->hasOffsetValueType($segment);

            if ($exists->no()) {
                $types[] = new NullType();

                continue;
            }

            $value = $array->getOffsetValueType($segment);

            if ($exists->maybe()) {
                $types[] = new NullType();
            }

            if (count($segments) === 1) {
                $types[] = $value;

                continue;
            }

            $nested = $this->searchNested($value, array_slice($segments, 1));

            if ($nested === null) {
                return null;
            }

            $types[] = $nested;
        }

        return TypeCombinator::union(...$types);
    }

    /**
     * @param list<string> $segments
     */
    private function searchNested(Type $value, array $segments): ?Type
    {
        $types = [];

        foreach (TypeUtils::flattenTypes($value) as $member) {
            if ($member->isArray()->no()) {
                $types[] = new NullType();

                continue;
            }

            $constantArrays = $member->getConstantArrays();

            if (count($constantArrays) !== 1) {
                return null;
            }

            if ($constantArrays[0]->getKeyTypes() === []) {
                $types[] = new NullType();

                continue;
            }

            $type = $this->search($member, $segments);

            if ($type === null) {
                return null;
            }

            $types[] = $type;
        }

        return TypeCombinator::union(...$types);
    }
}
