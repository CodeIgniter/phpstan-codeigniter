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

namespace CodeIgniter\PHPStan\Rules\Database;

use CodeIgniter\Database\Migration;
use CodeIgniter\PHPStan\Database\SchemaProvider;
use CodeIgniter\PHPStan\Helpers\ResultCacheDependencyTracker;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\DependencyInjection\RegisteredRule;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<InClassNode>
 */
#[RegisteredRule(level: 0)]
final class MigrationNotAppliedRule implements Rule
{
    public function __construct(
        private readonly SchemaProvider $schemaProvider,
        private readonly ResultCacheDependencyTracker $resultCacheDependencyTracker,
    ) {}

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $node->getClassReflection();

        if (! $classReflection->is(Migration::class) || $classReflection->isAbstract()) {
            return [];
        }

        $this->resultCacheDependencyTracker->trackSchema($scope);

        $message = $this->schemaProvider->get()->failedMigrations[$classReflection->getName()] ?? null;

        if ($message === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Migration %s failed on the schema SQLite3 connection, so its tables are missing from the inferred schema: %s',
                $classReflection->getDisplayName(),
                $message,
            ))
                ->identifier('codeigniter.migrationNotApplied')
                ->tip('Types that depend on its tables fall back to mixed. If the migration uses SQL that SQLite3 does not support, ignore this error.')
                ->build(),
        ];
    }
}
