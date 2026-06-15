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

namespace CodeIgniter\PHPStan\Database\Schema;

use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * Resolves a `$casts` entry to the type its handler reads back, using the framework built-ins and
 * reflecting custom `$castHandlers` for cast names the framework does not define.
 */
final class CastFieldTypeResolver
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly CastTypeResolver $castTypeResolver,
    ) {}

    /**
     * @param array<string, string> $castHandlers
     */
    public function resolve(string $cast, array $castHandlers, bool $columnIsNonNullable = false): Type
    {
        $type = $this->castTypeResolver->resolve($cast);

        if ($type === null) {
            return $this->resolveCustomHandler($cast, $castHandlers);
        }

        // A null column value survives null-preserving casts (datetime/json) as null, so the property
        // is nullable unless the backing column is known to be non-null.
        if (! $columnIsNonNullable && $this->castTypeResolver->preservesNull($cast)) {
            return TypeCombinator::addNull($type);
        }

        return $type;
    }

    /**
     * @param array<string, string> $castHandlers
     */
    private function resolveCustomHandler(string $cast, array $castHandlers): Type
    {
        $nullable = str_starts_with($cast, '?');

        if ($nullable) {
            $cast = substr($cast, 1);
        }

        $handler = $castHandlers[$this->castName($cast)] ?? null;

        if ($handler === null || ! $this->reflectionProvider->hasClass($handler)) {
            return new MixedType();
        }

        $handlerReflection = $this->reflectionProvider->getClass($handler);

        if (! $handlerReflection->hasNativeMethod('get')) {
            return new MixedType();
        }

        $type = ParametersAcceptorSelector::combineAcceptors($handlerReflection->getNativeMethod('get')->getVariants())->getReturnType();

        return $nullable ? TypeCombinator::addNull($type) : $type;
    }

    private function castName(string $cast): string
    {
        if (preg_match('/\A(.+)\[.+\]\z/', $cast, $matches) === 1) {
            return $matches[1];
        }

        return $cast;
    }
}
