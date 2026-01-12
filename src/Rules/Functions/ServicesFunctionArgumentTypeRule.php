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

use CodeIgniter\PHPStan\Helpers\ServicesReturnTypeHelper;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\MixedType;
use PHPStan\Type\VerbosityLevel;

/**
 * @implements Rule<Node\Expr\FuncCall>
 */
final class ServicesFunctionArgumentTypeRule implements Rule
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly ServicesReturnTypeHelper $servicesReturnTypeHelper,
    ) {}

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

        $errors = [];

        foreach ($nameType->getConstantStrings() as $nameStringType) {
            $name = $nameStringType->getValue();

            $returnType = $this->servicesReturnTypeHelper->checkReturnType($nameStringType, $scope);

            if ($returnType === null) {
                throw new ShouldNotHappenException(sprintf('Service return type for "%s" could not be determined.', $name));
            }

            if ($returnType->isObject()->yes()) {
                continue;
            }

            if (in_array(strtolower($name), ServicesReturnTypeHelper::IMPOSSIBLE_SERVICE_METHOD_NAMES, true)) {
                $errors[] = RuleErrorBuilder::message(sprintf(
                    'The method "%s" is reserved for service location internals and cannot be used as a service method.',
                    $name,
                ))
                    ->identifier('codeigniter.reservedServiceName')
                    ->build();

                continue;
            }

            if ($returnType->isNull()->maybe() && $returnType instanceof MixedType) {
                $errors[] = RuleErrorBuilder::message(sprintf('Service method "%s" returns mixed.', $name))
                    ->identifier('codeigniter.serviceMixedReturn')
                    ->tip('Perhaps you forgot to add a return type?')
                    ->build();

                continue;
            }

            $hasMethod = array_reduce(
                $this->servicesReturnTypeHelper->getServicesReflection(),
                static fn (bool $carry, ClassReflection $service): bool => $carry || $service->hasMethod($name),
                false,
            );

            if (! $returnType->isNull()->yes() || $hasMethod) {
                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Service method "%s" expected to return a service instance, got %s instead.',
                    $name,
                    $returnType->describe(VerbosityLevel::typeOnly()),
                ))->identifier('codeigniter.serviceNonObjectReturn')->build();

                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf('Call to unknown service method "%s".', $name))
                ->identifier('codeigniter.unknownServiceMethod')
                ->tip(sprintf(
                    'If "%s" is a valid service method, you can add its possible services factory class(es) ' .
                    'in <fg=cyan>codeigniter.additionalServices</> in your <fg=yellow>%%configurationFile%%</>.',
                    $name,
                ))
                ->build();
        }

        return $errors;
    }
}
