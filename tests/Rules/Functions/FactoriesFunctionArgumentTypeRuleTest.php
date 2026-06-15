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

namespace CodeIgniter\PHPStan\Tests\Rules\Functions;

use CodeIgniter\PHPStan\Helpers\FactoriesReturnTypeHelper;
use CodeIgniter\PHPStan\Rules\Functions\FactoriesFunctionArgumentTypeRule;
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesProvider;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @extends RuleTestCase<FactoriesFunctionArgumentTypeRule>
 *
 * @internal
 */
#[Group('static-analysis')]
final class FactoriesFunctionArgumentTypeRuleTest extends RuleTestCase
{
    use AdditionalConfigFilesProvider;

    private bool $checkArgumentTypeOfModel = true;

    protected function getRule(): Rule
    {
        return new FactoriesFunctionArgumentTypeRule(
            self::createReflectionProvider(),
            self::getContainer()->getByType(FactoriesReturnTypeHelper::class),
            true,
            $this->checkArgumentTypeOfModel,
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../data/rules/factories-argument-type.php'], [
            [
                'Parameter #1 $name of function config expects a valid class string, \'bar\' given.',
                22,
                'If \'bar\' is a valid class string, you can add its possible namespace(s) in <fg=cyan>codeigniter.additionalConfigNamespaces</> in your <fg=yellow>%configurationFile%</>.',
            ],
            [
                'Parameter #1 $name of function config expects a valid class string, \'Foo\\\\Bar\' given.',
                23,
                'If \'Foo\\\\Bar\' is a valid class string, you can add its possible namespace(s) in <fg=cyan>codeigniter.additionalConfigNamespaces</> in your <fg=yellow>%configurationFile%</>.',
            ],
            [
                'Argument #1 $name (\'stdClass\') passed to function config does not extend CodeIgniter\\Config\\BaseConfig.',
                25,
            ],
            [
                'Parameter #1 $name of function model expects a valid class string, \'foo\' given.',
                28,
                'If \'foo\' is a valid class string, you can add its possible namespace(s) in <fg=cyan>codeigniter.additionalModelNamespaces</> in your <fg=yellow>%configurationFile%</>.',
            ],
            [
                'Argument #1 $name (\'stdClass\') passed to function model does not extend CodeIgniter\\Model.',
                29,
            ],
            [
                'Argument #1 $name (\'Closure\') passed to function model does not extend CodeIgniter\\Model.',
                30,
            ],
        ]);
    }

    public function testAllowNonModelClassesOnModelCall(): void
    {
        $this->checkArgumentTypeOfModel = false;
        $this->analyse([__DIR__ . '/../../data/rules/factories-argument-type-non-model.php'], []);
    }
}
