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

namespace CodeIgniter\PHPStan;

use FilesystemIterator;
use JsonException;
use Phar;
use PharException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use UnexpectedValueException;

/**
 * @internal
 */
final class ComposerScripts
{
    private static string $vscodeSettingsJson = __DIR__ . '/../.vscode/settings.json';

    public static function postUpdate(): void
    {
        if (is_file(self::$vscodeSettingsJson)) {
            self::recursiveDelete(__DIR__ . '/../vendor/phpstan/phpstan-phar');
            self::extractPhpstanPhar();
            self::updateVscodeIntelephenseEnvironmentIncludePaths();
        }
    }

    private static function recursiveDelete(string $directory): void
    {
        if (! is_dir($directory)) {
            echo sprintf('Cannot recursively delete "%s" as it does not exist.', $directory) . PHP_EOL;

            return;
        }

        /** @var SplFileInfo $file */
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(rtrim($directory, '\\/'), FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        ) as $file) {
            $path = $file->getPathname();

            if ($file->isDir()) {
                rmdir($path);
            } else {
                unlink($path);
            }
        }
    }

    private static function extractPhpstanPhar(): void
    {
        try {
            (new Phar(__DIR__ . '/../vendor/phpstan/phpstan/phpstan.phar'))->extractTo(__DIR__ . '/../vendor/phpstan/phpstan-phar', null, true);
        } catch (PharException|UnexpectedValueException $e) {
            echo $e->getMessage();

            exit(1);
        }
    }

    private static function updateVscodeIntelephenseEnvironmentIncludePaths(): void
    {
        $contents = @file_get_contents(self::$vscodeSettingsJson);

        if ($contents === false) {
            echo sprintf('Cannot get the contents of %s as it is probably missing or unreadable.', self::$vscodeSettingsJson);

            exit(1);
        }

        $settingsJson = json_decode($contents, true);
        $settingsJson['intelephense.environment.includePaths'] ??= [];

        if (! in_array('vendor/phpstan/phpstan-phar/', $settingsJson['intelephense.environment.includePaths'], true)) {
            $settingsJson['intelephense.environment.includePaths'][] = 'vendor/phpstan/phpstan-phar/';
        }

        ksort($settingsJson);

        try {
            $newContents = json_encode($settingsJson, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

            if ($newContents === $contents) {
                return;
            }

            if (file_put_contents(self::$vscodeSettingsJson, $newContents) === false) {
                echo 'Cannot save the new contents to .vscode/settings.json.';

                exit(1);
            }
        } catch (JsonException $e) {
            echo $e->getMessage();

            exit(1);
        }
    }
}
