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

use CodeIgniter\Config\BaseService;
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
     * @var list<string>
     */
    public const IMPOSSIBLE_SERVICE_METHOD_NAMES = [
        '__callstatic',
        'buildservicescache',
        'createrequest',
        'discoverservices',
        'getsharedinstance',
        'injectmock',
        'reset',
        'resetsingle',
        'serviceexists',
    ];

    /**
     * @var list<class-string>
     */
    private array $services;

    /**
     * @var list<ClassReflection>
     */
    private static array $servicesReflection = [];

    /**
     * @param list<class-string> $additionalServices
     */
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        array $additionalServices,
    ) {
        $this->services = [
            FrameworkServices::class,
            AppServices::class,
            ...$additionalServices,
        ];
    }

    public function check(Type $type, Scope $scope): Type
    {
        $this->buildServicesCache();

        return TypeTraverser::map($type, static function (Type $type, callable $traverse) use ($scope): Type {
            if ($type instanceof UnionType || $type instanceof IntersectionType) {
                return $traverse($type);
            }

            foreach ($type->getConstantStrings() as $constantStringType) {
                $constantString = $constantStringType->getValue();

                foreach (self::IMPOSSIBLE_SERVICE_METHOD_NAMES as $impossibleServiceMethodName) {
                    if (strtolower($constantString) === $impossibleServiceMethodName) {
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

                return ParametersAcceptorSelector::selectFromArgs(
                    $scope,
                    [],
                    $methodReflection->getVariants(),
                )->getReturnType();
            }

            return new NullType();
        });
    }

    /**
     * @return list<ClassReflection>
     */
    public function getServicesReflection(): array
    {
        $this->buildServicesCache();

        return self::$servicesReflection;
    }

    private function buildServicesCache(): void
    {
        if (self::$servicesReflection === []) {
            self::$servicesReflection = array_map(function (string $service): ClassReflection {
                if (! $this->reflectionProvider->hasClass($service)) {
                    throw new ShouldNotHappenException(sprintf('Services factory class "%s" not found.', $service));
                }

                $serviceReflection = $this->reflectionProvider->getClass($service);

                if ($serviceReflection->getParentClass()?->getName() !== BaseService::class) {
                    throw new ShouldNotHappenException(sprintf('Services factory class "%s" does not extend %s.', $service, BaseService::class));
                }

                return $serviceReflection;
            }, $this->services);
        }
    }
}
