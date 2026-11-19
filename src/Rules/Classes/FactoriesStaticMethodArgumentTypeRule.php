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

namespace CodeIgniter\PHPStan\Rules\Classes;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Config\Factories;
use CodeIgniter\Model;
use CodeIgniter\PHPStan\Helpers\FactoriesReturnTypeHelper;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\DependencyInjection\AutowiredParameter;
use PHPStan\DependencyInjection\AutowiredService;
use PHPStan\DependencyInjection\Conditional;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use PHPStan\Type\VerbosityLevel;

/**
 * @implements Rule<Node\Expr\StaticCall>
 */
#[Conditional(attribute: new AutowiredService(), if: '%codeigniter.checkArgumentTypeOfFactories%')]
final class FactoriesStaticMethodArgumentTypeRule implements Rule
{
    /**
     * @var array<string, class-string>
     */
    private array $instanceofMap = [
        'config' => BaseConfig::class,
        'model'  => Model::class,
    ];

    /**
     * @var array<string, bool>
     */
    private array $argumentTypeCheck;

    public function __construct(
        private readonly FactoriesReturnTypeHelper $factoriesReturnTypeHelper,

        #[AutowiredParameter(ref: '%codeigniter.checkArgumentTypeOfConfig%')]
        bool $checkArgumentTypeOfConfig,

        #[AutowiredParameter(ref: '%codeigniter.checkArgumentTypeOfModel%')]
        bool $checkArgumentTypeOfModel,
    ) {
        $this->argumentTypeCheck = [
            'config' => $checkArgumentTypeOfConfig,
            'model'  => $checkArgumentTypeOfModel,
        ];
    }

    public function getNodeType(): string
    {
        return Node\Expr\StaticCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->class instanceof Node\Name || ! $node->name instanceof Node\Identifier) {
            return [];
        }

        if (strtolower($scope->resolveName($node->class)) !== strtolower(Factories::class)) {
            return [];
        }

        $method = $node->name->toLowerString();
        $args   = $node->getArgs();

        if ($method === 'get') {
            if (! isset($args[0], $args[1])) {
                return [];
            }

            $components = $scope->getType($args[0]->value)->getConstantStrings();

            if (count($components) !== 1) {
                return [];
            }

            $component = $components[0]->getValue();
            $position  = 1;
        } else {
            $component = $method;
            $position  = 0;
        }

        $function = match (strtolower($component)) {
            'config'          => 'config',
            'models', 'model' => 'model',
            default           => null,
        };

        if ($function === null || ! isset($args[$position])) {
            return [];
        }

        $aliasType = $scope->getType($args[$position]->value);

        if ($aliasType->isString()->no() || $aliasType->getConstantStrings() === []) {
            return [];
        }

        $called     = sprintf('static method Factories::%s()', $node->name->name);
        $returnType = $this->factoriesReturnTypeHelper->check($aliasType, $function);

        if ($returnType->isNull()->yes()) {
            $ruleErrorBuilder = RuleErrorBuilder::message(sprintf(
                'Parameter #%d $alias of %s expects a valid class string, %s given.',
                $position + 1,
                $called,
                $aliasType->describe(VerbosityLevel::precise()),
            ))->identifier(sprintf('codeigniter.%sArgumentType', $function));

            foreach ($aliasType->getConstantStrings() as $constantStringType) {
                $ruleErrorBuilder->addTip(sprintf(
                    'If %s is a valid class string, you can add its possible namespace(s) in <fg=cyan>codeigniter.additional%sNamespaces</> in your <fg=yellow>%%configurationFile%%</>.',
                    $constantStringType->describe(VerbosityLevel::precise()),
                    ucfirst($function),
                ));
            }

            return [$ruleErrorBuilder->build()];
        }

        if (! $this->argumentTypeCheck[$function] || (new ObjectType($this->instanceofMap[$function]))->isSuperTypeOf($returnType)->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Argument #%d $alias (%s) passed to %s does not extend %s.',
                $position + 1,
                $aliasType->describe(VerbosityLevel::precise()),
                $called,
                $this->instanceofMap[$function],
            ))
                ->identifier(sprintf('codeigniter.%sArgumentInstanceof', $function))
                ->build(),
        ];
    }
}
