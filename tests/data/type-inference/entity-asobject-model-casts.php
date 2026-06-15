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

use CodeIgniter\PHPStan\Tests\Fixtures\Entity\BlogComment;
use CodeIgniter\PHPStan\Tests\Fixtures\Models\LegacyCommentModel;

use function PHPStan\Testing\assertType;

$legacy = new LegacyCommentModel();

// Fetched through asObject(), so the entity carries the producing model's casts, not its own model's.
assertType('CodeIgniter\PHPStan\Tests\Fixtures\Entity\BlogComment|null', $legacy->asObject(BlogComment::class)->find(1));
assertType('list<CodeIgniter\PHPStan\Tests\Fixtures\Entity\BlogComment>', $legacy->asObject(BlogComment::class)->findAll());

$one = $legacy->asObject(BlogComment::class)->find(1);

if ($one !== null) {
    // `body` is a raw string column, retyped by the producing model's json-array cast.
    assertType('array|null', $one->body);

    // `votes` is retyped by the producing model's custom money handler.
    assertType('CodeIgniter\PHPStan\Tests\Fixtures\Entity\Money', $one->votes);

    // The entity's own `payload` cast still wins, as it runs last in `__get()`.
    assertType('stdClass|null', $one->payload);

    // `id` is cast by neither, so the raw column type is used. (Reached via the entity datamap too.)
    assertType('int', $one->id);
    assertType('int', $one->identifier);
}

// first() keeps the nullable element type.
assertType('array|null', $legacy->asObject(BlogComment::class)->first()?->body);
