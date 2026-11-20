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

namespace CodeIgniter\PHPStan\Tests\Helpers;

use CodeIgniter\PHPStan\Helpers\ResultCacheDependencyTracker;
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesProvider;
use PHPStan\Analyser\DependencyTracker;
use PHPStan\Analyser\Scope;
use PHPStan\Testing\PHPStanTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('static-analysis')]
final class ResultCacheDependencyTrackerTest extends PHPStanTestCase
{
    use AdditionalConfigFilesProvider;

    public function testTracksTheMigrationDirectoriesForTheSchema(): void
    {
        $directories = $this->trackedDirectories(static fn (ResultCacheDependencyTracker $tracker, Scope $scope) => $tracker->trackSchema($scope));

        self::assertContains(realpath(__DIR__ . '/../Fixtures/Database/Migrations'), $directories);
    }

    public function testTracksTheLanguageDirectories(): void
    {
        $directories = $this->trackedDirectories(static fn (ResultCacheDependencyTracker $tracker, Scope $scope) => $tracker->trackLanguage($scope));

        self::assertContains(realpath(__DIR__ . '/../Fixtures') . '/Language', $directories);
    }

    public function testIgnoresAScopeThatCannotTrackDependencies(): void
    {
        $tracker = self::getContainer()->getByType(ResultCacheDependencyTracker::class);

        $tracker->trackSchema(self::createStub(Scope::class));
        $tracker->trackLanguage(self::createStub(Scope::class));

        $this->expectNotToPerformAssertions();
    }

    /**
     * @param callable(ResultCacheDependencyTracker, Scope): void $track
     *
     * @return list<string>
     */
    private function trackedDirectories(callable $track): array
    {
        $directories = [];

        $scope = self::createStubForIntersectionOfInterfaces([Scope::class, DependencyTracker::class]);
        $scope->method('trackDirectoryDependency')->willReturnCallback(
            static function (string $directory, string $pattern) use (&$directories): void {
                self::assertSame('*.php', $pattern);

                $directories[] = $directory;
            },
        );

        $track(self::getContainer()->getByType(ResultCacheDependencyTracker::class), $scope);

        return $directories;
    }
}
