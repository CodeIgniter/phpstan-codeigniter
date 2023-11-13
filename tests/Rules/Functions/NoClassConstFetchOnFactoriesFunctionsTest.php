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

namespace CodeIgniter\PHPStan\Tests\Rules\Functions;

use CodeIgniter\PHPStan\Rules\Functions\NoClassConstFetchOnFactoriesFunctions;
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesTrait;
use CodeIgniter\PHPStan\Type\FactoriesReturnTypeHelper;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 *
 * @extends RuleTestCase<NoClassConstFetchOnFactoriesFunctions>
 */
#[Group('Integration')]
final class NoClassConstFetchOnFactoriesFunctionsTest extends RuleTestCase
{
    use AdditionalConfigFilesTrait;

    protected function getRule(): Rule
    {
        return new NoClassConstFetchOnFactoriesFunctions(
            self::createReflectionProvider(),
            self::getContainer()->getByType(FactoriesReturnTypeHelper::class)
        );
    }

    public function testRule(): void
    {
        $this->analyse([
            __DIR__ . '/../../Fixtures/Type/config.php',
            __DIR__ . '/../../Fixtures/Type/factories-in-tests.php',
            __DIR__ . '/../../Fixtures/Type/model.php',
        ], [
            [
                'Call to function config with CodeIgniter\Shield\Config\AuthJWT::class is discouraged.',
                38,
                'Use config(\'AuthJWT\') instead to allow overriding.',
            ],
            [
                'Call to function model with stdClass::class is discouraged.',
                19,
                'Use model(\'stdClass\') instead to allow overriding.',
            ],
            [
                'Call to function model with Closure::class is discouraged.',
                20,
                'Use model(\'Closure\') instead to allow overriding.',
            ],
        ]);
    }

    public function testOnAppNamespaceWithNonAppCall(): void
    {
        $this->analyse([__DIR__ . '/../../Fixtures/Rules/Functions/bug-9.php'], []);
    }
}
