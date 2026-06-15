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
     * @param string               $hash   Fingerprint of the migration set that produced this schema
     * @param array<string, Table> $tables Keyed by table name
     */
    public function __construct(
        public string $hash,
        public array $tables,
    ) {}

    /**
     * @param array{hash: string, tables: array<string, Table>} $state
     */
    public static function __set_state(array $state): self
    {
        return new self(
            hash: $state['hash'],
            tables: $state['tables'],
        );
    }

    public function getTable(string $name): ?Table
    {
        return $this->tables[$name] ?? null;
    }
}
