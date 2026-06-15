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
use Config\Database;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Group('unit')]
final class SchemaIntrospectorTest extends TestCase
{
    private string $databasePath;
    private Connection $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->databasePath = dirname(__DIR__, 2) . '/tmp/' . uniqid('schema-introspector-', true) . '.sqlite';

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

    public function testIntrospectsTablesAndColumns(): void
    {
        $forge = Database::forge($this->db);
        $forge->addField([
            'id'     => ['type' => 'INTEGER', 'auto_increment' => true],
            'name'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'email'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'active' => ['type' => 'BOOLEAN', 'default' => 1],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('users');

        $schema = (new SchemaIntrospector())->introspect($this->db, 'fingerprint');

        self::assertSame('fingerprint', $schema->hash);

        $table = $schema->getTable('users');
        self::assertNotNull($table);
        self::assertSame(['id', 'name', 'email', 'active'], array_keys($table->columns));

        $id = $table->getColumn('id');
        self::assertNotNull($id);
        self::assertTrue($id->primaryKey);
        self::assertSame('INTEGER', $id->type);

        $name = $table->getColumn('name');
        self::assertNotNull($name);
        self::assertFalse($name->nullable);
        self::assertSame('VARCHAR', $name->type);

        $email = $table->getColumn('email');
        self::assertNotNull($email);
        self::assertTrue($email->nullable);

        // SQLite has no native boolean: the SQLite3 Forge remaps BOOLEAN to INT.
        $active = $table->getColumn('active');
        self::assertNotNull($active);
        self::assertFalse($active->primaryKey);
        self::assertSame('INT', $active->type);
        self::assertSame('1', $active->default);
    }

    public function testIgnoresTheMigrationsTable(): void
    {
        $forge = Database::forge($this->db);
        $forge->addField([
            'id'      => ['type' => 'INTEGER', 'auto_increment' => true],
            'version' => ['type' => 'VARCHAR', 'constraint' => 255],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('migrations');

        $schema = (new SchemaIntrospector())->introspect($this->db, 'fingerprint');

        self::assertNull($schema->getTable('migrations'));
    }
}
