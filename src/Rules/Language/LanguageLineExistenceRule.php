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

namespace CodeIgniter\PHPStan\Rules\Language;

use CodeIgniter\Language\Language;
use CodeIgniter\PHPStan\Helpers\LanguageLineTypeHelper;
use CodeIgniter\PHPStan\Helpers\ResultCacheDependencyTracker;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\DependencyInjection\AutowiredParameter;
use PHPStan\DependencyInjection\RegisteredRule;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<Node\Expr\CallLike>
 */
#[RegisteredRule(level: 0)]
final class LanguageLineExistenceRule implements Rule
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,

        private readonly LanguageLineTypeHelper $languageLineTypeHelper,

        private readonly ResultCacheDependencyTracker $resultCacheDependencyTracker,

        #[AutowiredParameter(ref: '%codeigniter.reportUntranslatedLanguageLines%')]
        private readonly bool $reportUntranslatedLines,
    ) {}

    public function getNodeType(): string
    {
        return Node\Expr\CallLike::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (! $this->isLanguageLineCall($node, $scope)) {
            return [];
        }

        $args = $node->getArgs();

        if (! isset($args[0])) {
            return [];
        }

        $this->resultCacheDependencyTracker->trackLanguage($scope);

        $errors = [];

        foreach ($this->languageLineTypeHelper->constantLines($scope->getType($args[0]->value)) as $line) {
            if ($this->languageLineTypeHelper->isMissing($line)) {
                $errors[] = RuleErrorBuilder::message(sprintf('Language line "%s" was not found in any language file.', $line))
                    ->identifier('codeigniter.languageLineNotFound')
                    ->build();

                continue;
            }

            if (! $this->reportUntranslatedLines) {
                continue;
            }

            $untranslated = $this->languageLineTypeHelper->untranslatedLocales($line);

            if ($untranslated !== []) {
                $errors[] = RuleErrorBuilder::message(sprintf('Language line "%s" is not translated in: %s.', $line, implode(', ', $untranslated)))
                    ->identifier('codeigniter.languageLineUntranslated')
                    ->build();
            }
        }

        return $errors;
    }

    private function isLanguageLineCall(Node $node, Scope $scope): bool
    {
        if ($node instanceof Node\Expr\FuncCall) {
            return $node->name instanceof Node\Name
                && $this->reflectionProvider->resolveFunctionName($node->name, $scope) === 'lang';
        }

        if ($node instanceof Node\Expr\MethodCall) {
            return $node->name instanceof Node\Identifier
                && $node->name->toLowerString() === 'getline'
                && (new ObjectType(Language::class))->isSuperTypeOf($scope->getType($node->var))->yes();
        }

        return false;
    }
}
