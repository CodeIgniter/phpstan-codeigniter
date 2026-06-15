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
 * A single column of a database table, as introspected from the live schema.
 */
final readonly class Column
{
    public function __construct(
        public string $name,
        public string $type,
        public bool $nullable,
        public bool $primaryKey,
        public ?string $default,
    ) {}

    /**
     * @param array{name: string, type: string, nullable: bool, primaryKey: bool, default: string|null} $state
     */
    public static function __set_state(array $state): self
    {
        return new self(
            name: $state['name'],
            type: $state['type'],
            nullable: $state['nullable'],
            primaryKey: $state['primaryKey'],
            default: $state['default'],
        );
    }
}
