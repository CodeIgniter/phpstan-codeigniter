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

require_once __DIR__ . '/vendor/codeigniter4/framework/system/Test/bootstrap.php';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/vendor/codeigniter4/framework/system/Helpers'));

/** @var SplFileInfo $helper */
foreach ($iterator as $helper) {
    if ($helper->isFile()) {
        require_once $helper->getRealPath();
    }
}
