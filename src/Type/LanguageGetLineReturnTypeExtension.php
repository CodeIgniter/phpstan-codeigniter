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

namespace CodeIgniter\PHPStan\Type;

use CodeIgniter\Language\Language;
use CodeIgniter\PHPStan\Helpers\LanguageLineTypeHelper;
use CodeIgniter\PHPStan\Helpers\ResultCacheDependencyTracker;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\DependencyInjection\AutowiredService;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;

/**
 * Types `Language::getLine()` from the translation found in the language files.
 */
#[AutowiredService]
final class LanguageGetLineReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function __construct(
        private readonly LanguageLineTypeHelper $languageLineTypeHelper,
        private readonly ResultCacheDependencyTracker $resultCacheDependencyTracker,
    ) {}

    public function getClass(): string
    {
        return Language::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getLine';
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();

        if (! isset($args[0])) {
            return null;
        }

        $this->resultCacheDependencyTracker->trackLanguage($scope);

        return $this->languageLineTypeHelper->resolve(
            $scope->getType($args[0]->value),
            isset($args[1]) ? $scope->getType($args[1]->value) : null,
        );
    }
}
