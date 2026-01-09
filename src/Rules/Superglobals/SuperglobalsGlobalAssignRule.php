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
use CodeIgniter\PHPStan\NodeVisitor\ConsecutiveAssignsVisitor;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\Printer\ExprPrinter;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;

/**
 * @implements Rule<Node\Expr\Assign>
 */
final class SuperglobalsGlobalAssignRule implements Rule
{
    public function __construct(
        private readonly SuperglobalsHelper $superglobalsHelper,
        private readonly ExprPrinter $exprPrinter,
    ) {}

    public function getNodeType(): string
    {
        return Node\Expr\Assign::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
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

        $exprType = $scope->getType($node->expr);

        if (! $exprType->isArray()->yes()) {
            return [
                RuleErrorBuilder::message(sprintf('Cannot assign %s type to $%s.', $exprType->describe(VerbosityLevel::typeOnly()), $varName))
                    ->identifier('codeigniter.superglobalsGlobalAssignNonArray')
                    ->build(),
            ];
        }

        $methodGlobalSetter = $this->superglobalsHelper->getMethodGlobalSetter($varName);

        $expr = $node->expr;

        while ($expr instanceof Node\Expr\Assign) {
            $expr = $expr->expr;
        }

        $builder = RuleErrorBuilder::message(sprintf('Direct global assignment to $%s is not allowed.', $varName))
            ->identifier('codeigniter.superglobalsGlobalAssign')
            ->tip(sprintf('Use service(\'superglobals\')->%s(%s) instead.', $methodGlobalSetter, $this->exprPrinter->printExpr($expr)));

        if ($node->getAttribute(ConsecutiveAssignsVisitor::VISITOR_KEY) !== true) {
            $builder = $builder->fixNode($node, static fn (): Node\Expr\MethodCall => new Node\Expr\MethodCall(
                new Node\Expr\FuncCall(
                    new Node\Name('service'),
                    [new Node\Arg(new Node\Scalar\String_('superglobals'))],
                ),
                new Node\Identifier($methodGlobalSetter),
                [new Node\Arg($expr)],
            ));
        }

        return [$builder->build()];
    }
}
