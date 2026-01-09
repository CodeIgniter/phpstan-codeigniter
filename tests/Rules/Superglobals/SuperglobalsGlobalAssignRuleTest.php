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
use CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalsGlobalAssignRule;
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesProvider;
use PHPStan\Node\Printer\ExprPrinter;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @extends RuleTestCase<SuperglobalsGlobalAssignRule>
 *
 * @internal
 */
#[Group('static-analysis')]
final class SuperglobalsGlobalAssignRuleTest extends RuleTestCase
{
    use AdditionalConfigFilesProvider;

    protected function getRule(): Rule
    {
        return new SuperglobalsGlobalAssignRule(
            new SuperglobalsHelper(),
            self::getContainer()->getByType(ExprPrinter::class),
        );
    }

    public function testRuleAnalysis(): void
    {
        $this->analyse([__DIR__ . '/../../data/rules/superglobals-global-assign.php'], [
            [
                'Direct global assignment to $_SERVER is not allowed.',
                18,
                'Use service(\'superglobals\')->setServerArray([]) instead.',
            ],
            [
                'Direct global assignment to $_GET is not allowed.',
                19,
                'Use service(\'superglobals\')->setGetArray([]) instead.',
            ],
            [
                'Direct global assignment to $_POST is not allowed.',
                20,
                'Use service(\'superglobals\')->setPostArray([]) instead.',
            ],
            [
                'Direct global assignment to $_COOKIE is not allowed.',
                21,
                'Use service(\'superglobals\')->setCookieArray([]) instead.',
            ],
            [
                'Direct global assignment to $_FILES is not allowed.',
                22,
                'Use service(\'superglobals\')->setFilesArray([]) instead.',
            ],
            [
                'Direct global assignment to $_REQUEST is not allowed.',
                23,
                'Use service(\'superglobals\')->setRequestArray([]) instead.',
            ],
            [
                'Cannot assign int type to $_SERVER.',
                28,
            ],
            [
                'Cannot assign string type to $_GET.',
                29,
            ],
            [
                'Cannot assign null type to $_POST.',
                30,
            ],
            [
                'Cannot assign float type to $_COOKIE.',
                31,
            ],
            [
                'Cannot assign true type to $_FILES.',
                32,
            ],
            [
                'Cannot assign object{}&stdClass type to $_REQUEST.',
                33,
            ],
            [
                'Direct global assignment to $_GET is not allowed.',
                38,
                'Use service(\'superglobals\')->setGetArray([]) instead.',
            ],
            [
                'Direct global assignment to $_POST is not allowed.',
                38,
                'Use service(\'superglobals\')->setPostArray([]) instead.',
            ],
            [
                'Direct global assignment to $_REQUEST is not allowed.',
                38,
                'Use service(\'superglobals\')->setRequestArray([]) instead.',
            ],
        ]);
    }

    public function testRuleWithFix(): void
    {
        $this->fix(
            __DIR__ . '/../../data/rules/superglobals-global-assign.php',
            __DIR__ . '/../../data/rules/superglobals-global-assign-fixed.php',
        );
    }
}
