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

use CodeIgniter\Shield\Models\UserModel;

use function PHPStan\Testing\assertType;

$users = model(UserModel::class);
assertType('array{}', $users->find([]));
assertType('list<CodeIgniter\Shield\Entities\User>', $users->find([1]));

// Model::find() does not fail if not `array|int|string|null` but defaults to get all
assertType('list<CodeIgniter\Shield\Entities\User>', $users->find(new \stdClass()));
