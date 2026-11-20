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

namespace CodeIgniter\PHPStan\Tests\Type;

use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesProvider;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\MixedType;
use PHPUnit\Framework\Attributes\Group;

/**
 * @extends RuleTestCase<Rule<Node\Expr\Assign>>
 *
 * @internal
 */
#[Group('static-analysis')]
final class ExplicitMixedTest extends RuleTestCase
{
    use AdditionalConfigFilesProvider;

    protected function getRule(): Rule
    {
        return new /**
         * @implements Rule<Node\Expr\Assign>
         */ class () implements Rule {
            public function getNodeType(): string
            {
                return Node\Expr\Assign::class;
            }

            public function processNode(Node $node, Scope $scope): array
            {
                $type = $scope->getType($node->expr);

                if (! $type instanceof MixedType || $type->isExplicitMixed()) {
                    return [];
                }

                return [RuleErrorBuilder::message('Implicit mixed.')->identifier('test.implicitMixed')->build()];
            }
        };
    }

    public function testUnknownTypesAreExplicitMixed(): void
    {
        $this->analyse([__DIR__ . '/../data/rules/explicit-mixed.php'], []);
    }
}
