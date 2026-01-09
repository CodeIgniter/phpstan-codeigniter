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

use CodeIgniter\PHPStan\Helpers\SuperglobalsHelper;
use CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalsOffsetUnsetRule;
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesProvider;
use PHPStan\Node\Printer\ExprPrinter;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @extends RuleTestCase<SuperglobalsOffsetUnsetRule>
 *
 * @internal
 */
#[Group('static-analysis')]
final class SuperglobalsOffsetUnsetRuleTest extends RuleTestCase
{
    use AdditionalConfigFilesProvider;

    protected function getRule(): Rule
    {
        return new SuperglobalsOffsetUnsetRule(
            new SuperglobalsHelper(),
            self::getContainer()->getByType(ExprPrinter::class),
        );
    }

    public function testRuleAnalysis(): void
    {
        $this->analyse([__DIR__ . '/../../data/rules/superglobals-offset-unset.php'], [
            [
                'Direct unset of $_SERVER[$name] is not allowed.',
                19,
                'Use service(\'superglobals\')->unsetServer($name) instead.',
            ],
            [
                'Direct unset of $_SERVER[\'key\'] is not allowed.',
                20,
                'Use service(\'superglobals\')->unsetServer(\'key\') instead.',
            ],
            [
                'Direct unset of $_GET[\'key\'] is not allowed.',
                21,
                'Use service(\'superglobals\')->unsetGet(\'key\') instead.',
            ],
            [
                'Direct unset of $_POST[\'key\'] is not allowed.',
                22,
                'Use service(\'superglobals\')->unsetPost(\'key\') instead.',
            ],
            [
                'Direct unset of $_COOKIE[\'key\'] is not allowed.',
                23,
                'Use service(\'superglobals\')->unsetCookie(\'key\') instead.',
            ],
            [
                'Direct unset of $_REQUEST[\'key\'] is not allowed.',
                24,
                'Use service(\'superglobals\')->unsetRequest(\'key\') instead.',
            ],
            [
                'Direct unset of $_SERVER[$key] is not allowed.',
                29,
                'Use service(\'superglobals\')->unsetServer($key) instead.',
            ],
        ]);
    }
}
