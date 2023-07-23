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

namespace CodeIgniter\PHPStan\Tests\Rules\Classes;

use CodeIgniter\PHPStan\Rules\Classes\CacheHandlerInstantiationRule;
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesTrait;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 *
 * @extends RuleTestCase<CacheHandlerInstantiationRule>
 */
#[Group('Integration')]
final class CacheHandlerInstantiationRuleTest extends RuleTestCase
{
    use AdditionalConfigFilesTrait;

    protected function getRule(): Rule
    {
        return new CacheHandlerInstantiationRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../Fixtures/Rules/Classes/cache-handler.php'], [
            [
                'Calling new FileHandler() directly is incomplete to get the cache instance.',
                18,
                'Use CacheFactory::getHandler() or the cache() function to get the cache instance instead.',
            ],
            [
                'Calling new RedisHandler() directly is incomplete to get the cache instance.',
                19,
                'Use CacheFactory::getHandler() or the cache() function to get the cache instance instead.',
            ],
        ]);
    }
}
