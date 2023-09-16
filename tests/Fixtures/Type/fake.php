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

use CodeIgniter\PHPStan\Tests\Fixtures\Type\BarModel;
use CodeIgniter\Shield\Entities\Login;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Entities\UserIdentity;
use CodeIgniter\Shield\Models\GroupModel;
use CodeIgniter\Shield\Models\LoginModel;
use CodeIgniter\Shield\Models\TokenLoginModel;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;

use function PHPStan\Testing\assertType;

assertType('never', fake('baz'));
assertType(stdClass::class, fake(BarModel::class));
assertType(User::class, fake(UserModel::class));
assertType(UserIdentity::class, fake(UserIdentityModel::class));
assertType(Login::class, fake(LoginModel::class));
assertType(Login::class, fake(TokenLoginModel::class));
assertType('array{user_id: int, group: string, created_at: string}', fake(GroupModel::class));
