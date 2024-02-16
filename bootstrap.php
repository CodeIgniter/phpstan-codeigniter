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

use Composer\InstalledVersions;

foreach (['codeigniter4/codeigniter4', 'codeigniter4/framework'] as $framework) {
    if (InstalledVersions::isInstalled($framework)) {
        require_once __DIR__ . '/vendor/' . $framework . '/system/Test/bootstrap.php';

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                __DIR__ . '/vendor/' . $framework . '/system/Helpers'
            )
        );

        /** @var SplFileInfo $helper */
        foreach ($iterator as $helper) {
            if ($helper->isFile()) {
                require_once $helper->getRealPath();
            }
        }

        return;
    }
}

throw new RuntimeException('There is no CodeIgniter4 framework installed.');
