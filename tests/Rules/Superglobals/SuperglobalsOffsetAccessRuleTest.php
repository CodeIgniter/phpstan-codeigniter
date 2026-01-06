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
        return new SuperglobalsOffsetAccessRule(new SuperglobalsHelper());
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../data/rules/superglobals-offset-access.php'], [
            [
                'Accessing $_SERVER directly with string key is not allowed.',
                18,
                'Use service(\'superglobals\')->server(<key>) instead.',
            ],
            [
                'Accessing $_GET directly with key \'key\' is not allowed.',
                19,
                'Use service(\'superglobals\')->get(\'key\') instead.',
            ],
            [
                'Accessing $_POST directly with key \'key\' is not allowed.',
                20,
                'Use service(\'superglobals\')->post(\'key\') instead.',
            ],
            [
                'Accessing $_COOKIE directly with key \'key\' is not allowed.',
                21,
                'Use service(\'superglobals\')->cookie(\'key\') instead.',
            ],
            [
                'Accessing $_REQUEST directly with key \'key\' is not allowed.',
                23,
                'Use service(\'superglobals\')->request(\'key\') instead.',
            ],
            [
                'Accessing $_SERVER directly with key \'key1\' is not allowed.',
                26,
                'Use service(\'superglobals\')->server(\'key1\') instead.',
            ],
            [
                'Accessing $_SERVER directly with key \'key2\' is not allowed.',
                26,
                'Use service(\'superglobals\')->server(\'key2\') instead.',
            ],
        ]);
    }
}
