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

namespace CodeIgniter\PHPStan\Tests\Rules\Superglobals;

use CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalAssignRule;
use CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalRuleHelper;
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesTrait;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 *
 * @extends RuleTestCase<SuperglobalAssignRule>
 */
#[Group('Integration')]
final class SuperglobalAssignRuleTest extends RuleTestCase
{
    use AdditionalConfigFilesTrait;

    protected function getRule(): Rule
    {
        return new SuperglobalAssignRule(new SuperglobalRuleHelper());
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../Fixtures/Rules/Superglobals/superglobal-assign-cases.php'], [
            [
                'Assigning \'https://localhost\' directly on offset \'HTTP_HOST\' of $_SERVER is discouraged.',
                18,
                'Use \\Config\\Services::superglobals()->setServer(\'HTTP_HOST\', \'https://localhost\') instead.',
            ],
            [
                'Assigning \'John Doe\' directly on offset \'first_name\' of $_GET is discouraged.',
                20,
                'Use \\Config\\Services::superglobals()->setGet(\'first_name\', \'John Doe\') instead.',
            ],
            [
                'Assigning string directly on offset string of $_SERVER is discouraged.',
                27,
                'Use \\Config\\Services::superglobals()->setServer() instead.',
            ],
            [
                'Assigning string directly on offset string of $_GET is discouraged.',
                29,
                'Use \Config\Services::superglobals()->setGet() instead.',
            ],
            [
                'Cannot re-assign non-arrays to $_GET, got string.',
                32,
            ],
            [
                'Cannot re-assign non-arrays to $_GET, got int.',
                33,
            ],
        ]);
    }
}
