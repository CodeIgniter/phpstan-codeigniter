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

namespace CodeIgniter\PHPStan\Tests\Database\Schema;

use CodeIgniter\PHPStan\Database\Schema\Column;
use CodeIgniter\PHPStan\Database\Schema\ColumnTypeResolver;
use PHPStan\Type\VerbosityLevel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Group('unit')]
final class ColumnTypeResolverTest extends TestCase
{
    #[DataProvider('provideMapsDeclaredTypeToPhpStanTypeCases')]
    public function testMapsDeclaredTypeToPhpStanType(string $declaredType, bool $nullable, string $expected): void
    {
        $type = (new ColumnTypeResolver())->resolve(new Column('column', $declaredType, $nullable, false, null));

        self::assertSame($expected, $type->describe(VerbosityLevel::precise()));
    }

    /**
     * @return iterable<string, array{string, bool, string}>
     */
    public static function provideMapsDeclaredTypeToPhpStanTypeCases(): iterable
    {
        yield 'INTEGER' => ['INTEGER', false, 'int'];

        yield 'INT' => ['INT', false, 'int'];

        yield 'BIGINT' => ['BIGINT', false, 'int'];

        yield 'VARCHAR' => ['VARCHAR', false, 'string'];

        yield 'TEXT' => ['TEXT', false, 'string'];

        yield 'BLOB' => ['BLOB', false, 'string'];

        yield 'REAL' => ['REAL', false, 'float'];

        yield 'FLOAT' => ['FLOAT', false, 'float'];

        yield 'DOUBLE' => ['DOUBLE', false, 'float'];

        yield 'DATETIME (numeric -> string)' => ['DATETIME', false, 'string'];

        yield 'DECIMAL (numeric -> string)' => ['DECIMAL', false, 'string'];

        yield 'nullable INTEGER' => ['INTEGER', true, 'int|null'];

        yield 'nullable VARCHAR' => ['VARCHAR', true, 'string|null'];
    }

    public function testPrimaryKeyColumnIsNeverNullable(): void
    {
        $type = (new ColumnTypeResolver())->resolve(new Column('id', 'INTEGER', true, true, null));

        self::assertSame('int', $type->describe(VerbosityLevel::precise()));
    }
}
