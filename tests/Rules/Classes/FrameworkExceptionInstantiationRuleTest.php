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
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesProvider;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 * @extends RuleTestCase<FrameworkExceptionInstantiationRule>
 */
#[Group('static-analysis')]
final class FrameworkExceptionInstantiationRuleTest extends RuleTestCase
{
    use AdditionalConfigFilesProvider;

    protected function getRule(): Rule
    {
        return new FrameworkExceptionInstantiationRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../data/rules/framework-exception.php'], [
            [
                'Instantiating "CodeIgniter\Exceptions\FrameworkException" using new is forbidden.',
                18,
                'Use one of its named constructors instead.',
            ],
            [
                'Instantiating "CodeIgniter\View\Exceptions\ViewException" using new is forbidden.',
                19,
                'Use one of its named constructors instead.',
            ],
            [
                'Instantiating "CodeIgniter\HTTP\Exceptions\HTTPException" using new is forbidden.',
                21,
                'Use one of its named constructors instead.',
            ],
        ]);
    }
}
