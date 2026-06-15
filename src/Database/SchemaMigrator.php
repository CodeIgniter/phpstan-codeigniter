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
 * Runs the project's migrations into a schema connection and returns a fingerprint
 * of the migration set.
 *
 * Migrations are run one by one rather than through `MigrationRunner::latest()`: a migration
 * that cannot run on SQLite leaves its tables out of the inferred schema instead of aborting
 * the whole build, and migrations pinned to a specific database group are skipped.
 */
final class SchemaMigrator
{
    public function migrate(Connection $db, ?string $namespace = null): string
    {
        $runner = new MigrationRunner(config(Migrations::class), $db);

        if ($namespace !== null) {
            $runner->setNamespace($namespace);
        }

        $forge = Database::forge($db);

        $fingerprint = '';

        foreach ($runner->findMigrations() as $migration) {
            if (! $migration instanceof stdClass) {
                continue;
            }

            $path  = $migration->path;
            $class = $migration->class;
            $uid   = $migration->uid;

            if (! is_string($path) || ! is_string($class) || ! is_string($uid)) {
                continue;
            }

            $fileHash = md5_file($path);
            $fingerprint .= $uid . ':' . ($fileHash === false ? '' : $fileHash) . "\n";

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

        return hash('sha256', $fingerprint);
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
