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

use CodeIgniter\PHPStan\Database\Schema\Schema;
use CodeIgniter\PHPStan\Database\Schema\Table;
use CodeIgniter\PHPStan\Database\SchemaCache;
use CodeIgniter\PHPStan\Database\SchemaConnectionFactory;
use CodeIgniter\PHPStan\Database\SchemaIntrospector;
use CodeIgniter\PHPStan\Database\SchemaMigrator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Group('unit')]
final class SchemaCacheTest extends TestCase
{
    private const FIXTURE_NAMESPACE = 'CodeIgniter\\PHPStan\\Tests\\Fixtures';

    private string $cacheDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        service('autoloader')->addNamespace(self::FIXTURE_NAMESPACE, dirname(__DIR__) . '/Fixtures');

        $this->cacheDirectory = dirname(__DIR__, 2) . '/tmp/' . uniqid('schema-cache-', true);
        mkdir($this->cacheDirectory, 0775, true);
    }

    protected function tearDown(): void
    {
        $files = glob($this->cacheDirectory . '/*');

        if ($files !== false) {
            foreach ($files as $file) {
                unlink($file);
            }
        }

        rmdir($this->cacheDirectory);

        parent::tearDown();
    }

    public function testBuildsSchemaOnMissAndWritesCacheFile(): void
    {
        $schema = $this->createCache()->get(self::FIXTURE_NAMESPACE);

        self::assertNotNull($schema->getTable('blog_users'));
        self::assertNotNull($schema->getTable('blog_posts'));
        self::assertFileExists($this->cacheDirectory . '/schema.php');
    }

    public function testReadsFromCacheOnHitWithoutRebuilding(): void
    {
        $cache = $this->createCache();

        $built = $cache->get(self::FIXTURE_NAMESPACE);

        // Overwrite the cache file with a sentinel that keeps the same fingerprint. A second get()
        // must return it, which is only possible if it reads the cache instead of rebuilding.
        $sentinel = new Schema(hash: $built->hash, tables: ['sentinel' => new Table('sentinel', [])]);
        $this->writeCache($sentinel);

        $reloaded = $cache->get(self::FIXTURE_NAMESPACE);

        self::assertNotNull($reloaded->getTable('sentinel'));
        self::assertNull($reloaded->getTable('blog_users'));
    }

    public function testRebuildsWhenTheFingerprintChanges(): void
    {
        // Seed the cache with a schema whose hash no longer matches the migration fingerprint.
        $this->writeCache(new Schema(hash: 'stale-fingerprint', tables: ['old' => new Table('old', [])]));

        $schema = $this->createCache()->get(self::FIXTURE_NAMESPACE);

        self::assertNull($schema->getTable('old'));
        self::assertNotNull($schema->getTable('blog_users'));
        self::assertNotSame('stale-fingerprint', $schema->hash);
    }

    private function writeCache(Schema $schema): void
    {
        file_put_contents($this->cacheDirectory . '/schema.php', '<?php return ' . var_export($schema, true) . ';');
    }

    private function createCache(): SchemaCache
    {
        return new SchemaCache(
            $this->cacheDirectory,
            new SchemaConnectionFactory(),
            new SchemaMigrator(),
            new SchemaIntrospector(),
        );
    }
}
