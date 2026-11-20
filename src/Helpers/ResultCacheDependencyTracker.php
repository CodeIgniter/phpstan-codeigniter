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

namespace CodeIgniter\PHPStan\Helpers;

use CodeIgniter\PHPStan\Database\SchemaProvider;
use PHPStan\Analyser\DeclarationDependencyTracker;
use PHPStan\Analyser\DependencyTracker;
use PHPStan\Analyser\Scope;
use PHPStan\DependencyInjection\AutowiredService;
use PHPStan\Reflection\ClassReflection;

/**
 * Declarations of the files outside the analysed code that the extensions read, made to PHPStan's result cache.
 */
#[AutowiredService(name: 'resultCacheDependencyTracker')]
final class ResultCacheDependencyTracker
{
    /**
     * @var array<string, list<string>>
     */
    private array $namespaceDirectories = [];

    /**
     * @var array<class-string, true>
     */
    private array $declaredEntities = [];

    public function __construct(
        private readonly SchemaProvider $schemaProvider,
        private readonly DeclarationDependencyTracker $declarationDependencyTracker,
    ) {}

    /**
     * The analysis reads the database schema, which is built from the migrations.
     */
    public function trackSchema(Scope $scope): void
    {
        if (! $scope instanceof DependencyTracker) {
            return;
        }

        foreach ($this->schemaProvider->get()->migrationDirectories as $directory) {
            $scope->trackDirectoryDependency($directory, '*.php');
        }
    }

    /**
     * The analysis reads the language files of every registered namespace.
     */
    public function trackLanguage(Scope $scope): void
    {
        if (! $scope instanceof DependencyTracker) {
            return;
        }

        foreach ($this->namespaceDirectories('Language') as $directory) {
            $scope->trackDirectoryDependency($directory, '*.php');
        }
    }

    /**
     * The properties of the entity depend on the schema and on the models that return it.
     */
    public function declareEntity(ClassReflection $classReflection): void
    {
        if (isset($this->declaredEntities[$classReflection->getName()])) {
            return;
        }

        $this->declaredEntities[$classReflection->getName()] = true;

        foreach ($this->schemaProvider->get()->migrationDirectories as $directory) {
            $this->declarationDependencyTracker->trackDirectoryDependency($classReflection, $directory, '*.php');
        }

        foreach ($this->namespaceDirectories('Models') as $directory) {
            $this->declarationDependencyTracker->trackDirectoryDependency($classReflection, $directory, '*.php');
        }
    }

    /**
     * @return list<string>
     */
    private function namespaceDirectories(string $folder): array
    {
        if (isset($this->namespaceDirectories[$folder])) {
            return $this->namespaceDirectories[$folder];
        }

        $directories = [];

        foreach (service('autoloader')->getNamespace() as $paths) {
            foreach ($paths as $path) {
                $directories[] = rtrim($path, '/\\') . '/' . $folder;
            }
        }

        return $this->namespaceDirectories[$folder] = array_values(array_unique($directories));
    }
}
