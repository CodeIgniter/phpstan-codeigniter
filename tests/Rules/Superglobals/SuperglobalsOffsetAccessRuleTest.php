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
use CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalsOffsetAccessRule;
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesProvider;
use PHPStan\Node\Printer\ExprPrinter;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @extends RuleTestCase<SuperglobalsOffsetAccessRule>
 *
 * @internal
 */
#[Group('static-analysis')]
final class SuperglobalsOffsetAccessRuleTest extends RuleTestCase
{
    use AdditionalConfigFilesProvider;

    protected function getRule(): Rule
    {
        return new SuperglobalsOffsetAccessRule(
            new SuperglobalsHelper(),
            $this->getContainer()->getByType(ExprPrinter::class),
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../data/rules/superglobals-offset-access.php'], [
            [
                'Direct access to $_SERVER[$name] is not allowed.',
                18,
                'Use service(\'superglobals\')->server($name) instead.',
            ],
            [
                'Direct access to $_GET[\'key\'] is not allowed.',
                19,
                'Use service(\'superglobals\')->get(\'key\') instead.',
            ],
            [
                'Direct access to $_POST[\'key\'] is not allowed.',
                20,
                'Use service(\'superglobals\')->post(\'key\') instead.',
            ],
            [
                'Direct access to $_COOKIE[\'key\'] is not allowed.',
                21,
                'Use service(\'superglobals\')->cookie(\'key\') instead.',
            ],
            [
                'Direct access to $_REQUEST[\'key\'] is not allowed.',
                23,
                'Use service(\'superglobals\')->request(\'key\') instead.',
            ],
            [
                'Direct access to $_SERVER[$key] is not allowed.',
                26,
                'Use service(\'superglobals\')->server($key) instead.',
            ],
        ]);
    }
}
