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

namespace CodeIgniter\PHPStan\Tests\Fixtures\Entity;

use CodeIgniter\Entity\Entity;

/**
 * @property string $broken
 * @property string $id
 * @property string $tagged
 */
final class TaggedEntity extends Entity
{
    protected $casts = [
        'id'     => 'integer',
        'broken' => 'unregistered',
    ];
}
