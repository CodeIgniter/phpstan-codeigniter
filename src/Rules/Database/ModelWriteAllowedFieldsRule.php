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

namespace CodeIgniter\PHPStan\Rules\Database;

use CodeIgniter\BaseModel;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\DependencyInjection\RegisteredRule;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node\Expr\MethodCall>
 */
#[RegisteredRule(level: 0)]
final class ModelWriteAllowedFieldsRule implements Rule
{
    private const DATA_ARGUMENT_POSITIONS = [
        'insert' => 0,
        'save'   => 0,
        'update' => 1,
    ];

    public function getNodeType(): string
    {
        return Node\Expr\MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->name instanceof Node\Identifier) {
            return [];
        }

        $method = $node->name->toLowerString();

        if (! array_key_exists($method, self::DATA_ARGUMENT_POSITIONS)) {
            return [];
        }

        $args = $node->getArgs();

        if (! isset($args[self::DATA_ARGUMENT_POSITIONS[$method]])) {
            return [];
        }

        $classReflections = $scope->getType($node->var)->getObjectClassReflections();

        if (count($classReflections) !== 1 || ! $classReflections[0]->is(BaseModel::class)) {
            return [];
        }

        $nativeReflection = $classReflections[0]->getNativeReflection();
        $defaults         = $nativeReflection->getDefaultProperties();
        $allowedFields    = $defaults['allowedFields'] ?? [];

        if (($defaults['protectFields'] ?? true) !== true || ! is_array($allowedFields) || $allowedFields === []) {
            return [];
        }

        $errors = [];
        $keys   = [];

        foreach ($scope->getType($args[self::DATA_ARGUMENT_POSITIONS[$method]]->value)->getConstantArrays() as $constantArray) {
            foreach ($constantArray->getKeyTypes() as $keyType) {
                foreach ($keyType->getConstantStrings() as $key) {
                    $keys[$key->getValue()] = true;
                }
            }
        }

        foreach (array_keys($keys) as $key) {
            if (in_array($key, $allowedFields, true)) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf(
                'Field "%s" is not in %s::$allowedFields and is dropped by %s().',
                $key,
                $classReflections[0]->getDisplayName(),
                $node->name->name,
            ))
                ->identifier('codeigniter.modelFieldNotAllowed')
                ->build();
        }

        return $errors;
    }
}
