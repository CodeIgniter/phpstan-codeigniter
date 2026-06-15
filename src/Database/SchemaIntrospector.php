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
use CodeIgniter\PHPStan\Database\Schema\Column;
use CodeIgniter\PHPStan\Database\Schema\Schema;
use CodeIgniter\PHPStan\Database\Schema\Table;

/**
 * Reads the tables and columns of a live database connection into a `Schema`.
 */
final class SchemaIntrospector
{
    /**
     * Tables created by the framework that are not part of the user's domain schema.
     */
    private const IGNORED_TABLES = [
        'migrations',
    ];

    public function introspect(Connection $db, string $hash): Schema
    {
        $tableNames = $db->listTables();

        if ($tableNames === false) {
            return new Schema(hash: $hash, tables: []);
        }

        $tables = [];

        foreach ($tableNames as $tableName) {
            if (in_array($tableName, self::IGNORED_TABLES, true)) {
                continue;
            }

            $columns = [];

            foreach ($db->getFieldData($tableName) as $field) {
                $name = $field->name;

                if (! is_string($name)) {
                    continue;
                }

                $type    = $field->type;
                $default = $field->default;

                $columns[$name] = new Column(
                    name: $name,
                    type: is_string($type) ? $type : '',
                    nullable: (bool) $field->nullable,
                    primaryKey: (bool) $field->primary_key,
                    default: is_string($default) ? $default : null,
                );
            }

            $tables[$tableName] = new Table(name: $tableName, columns: $columns);
        }

        return new Schema(hash: $hash, tables: $tables);
    }
}
