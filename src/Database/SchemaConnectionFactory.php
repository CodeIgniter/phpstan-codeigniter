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
use Config\Database;
use RuntimeException;

/**
 * Creates an isolated SQLite3 connection used to materialize the user's schema,
 * leaving the user's own database connections untouched.
 */
final class SchemaConnectionFactory
{
    /**
     * @throws RuntimeException
     */
    public function create(string $databasePath): Connection
    {
        $directory = dirname($databasePath);

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException(sprintf('Unable to create database directory "%s".', $directory));
        }

        $connection = Database::connect([
            'DBDriver'    => 'SQLite3',
            'database'    => $databasePath,
            'foreignKeys' => true,
        ], false);

        assert($connection instanceof Connection);

        return $connection;
    }
}
