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

use CodeIgniter\PHPStan\Helpers\FactoriesReturnTypeHelper;
use CodeIgniter\PHPStan\Rules\Classes\FactoriesStaticMethodArgumentTypeRule;
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesProvider;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @extends RuleTestCase<FactoriesStaticMethodArgumentTypeRule>
 *
 * @internal
 */
#[Group('static-analysis')]
final class FactoriesStaticMethodArgumentTypeRuleTest extends RuleTestCase
{
    use AdditionalConfigFilesProvider;

    private bool $checkArgumentTypeOfModel = true;

    protected function getRule(): Rule
    {
        return new FactoriesStaticMethodArgumentTypeRule(
            self::getContainer()->getByType(FactoriesReturnTypeHelper::class),
            true,
            $this->checkArgumentTypeOfModel,
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../data/rules/factories-static-argument-type.php'], [
            [
                'Parameter #1 $alias of static method Factories::config() expects a valid class string, \'bar\' given.',
                22,
                'If \'bar\' is a valid class string, you can add its possible namespace(s) in <fg=cyan>codeigniter.additionalConfigNamespaces</> in your <fg=yellow>%configurationFile%</>.',
            ],
            [
                'Argument #1 $alias (\'stdClass\') passed to static method Factories::config() does not extend CodeIgniter\\Config\\BaseConfig.',
                24,
            ],
            [
                'Parameter #1 $alias of static method Factories::models() expects a valid class string, \'foo\' given.',
                27,
                'If \'foo\' is a valid class string, you can add its possible namespace(s) in <fg=cyan>codeigniter.additionalModelNamespaces</> in your <fg=yellow>%configurationFile%</>.',
            ],
            [
                'Argument #1 $alias (\'stdClass\') passed to static method Factories::models() does not extend CodeIgniter\\Model.',
                28,
            ],
            [
                'Parameter #2 $alias of static method Factories::get() expects a valid class string, \'bar\' given.',
                32,
                'If \'bar\' is a valid class string, you can add its possible namespace(s) in <fg=cyan>codeigniter.additionalConfigNamespaces</> in your <fg=yellow>%configurationFile%</>.',
            ],
            [
                'Parameter #2 $alias of static method Factories::get() expects a valid class string, \'foo\' given.',
                33,
                'If \'foo\' is a valid class string, you can add its possible namespace(s) in <fg=cyan>codeigniter.additionalModelNamespaces</> in your <fg=yellow>%configurationFile%</>.',
            ],
            [
                'Argument #2 $alias (\'stdClass\') passed to static method Factories::get() does not extend CodeIgniter\\Model.',
                34,
            ],
        ]);
    }

    public function testAllowNonModelClasses(): void
    {
        $this->checkArgumentTypeOfModel = false;

        $this->analyse([__DIR__ . '/../../data/rules/factories-static-argument-type-non-model.php'], []);
    }
}
