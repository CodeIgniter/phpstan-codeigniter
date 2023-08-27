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

namespace CodeIgniter\PHPStan\Rules\Classes;

use CodeIgniter\Cache\CacheFactory;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Test\Mock\MockCache;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<Node\Expr\New_>
 */
final class CacheHandlerInstantiationRule implements Rule
{
    public function getNodeType(): string
    {
        return Node\Expr\New_::class;
    }

    /**
     * @param Node\Expr\New_ $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->class instanceof Node\Name) {
            return [];
        }

        $objectType = new ObjectType((string) $node->class);
        $reflection = $objectType->getClassReflection();

        if ($reflection === null) {
            return [];
        }

        if (! (new ObjectType(CacheInterface::class))->isSuperTypeOf($objectType)->yes()) {
            return [];
        }

        if ($reflection->getName() === MockCache::class) {
            return [];
        }

        if ($scope->isInClass() && $scope->getClassReflection()->getName() === CacheFactory::class) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Calling new %s() directly is incomplete to get the cache instance.',
                $reflection->getNativeReflection()->getShortName(),
            ))
                ->tip('Use CacheFactory::getHandler() or the cache() function to get the cache instance instead.')
                ->identifier('codeigniter.cacheHandlerInstance')
                ->build(),
        ];
    }
}
