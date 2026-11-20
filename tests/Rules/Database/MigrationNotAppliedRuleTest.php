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

namespace CodeIgniter\PHPStan\Tests\Rules\Database;

use CodeIgniter\PHPStan\Database\SchemaProvider;
use CodeIgniter\PHPStan\Rules\Database\MigrationNotAppliedRule;
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesProvider;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @extends RuleTestCase<MigrationNotAppliedRule>
 *
 * @internal
 */
#[Group('static-analysis')]
final class MigrationNotAppliedRuleTest extends RuleTestCase
{
    use AdditionalConfigFilesProvider;

    protected function getRule(): Rule
    {
        return new MigrationNotAppliedRule(self::getContainer()->getByType(SchemaProvider::class));
    }

    public function testReportsOnlyTheMigrationThatFailed(): void
    {
        $this->analyse([__DIR__ . '/../../data/rules/migration-not-applied.php'], [
            [
                'Migration CodeIgniter\PHPStan\Tests\Fixtures\Database\Migrations\FailBlogTable failed on the schema SQLite3 connection, so its tables are missing from the inferred schema: Simulated non-portable migration.',
                18,
                'Types that depend on its tables fall back to mixed. If the migration uses SQL that SQLite3 does not support, ignore this error.',
            ],
        ]);
    }
}
