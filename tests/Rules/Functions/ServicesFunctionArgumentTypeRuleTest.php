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

use CodeIgniter\PHPStan\Rules\Functions\ServicesFunctionArgumentTypeRule;
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesTrait;
use CodeIgniter\PHPStan\Type\ServicesReturnTypeHelper;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 * @extends RuleTestCase<ServicesFunctionArgumentTypeRule>
 */
#[Group('integration')]
final class ServicesFunctionArgumentTypeRuleTest extends RuleTestCase
{
    use AdditionalConfigFilesTrait;

    protected function getRule(): Rule
    {
        return new ServicesFunctionArgumentTypeRule(
            self::createReflectionProvider(),
            self::getContainer()->getByType(ServicesReturnTypeHelper::class)
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../Type/data/services.php'], [
            [
                'The method \'createRequest\' is reserved for service location internals and cannot be used as a service method.',
                51,
            ],
            [
                'The method \'__callStatic\' is reserved for service location internals and cannot be used as a service method.',
                56,
            ],
            [
                'The method \'serviceExists\' is reserved for service location internals and cannot be used as a service method.',
                57,
            ],
            [
                'The method \'reset\' is reserved for service location internals and cannot be used as a service method.',
                58,
            ],
            [
                'The method \'resetSingle\' is reserved for service location internals and cannot be used as a service method.',
                59,
            ],
            [
                'The method \'injectMock\' is reserved for service location internals and cannot be used as a service method.',
                60,
            ],
            [
                'Call to unknown service method \'bar\'.',
                68,
                'If \'bar\' is a valid service method, you can add its possible services factory class(es) in <fg=cyan>codeigniter.additionalServices</> in your <fg=yellow>%configurationFile%</>.',
            ],
            [
                'Call to unknown service method \'timers\'.',
                69,
                'If \'timers\' is a valid service method, you can add its possible services factory class(es) in <fg=cyan>codeigniter.additionalServices</> in your <fg=yellow>%configurationFile%</>.',
            ],
            [
                'Service method \'toBool\' expected to return a service instance, got bool instead.',
                72,
            ],
            [
                'Service method \'noReturn\' returns mixed.',
                73,
                'Perhaps you forgot to add a return type?',
            ],
            [
                'Service method \'returnNull\' expected to return a service instance, got null instead.',
                74,
            ],
        ]);
    }
}
