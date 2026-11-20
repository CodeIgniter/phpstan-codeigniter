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

use CodeIgniter\PHPStan\Tests\Fixtures\Entity\CastedEntity;
use CodeIgniter\PHPStan\Tests\Fixtures\Models\BlogCommentModel;

function test_explicit_mixed(CastedEntity $entity, BlogCommentModel $comments, string $select): void
{
    $unknownProperty = $entity->unknown;

    $row      = $comments->select($select)->asArray()->first();
    $anything = $row['anything'];

    $unknownField = $comments->select('COUNT(*) AS cnt')->asArray()->first();
    $count        = $unknownField['cnt'];
}
