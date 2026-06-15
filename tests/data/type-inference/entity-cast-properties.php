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

use CodeIgniter\PHPStan\Tests\Fixtures\Entity\CastedEntity;

use function PHPStan\Testing\assertType;

$entity = new CastedEntity();

assertType('int', $entity->id);
assertType('int', $entity->identifier);
assertType('bool', $entity->is_active);
assertType('float|null', $entity->rating);
assertType('string', $entity->name);
assertType('stdClass', $entity->options);
assertType('array', $entity->tags);
assertType('list<string>', $entity->roles);
assertType('CodeIgniter\I18n\Time', $entity->published);
assertType('CodeIgniter\PHPStan\Tests\Fixtures\Entity\Money', $entity->balance);
assertType('CodeIgniter\PHPStan\Tests\Fixtures\Entity\Money|null', $entity->discount);

assertType('mixed', $entity->unknown);
