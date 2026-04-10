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

use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\View\Exceptions\ViewException;

$e1 = new FrameworkException('Hello.');
$e2 = new ViewException('Hi!');
$e3 = new RuntimeException('Thanks.'); // This one should not trigger as it's not a FrameworkException.
$e4 = new HTTPException('Nice');
