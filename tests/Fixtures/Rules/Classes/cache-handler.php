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

use CodeIgniter\Cache\Handlers\FileHandler;
use CodeIgniter\Cache\Handlers\RedisHandler;
use CodeIgniter\Test\Mock\MockCache;
use Config\Cache;

$handler1 = new FileHandler(new Cache());
$handler2 = new RedisHandler(new Cache());
$handler3 = new MockCache();

$cache1 = $handler1->get('foo');
$cache2 = $handler2->get('bar');
$cache3 = $handler3->get('baz');
