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

namespace CodeIgniter\PHPStan\Tests\Type;

use function PHPStan\Testing\assertType;

/**
 * @param 'argc'|'CI_ENVIRONMENT' $literals
 */
function envFunction(string $key, mixed $default, string $literals): void
{
    assertType('bool|string|null', env('CI_ENVIRONMENT'));
    assertType('bool|string|null', env('CI_ENVIRONMENT', null));
    assertType('bool|string|null', env('CI_ENVIRONMENT', 'production'));
    assertType('1|bool|string|null', env('CI_ENVIRONMENT', 1));
    assertType('bool|int|string|null', env('argc'));
    assertType('array<mixed>|bool|string|null', env('argv'));
    assertType('0|bool|float|string|null', env('REQUEST_TIME_FLOAT', 0));
    assertType('bool|int|string|null', env($literals));

    assertType('mixed', env('CI_ENVIRONMENT', $default));
    assertType('mixed', env($key));
    assertType('mixed', env($key, 'production'));
}
