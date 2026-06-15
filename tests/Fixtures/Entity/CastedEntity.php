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

final class CastedEntity extends Entity
{
    protected $datamap = [
        'identifier' => 'id',
    ];
    protected $casts = [
        'id'        => 'integer',
        'is_active' => 'boolean',
        'rating'    => '?float',
        'name'      => 'string',
        'options'   => 'json',
        'tags'      => 'json-array',
        'roles'     => 'csv',
        'published' => 'datetime',
        'balance'   => 'money',
        'discount'  => '?money',
    ];
    protected $castHandlers = [
        'money' => MoneyCast::class,
    ];
}
