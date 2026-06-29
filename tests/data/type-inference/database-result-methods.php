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

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\PHPStan\Tests\Fixtures\Entity\BlogComment;

use function PHPStan\Testing\assertType;

function resultAccessors(ResultInterface $result, string $dynamic): void
{
    // The framework annotates these only on BaseResult, not on the interface, so the interface type is bare.
    assertType('list<array<string, mixed>>', $result->getResultArray());
    assertType('list<stdClass>', $result->getResultObject());
    assertType('array<string, mixed>|null', $result->getRowArray());
    assertType('stdClass|null', $result->getRowObject());

    // getCustomResultObject() is typed from its class-string argument.
    assertType('list<CodeIgniter\PHPStan\Tests\Fixtures\Entity\BlogComment>', $result->getCustomResultObject(BlogComment::class));

    // A non-constant class name degrades to a list of unknown objects rather than the framework's bare array.
    assertType('list<object>', $result->getCustomResultObject($dynamic));

    // Already precise on the interface via a conditional @return, so the extension leaves it untouched.
    assertType('stdClass|null', $result->getRow(0));
}
