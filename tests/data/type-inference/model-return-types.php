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

use CodeIgniter\PHPStan\Tests\Fixtures\Models\BlogCommentModel;
use CodeIgniter\PHPStan\Tests\Fixtures\Models\BlogPostModel;

use function PHPStan\Testing\assertType;

$comments = new BlogCommentModel();

assertType('CodeIgniter\PHPStan\Tests\Fixtures\Entity\BlogComment|null', $comments->find(1));
assertType('list<CodeIgniter\PHPStan\Tests\Fixtures\Entity\BlogComment>', $comments->find([1, 2]));
assertType('list<CodeIgniter\PHPStan\Tests\Fixtures\Entity\BlogComment>', $comments->find());
assertType('list<CodeIgniter\PHPStan\Tests\Fixtures\Entity\BlogComment>', $comments->findAll());
assertType('CodeIgniter\PHPStan\Tests\Fixtures\Entity\BlogComment|null', $comments->first());

assertType('array{id: int, body: string|null, votes: int, payload: string|null, created_at: string|null}|null', $comments->asArray()->first());
assertType('stdClass|null', $comments->asObject()->first());

$posts = new BlogPostModel();

assertType('array{id: int, user_id: int, title: string}|null', $posts->first());
assertType('list<array{id: int, user_id: int, title: string}>', $posts->findAll());
