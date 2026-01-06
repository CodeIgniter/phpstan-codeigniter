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
final class SuperglobalsGlobalAssignRule implements Rule
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

        $exprType = $scope->getType($node->expr);

        if (! $exprType->isArray()->yes()) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Cannot assign %s type to $%s.',
                    $exprType->describe(VerbosityLevel::typeOnly()),
                    $varName,
                ))->identifier('codeigniter.superglobalsGlobalAssignNonArray')->build(),
            ];
        }

        $methodGlobalSetter = $this->superglobalsHelper->getMethodGlobalSetter($varName);

        return [
            RuleErrorBuilder::message(sprintf('Direct global assignment to $%s is not allowed.', $varName))
                ->identifier('codeigniter.superglobalsGlobalAssign')
                ->tip(sprintf('Use service(\'superglobals\')->%s($array) instead.', $methodGlobalSetter))
                ->build(),
        ];
    }
}
