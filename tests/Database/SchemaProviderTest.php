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

use CodeIgniter\PHPStan\Database\SchemaCache;
use CodeIgniter\PHPStan\Database\SchemaConnectionFactory;
use CodeIgniter\PHPStan\Database\SchemaIntrospector;
use CodeIgniter\PHPStan\Database\SchemaMigrator;
use CodeIgniter\PHPStan\Database\SchemaProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Group('unit')]
final class SchemaProviderTest extends TestCase
{
    private const FIXTURE_NAMESPACE = 'CodeIgniter\\PHPStan\\Tests\\Fixtures';

    private string $cacheDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        service('autoloader')->addNamespace(self::FIXTURE_NAMESPACE, dirname(__DIR__) . '/Fixtures');

        $this->cacheDirectory = dirname(__DIR__, 2) . '/tmp/' . uniqid('schema-provider-', true);
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

    public function testProvidesTheSchemaAndMemoizesIt(): void
    {
        $cache = new SchemaCache(
            $this->cacheDirectory,
            new SchemaConnectionFactory(),
            new SchemaMigrator(),
            new SchemaIntrospector(),
        );
        $provider = new SchemaProvider($cache, self::FIXTURE_NAMESPACE);

        $first  = $provider->get();
        $second = $provider->get();

        self::assertNotNull($first->getTable('blog_users'));
        self::assertSame($first, $second);
    }
}
