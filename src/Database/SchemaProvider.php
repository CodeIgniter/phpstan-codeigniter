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
 * Provides the project's database schema, building it once per process.
 */
final class SchemaProvider
{
    private ?Schema $schema = null;

    public function __construct(
        private readonly SchemaCache $cache,
        private readonly ?string $namespace = null,
    ) {}

    public function get(): Schema
    {
        $this->schema ??= $this->cache->get($this->namespace);

        return $this->schema;
    }
}
