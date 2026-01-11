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

namespace CodeIgniter\PHPStan\Helpers;

use CodeIgniter\Config\BaseService;
use CodeIgniter\Config\Services as FrameworkServices;
use Config\Services as AppServices;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;

final class ServicesReturnTypeHelper
{
    public const IMPOSSIBLE_SERVICE_METHOD_NAMES = [
        '__callstatic',
        'buildservicescache',
        'createrequest',
        'discoverservices',
        'getsharedinstance',
        'injectmock',
        'override',
        'reset',
        'resetservicescache',
        'resetsingle',
        'serviceexists',
        'set',
    ];

    /**
     * @var list<class-string>
     */
    private array $services;

    /**
     * @var array<class-string, ClassReflection>
     */
    private static array $servicesReflection = [];

    /**
     * @param list<class-string> $additionalServices
     */
    public function __construct(private readonly ReflectionProvider $reflectionProvider, array $additionalServices)
    {
        $this->services = [FrameworkServices::class, AppServices::class, ...$additionalServices];
    }

    public function checkReturnType(Type $argType, Scope $scope): ?Type
    {
        $this->buildServicesReflectionCache();

        foreach ($argType->getConstantStrings() as $constantStringType) {
            $serviceMethodName = $constantStringType->getValue();

            if (in_array(strtolower($serviceMethodName), self::IMPOSSIBLE_SERVICE_METHOD_NAMES, true)) {
                return new NullType();
            }

            $methodReflection = null;

            foreach (self::$servicesReflection as $serviceReflection) {
                if ($serviceReflection->hasMethod($serviceMethodName)) {
                    $methodReflection = $serviceReflection->getMethod($serviceMethodName, $scope);
                    // no break;
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

        return null;
    }

    /**
     * @return array<class-string, ClassReflection>
     */
    public function getServicesReflection(): array
    {
        $this->buildServicesReflectionCache();

        return self::$servicesReflection;
    }

    private function buildServicesReflectionCache(): void
    {
        if (self::$servicesReflection !== []) {
            return;
        }

        foreach ($this->services as $service) {
            if (! $this->reflectionProvider->hasClass($service)) {
                throw new ShouldNotHappenException(sprintf('Services factory class "%s" not found.', $service));
            }

            $serviceReflection = $this->reflectionProvider->getClass($service);

            if (! $serviceReflection->is(BaseService::class)) {
                throw new ShouldNotHappenException(sprintf('Services factory class "%s" must extend "%s".', $service, BaseService::class));
            }

            self::$servicesReflection[$service] = $serviceReflection;
        }
    }
}
