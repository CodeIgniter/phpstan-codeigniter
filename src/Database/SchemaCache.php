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

namespace CodeIgniter\PHPStan\Database;

use CodeIgniter\Database\SQLite3\Connection;
use CodeIgniter\PHPStan\Database\Schema\Schema;
use stdClass;

/**
 * Builds and caches the project's database schema as a var_export'd PHP file, rebuilding it when
 * the migration fingerprint changes.
 */
final class SchemaCache
{
    public function __construct(
        private readonly string $cacheDirectory,
        private readonly SchemaConnectionFactory $connectionFactory,
        private readonly SchemaMigrator $migrator,
        private readonly SchemaIntrospector $introspector,
    ) {}

    public function get(?string $namespace = null): Schema
    {
        $cacheFile    = $this->cacheDirectory . '/schema.php';
        $databasePath = $this->cacheDirectory . '/' . uniqid('schema-build-', true) . '.sqlite';
        $db           = $this->connectionFactory->create($databasePath);

        try {
            $migrations  = $this->migrator->discover($db, $namespace);
            $fingerprint = $this->migrator->fingerprint($migrations);

            $cached = $this->load($cacheFile);

            if ($cached !== null && $cached->hash === $fingerprint) {
                return $cached;
            }

            return $this->build($cacheFile, $db, $migrations, $fingerprint);
        } finally {
            $db->close();

            if (is_file($databasePath)) {
                unlink($databasePath);
            }
        }
    }

    /**
     * Builds the schema under an exclusive lock, then reads back any matching cache a concurrent worker
     * wrote while this one waited for the lock, so only the first worker on a cold cache runs the migrations.
     *
     * @param list<stdClass> $migrations
     */
    private function build(string $cacheFile, Connection $db, array $migrations, string $fingerprint): Schema
    {
        $lock = fopen(sprintf('%s.lock', $cacheFile), 'cb');

        if ($lock !== false) {
            flock($lock, LOCK_EX);
        }

        try {
            $cached = $this->load($cacheFile);

            if ($cached !== null && $cached->hash === $fingerprint) {
                return $cached;
            }

            $this->migrator->migrate($db, $migrations);
            $schema = $this->introspector->introspect($db, $fingerprint);
            $this->store($cacheFile, $schema);

            return $schema;
        } finally {
            if ($lock !== false) {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        }
    }

    private function load(string $cacheFile): ?Schema
    {
        $cached = is_file($cacheFile) ? include $cacheFile : null;

        return $cached instanceof Schema ? $cached : null;
    }

    private function store(string $cacheFile, Schema $schema): void
    {
        $temporaryFile = sprintf('%s.%s.tmp', $cacheFile, uniqid('', true));

        file_put_contents($temporaryFile, sprintf("<?php\n\nreturn %s;\n", var_export($schema, true)));
        rename($temporaryFile, $cacheFile);
    }
}
