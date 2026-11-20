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

namespace CodeIgniter\PHPStan\Rules\Classes;

use CodeIgniter\BaseModel;
use CodeIgniter\DataCaster\Cast\CastInterface;
use CodeIgniter\Entity\Cast\CastInterface as EntityCastInterface;
use CodeIgniter\Entity\Entity;
use CodeIgniter\PHPStan\Database\Schema\CastTypeResolver;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\DependencyInjection\RegisteredRule;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<InClassNode>
 */
#[RegisteredRule(level: 0)]
final class CastHandlerRule implements Rule
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly CastTypeResolver $castTypeResolver,
    ) {}

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $node->getClassReflection();

        if (! $classReflection->is(Entity::class) && ! $classReflection->is(BaseModel::class)) {
            return [];
        }

        $classNode    = $node->getOriginalNode();
        $castsNode    = $classNode->getProperty('casts');
        $handlersNode = $classNode->getProperty('castHandlers');
        $line         = $castsNode?->getStartLine() ?? $handlersNode?->getStartLine();
        $castLines    = $this->entryLines($castsNode);
        $handlerLines = $this->entryLines($handlersNode);

        if ($line === null) {
            return [];
        }

        $defaults = $classReflection->getNativeReflection()->getDefaultProperties();
        $casts    = $defaults['casts'] ?? [];
        $handlers = $defaults['castHandlers'] ?? [];

        if (! is_array($casts) || ! is_array($handlers)) {
            return [];
        }

        $handlerMap = [];

        foreach ($handlers as $name => $handler) {
            if (is_string($handler)) {
                $handlerMap[(string) $name] = $handler;
            }
        }

        $errors = [];

        foreach ($casts as $field => $cast) {
            if (! is_string($cast)) {
                continue;
            }

            $castLine = $castLines[(string) $field] ?? $line;
            $error    = $this->check((string) $field, $cast, $handlerMap, $castLine, $handlerLines);

            if ($error !== null) {
                $errors[] = $error;
            }
        }

        return $errors;
    }

    /**
     * @return array<string, int> the line of each string-keyed entry in the property's array default
     */
    private function entryLines(?Node\Stmt\Property $property): array
    {
        $lines = [];

        foreach ($property->props ?? [] as $item) {
            if (! $item->default instanceof Node\Expr\Array_) {
                continue;
            }

            foreach ($item->default->items as $entry) {
                if ($entry->key instanceof Node\Scalar\String_) {
                    $lines[$entry->key->value] = $entry->getStartLine();
                }
            }
        }

        return $lines;
    }

    /**
     * @param array<string, string> $handlers
     * @param array<string, int>    $handlerLines
     */
    private function check(string $field, string $cast, array $handlers, int $castLine, array $handlerLines): ?IdentifierRuleError
    {
        if ($this->castTypeResolver->resolve($cast) !== null) {
            return null;
        }

        $name = ltrim($cast, '?');

        if (preg_match('/\A(.+)\[.+\]\z/', $name, $matches) === 1) {
            $name = $matches[1];
        }

        $handler = $handlers[$name] ?? null;

        if ($handler === null) {
            return RuleErrorBuilder::message(sprintf('Cast "%s" of field "%s" is not a built-in cast and has no handler in $castHandlers.', $name, $field))
                ->identifier('codeigniter.castHandlerNotRegistered')
                ->line($castLine)
                ->build();
        }

        if (! $this->reflectionProvider->hasClass($handler)) {
            return RuleErrorBuilder::message(sprintf('Cast handler "%s" of cast "%s" does not exist.', $handler, $name))
                ->identifier('codeigniter.castHandlerNotFound')
                ->line($handlerLines[$name] ?? $castLine)
                ->build();
        }

        $handlerReflection = $this->reflectionProvider->getClass($handler);

        if (! $handlerReflection->implementsInterface(CastInterface::class) && ! $handlerReflection->implementsInterface(EntityCastInterface::class)) {
            return RuleErrorBuilder::message(sprintf(
                'Cast handler "%s" of cast "%s" must implement %s or %s.',
                $handler,
                $name,
                CastInterface::class,
                EntityCastInterface::class,
            ))
                ->identifier('codeigniter.castHandlerInvalid')
                ->line($handlerLines[$name] ?? $castLine)
                ->build();
        }

        return null;
    }
}
