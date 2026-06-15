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

use function PHPStan\Testing\assertType;

$comment = new BlogComment();

assertType('int', $comment->id);
assertType('int', $comment->identifier);
assertType('string|null', $comment->body);
assertType('int', $comment->votes);
assertType('stdClass', $comment->payload);
assertType('CodeIgniter\I18n\Time', $comment->created_at);

assertType('mixed', $comment->nonexistent);
