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

use Config\Cache;
use PHPUnit\Framework\TestCase;

use function PHPStan\Testing\assertType;

assertType('Config\Cache', config(Cache::class));

/**
 * @internal
 */
final class ConfigTest extends TestCase
{
    public function testConfig(): void
    {
        self::assertContains('file', config(Cache::class)->validHandlers);
    }
}
