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

use CodeIgniter\PHPStan\Helpers\SuperglobalsHelper;
use CodeIgniter\PHPStan\NodeVisitor\UnsetOnSuperglobalsDimFetchVisitor;
use CodeIgniter\PHPStan\NodeVisitor\VariableContextOnSuperglobalsDimFetchVisitor;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\DependencyInjection\RegisteredRule;
use PHPStan\Node\Printer\ExprPrinter;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;

/**
 * @implements Rule<Node\Expr\ArrayDimFetch>
 */
#[RegisteredRule(level: 0)]
final class SuperglobalsOffsetAccessRule implements Rule
{
    public function __construct(
        private readonly SuperglobalsHelper $superglobalsHelper,
        private readonly ExprPrinter $exprPrinter,
    ) {}

    public function getNodeType(): string
    {
        return Node\Expr\ArrayDimFetch::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($scope->isInExpressionAssign($node)) {
            return [];
        }

        if (! $node->var instanceof Node\Expr\Variable) {
            return [];
        }

        $varName = $node->var->name;

        if (! is_string($varName)) {
            return [];
        }

        if (! $this->superglobalsHelper->isHandledSuperglobal($varName, $scope)) {
            return [];
        }

        $dim = $node->dim;

        if ($dim === null) {
            return [];
        }

        $dimType = $scope->getType($dim);

        if ($dimType->isString()->no()) {
            return [];
        }

        if ($node->getAttribute(UnsetOnSuperglobalsDimFetchVisitor::VISITOR_KEY) === true) {
            return [];
        }

        $methodGetter = $this->superglobalsHelper->getMethodGetter($varName);

        if ($methodGetter === null) {
            return [];
        }

        if (count($dimType->getConstantStrings()) === 1) {
            $value = $dimType->describe(VerbosityLevel::precise());
        } else {
            $value = $this->exprPrinter->printExpr($dim);
        }

        $builder = RuleErrorBuilder::message(sprintf('Direct access to $%s[%s] is not allowed.', $varName, $value))
            ->identifier('codeigniter.superglobalsOffsetAccess');

        $context = $node->getAttribute(VariableContextOnSuperglobalsDimFetchVisitor::VISITOR_KEY);

        $isset = $node->getAttribute(VariableContextOnSuperglobalsDimFetchVisitor::ISSET_KEY);

        if ($isset instanceof Node\Expr\Isset_) {
            return [
                $builder
                    ->tip(sprintf('Use single_service(\'superglobals\')->%s(%s) !== null instead.', $methodGetter, $value))
                    ->fixNode($isset, static fn (): Node\Expr\BinaryOp\NotIdentical => new Node\Expr\BinaryOp\NotIdentical(
                        new Node\Expr\MethodCall(
                            new Node\Expr\FuncCall(
                                new Node\Name('single_service'),
                                [new Node\Arg(new Node\Scalar\String_('superglobals'))],
                            ),
                            new Node\Identifier($methodGetter),
                            [new Node\Arg($dim)],
                        ),
                        new Node\Expr\ConstFetch(new Node\Name('null')),
                    ))
                    ->build(),
            ];
        }

        if ($context !== null) {
            return [$builder->build()];
        }

        return [
            $builder
                ->tip(sprintf('Use single_service(\'superglobals\')->%s(%s) instead.', $methodGetter, $value))
                ->fixNode($node, static fn (): Node\Expr\MethodCall => new Node\Expr\MethodCall(
                    new Node\Expr\FuncCall(
                        new Node\Name('single_service'),
                        [new Node\Arg(new Node\Scalar\String_('superglobals'))],
                    ),
                    new Node\Identifier($methodGetter),
                    [new Node\Arg($dim)],
                ))
                ->build(),
        ];
    }
}
