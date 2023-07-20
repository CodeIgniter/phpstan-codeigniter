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
use Config\Cache;

$handler1 = new FileHandler(new Cache());
$handler2 = new RedisHandler(new Cache());

$cache1 = $handler1->get('foo');
$cache2 = $handler2->get('bar');
