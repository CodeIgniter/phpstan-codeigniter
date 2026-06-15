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

use CodeIgniter\PHPStan\Database\Schema\CastTypeResolver;
use PHPStan\Type\VerbosityLevel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Group('unit')]
final class CastTypeResolverTest extends TestCase
{
    #[DataProvider('provideResolvesBuiltInCastCases')]
    public function testResolvesBuiltInCast(string $cast, string $expected): void
    {
        $type = (new CastTypeResolver())->resolve($cast);

        self::assertNotNull($type);
        self::assertSame($expected, $type->describe(VerbosityLevel::precise()));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideResolvesBuiltInCastCases(): iterable
    {
        yield 'int' => ['int', 'int'];

        yield 'integer' => ['integer', 'int'];

        yield 'float' => ['float', 'float'];

        yield 'double' => ['double', 'float'];

        yield 'bool' => ['bool', 'bool'];

        yield 'int-bool' => ['int-bool', 'bool'];

        yield 'string' => ['string', 'string'];

        yield 'object' => ['object', 'stdClass'];

        yield 'array' => ['array', 'array'];

        yield 'csv' => ['csv', 'list<string>'];

        yield 'json (object)' => ['json', 'stdClass'];

        yield 'json[array]' => ['json[array]', 'array'];

        yield 'json-array' => ['json-array', 'array'];

        yield 'datetime' => ['datetime', 'CodeIgniter\I18n\Time'];

        yield 'timestamp' => ['timestamp', 'CodeIgniter\I18n\Time'];

        yield 'uri' => ['uri', 'CodeIgniter\HTTP\URI'];

        yield 'enum with class' => ['enum[CodeIgniter\Test\TestLogger]', 'CodeIgniter\Test\TestLogger'];

        yield 'enum without class' => ['enum', 'UnitEnum'];

        yield 'nullable int' => ['?int', 'int|null'];

        yield 'nullable datetime' => ['?datetime', 'CodeIgniter\I18n\Time|null'];
    }

    public function testReturnsNullForUnknownCast(): void
    {
        self::assertNull((new CastTypeResolver())->resolve('mycustomhandler'));
    }
}
