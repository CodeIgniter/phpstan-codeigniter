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
