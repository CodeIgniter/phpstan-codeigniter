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

use RuntimeException;

/**
 * @internal
 */
final class ComposerJsonRewriter
{
    private string $composerJson = __DIR__ . '/../composer.json';

    /**
     * @var array<int, string>
     */
    private array $replacements = [
        70400 => '^7.4 || ^8.0',
        80000 => '^8.0',
    ];

    public function rewriteUsingPhp(int $targetPhpVersionId): void
    {
        if (! isset($this->replacements[$targetPhpVersionId])) {
            throw new RuntimeException(sprintf('Rewriting composer.json to PHP_VERSION_ID %d is not supported.', $targetPhpVersionId));
        }

        $composerJson = json_decode((string) @file_get_contents($this->composerJson), true, 512, JSON_THROW_ON_ERROR);

        $composerJson['require']['php'] = $this->replacements[$targetPhpVersionId];

        $composerJson = json_encode($composerJson, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) . "\n";

        if (file_put_contents($this->composerJson, $composerJson) === false) {
            throw new RuntimeException('Rewriting to composer.json failed.');
        }
    }
}
