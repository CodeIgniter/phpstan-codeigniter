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

use CodeIgniter\PHPStan\Rules\Functions\FactoriesFunctionArgumentTypeRule;
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesTrait;
use CodeIgniter\PHPStan\Type\FactoriesReturnTypeHelper;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 *
 * @extends RuleTestCase<FactoriesFunctionArgumentTypeRule>
 */
#[Group('Integration')]
final class FactoriesFunctionArgumentTypeRuleTest extends RuleTestCase
{
    use AdditionalConfigFilesTrait;

    private bool $checkArgumentTypeOfConfig;
    private bool $checkArgumentTypeOfModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->checkArgumentTypeOfConfig = true;
        $this->checkArgumentTypeOfModel  = true;
    }

    protected function getRule(): Rule
    {
        return new FactoriesFunctionArgumentTypeRule(
            self::createReflectionProvider(),
            self::getContainer()->getByType(FactoriesReturnTypeHelper::class),
            $this->checkArgumentTypeOfConfig,
            $this->checkArgumentTypeOfModel
        );
    }

    public function testRule(): void
    {
        $this->analyse([
            __DIR__ . '/../../Type/data/config.php',
            __DIR__ . '/../../Type/data/model.php',
        ], [
            [
                'Parameter #1 $name of function config expects a valid class string, \'bar\' given.',
                23,
                'If \'bar\' is a valid class string, you can add its possible namespace(s) in <fg=cyan>codeigniter.additionalConfigNamespaces</> in your <fg=yellow>%configurationFile%</>.',
            ],
            [
                'Parameter #1 $name of function config expects a valid class string, \'Foo\\\\Bar\' given.',
                24,
                'If \'Foo\\\\Bar\' is a valid class string, you can add its possible namespace(s) in <fg=cyan>codeigniter.additionalConfigNamespaces</> in your <fg=yellow>%configurationFile%</>.',
            ],
            [
                'Argument #1 $name (\'Foo\'|\'stdClass\') passed to function config does not extend CodeIgniter\\\\Config\\\\BaseConfig.',
                27,
            ],
            [
                'Argument #1 $name (class-string) passed to function config does not extend CodeIgniter\\\\Config\\\\BaseConfig.',
                32,
            ],
            [
                'Parameter #1 $name of function config expects a valid class string, string given.',
                35,
            ],
            [
                'Parameter #1 $name of function model expects a valid class string, \'foo\' given.',
                18,
                'If \'foo\' is a valid class string, you can add its possible namespace(s) in <fg=cyan>codeigniter.additionalModelNamespaces</> in your <fg=yellow>%configurationFile%</>.',
            ],
            [
                'Argument #1 $name (\'stdClass\') passed to function model does not extend CodeIgniter\\\\Model.',
                19,
            ],
            [
                'Argument #1 $name (\'Closure\') passed to function model does not extend CodeIgniter\\\\Model.',
                20,
            ],
            [
                'Parameter #1 $name of function model expects a valid class string, \'App\' given.',
                21,
                'If \'App\' is a valid class string, you can add its possible namespace(s) in <fg=cyan>codeigniter.additionalModelNamespaces</> in your <fg=yellow>%configurationFile%</>.',
            ],
            [
                'Argument #1 $name (\'Foo\'|\'stdClass\') passed to function model does not extend CodeIgniter\\\\Model.',
                22,
            ],
        ]);
    }

    public function testAllowNonModelClassesOnModelCall(): void
    {
        $this->checkArgumentTypeOfModel = false;
        $this->analyse([__DIR__ . '/data/bug-8.php'], []);
    }
}
