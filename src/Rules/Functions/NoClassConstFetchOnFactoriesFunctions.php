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

use CodeIgniter\PHPStan\Type\FactoriesReturnTypeHelper;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @implements Rule<Node\Expr\FuncCall>
 */
final class NoClassConstFetchOnFactoriesFunctions implements Rule
{
    /**
     * @var array<string, string>
     */
    private static array $namespaceMap = [
        'config' => 'Config',
        'model'  => 'App\\Models',
    ];

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly FactoriesReturnTypeHelper $factoriesReturnTypeHelper,
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

        if (! in_array($function, ['config', 'model'], true)) {
            return [];
        }

        $args = $node->getArgs();

        if ($args === []) {
            return [];
        }

        $classConstFetch = $args[0]->value;

        if (! $classConstFetch instanceof Node\Expr\ClassConstFetch) {
            return [];
        }

        if (! $classConstFetch->class instanceof Node\Name) {
            return [];
        }

        if (! $classConstFetch->name instanceof Node\Identifier || $classConstFetch->name->name !== 'class') {
            return [];
        }

        if ($scope->isInClass()) {
            $classRef = $scope->getClassReflection();

            if (
                $this->reflectionProvider->hasClass(TestCase::class)
                && $classRef->isSubclassOfClass($this->reflectionProvider->getClass(TestCase::class))
            ) {
                return []; // skip uses in test classes as tests are internal
            }
        }

        $returnType = $this->factoriesReturnTypeHelper->check($scope->getType($classConstFetch), $function);

        if ($returnType->isNull()->yes()) {
            return [];
        }

        $reflections = $returnType->getObjectClassReflections();

        if ($reflections === []) {
            return [];
        }

        $reflection = $reflections[0];

        if ($reflection->getNativeReflection()->getNamespaceName() === self::$namespaceMap[$function]) {
            return [];
        }

        $fileNamespace = $scope->getNamespace();

        if (
            $fileNamespace !== null
            && ((defined('APP_NAMESPACE') && str_starts_with($fileNamespace, APP_NAMESPACE))
                || str_starts_with($fileNamespace, 'App\\'))
        ) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Call to function %s with %s::class is discouraged.',
                $function,
                $reflection->getDisplayName(),
            ))->tip(sprintf(
                'Use %s(\'%s\') instead to allow overriding.',
                $function,
                $reflection->getNativeReflection()->getShortName(),
            ))->identifier('codeigniter.factoriesClassConstFetch')->build(),
        ];
    }
}
