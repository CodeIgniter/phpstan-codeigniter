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

namespace CodeIgniter\PHPStan\Rules\Superglobals;

use CodeIgniter\Superglobals;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;

/**
 * @implements Rule<Node\Expr\Assign>
 */
final class SuperglobalAssignRule implements Rule
{
    /**
     * @readonly
     */
    private SuperglobalRuleHelper $superglobalRuleHelper;

    public function __construct(SuperglobalRuleHelper $superglobalRuleHelper)
    {
        $this->superglobalRuleHelper = $superglobalRuleHelper;
    }

    public function getNodeType(): string
    {
        return Node\Expr\Assign::class;
    }

    /**
     * @param Node\Expr\Assign $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->var instanceof Node\Expr\ArrayDimFetch) {
            return $this->processArrayDimFetch($node, $scope);
        }

        if ($node->var instanceof Node\Expr\Variable) {
            return $this->processVariableExpr($node, $scope);
        }

        return [];
    }

    /**
     * @param Node\Expr\Assign $node
     *
     * @return RuleError[]
     */
    private function processArrayDimFetch(Node $node, Scope $scope): array
    {
        assert($node->var instanceof Node\Expr\ArrayDimFetch);

        $arrayDimFetch = $node->var;

        if ($arrayDimFetch->dim === null) {
            return [];
        }

        $dimType = $scope->getType($arrayDimFetch->dim);

        if ($dimType->isString()->no()) {
            return [];
        }

        if (! $arrayDimFetch->var instanceof Node\Expr\Variable) {
            return [];
        }

        $name = $arrayDimFetch->var->name;

        if (! is_string($name)) {
            return [];
        }

        if (! $this->superglobalRuleHelper->isHandledSuperglobal($name)) {
            return [];
        }

        if ($scope->getFunction() === null) {
            return []; // ignore uses in root level (not inside function or method)
        }

        if ($scope->isInClass() && $scope->getClassReflection()->getName() === Superglobals::class) {
            return [];
        }

        $exprType = $scope->getType($node->expr);

        $expr   = $exprType->describe(VerbosityLevel::precise());
        $dim    = $dimType->describe(VerbosityLevel::precise());
        $method = $this->superglobalRuleHelper->getSuperglobalMethodSetter($name);

        $addTip = static function (RuleErrorBuilder $ruleErrorBuilder) use ($method, $dimType, $exprType): RuleErrorBuilder {
            if ($dimType->getConstantStrings() !== [] && $exprType->getConstantStrings() !== []) {
                foreach ($dimType->getConstantStrings() as $dimString) {
                    foreach ($exprType->getConstantStrings() as $exprString) {
                        $ruleErrorBuilder->addTip(sprintf(
                            'Use \\Config\\Services::superglobals()->%s(%s, %s) instead.',
                            $method,
                            $dimString->describe(VerbosityLevel::precise()),
                            $exprString->describe(VerbosityLevel::precise())
                        ));
                    }
                }

                return $ruleErrorBuilder;
            }

            return $ruleErrorBuilder->tip(sprintf('Use \\Config\\Services::superglobals()->%s() instead.', $method));
        };

        return [
            $addTip(RuleErrorBuilder::message(sprintf('Assigning %s directly on offset %s of $%s is discouraged.', $expr, $dim, $name)))
                ->identifier('codeigniter.superglobalAccessAssign')
                ->build(),
        ];
    }

    /**
     * @param Node\Expr\Assign $node
     *
     * @return RuleError[]
     */
    private function processVariableExpr(Node $node, Scope $scope): array
    {
        assert($node->var instanceof Node\Expr\Variable);

        $name = $node->var->name;

        if (! is_string($name)) {
            return [];
        }

        if (! $this->superglobalRuleHelper->isHandledSuperglobal($name)) {
            return [];
        }

        if ($name !== '_GET') {
            return [];
        }

        if ($scope->isInClass() && $scope->getClassReflection()->getName() === Superglobals::class) {
            return [];
        }

        $exprType = $scope->getType($node->expr);

        if (! $exprType->isArray()->yes()) {
            return [
                RuleErrorBuilder::message(sprintf('Cannot re-assign non-arrays to $_GET, got %s.', $exprType->describe(VerbosityLevel::typeOnly())))
                    ->identifier('codeigniter.getReassignNonarray')
                    ->build(),
            ];
        }

        if ($scope->getFunction() === null) {
            return []; // ignore uses in root level (not inside function or method)
        }

        return [
            RuleErrorBuilder::message('Re-assigning arrays to $_GET directly is discouraged.')
                ->tip('Use \\Config\\Services::superglobals()->setGetArray() instead.')
                ->identifier('codeigniter.getReassignArray')
                ->build(),
        ];
    }
}
