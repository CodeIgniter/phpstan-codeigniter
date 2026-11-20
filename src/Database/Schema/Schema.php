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

namespace CodeIgniter\PHPStan\Database\Schema;

/**
 * The database schema, as introspected from the live SQLite connection.
 */
final readonly class Schema
{
    /**
     * @param string                      $hash             Fingerprint of the migration set that produced this schema
     * @param array<string, Table>        $tables           Keyed by table name
     * @param array<class-string, string> $failedMigrations The error message of each migration that failed to run, keyed by class
     */
    public function __construct(
        public string $hash,
        public array $tables,
        public array $failedMigrations = [],
    ) {}

    /**
     * @param array{hash: string, tables: array<string, Table>, failedMigrations?: array<class-string, string>} $state
     */
    public static function __set_state(array $state): self
    {
        return new self(
            hash: $state['hash'],
            tables: $state['tables'],
            failedMigrations: $state['failedMigrations'] ?? [],
        );
    }

    /**
     * @param array<class-string, string> $failedMigrations
     */
    public function withFailedMigrations(array $failedMigrations): self
    {
        return new self($this->hash, $this->tables, $failedMigrations);
    }

    public function getTable(string $name): ?Table
    {
        return $this->tables[$name] ?? null;
    }
}
