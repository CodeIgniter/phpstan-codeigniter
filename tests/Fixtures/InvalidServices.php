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

namespace CodeIgniter\PHPStan\Tests\Fixtures;

use CodeIgniter\Config\BaseService;

final class InvalidServices extends BaseService
{
    public static function boolreturn(): bool
    {
        return true;
    }

    public static function intreturn(): int
    {
        return 42;
    }

    public static function voidreturn(): void {}
}
