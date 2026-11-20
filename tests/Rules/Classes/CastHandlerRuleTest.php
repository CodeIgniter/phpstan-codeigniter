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

use CodeIgniter\PHPStan\Database\Schema\CastTypeResolver;
use CodeIgniter\PHPStan\Rules\Classes\CastHandlerRule;
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesProvider;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @extends RuleTestCase<CastHandlerRule>
 *
 * @internal
 */
#[Group('static-analysis')]
final class CastHandlerRuleTest extends RuleTestCase
{
    use AdditionalConfigFilesProvider;

    protected function getRule(): Rule
    {
        return new CastHandlerRule(
            self::createReflectionProvider(),
            self::getContainer()->getByType(CastTypeResolver::class),
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../data/rules/cast-handler.php'], [
            ['Cast "unregistered" of field "a" is not a built-in cast and has no handler in $castHandlers.', 37],
            ['Cast handler "Does\Not\Exist" of cast "missing" does not exist.', 43],
            ['Cast handler "stdClass" of cast "invalid" must implement CodeIgniter\DataCaster\Cast\CastInterface or CodeIgniter\Entity\Cast\CastInterface.', 44],
            ['Cast "unregistered" of field "title" is not a built-in cast and has no handler in $castHandlers.', 53],
        ]);
    }
}
