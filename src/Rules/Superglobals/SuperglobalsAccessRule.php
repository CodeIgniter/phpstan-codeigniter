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
use CodeIgniter\Superglobals;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node\Expr\ArrayDimFetch>
 */
final class SuperglobalsAccessRule implements Rule
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

        if (! array_key_exists($varName, SuperglobalsHelper::HANDLED_SUPERGLOBALS)) {
            return [];
        }

        if ($scope->getFunction() === null) {
            return []; // ignore uses in root level (not inside function or method)
        }

        if ($scope->isInClass() && $scope->getClassReflection()->getName() === Superglobals::class) {
            return []; // ignore assignments inside `Superglobals`
        }

        if ($node->dim === null) {
            return [];
        }

        $dimType = $scope->getType($node->dim);

        if ($dimType->isString()->no()) {
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
                'Accessing $%s directly with key \'%s\' is not allowed.',
                $varName,
                $dimString,
            ))->tip(sprintf('Use service(\'superglobals\')->%s(\'%s\') instead.', $methodGetter, $dimString))
                ->identifier('codeigniter.superglobalsAccess')
                ->build();
        }

        if ($errors !== []) {
            return $errors;
        }

        return [
            RuleErrorBuilder::message(sprintf('Accessing $%s directly with string key is not allowed.', $varName))
                ->tip(sprintf('Use service(\'superglobals\')->%s(<key>) instead.', $methodGetter))
                ->identifier('codeigniter.superglobalsAccess')
                ->build(),
        ];
    }
}
