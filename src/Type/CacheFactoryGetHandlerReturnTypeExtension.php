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

use CodeIgniter\Cache\CacheFactory;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Cache\Handlers\BaseHandler;
use Config\Cache;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;

final class CacheFactoryGetHandlerReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    public function __construct(
        private readonly bool $addBackupHandlerAsReturnType,
    ) {}

    public function getClass(): string
    {
        return CacheFactory::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getHandler';
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();

        if ($args === []) {
            return null;
        }

        $cache = $this->getCache($args[0]->value, $scope);

        if ($cache === null || $cache->validHandlers === []) {
            return new NeverType(true);
        }

        $handlerType = $this->getHandlerType(
            $args[1]->value ?? new ConstFetch(new Name('null')),
            $scope,
            $cache->validHandlers,
            $cache->handler,
        );
        $backupHandlerType = $this->getHandlerType(
            $args[2]->value ?? new ConstFetch(new Name('null')),
            $scope,
            $cache->validHandlers,
            $cache->backupHandler,
        );

        if (! $handlerType->isObject()->yes()) {
            return $backupHandlerType;
        }

        if (! $this->addBackupHandlerAsReturnType) {
            return $handlerType;
        }

        return TypeUtils::toBenevolentUnion(TypeCombinator::union($handlerType, $backupHandlerType));
    }

    private function getCache(Expr $expr, Scope $scope): ?Cache
    {
        foreach ($scope->getType($expr)->getObjectClassReflections() as $classReflection) {
            if ($classReflection->getName() === Cache::class) {
                $cache = $classReflection->getNativeReflection()->newInstance();

                if ($cache instanceof Cache) {
                    return $cache;
                }
            }
        }

        return null;
    }

    /**
     * @param array<string, class-string<CacheInterface>> $validHandlers
     */
    private function getHandlerType(Expr $expr, Scope $scope, array $validHandlers, string $default): Type
    {
        return TypeTraverser::map(
            $scope->getType($expr),
            static function (Type $type, callable $traverse) use ($validHandlers, $default): Type {
                if ($type instanceof UnionType || $type instanceof IntersectionType) {
                    return $traverse($type);
                }

                if ($type->isNull()->yes()) {
                    if (! isset($validHandlers[$default])) {
                        return new NeverType(true);
                    }

                    return new ObjectType($validHandlers[$default]);
                }

                $types = [];

                foreach ($type->getConstantStrings() as $constantString) {
                    $name = $constantString->getValue();

                    if (isset($validHandlers[$name])) {
                        $types[] = new ObjectType($validHandlers[$name]);
                    } else {
                        $types[] = new NeverType(true);
                    }
                }

                if ($types === []) {
                    return new ObjectType(BaseHandler::class);
                }

                return TypeCombinator::union(...$types);
            },
        );
    }
}
