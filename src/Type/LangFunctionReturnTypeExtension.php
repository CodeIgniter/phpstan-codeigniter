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

use CodeIgniter\PHPStan\Helpers\LanguageLineTypeHelper;
use CodeIgniter\PHPStan\Helpers\ResultCacheDependencyTracker;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\DependencyInjection\AutowiredService;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\Type;

/**
 * Types `lang()` from the translation found in the language files.
 */
#[AutowiredService]
final class LangFunctionReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
    public function __construct(
        private readonly LanguageLineTypeHelper $languageLineTypeHelper,
        private readonly ResultCacheDependencyTracker $resultCacheDependencyTracker,
    ) {}

    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return $functionReflection->getName() === 'lang';
    }

    public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): ?Type
    {
        $args = $functionCall->getArgs();

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
