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
 * A database table and its columns, as introspected from the live schema.
 */
final readonly class Table
{
    /**
     * @param array<string, Column> $columns Keyed by column name
     */
    public function __construct(
        public string $name,
        public array $columns,
    ) {}

    /**
     * @param array{name: string, columns: array<string, Column>} $state
     */
    public static function __set_state(array $state): self
    {
        return new self(
            name: $state['name'],
            columns: $state['columns'],
        );
    }

    public function getColumn(string $name): ?Column
    {
        return $this->columns[$name] ?? null;
    }
}
