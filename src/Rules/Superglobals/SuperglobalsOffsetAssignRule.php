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
use PHPStan\Type\VerbosityLevel;

/**
 * @implements Rule<Node\Expr\Assign>
 */
final class SuperglobalsOffsetAssignRule implements Rule
{
    public function __construct(
        private readonly SuperglobalsHelper $superglobalsHelper,
    ) {}

    public function getNodeType(): string
    {
        return Node\Expr\Assign::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->var instanceof Node\Expr\ArrayDimFetch) {
            return [];
        }

        $dimFetch = $node->var;

        if ($dimFetch->dim === null) {
            return [];
        }

        $dimType = $scope->getType($dimFetch->dim);

        if ($dimType->isString()->no()) {
            return [];
        }

        if (! $dimFetch->var instanceof Node\Expr\Variable) {
            return [];
        }

        $varName = $dimFetch->var->name;

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

        $methodSetter = $this->superglobalsHelper->getMethodSetter($varName);

        if ($methodSetter === null) {
            return [];
        }

        $errors = [];

        $expr = $scope->getType($node->expr)->describe(VerbosityLevel::precise());

        foreach ($dimType->getConstantStrings() as $dimStringType) {
            $dimString = $dimStringType->getValue();

            $errors[] = RuleErrorBuilder::message(sprintf(
                'Direct assignment of %s to $%s[\'%s\'] is not allowed.',
                $expr,
                $varName,
                $dimString,
            ))->identifier('codeigniter.superglobalsOffsetAssign')
                ->tip(sprintf(
                    'Use service(\'superglobals\')->%s(\'%s\', %s) instead.',
                    $methodSetter,
                    $dimString,
                    $expr,
                ))
                ->build();
        }

        if ($errors !== []) {
            return $errors;
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Direct assignment of %s to string offset of $%s is not allowed.',
                $expr,
                $varName,
            ))->identifier('codeigniter.superglobalsOffsetAssign')
                ->tip(sprintf('Use service(\'superglobals\')->%s(<key>, <value>) instead.', $methodSetter))
                ->build(),
        ];
    }
}
