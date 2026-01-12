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

use CodeIgniter\PHPStan\Helpers\ServicesReturnTypeHelper;
use CodeIgniter\PHPStan\Rules\Functions\ServicesFunctionArgumentTypeRule;
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesProvider;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @extends RuleTestCase<ServicesFunctionArgumentTypeRule>
 *
 * @internal
 */
#[Group('static-analysis')]
final class ServicesFunctionArgumentTypeRuleTest extends RuleTestCase
{
    use AdditionalConfigFilesProvider;

    protected function getRule(): Rule
    {
        return new ServicesFunctionArgumentTypeRule(
            self::createReflectionProvider(),
            self::getContainer()->getByType(ServicesReturnTypeHelper::class),
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../data/rules/services-argument-type.php'], [
            [
                'Call to unknown service method "non_existent_service".',
                19,
                'If "non_existent_service" is a valid service method, you can add its possible services factory class(es) in <fg=cyan>codeigniter.additionalServices</> in your <fg=yellow>%configurationFile%</>.',
            ],
            [
                'The method "createRequest" is reserved for service location internals and cannot be used as a service method.',
                24,
            ],
            [
                'Call to unknown service method "bar".',
                40,
                'If "bar" is a valid service method, you can add its possible services factory class(es) in <fg=cyan>codeigniter.additionalServices</> in your <fg=yellow>%configurationFile%</>.',
            ],
            [
                'Call to unknown service method "baz".',
                40,
                'If "baz" is a valid service method, you can add its possible services factory class(es) in <fg=cyan>codeigniter.additionalServices</> in your <fg=yellow>%configurationFile%</>.',
            ],
            [
                'Service method "boolreturn" expected to return a service instance, got bool instead.',
                50,
            ],
            [
                'Service method "intreturn" expected to return a service instance, got int instead.',
                51,
            ],
            [
                'Service method "voidreturn" expected to return a service instance, got void instead.',
                52,
            ],
        ]);
    }
}
