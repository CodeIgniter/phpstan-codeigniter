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

namespace App\Controllers;

use CodeIgniter\Shield\Models\RememberModel;

class HelloWorld
{
    public function touch(): void
    {
        model(RememberModel::class)
            ->allowCallbacks(false)
            ->update();
    }
}
