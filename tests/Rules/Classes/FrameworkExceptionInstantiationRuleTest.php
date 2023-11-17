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

namespace CodeIgniter\PHPStan\Tests\Rules\Classes;

use CodeIgniter\PHPStan\Rules\Classes\FrameworkExceptionInstantiationRule;
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesTrait;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 * @extends RuleTestCase<FrameworkExceptionInstantiationRule>
 */
#[Group('integration')]
final class FrameworkExceptionInstantiationRuleTest extends RuleTestCase
{
    use AdditionalConfigFilesTrait;

    protected function getRule(): Rule
    {
        return new FrameworkExceptionInstantiationRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/framework-exception.php'], [
            [
                'Instantiating FrameworkException using new is not allowed. Use one of its named constructors instead.',
                17,
            ],
            [
                'Instantiating ViewException using new is not allowed. Use one of its named constructors instead.',
                18,
            ],
            [
                'Instantiating HTTPException using new is not allowed. Use one of its named constructors instead.',
                20,
            ],
        ]);
    }
}
