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

namespace CodeIgniter\PHPStan\Tests\Database;

use CodeIgniter\Database\SQLite3\Connection;
use CodeIgniter\PHPStan\Database\SchemaConnectionFactory;
use CodeIgniter\PHPStan\Database\SchemaIntrospector;
use CodeIgniter\PHPStan\Database\SchemaMigrator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Group('unit')]
final class SchemaMigratorTest extends TestCase
{
    private const FIXTURE_NAMESPACE = 'CodeIgniter\\PHPStan\\Tests\\Fixtures';

    private string $databasePath;
    private Connection $db;

    protected function setUp(): void
    {
        parent::setUp();

        service('autoloader')->addNamespace(self::FIXTURE_NAMESPACE, dirname(__DIR__) . '/Fixtures');

        $this->databasePath = dirname(__DIR__, 2) . '/tmp/' . uniqid('schema-migrator-', true) . '.sqlite';

        $this->db = (new SchemaConnectionFactory())->create($this->databasePath);
    }

    protected function tearDown(): void
    {
        $this->db->close();

        if (is_file($this->databasePath)) {
            unlink($this->databasePath);
        }

        parent::tearDown();
    }

    public function testRunsMigrationsResilientlyAndReturnsFingerprint(): void
    {
        $migrator    = new SchemaMigrator();
        $fingerprint = $migrator->fingerprint($this->db, self::FIXTURE_NAMESPACE);

        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $fingerprint);

        $migrator->migrate($this->db, self::FIXTURE_NAMESPACE);

        $schema = (new SchemaIntrospector())->introspect($this->db, $fingerprint);

        // Plain migrations are applied, including the one after the failing migration.
        self::assertNotNull($schema->getTable('blog_users'));
        self::assertNotNull($schema->getTable('blog_posts'));

        // A migration pinned to another database group is skipped.
        self::assertNull($schema->getTable('blog_groups'));
    }

    public function testNullNamespaceScansEveryRegisteredNamespace(): void
    {
        $migrator    = new SchemaMigrator();
        $fingerprint = $migrator->fingerprint($this->db, null);
        $migrator->migrate($this->db, null);

        $schema = (new SchemaIntrospector())->introspect($this->db, $fingerprint);

        // The fixture migrations live outside the app namespace, so finding them proves a null
        // namespace scans all registered namespaces rather than only `App\Database\Migrations`.
        self::assertNotNull($schema->getTable('blog_users'));
    }
}
