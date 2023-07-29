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
        $this->analyse([__DIR__ . '/../../Fixtures/Type/services.php'], [
            [
                'The name \'createRequest\' is reserved for service location internals and cannot be used as a service name.',
                51,
            ],
            [
                'The name \'__callStatic\' is reserved for service location internals and cannot be used as a service name.',
                56,
            ],
            [
                'The name \'serviceExists\' is reserved for service location internals and cannot be used as a service name.',
                57,
            ],
            [
                'The name \'reset\' is reserved for service location internals and cannot be used as a service name.',
                58,
            ],
            [
                'The name \'resetSingle\' is reserved for service location internals and cannot be used as a service name.',
                59,
            ],
            [
                'The name \'injectMock\' is reserved for service location internals and cannot be used as a service name.',
                60,
            ],
            [
                'Call to unknown service name \'bar\'.',
                68,
                'If \'bar\' is a valid service name, you can add its possible service class(es) in <fg=cyan>codeigniter.additionalServices</> in your <fg=yellow>%configurationFile%</>.',
            ],
            [
                'Call to unknown service name \'timers\'.',
                69,
                'If \'timers\' is a valid service name, you can add its possible service class(es) in <fg=cyan>codeigniter.additionalServices</> in your <fg=yellow>%configurationFile%</>.',
            ],
        ]);
    }
}
