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

namespace CodeIgniter\PHPStan\Tests\Rules;

use CodeIgniter\Language\Language;

function test_language_line_existence(string $line, Language $language): void
{
    lang('Fixture.title');
    lang('Fixture.greeting');
    lang('Fixture.nested.label');
    lang('Fixture.missing');
    lang('Unknown.line');
    lang('no-file-part');
    lang($line);

    $language->getLine('Fixture.title');
    $language->getLine('Fixture.nope');
}

/**
 * @param 'Fixture.missing' $line
 */
function test_single_literal(string $line): void
{
    lang($line);
}

/**
 * @param 'Fixture.steps'|'Fixture.title' $line
 */
function test_two_existing_literals(string $line): void
{
    lang($line);
}

/**
 * @param 'Fixture.missing'|'Fixture.nope' $line
 */
function test_two_missing_literals(string $line): void
{
    lang($line);
}

/**
 * @param 'Fixture.nope'|'Fixture.title' $line
 */
function test_one_missing_literal(string $line): void
{
    lang($line);
}
