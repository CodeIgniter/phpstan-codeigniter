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

use CodeIgniter\HTTP\URI;
use CodeIgniter\I18n\Time;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use stdClass;
use UnitEnum;

/**
 * Maps a CodeIgniter `$casts` entry to the PHPStan type its handler reads back, or null when the
 * cast name is not a framework built-in.
 */
final class CastTypeResolver
{
    public function resolve(string $cast): ?Type
    {
        $nullable = str_starts_with($cast, '?');

        if ($nullable) {
            $cast = substr($cast, 1);
        }

        if ($cast === 'json-array') {
            $cast = 'json[array]';
        }

        $params = [];

        if (preg_match('/\A(.+)\[(.+)\]\z/', $cast, $matches) === 1) {
            $cast   = $matches[1];
            $params = array_map(trim(...), explode(',', $matches[2]));
        }

        $type = $this->mapCast($cast, $params);

        if ($type === null) {
            return null;
        }

        return $nullable ? TypeCombinator::addNull($type) : $type;
    }

    /**
     * Whether the cast's handler returns null for a null input, so a null column value survives the
     * cast as null. The other built-in handlers coerce null (e.g. `(int) null` is `0`) or throw.
     */
    public function preservesNull(string $cast): bool
    {
        if (str_starts_with($cast, '?') || $cast === 'json-array') {
            return true;
        }

        if (preg_match('/\A(.+)\[.+\]\z/', $cast, $matches) === 1) {
            $cast = $matches[1];
        }

        return in_array($cast, ['datetime', 'json'], true);
    }

    /**
     * @param list<string> $params
     */
    private function mapCast(string $cast, array $params): ?Type
    {
        return match ($cast) {
            'int', 'integer'              => new IntegerType(),
            'float', 'double'             => new FloatType(),
            'bool', 'boolean', 'int-bool' => new BooleanType(),
            'string'                      => new StringType(),
            'object'                      => new ObjectType(stdClass::class),
            'array'                       => new ArrayType(new MixedType(), new MixedType()),
            'csv'                         => TypeCombinator::intersect(new ArrayType(new IntegerType(), new StringType()), new AccessoryArrayListType()),
            'json'                        => in_array('array', $params, true) ? new ArrayType(new MixedType(), new MixedType()) : new ObjectType(stdClass::class),
            'datetime', 'timestamp'       => new ObjectType(Time::class),
            'uri'                         => new ObjectType(URI::class),
            'enum'                        => $this->enumType($params),
            default                       => null,
        };
    }

    /**
     * @param list<string> $params
     */
    private function enumType(array $params): Type
    {
        if (isset($params[0])) {
            return new ObjectType($params[0]);
        }

        return new ObjectType(UnitEnum::class);
    }
}
