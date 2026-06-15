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
use CodeIgniter\PHPStan\Tests\Fixtures\Entity\MoneyCast;

final class BlogPostModel extends Model
{
    protected $table       = 'blog_posts';
    protected array $casts = [
        'user_id' => 'money',
        'title'   => 'datetime',
    ];
    protected array $castHandlers = [
        'money' => MoneyCast::class,
    ];
}
