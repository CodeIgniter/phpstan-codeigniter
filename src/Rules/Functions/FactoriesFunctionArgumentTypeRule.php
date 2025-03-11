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

namespace CodeIgniter\PHPStan\Rules\Functions;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Model;
use CodeIgniter\PHPStan\Type\FactoriesReturnTypeHelper;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use PHPStan\Type\VerbosityLevel;

/**
 * @implements Rule<Node\Expr\FuncCall>
 */
final class FactoriesFunctionArgumentTypeRule implements Rule
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
    private array $argumentTypeCheck = [];

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly FactoriesReturnTypeHelper $factoriesReturnTypeHelper,
        bool $checkArgumentTypeOfConfig,
        bool $checkArgumentTypeOfModel,
    ) {
        $this->argumentTypeCheck = [
            'config' => $checkArgumentTypeOfConfig,
            'model'  => $checkArgumentTypeOfModel,
        ];
    }

    public function getNodeType(): string
    {
        return Node\Expr\FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->name instanceof Node\Name) {
            return [];
        }

        $nameNode = $node->name;
        $function = $this->reflectionProvider->resolveFunctionName($nameNode, $scope);

        if (! in_array($function, ['config', 'model'], true)) {
            return [];
        }

        $args = $node->getArgs();

        if ($args === []) {
            return [];
        }

        $nameType = $scope->getType($args[0]->value);

        if ($nameType->isString()->no()) {
            return []; // caught elsewhere
        }

        $returnType = $this->factoriesReturnTypeHelper->check($nameType, $function);

        $firstParameter = ParametersAcceptorSelector::selectFromArgs(
            $scope,
            $node->getArgs(),
            $this->reflectionProvider->getFunction($nameNode, $scope)->getVariants(),
        )->getParameters()[0];

        if ($returnType->isNull()->yes()) {
            $addTip = static function (RuleErrorBuilder $ruleErrorBuilder) use ($nameType, $function): RuleErrorBuilder {
                foreach ($nameType->getConstantStrings() as $constantStringType) {
                    $ruleErrorBuilder->addTip(sprintf(
                        'If %s is a valid class string, you can add its possible namespace(s) in <fg=cyan>codeigniter.additional%sNamespaces</> in your <fg=yellow>%%configurationFile%%</>.',
                        $constantStringType->describe(VerbosityLevel::precise()),
                        ucfirst($function),
                    ));
                }

                return $ruleErrorBuilder;
            };

            return [$addTip(RuleErrorBuilder::message(sprintf(
                'Parameter #1 $%s of function %s expects a valid class string, %s given.',
                $firstParameter->getName(),
                $function,
                $nameType->describe(VerbosityLevel::precise()),
            )))->identifier(sprintf('codeigniter.%sArgumentType', $function))->build()];
        }

        if (! (new ObjectType($this->instanceofMap[$function]))->isSuperTypeOf($returnType)->yes()) {
            if (! $this->argumentTypeCheck[$function]) {
                return [];
            }

            return [RuleErrorBuilder::message(sprintf(
                'Argument #1 $%s (%s) passed to function %s does not extend %s.',
                $firstParameter->getName(),
                $nameType->describe(VerbosityLevel::precise()),
                $function,
                addcslashes($this->instanceofMap[$function], '\\'),
            ))->identifier(sprintf('codeigniter.%sArgumentInstanceof', $function))->build()];
        }

        return [];
    }
}
