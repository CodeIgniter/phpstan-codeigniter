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

use CodeIgniter\PHPStan\Tests\Fixtures\Entity\Account;
use CodeIgniter\PHPStan\Tests\Fixtures\Models\AccountModel;

use function PHPStan\Testing\assertType;

$account = new Account();

// The entity casts `payload`, so the entity cast wins over the model's cast of the same column.
assertType('stdClass|null', $account->payload);

// `body` is cast only by the model, so the model cast applies (was the raw column type before the fix).
assertType('array|null', $account->body);

// `votes` is cast by the model through a custom handler, reflected from the handler's get() return type.
assertType('CodeIgniter\PHPStan\Tests\Fixtures\Entity\Money', $account->votes);

// `id` is cast by neither, so the raw column type is used.
assertType('int', $account->id);

// `created_at` is mutated to Time as a date field.
assertType('CodeIgniter\I18n\Time|null', $account->created_at);

assertType('CodeIgniter\PHPStan\Tests\Fixtures\Entity\Account|null', (new AccountModel())->find(1));
