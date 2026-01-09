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
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node\Expr\ArrayDimFetch>
 */
final class SuperglobalsOffsetAccessRule implements Rule
{
    public function __construct(
        private readonly SuperglobalsHelper $superglobalsHelper,
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

        if ($node->dim === null) {
            return [];
        }

        $dimType = $scope->getType($node->dim);

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

        $errors = [];

        foreach ($dimType->getConstantStrings() as $dimStringType) {
            $dimString = $dimStringType->getValue();

            $errors[] = RuleErrorBuilder::message(sprintf(
                'Direct access to $%s[\'%s\'] is not allowed.',
                $varName,
                $dimString,
            ))->tip(sprintf('Use service(\'superglobals\')->%s(\'%s\') instead.', $methodGetter, $dimString))
                ->identifier('codeigniter.superglobalsOffsetAccess')
                ->build();
        }

        if ($errors !== []) {
            return $errors;
        }

        return [
            RuleErrorBuilder::message(sprintf('Accessing $%s directly with string key is not allowed.', $varName))
                ->tip(sprintf('Use service(\'superglobals\')->%s(<key>) instead.', $methodGetter))
                ->identifier('codeigniter.superglobalsOffsetAccess')
                ->build(),
        ];
    }
}
