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
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\MixedType;
use PHPStan\Type\VerbosityLevel;

/**
 * @implements Rule<Node\Expr\FuncCall>
 */
final class ServicesFunctionArgumentTypeRule implements Rule
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;

    /**
     * @readonly
     */
    private ServicesReturnTypeHelper $servicesReturnTypeHelper;

    public function __construct(ReflectionProvider $reflectionProvider, ServicesReturnTypeHelper $servicesReturnTypeHelper)
    {
        $this->reflectionProvider       = $reflectionProvider;
        $this->servicesReturnTypeHelper = $servicesReturnTypeHelper;
    }

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

        if ($returnType->isObject()->yes()) {
            return [];
        }

        $name = $nameType->describe(VerbosityLevel::precise());

        $trimmedName = trim($name, "'");

        if (in_array(strtolower($trimmedName), ServicesReturnTypeHelper::IMPOSSIBLE_SERVICE_METHOD_NAMES, true)) {
            return [
                RuleErrorBuilder::message(sprintf('The method %s is reserved for service location internals and cannot be used as a service method.', $name))
                    ->identifier('codeigniter.reservedServiceName')
                    ->build(),
            ];
        }

        if ($returnType->isNull()->maybe() && $returnType instanceof MixedType) {
            return [
                RuleErrorBuilder::message(sprintf('Service method %s returns mixed.', $name))
                    ->tip('Perhaps you forgot to add a return type?')
                    ->identifier('codeigniter.serviceMixedReturn')
                    ->build(),
            ];
        }

        $hasMethod = array_reduce(
            $this->servicesReturnTypeHelper->getServicesReflection(),
            static fn (bool $carry, ClassReflection $service): bool => $carry || $service->hasMethod($trimmedName),
            false
        );

        if (! $returnType->isNull()->yes() || $hasMethod) {
            return [RuleErrorBuilder::message(sprintf(
                'Service method %s expected to return a service instance, got %s instead.',
                $name,
                $returnType->describe(VerbosityLevel::precise())
            ))->identifier('codeigniter.serviceNonObjectReturn')->build()];
        }

        $addTip = static function (RuleErrorBuilder $ruleErrorBuilder) use ($nameType): RuleErrorBuilder {
            foreach ($nameType->getConstantStrings() as $constantStringType) {
                $ruleErrorBuilder->addTip(sprintf(
                    'If %s is a valid service method, you can add its possible services factory class(es) ' .
                    'in <fg=cyan>codeigniter.additionalServices</> in your <fg=yellow>%%configurationFile%%</>.',
                    $constantStringType->describe(VerbosityLevel::precise()),
                ));
            }

            return $ruleErrorBuilder;
        };

        return [$addTip(RuleErrorBuilder::message(sprintf(
            'Call to unknown service method %s.',
            $nameType->describe(VerbosityLevel::precise())
        )))->identifier('codeigniter.unknownServiceMethod')->build()];
    }
}
