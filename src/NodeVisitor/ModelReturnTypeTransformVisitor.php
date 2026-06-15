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

/**
 * Annotates `find()`/`findAll()`/`first()` calls with the effective return type forced by an
 * `asArray()`/`asObject()` and with the `select()` argument expressions earlier in the same chain.
 */
final class ModelReturnTypeTransformVisitor extends NodeVisitorAbstract
{
    public const RETURN_TYPE               = 'returnType';
    public const SELECTS                   = 'selects';
    private const RETURN_TYPE_GETTERS      = ['find', 'findAll', 'first'];
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

        $getter             = $node;
        $selects            = [];
        $returnTypeResolved = false;

        while ($node->var instanceof MethodCall) {
            $node = $node->var;

            if (! $node->name instanceof Identifier) {
                continue;
            }

            if ($node->name->name === 'select') {
                $args = $node->getArgs();

                if (isset($args[0])) {
                    $selects[] = $args[0]->value;
                }

                continue;
            }

            if ($returnTypeResolved || ! in_array($node->name->name, self::RETURN_TYPE_TRANSFORMERS, true)) {
                continue;
            }

            $returnTypeResolved = true;

            if ($node->name->name === 'asArray') {
                $getter->setAttribute(self::RETURN_TYPE, new Scalar\String_('array'));

                continue;
            }

            $args = $node->getArgs();

            $getter->setAttribute(self::RETURN_TYPE, $args === [] ? new Scalar\String_('object') : $args[0]->value);
        }

        if ($selects !== []) {
            $getter->setAttribute(self::SELECTS, array_reverse($selects));
        }

        return null;
    }
}
