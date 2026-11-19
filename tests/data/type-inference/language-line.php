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

use CodeIgniter\Language\Language;

use function PHPStan\Testing\assertType;

assertType('non-empty-string', lang('Fixture.title'));
assertType('non-empty-string', lang('Fixture.title', []));
assertType('non-empty-string', lang('Fixture.title', ['a' => 'b'], 'fr'));
assertType('non-empty-string', lang('Fixture.greeting', ['a' => 'b']));
assertType('string', lang('Fixture.placeholder', ['a' => 'b']));
assertType('non-empty-string', lang('Fixture.placeholder'));
assertType('list<non-empty-string>', lang('Fixture.steps'));
assertType('list<non-empty-string>', lang('Fixture.steps', ['a' => 'b']));
assertType('non-empty-string', lang('Fixture.nested.label'));
assertType('non-empty-string', lang('Fixture.missing'));
assertType('non-empty-string', lang('no-file-part'));
assertType('non-empty-string', lang('no-file-part', ['a' => 'b']));
assertType('string', lang('{0}', ['a' => 'b']));
assertType('list<string>|string', lang('Unknown.line'));

/**
 * @param 'Fixture.steps'|'Fixture.title' $line
 */
function fromUnionedLiterals(string $line): void
{
    assertType('list<non-empty-string>|non-empty-string', lang($line));
}

/**
 * @param 'Fixture.title'|'Unknown.line' $line
 */
function fromUnionedLiteralsWithUnknownFile(string $line): void
{
    assertType('list<string>|string', lang($line));
}

function fromNonConstant(string $line, Language $language): void
{
    assertType('list<string>|string', lang($line));
    assertType('non-empty-string', $language->getLine('Fixture.title'));
    assertType('list<non-empty-string>', $language->getLine('Fixture.steps'));
    assertType('list<string>|string', $language->getLine($line));
}
