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

use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesProvider;
use PHPStan\Testing\TypeInferenceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('static-analysis')]
final class ExtensionTypeInferenceTest extends TypeInferenceTestCase
{
    use AdditionalConfigFilesProvider;

    #[DataProvider('provideFileAssertsCases')]
    public function testFileAsserts(string $assertType, string $file, mixed ...$args): void
    {
        $this->assertFileAsserts($assertType, $file, ...$args);
    }

    /**
     * @return iterable<string, array<array-key, mixed>>
     */
    public static function provideFileAssertsCases(): iterable
    {
        // @phpstan-ignore argument.type, argument.type
        yield from self::gatherAssertTypesFromDirectory(__DIR__ . '/../data/type-inference');
    }
}
