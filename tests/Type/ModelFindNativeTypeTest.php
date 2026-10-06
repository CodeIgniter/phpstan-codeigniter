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
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @extends RuleTestCase<Rule<Return_>>
 *
 * @internal
 */
#[Group('static-analysis')]
final class ModelFindNativeTypeTest extends RuleTestCase
{
    use AdditionalConfigFilesProvider;

    protected function getRule(): Rule
    {
        return new /**
         * @implements Rule<Return_>
         */ class () implements Rule {
            public function getNodeType(): string
            {
                return Return_::class;
            }

            public function processNode(Node $node, Scope $scope): array
            {
                if ($node->expr !== null) {
                    // A clone has no stored result, so PHPStan resolves it on demand as Rector does.
                    $scope->getNativeType(clone $node->expr);
                }

                return [];
            }
        };
    }

    public function testNativeTypeOfModelFetchOnNativelyMixedReceiver(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/Models/ChainedFetchModel.php'], []);
    }
}
