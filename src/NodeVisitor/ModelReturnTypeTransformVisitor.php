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

namespace CodeIgniter\PHPStan\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar;
use PhpParser\NodeVisitorAbstract;

final class ModelReturnTypeTransformVisitor extends NodeVisitorAbstract
{
    public const RETURN_TYPE = 'returnType';

    /**
     * @var list<string>
     */
    private const RETURN_TYPE_GETTERS = ['find', 'findAll', 'first'];

    /**
     * @var list<string>
     */
    private const RETURN_TYPE_TRANSFORMERS = ['asArray', 'asObject'];

    /**
     * @return null
     */
    public function enterNode(Node $node)
    {
        if (! $node instanceof MethodCall) {
            return null;
        }

        if (! $node->name instanceof Identifier) {
            return null;
        }

        if (! in_array($node->name->name, self::RETURN_TYPE_GETTERS, true)) {
            return null;
        }

        $lastNode = $node;

        while ($node->var instanceof MethodCall) {
            $node = $node->var;

            if (! $node->name instanceof Identifier) {
                continue;
            }

            if (! in_array($node->name->name, self::RETURN_TYPE_TRANSFORMERS, true)) {
                continue;
            }

            if ($node->name->name === 'asArray') {
                $lastNode->setAttribute(self::RETURN_TYPE, new Scalar\String_('array'));
                break;
            }

            $args = $node->getArgs();

            if ($args === []) {
                $lastNode->setAttribute(self::RETURN_TYPE, new Scalar\String_('object'));
                break;
            }

            $lastNode->setAttribute(self::RETURN_TYPE, $args[0]->value);
            break;
        }

        return null;
    }
}
