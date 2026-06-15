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

use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * Maps an introspected column to the PHPStan type of its raw, uncast database value.
 */
final class ColumnTypeResolver
{
    public function resolve(Column $column): Type
    {
        $type = $this->mapDeclaredType($column->type);

        return $column->nullable && ! $column->primaryKey ? TypeCombinator::addNull($type) : $type;
    }

    private function mapDeclaredType(string $declaredType): Type
    {
        $declaredType = strtoupper($declaredType);

        // Checks follow SQLite's column affinity precedence.
        // https://www.sqlite.org/datatype3.html#determination_of_column_affinity
        if (str_contains($declaredType, 'INT')) {
            return new IntegerType();
        }

        if (
            str_contains($declaredType, 'CHAR')
            || str_contains($declaredType, 'CLOB')
            || str_contains($declaredType, 'TEXT')
            || str_contains($declaredType, 'BLOB')
            || $declaredType === ''
        ) {
            return new StringType();
        }

        if (
            str_contains($declaredType, 'REAL')
            || str_contains($declaredType, 'FLOA')
            || str_contains($declaredType, 'DOUB')
        ) {
            return new FloatType();
        }

        // NUMERIC affinity (DECIMAL, NUMERIC, DATE, DATETIME, ...), read back as strings without a cast.
        return new StringType();
    }
}
