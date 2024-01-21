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
use CodeIgniter\View\Exceptions\ViewException;

$e1 = new FrameworkException('Hello.');
$e2 = new ViewException('Hi!');
$e3 = new RuntimeException('Thanks.');
$e4 = new CodeIgniter\HTTP\Exceptions\HTTPException('Nice');
