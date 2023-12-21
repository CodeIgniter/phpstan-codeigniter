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

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;

/**
 * @implements Rule<Node\Expr\ArrayDimFetch>
 */
final class SuperglobalAccessRule implements Rule
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
        return Node\Expr\ArrayDimFetch::class;
    }

    /**
     * @param Node\Expr\ArrayDimFetch $node
     *
     * @return list<RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($scope->isInExpressionAssign($node)) {
            return [];
        }

        if (! $node->var instanceof Node\Expr\Variable) {
            return [];
        }

        $name = $node->var->name;

        if (! is_string($name)) {
            return [];
        }

        if (! $this->superglobalRuleHelper->isHandledSuperglobal($name)) {
            return [];
        }

        if ($scope->getFunction() === null) {
            return []; // ignore uses in root level (not inside function or method)
        }

        if ($node->dim === null) {
            return [];
        }

        $dimType = $scope->getType($node->dim);

        if ($dimType->isString()->no()) {
            return [];
        }

        $method = $this->superglobalRuleHelper->getSuperglobalMethodGetter($name);
        $errors = [];

        if ($dimType->getConstantStrings() !== []) {
            foreach ($dimType->getConstantStrings() as $dimString) {
                $dim = $dimString->getValue();

                $errors[] = RuleErrorBuilder::message(sprintf('Accessing offset \'%s\' directly on $%s is discouraged.', $dim, $name))
                    ->tip(sprintf('Use \\Config\\Services::superglobals()->%s(\'%s\') instead.', $method, $dim))
                    ->identifier('codeigniter.superglobalAccess')
                    ->build();
            }

            return $errors;
        }

        $dim = $dimType->describe(VerbosityLevel::precise());

        return [
            RuleErrorBuilder::message(sprintf('Accessing offset %s directly on $%s is discouraged.', $dim, $name))
                ->tip(sprintf('Use \\Config\\Services::superglobals()->%s() instead.', $method))
                ->identifier('codeigniter.superglobalAccess')
                ->build(),
        ];
    }
}
