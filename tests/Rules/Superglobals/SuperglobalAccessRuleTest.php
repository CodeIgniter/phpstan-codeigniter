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

namespace CodeIgniter\PHPStan\Tests\Superglobals;

use CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalAccessRule;
use CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalRuleHelper;
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesTrait;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 *
 * @extends RuleTestCase<SuperglobalAccessRule>
 */
#[Group('Integration')]
final class SuperglobalAccessRuleTest extends RuleTestCase
{
    use AdditionalConfigFilesTrait;

    protected function getRule(): Rule
    {
        return new SuperglobalAccessRule(new SuperglobalRuleHelper());
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../Fixtures/Rules/Superglobals/superglobal-access-cases.php'], [
            [
                'Accessing offset \'foo\' directly on $_SERVER is discouraged.',
                16,
                'Use \\Config\\Services::superglobals()->server(\'foo\') instead.',
            ],
            [
                'Accessing offset \'a\' directly on $_GET is discouraged.',
                19,
                'Use \\Config\\Services::superglobals()->get(\'a\') instead.',
            ],
            [
                'Accessing offset \'b\' directly on $_GET is discouraged.',
                19,
                'Use \\Config\\Services::superglobals()->get(\'b\') instead.',
            ],
            [
                'Accessing offset string directly on $_SERVER is discouraged.',
                23,
                'Use \\Config\\Services::superglobals()->server() instead.',
            ]
        ]);
    }
}
