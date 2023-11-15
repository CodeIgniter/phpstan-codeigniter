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

namespace CodeIgniter\PHPStan\Tests\Fixtures\Type;

use Closure;
use CodeIgniter\Config\BaseService;
use stdClass;

final class OtherServices extends BaseService
{
    /**
     * This should overwrite native migrations.
     */
    public static function migrations(): stdClass
    {
        return new stdClass();
    }

    public static function invoker(string $callable): Closure
    {
        return Closure::fromCallable($callable);
    }

    public static function toBool(string $string): bool
    {
        return (bool) $string;
    }

    public static function noReturn()
    {
        return self::class;
    }

    /**
     * @return null
     */
    public static function returnNull()
    {
        return null;
    }
}
