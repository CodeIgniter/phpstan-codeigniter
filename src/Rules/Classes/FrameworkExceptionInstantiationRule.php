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

namespace CodeIgniter\PHPStan\Rules\Classes;

use CodeIgniter\Exceptions\FrameworkException;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<Node\Expr\New_>
 */
final class FrameworkExceptionInstantiationRule implements Rule
{
    public function getNodeType(): string
    {
        return Node\Expr\New_::class;
    }

    /**
     * @param Node\Expr\New_ $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $class = $node->class;

        if (! $class instanceof Node\Name) {
            return [];
        }

        $objectType = new ObjectType($class->toString());

        if (! (new ObjectType(FrameworkException::class))->isSuperTypeOf($objectType)->yes()) {
            return [];
        }

        if ($objectType->getClassReflection() === null) {
            throw new ShouldNotHappenException();
        }

        return [RuleErrorBuilder::message(sprintf(
            'Instantiating %s using new is not allowed. Use one of its named constructors instead.',
            $objectType->getClassReflection()->getNativeReflection()->getShortName()
        ))->identifier('codeigniter.frameworkExceptionInstance')->build()];
    }
}
