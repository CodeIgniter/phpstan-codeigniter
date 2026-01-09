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
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\Printer\ExprPrinter;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;

/**
 * @implements Rule<Node\Stmt\Unset_>
 */
final class SuperglobalsOffsetUnsetRule implements Rule
{
    public function __construct(
        private readonly SuperglobalsHelper $superglobalsHelper,
        private readonly ExprPrinter $exprPrinter,
    ) {}

    public function getNodeType(): string
    {
        return Node\Stmt\Unset_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];

        foreach ($node->vars as $var) {
            if (! $var instanceof Node\Expr\ArrayDimFetch) {
                continue;
            }

            if (! $var->var instanceof Node\Expr\Variable) {
                continue;
            }

            $varName = $var->var->name;

            if (! is_string($varName)) {
                continue;
            }

            if (! $this->superglobalsHelper->isHandledSuperglobal($varName, $scope)) {
                continue;
            }

            if ($var->dim === null) {
                continue;
            }

            $dimType = $scope->getType($var->dim);

            if ($dimType->isString()->no()) {
                continue;
            }

            if ($var->getAttribute(UnsetOnSuperglobalsDimFetchVisitor::VISITOR_KEY) !== true) {
                continue;
            }

            $methodUnsetter = $this->superglobalsHelper->getMethodUnsetter($varName);

            if ($methodUnsetter === null) {
                continue;
            }

            if (count($dimType->getConstantStrings()) === 1) {
                $value = $dimType->describe(VerbosityLevel::precise());
            } else {
                $value = $this->exprPrinter->printExpr($var->dim);
            }

            $errors[] = RuleErrorBuilder::message(sprintf('Direct unset of $%s[%s] is not allowed.', $varName, $value))
                ->identifier('codeigniter.superglobalsOffsetUnset')
                ->tip(sprintf('Use service(\'superglobals\')->%s(%s) instead.', $methodUnsetter, $value))
                ->line($var->getStartLine())
                ->build();
        }

        return $errors;
    }
}
