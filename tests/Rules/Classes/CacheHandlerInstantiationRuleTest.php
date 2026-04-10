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
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesProvider;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 *
 * @extends RuleTestCase<CacheHandlerInstantiationRule>
 */
#[Group('static-analysis')]
final class CacheHandlerInstantiationRuleTest extends RuleTestCase
{
    use AdditionalConfigFilesProvider;

    protected function getRule(): Rule
    {
        return new CacheHandlerInstantiationRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../data/rules/cache-handler.php'], [
            [
                'Instantiating "CodeIgniter\Cache\Handlers\FileHandler" using new is incomplete to get a fully configured cache instance.',
                19,
                'Use "CacheFactory::getHandler()" or the "cache()" function instead.',
            ],
            [
                'Instantiating "CodeIgniter\Cache\Handlers\RedisHandler" using new is incomplete to get a fully configured cache instance.',
                20,
                'Use "CacheFactory::getHandler()" or the "cache()" function instead.',
            ],
        ]);
    }
}
