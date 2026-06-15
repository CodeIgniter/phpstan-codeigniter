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

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\MigrationRunner;
use CodeIgniter\Database\SQLite3\Connection;
use Config\Database;
use Config\Migrations;
use ReflectionClass;
use stdClass;
use Throwable;

/**
 * Discovers and runs the project's migrations against a schema connection, skipping migrations
 * pinned to another database group and tolerating migrations that fail to run.
 */
final class SchemaMigrator
{
    /**
     * Fingerprint of the migration set, computed from the files without running them.
     */
    public function fingerprint(Connection $db, ?string $namespace = null): string
    {
        $fingerprint = '';

        foreach ($this->discoverMigrations($db, $namespace) as $migration) {
            $path = $migration->path;
            $uid  = $migration->uid;

            if (! is_string($path) || ! is_string($uid)) {
                continue;
            }

            $fileHash = md5_file($path);
            $fingerprint .= $uid . ':' . ($fileHash === false ? '' : $fileHash) . "\n";
        }

        return hash('sha256', $fingerprint);
    }

    public function migrate(Connection $db, ?string $namespace = null): void
    {
        $forge = Database::forge($db);

        foreach ($this->discoverMigrations($db, $namespace) as $migration) {
            $path  = $migration->path;
            $class = $migration->class;

            if (! is_string($path) || ! is_string($class)) {
                continue;
            }

            require_once $path;

            if (! class_exists($class, false) || $this->isPinnedToGroup($class)) {
                continue;
            }

            $instance = new $class($forge);

            if (! $instance instanceof Migration) {
                continue;
            }

            try {
                $instance->up();
            } catch (Throwable) {
                // Degrade: the failed migration's tables are simply absent from the schema.
            }
        }
    }

    /**
     * @return list<stdClass>
     */
    private function discoverMigrations(Connection $db, ?string $namespace): array
    {
        $runner = new MigrationRunner(config(Migrations::class), $db);

        // A null namespace makes the runner scan every registered namespace (the app plus installed
        // packages), so a library analyzed on its own and an app's vendor migrations are both found.
        $runner->setNamespace($namespace);

        return array_values(array_filter(
            $runner->findMigrations(),
            static fn (mixed $migration): bool => $migration instanceof stdClass,
        ));
    }

    /**
     * Reads the migration's `$DBGroup` default without instantiating it, since instantiating a
     * pinned migration would open a connection to that group.
     *
     * @param class-string $class
     */
    private function isPinnedToGroup(string $class): bool
    {
        return ((new ReflectionClass($class))->getDefaultProperties()['DBGroup'] ?? null) !== null;
    }
}
