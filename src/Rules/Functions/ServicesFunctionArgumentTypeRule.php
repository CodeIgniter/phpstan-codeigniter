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

use CodeIgniter\PHPStan\Type\ServicesReturnTypeHelper;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;

/**
 * @implements Rule<Node\Expr\FuncCall>
 */
final class ServicesFunctionArgumentTypeRule implements Rule
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly ServicesReturnTypeHelper $servicesReturnTypeHelper
    ) {}

    public function getNodeType(): string
    {
        return Node\Expr\FuncCall::class;
    }

    /**
     * @param Node\Expr\FuncCall $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->name instanceof Node\Name) {
            return [];
        }

        $nameNode = $node->name;
        $function = $this->reflectionProvider->resolveFunctionName($nameNode, $scope);

        if (! in_array($function, ['service', 'single_service'], true)) {
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

        $returnType = $this->servicesReturnTypeHelper->check($nameType, $scope);

        if ($returnType->isNull()->no()) {
            return [];
        }

        $name = $nameType->describe(VerbosityLevel::precise());

        if (in_array(strtolower(trim($name, "'")), ServicesReturnTypeHelper::IMPOSSIBLE_SERVICE_METHOD_NAMES, true)) {
            return [RuleErrorBuilder::message(sprintf(
                'The name %s is reserved for service location internals and cannot be used as a service name.',
                $name
            ))->identifier('codeigniter.reservedServiceName')->build()];
        }

        $addTip = static function (RuleErrorBuilder $ruleErrorBuilder) use ($nameType): RuleErrorBuilder {
            if ($nameType->getConstantStrings() === []) {
                return $ruleErrorBuilder;
            }

            foreach ($nameType->getConstantStrings() as $constantStringType) {
                $ruleErrorBuilder->addTip(sprintf(
                    'If %s is a valid service name, you can add its possible service class(es) in <fg=cyan>codeigniter.additionalServices</> in your <fg=yellow>%%configurationFile%%</>.',
                    $constantStringType->describe(VerbosityLevel::precise()),
                ));
            }

            return $ruleErrorBuilder;
        };

        return [$addTip(RuleErrorBuilder::message(sprintf(
            'Call to unknown service name %s.',
            $nameType->describe(VerbosityLevel::precise())
        )))->identifier('codeigniter.unknownServiceName')->build()];
    }
}
