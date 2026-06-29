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

use function PHPStan\Testing\assertType;

function test_url_helper_functions(bool $flag): void
{
    // A false or absent flag returns a string, a constant true returns a URI.
    assertType('string', current_url());
    assertType('string', current_url(false));
    assertType('CodeIgniter\HTTP\URI', current_url(true));

    assertType('string', previous_url());
    assertType('string', previous_url(false));
    assertType('CodeIgniter\HTTP\URI', previous_url(true));

    // A non-constant flag leaves the declared union.
    assertType('CodeIgniter\HTTP\URI|string', current_url($flag));
    assertType('CodeIgniter\HTTP\URI|string', previous_url($flag));
}
