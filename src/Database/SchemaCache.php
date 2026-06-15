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

use CodeIgniter\PHPStan\Database\Schema\Schema;

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
            $fingerprint = $this->migrator->fingerprint($db, $namespace);

            $cached = is_file($cacheFile) ? include $cacheFile : null;

            if ($cached instanceof Schema && $cached->hash === $fingerprint) {
                return $cached;
            }

            $this->migrator->migrate($db, $namespace);
            $schema = $this->introspector->introspect($db, $fingerprint);
            $this->store($cacheFile, $schema);

            return $schema;
        } finally {
            $db->close();

            if (is_file($databasePath)) {
                unlink($databasePath);
            }
        }
    }

    private function store(string $cacheFile, Schema $schema): void
    {
        $temporaryFile = $cacheFile . '.' . uniqid('', true) . '.tmp';

        file_put_contents($temporaryFile, '<?php return ' . var_export($schema, true) . ";\n");
        rename($temporaryFile, $cacheFile);
    }
}
