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

namespace CodeIgniter\PHPStan\Tests\Rules;

use CodeIgniter\Entity\Entity;
use CodeIgniter\Model;
use CodeIgniter\PHPStan\Tests\Fixtures\Entity\MoneyCast;
use stdClass;

final class ValidCastsEntity extends Entity
{
    protected $casts = [
        'id'      => 'integer',
        'name'    => '?string',
        'balance' => 'money',
        'tags'    => 'json[array]',
    ];
    protected $castHandlers = [
        'money' => MoneyCast::class,
    ];
}

final class BrokenCastsEntity extends Entity
{
    protected $casts = [
        'a' => 'unregistered',
        'b' => '?missing',
        'c' => 'invalid',
        'd' => 'money',
    ];
    protected $castHandlers = [
        'missing' => 'Does\Not\Exist',
        'invalid' => stdClass::class,
        'money'   => MoneyCast::class,
    ];
}

final class BrokenCastsModel extends Model
{
    protected $table = 'blog_posts';
    protected $casts = [
        'title' => 'unregistered',
    ];
}

final class InheritingEntity extends Entity {}

final class OnlyHandlersEntity extends Entity
{
    protected $castHandlers = [
        'money' => MoneyCast::class,
    ];
}
