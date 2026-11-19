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

use CodeIgniter\PHPStan\Tests\Fixtures\Models\AllowedFieldsModel;
use CodeIgniter\PHPStan\Tests\Fixtures\Models\BlogCommentModel;
use CodeIgniter\PHPStan\Tests\Fixtures\Models\UnprotectedFieldsModel;

/**
 * @param array<string, mixed> $dynamic
 */
function test_model_write_allowed_fields(
    AllowedFieldsModel $model,
    UnprotectedFieldsModel $unprotected,
    BlogCommentModel $noAllowedFields,
    array $dynamic,
): void {
    $model->insert(['title' => 'a', 'user_id' => 1]);
    $model->insert(['title' => 'a', 'titel' => 'b']);
    $model->save(['body' => 'x', 'user_id' => 1]);
    $model->update(1, ['title' => 'a', 'body' => 'x']);
    $model->update(['title' => 'a']);
    $model->insert($dynamic);
    $model->insert();

    $unprotected->insert(['body' => 'x']);
    $noAllowedFields->insert(['anything' => 'x']);
}
