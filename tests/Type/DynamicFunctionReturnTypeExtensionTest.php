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

use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesTrait;
use PHPStan\Testing\TypeInferenceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Integration')]
final class DynamicFunctionReturnTypeExtensionTest extends TypeInferenceTestCase
{
    use AdditionalConfigFilesTrait;

    #[DataProvider('provideFileAssertsCases')]
    public function testFileAsserts(string $assertType, string $file, mixed ...$args): void
    {
        $this->assertFileAsserts($assertType, $file, ...$args);
    }

    public static function provideFileAssertsCases(): iterable
    {
        yield from self::gatherAssertTypes(__DIR__ . '/../Fixtures/Type/config.php');

        yield from self::gatherAssertTypes(__DIR__ . '/../Fixtures/Type/model.php');

        yield from self::gatherAssertTypes(__DIR__ . '/../Fixtures/Type/services.php');
    }
}
