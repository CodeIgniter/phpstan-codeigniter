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

use Config\App;
use PHPStan\DependencyInjection\AutowiredService;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\Accessory\AccessoryNonEmptyStringType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * Resolves the return type of a language line from the project's language files.
 */
#[AutowiredService(name: 'languageLineTypeHelper')]
final class LanguageLineTypeHelper
{
    /**
     * @var array<string, array<array-key, mixed>>
     */
    private array $files = [];

    /**
     * @return list<string> the lines when the type consists only of constant strings
     */
    public function constantLines(Type $lineType): array
    {
        if (! $lineType->isString()->yes()) {
            return [];
        }

        $lines = [];

        foreach ($lineType->getConstantStrings() as $constant) {
            $lines[] = $constant->getValue();
        }

        return array_values(array_unique($lines));
    }

    public function resolve(Type $lineType, ?Type $argsType = null): ?Type
    {
        $lines = $this->constantLines($lineType);

        if ($lines === []) {
            return null;
        }

        $refine = $argsType === null || $argsType->isIterableAtLeastOnce()->no();
        $types  = [];

        foreach ($lines as $line) {
            $type = $this->resolveLine($line, $refine);

            if ($type === null) {
                return null;
            }

            $types[] = $type;
        }

        return TypeCombinator::union(...$types);
    }

    private function resolveLine(string $line, bool $refine): ?Type
    {
        if (! str_contains($line, '.')) {
            return $this->stringType($this->isNonEmpty($line, $refine));
        }

        [$file, $key] = explode('.', $line, 2);

        $types     = [];
        $fileFound = false;

        foreach ($this->locales() as $locale) {
            $strings = $this->load($locale, $file);

            if ($strings === null) {
                continue;
            }

            $fileFound   = true;
            $translation = $this->lookup($strings, $key);

            if ($translation === null) {
                continue;
            }

            $type = $this->typeOf($translation, $refine);

            if ($type === null) {
                return null;
            }

            $types[] = $type;
        }

        if (! $fileFound) {
            return null;
        }

        return TypeCombinator::union(
            ...$types === [] ? [$this->stringType($this->isNonEmpty($line, $refine))] : $types,
        );
    }

    /**
     * Whether a language file exists for the line but none of the locales defines its key.
     */
    public function isMissing(string $line): bool
    {
        if (! str_contains($line, '.')) {
            return false;
        }

        [$file, $key] = explode('.', $line, 2);

        $fileFound = false;

        foreach ($this->locales() as $locale) {
            $strings = $this->load($locale, $file);

            if ($strings === null) {
                continue;
            }

            $fileFound = true;

            if ($this->lookup($strings, $key) !== null) {
                return false;
            }
        }

        return $fileFound;
    }

    /**
     * @return list<string> the locales lacking a key that the English file defines
     */
    public function untranslatedLocales(string $line): array
    {
        if (! str_contains($line, '.')) {
            return [];
        }

        [$file, $key] = explode('.', $line, 2);

        $english = $this->load('en', $file);

        if ($english === null || $this->lookup($english, $key) === null) {
            return [];
        }

        $untranslated = [];

        foreach ($this->locales() as $locale) {
            if ($locale === 'en') {
                continue;
            }

            $strings = $this->load($locale, $file);

            if ($strings === null || $this->lookup($strings, $key) === null) {
                $untranslated[] = $locale;
            }
        }

        return $untranslated;
    }

    /**
     * @return list<string>
     */
    private function locales(): array
    {
        $config = config(App::class);

        return array_values(array_unique(['en', $config->defaultLocale, ...$config->supportedLocales]));
    }

    /**
     * @return array<array-key, mixed>|null
     */
    private function load(string $locale, string $file): ?array
    {
        $path = sprintf('Language/%s/%s.php', $locale, $file);

        if (array_key_exists($path, $this->files)) {
            return $this->files[$path] === [] ? null : $this->files[$path];
        }

        $merged = [];

        foreach (service('locator')->search($path, 'php', false) as $candidate) {
            if (! is_file($candidate)) {
                continue;
            }

            $strings = require $candidate;

            if (is_array($strings)) {
                $merged = array_replace($merged, $strings);
            }
        }

        $this->files[$path] = $merged;

        return $merged === [] ? null : $merged;
    }

    /**
     * @param array<array-key, mixed> $strings
     */
    private function lookup(array $strings, string $key): mixed
    {
        if (array_key_exists($key, $strings)) {
            return $strings[$key];
        }

        $current = $strings;

        foreach (explode('.', $key) as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                return null;
            }

            $current = $current[$segment];
        }

        return $current;
    }

    /**
     * A formatted message stays non-empty only when it has literal text outside the `{...}` placeholders.
     */
    private function isNonEmpty(string $message, bool $noArgs): bool
    {
        if ($noArgs) {
            return $message !== '';
        }

        $depth = 0;

        foreach (str_split($message) as $char) {
            if ($char === '{') {
                $depth++;
            } elseif ($char === '}') {
                $depth = max(0, $depth - 1);
            } elseif ($depth === 0) {
                return true;
            }
        }

        return false;
    }

    private function stringType(bool $nonEmpty): Type
    {
        return $nonEmpty
            ? new IntersectionType([new StringType(), new AccessoryNonEmptyStringType()])
            : new StringType();
    }

    private function typeOf(mixed $translation, bool $refine): ?Type
    {
        if (is_string($translation)) {
            return $this->stringType($this->isNonEmpty($translation, $refine));
        }

        if (! is_array($translation) || ! array_is_list($translation)) {
            return null;
        }

        $nonEmpty = true;

        foreach ($translation as $item) {
            $nonEmpty = $nonEmpty && is_string($item) && $this->isNonEmpty($item, $refine);
        }

        return TypeCombinator::intersect(
            new ArrayType(new IntegerType(), $this->stringType($nonEmpty)),
            new AccessoryArrayListType(),
        );
    }
}
