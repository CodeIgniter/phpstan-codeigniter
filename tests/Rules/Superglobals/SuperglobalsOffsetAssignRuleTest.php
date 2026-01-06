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
use CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalsOffsetAssignRule;
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesProvider;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @extends RuleTestCase<SuperglobalsOffsetAssignRule>
 *
 * @internal
 */
#[Group('static-analysis')]
final class SuperglobalsOffsetAssignRuleTest extends RuleTestCase
{
    use AdditionalConfigFilesProvider;

    protected function getRule(): Rule
    {
        return new SuperglobalsOffsetAssignRule(new SuperglobalsHelper());
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../data/rules/superglobals-offset-assign.php'], [
            [
                'Direct assignment of \'value\' to string offset of $_SERVER is not allowed.',
                18,
                'Use service(\'superglobals\')->setServer(<key>, <value>) instead.',
            ],
            [
                'Direct assignment of \'value\' to $_GET[\'key\'] is not allowed.',
                19,
                'Use service(\'superglobals\')->setGet(\'key\', \'value\') instead.',
            ],
            [
                'Direct assignment of \'value\' to $_POST[\'key\'] is not allowed.',
                20,
                'Use service(\'superglobals\')->setPost(\'key\', \'value\') instead.',
            ],
            [
                'Direct assignment of \'value\' to $_COOKIE[\'key\'] is not allowed.',
                21,
                'Use service(\'superglobals\')->setCookie(\'key\', \'value\') instead.',
            ],
            [
                'Direct assignment of \'value\' to $_REQUEST[\'key\'] is not allowed.',
                23,
                'Use service(\'superglobals\')->setRequest(\'key\', \'value\') instead.',
            ],
            [
                'Direct assignment of \'value\' to $_SERVER[\'key1\'] is not allowed.',
                27,
                'Use service(\'superglobals\')->setServer(\'key1\', \'value\') instead.',
            ],
            [
                'Direct assignment of \'value\' to $_SERVER[\'key2\'] is not allowed.',
                27,
                'Use service(\'superglobals\')->setServer(\'key2\', \'value\') instead.',
            ],
        ]);
    }
}
