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

use CodeIgniter\PHPStan\Tests\Fixtures\Entity\TaggedEntity;

use function PHPStan\Testing\assertType;

$entity = new TaggedEntity();

// A property that the extension cannot type falls back to its `@property` tag.
assertType('string', $entity->tagged);

// A cast whose handler cannot be resolved is unknown, so the tag applies.
assertType('string', $entity->broken);

// A cast keeps precedence over the tag.
assertType('int', $entity->id);

// Without a tag, an unknown property stays `mixed`.
assertType('mixed', $entity->unknown);
