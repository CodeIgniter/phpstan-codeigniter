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

namespace CodeIgniter\PHPStan\Tests\Fixtures\Models;

use CodeIgniter\Model;
use CodeIgniter\PHPStan\Tests\Fixtures\Entity\Account;
use CodeIgniter\PHPStan\Tests\Fixtures\Entity\MoneyCast;

final class AccountModel extends Model
{
    protected $table       = 'blog_comments';
    protected $returnType  = Account::class;
    protected array $casts = [
        'payload' => 'json-array',
        'body'    => 'json-array',
        'votes'   => 'money',
    ];
    protected array $castHandlers = [
        'money' => MoneyCast::class,
    ];
}
