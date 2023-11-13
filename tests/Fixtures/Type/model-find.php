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

namespace CodeIgniter\PHPStan\Tests\Fixtures\Type;

use CodeIgniter\Shield\Entities\AccessToken;
use CodeIgniter\Shield\Models\GroupModel;
use CodeIgniter\Shield\Models\UserModel;

use function PHPStan\Testing\assertType;

$users = model(UserModel::class);
assertType('CodeIgniter\Shield\Entities\User|null', $users->find(1));
assertType('list<CodeIgniter\Shield\Entities\User>', $users->find());
assertType('list<CodeIgniter\Shield\Entities\User>', $users->find(null));
assertType('list<CodeIgniter\Shield\Entities\User>', $users->find([1, 2, 3]));

$groups = model(GroupModel::class);
assertType('array{user_id: int, group: string, created_at: string}|null', $groups->find(1));
assertType('list<array{user_id: int, group: string, created_at: string}>', $groups->find());
assertType('list<array{user_id: int, group: string, created_at: string}>', $groups->find(null));
assertType('list<array{user_id: int, group: string, created_at: string}>', $groups->find([1, 2, 3]));

function bar(null|int|string $id): void
{
    $model = model(UserModel::class);

    assertType('list<CodeIgniter\Shield\Entities\User>|CodeIgniter\Shield\Entities\User|null', $model->find($id));
}

function foo(): void
{
    $model = model(UserModel::class);

    assertType('CodeIgniter\Shield\Entities\AccessToken|null', $model->asObject(AccessToken::class)->first());
    assertType('stdClass|null', $model->asObject()->find(1));
    assertType('stdClass|null', $model->asObject('object')->find(45));
    assertType('list<array{username: string, status: string, status_message: string, active: bool, last_active: string, deleted_at: string}>', $model->asArray()->findAll());

    assertType('stdClass|null', $model->asArray()->asObject()->first());
    assertType('array{username: string, status: string, status_message: string, active: bool, last_active: string, deleted_at: string}|null', $model->asObject()->asArray()->first());
}
