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

use CodeIgniter\Config\Services as FrameworkServices;
use Config\Services as AppServices;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\UnionType;

final class ServicesReturnTypeHelper
{
    /**
     * @var array<int, string>
     */
    private const IMPOSSIBLE_SERVICE_METHOD_NAMES = [
        '__callStatic',
        'buildServicesCache',
        'createRequest',
        'discoverServices',
        'getSharedInstance',
        'injectMock',
        'reset',
        'resetSingle',
        'serviceExists',
    ];

    /**
     * @var array<int, class-string>
     */
    private array $services;

    /**
     * @var array<int, ClassReflection>
     */
    private static array $servicesReflection = [];

    /**
     * @param array<int, class-string> $additionalServices
     */
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        array $additionalServices
    ) {
        $this->services = [
            FrameworkServices::class,
            AppServices::class,
            ...$additionalServices,
        ];
    }

    public function check(Type $type, Scope $scope): Type
    {
        if (self::$servicesReflection === []) {
            self::$servicesReflection = array_map(function (string $service): ClassReflection {
                if (! $this->reflectionProvider->hasClass($service)) {
                    throw new ShouldNotHappenException(sprintf('Service class "%s" is not found.', $service));
                }

                return $this->reflectionProvider->getClass($service);
            }, $this->services);
        }

        return TypeTraverser::map($type, static function (Type $type, callable $traverse) use ($scope): Type {
            if ($type instanceof UnionType || $type instanceof IntersectionType) {
                return $traverse($type);
            }

            $constantStrings = $type->getConstantStrings();

            if ($constantStrings === []) {
                return new NullType();
            }

            $constantString = current($constantStrings)->getValue();

            foreach (self::IMPOSSIBLE_SERVICE_METHOD_NAMES as $impossibleServiceMethodName) {
                if (strtolower($constantString) === strtolower($impossibleServiceMethodName)) {
                    return new NullType();
                }
            }

            $methodReflection = null;

            foreach (self::$servicesReflection as $servicesReflection) {
                if ($servicesReflection->hasMethod($constantString)) {
                    $methodReflection = $servicesReflection->getMethod($constantString, $scope);
                }
            }

            if ($methodReflection === null) {
                return new NullType();
            }

            if (! $methodReflection->isStatic() || ! $methodReflection->isPublic()) {
                return new NullType();
            }

            return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
        });
    }
}
