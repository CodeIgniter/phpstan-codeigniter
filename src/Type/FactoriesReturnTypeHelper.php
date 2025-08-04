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

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\UnionType;

final class FactoriesReturnTypeHelper
{
    /**
     * @var array<string, string>
     */
    private array $namespaceMap = [
        'config' => 'Config\\',
        'model'  => 'App\\Models\\',
    ];

    /**
     * @var array<string, list<string>>
     */
    private array $additionalNamespacesMap = [
        'config' => [],
        'model'  => [],
    ];

    /**
     * @param list<string> $additionalConfigNamespaces
     * @param list<string> $additionalModelNamespaces
     */
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        array $additionalConfigNamespaces,
        array $additionalModelNamespaces,
    ) {
        $cb = static fn (string $item): string => rtrim($item, '\\') . '\\';

        $this->additionalNamespacesMap = [
            'config' => [...$this->additionalNamespacesMap['config'], ...array_map($cb, $additionalConfigNamespaces)],
            'model'  => [...$this->additionalNamespacesMap['model'], ...array_map($cb, $additionalModelNamespaces)],
        ];
    }

    public function check(Type $type, string $function): Type
    {
        return TypeTraverser::map($type, function (Type $type, callable $traverse) use ($function): Type {
            if ($type instanceof UnionType || $type instanceof IntersectionType) {
                return $traverse($type);
            }

            if ($type->isClassString()->yes()) {
                return $type->getClassStringObjectType();
            }

            foreach ($type->getConstantStrings() as $constantStringType) {
                if ($constantStringType->isClassString()->yes()) {
                    return $constantStringType->getClassStringObjectType();
                }

                $constantString = $constantStringType->getValue();

                $appName = $this->namespaceMap[$function] . $constantString;

                if ($this->reflectionProvider->hasClass($appName)) {
                    return new ObjectType($appName);
                }

                foreach ($this->additionalNamespacesMap[$function] as $additionalNamespace) {
                    $moduleClassName = $additionalNamespace . $constantString;

                    if ($this->reflectionProvider->hasClass($moduleClassName)) {
                        return new ObjectType($moduleClassName);
                    }
                }
            }

            return new NullType();
        });
    }
}
