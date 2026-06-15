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
assertType('object{id: int, body: string|null, votes: int, payload: string|null, created_at: string|null}|null', $comments->asObject()->first());

$posts = new BlogPostModel();

assertType('array{id: int, user_id: CodeIgniter\PHPStan\Tests\Fixtures\Entity\Money, title: CodeIgniter\I18n\Time}|null', $posts->first());
assertType('list<array{id: int, user_id: CodeIgniter\PHPStan\Tests\Fixtures\Entity\Money, title: CodeIgniter\I18n\Time}>', $posts->findAll());

// select() narrows and renames the row shape.
assertType('array{id: int, body: string|null}|null', $comments->select('id, body')->asArray()->first());
assertType('array{id: int, note: string|null}|null', $comments->select('id, body as note')->asArray()->first());
assertType('object{id: int, body: string|null}|null', $comments->select('id, body')->asObject()->first());

// `table.*` and a qualified field from a joined table are resolved against the live schema.
assertType('array{id: int, body: string|null, votes: int, payload: string|null, created_at: string|null, author: string}|null', $comments->select('blog_comments.*, blog_users.name as author')->asArray()->first());

// Expressions are typed as mixed under their alias.
assertType('array{id: int, lowered: mixed}|null', $comments->select('id, LOWER(body) as lowered')->asArray()->first());

// A selected field is still cast by the model (output name `user_id` casts via the model handler).
assertType('array{user_id: CodeIgniter\PHPStan\Tests\Fixtures\Entity\Money}|null', $posts->select('user_id')->first());

function selectDynamically(BlogCommentModel $model, string $columns): void
{
    assertType('array<string, mixed>|null', $model->select($columns)->asArray()->first());
}
