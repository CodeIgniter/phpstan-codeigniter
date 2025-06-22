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

use CodeIgniter\Cache\CacheFactory;
use CodeIgniter\Cache\Handlers\BaseHandler;
use CodeIgniter\Cache\Handlers\DummyHandler;
use CodeIgniter\Cache\Handlers\FileHandler;
use CodeIgniter\Cache\Handlers\MemcachedHandler;
use CodeIgniter\Cache\Handlers\PredisHandler;
use CodeIgniter\Cache\Handlers\RedisHandler;
use CodeIgniter\Cache\Handlers\WincacheHandler;
use Config\Cache;

use function PHPStan\Testing\assertType;

$cache = new Cache();
assertType(FileHandler::class, CacheFactory::getHandler($cache));
assertType(FileHandler::class, CacheFactory::getHandler($cache, null));

assertType(DummyHandler::class, CacheFactory::getHandler($cache, 'dummy'));
assertType(FileHandler::class, CacheFactory::getHandler($cache, 'file'));
assertType(MemcachedHandler::class, CacheFactory::getHandler($cache, 'memcached'));
assertType(PredisHandler::class, CacheFactory::getHandler($cache, 'predis'));
assertType(RedisHandler::class, CacheFactory::getHandler($cache, 'redis'));
assertType(WincacheHandler::class, CacheFactory::getHandler($cache, 'wincache'));

assertType(DummyHandler::class, CacheFactory::getHandler($cache, 'invalid'));
assertType('*NEVER*', CacheFactory::getHandler($cache, 'unknown', 'invalid'));

assertType(
    FileHandler::class . '|' . RedisHandler::class,
    CacheFactory::getHandler($cache, (static fn (): string => mt_rand(0, 1) ? 'file' : 'redis')()),
);

/**
 * @param non-empty-string $name
 */
function getCache(string $name): void
{
    assertType(BaseHandler::class, CacheFactory::getHandler(new Cache(), $name));
}
