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

namespace CodeIgniter\PHPStan\Tests\Rules\Language;

use CodeIgniter\PHPStan\Helpers\LanguageLineTypeHelper;
use CodeIgniter\PHPStan\Rules\Language\LanguageLineExistenceRule;
use CodeIgniter\PHPStan\Tests\AdditionalConfigFilesProvider;
use Config\App;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @extends RuleTestCase<LanguageLineExistenceRule>
 *
 * @internal
 */
#[Group('static-analysis')]
final class LanguageLineExistenceRuleTest extends RuleTestCase
{
    use AdditionalConfigFilesProvider;

    private bool $reportUntranslatedLines = false;

    /**
     * @var list<string>
     */
    private array $supportedLocales = [];

    protected function setUp(): void
    {
        parent::setUp();

        $config = config(App::class);

        $this->supportedLocales   = $config->supportedLocales;
        $config->supportedLocales = ['en', 'fr'];
    }

    protected function tearDown(): void
    {
        config(App::class)->supportedLocales = $this->supportedLocales;

        parent::tearDown();
    }

    protected function getRule(): Rule
    {
        return new LanguageLineExistenceRule(
            self::createReflectionProvider(),
            self::getContainer()->getByType(LanguageLineTypeHelper::class),
            $this->reportUntranslatedLines,
        );
    }

    public function testMissingLines(): void
    {
        $this->analyse([__DIR__ . '/../../data/rules/language-line-existence.php'], [
            ['Language line "Fixture.missing" was not found in any language file.', 23],
            ['Language line "Fixture.nope" was not found in any language file.', 29],
            ['Language line "Fixture.missing" was not found in any language file.', 37],
            ['Language line "Fixture.missing" was not found in any language file.', 53],
            ['Language line "Fixture.nope" was not found in any language file.', 53],
            ['Language line "Fixture.nope" was not found in any language file.', 61],
        ]);
    }

    public function testUntranslatedLines(): void
    {
        $this->reportUntranslatedLines = true;

        $this->analyse([__DIR__ . '/../../data/rules/language-line-existence.php'], [
            ['Language line "Fixture.greeting" is not translated in: fr.', 21],
            ['Language line "Fixture.nested.label" is not translated in: fr.', 22],
            ['Language line "Fixture.missing" was not found in any language file.', 23],
            ['Language line "Fixture.nope" was not found in any language file.', 29],
            ['Language line "Fixture.missing" was not found in any language file.', 37],
            ['Language line "Fixture.missing" was not found in any language file.', 53],
            ['Language line "Fixture.nope" was not found in any language file.', 53],
            ['Language line "Fixture.nope" was not found in any language file.', 61],
        ]);
    }
}
