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

namespace CodeIgniter\PHPStan\Tests\Type;

use CodeIgniter\PHPStan\Tests\Fixtures\Models\BlogCommentModel;
use CodeIgniter\PHPStan\Tests\Fixtures\Models\BlogPostModel;

use function PHPStan\Testing\assertType;

assertType('CodeIgniter\PHPStan\Tests\Fixtures\Entity\BlogComment', \fake(BlogCommentModel::class));
assertType('array{id: int, user_id: CodeIgniter\PHPStan\Tests\Fixtures\Entity\Money, title: CodeIgniter\I18n\Time}', \fake(BlogPostModel::class));

function fakeFromInstance(BlogCommentModel $model): void
{
    assertType('CodeIgniter\PHPStan\Tests\Fixtures\Entity\BlogComment', \fake($model));
}
