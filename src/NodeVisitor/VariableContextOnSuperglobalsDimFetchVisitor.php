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

use CodeIgniter\PHPStan\Helpers\SuperglobalsHelper;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PHPStan\DependencyInjection\AutowiredService;

/**
 * Marks superglobal offset fetches in positions that PHP only accepts as a variable, not as a call result.
 */
#[AutowiredService]
final class VariableContextOnSuperglobalsDimFetchVisitor extends NodeVisitorAbstract
{
    public const VISITOR_KEY = 'variable_context_on_superglobals_dim_fetch';

    /**
     * @return null
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Expr\Isset_) {
            foreach ($node->vars as $var) {
                $this->mark($var);
            }
        } elseif ($node instanceof Node\Expr\AssignOp || $node instanceof Node\Expr\AssignRef) {
            $this->mark($node->var);

            if ($node instanceof Node\Expr\AssignRef) {
                $this->mark($node->expr);
            }
        } elseif (
            $node instanceof Node\Expr\PreInc
            || $node instanceof Node\Expr\PreDec
            || $node instanceof Node\Expr\PostInc
            || $node instanceof Node\Expr\PostDec
        ) {
            $this->mark($node->var);
        } elseif ($node instanceof Node\Stmt\Foreach_) {
            $this->mark($node->valueVar);

            if ($node->keyVar !== null) {
                $this->mark($node->keyVar);
            }
        } elseif ($node instanceof Node\Expr\Assign && $node->var instanceof Node\Expr\Array_) {
            $this->mark($node->var);
        }

        return null;
    }

    private function mark(Node\Expr $expr): void
    {
        if ($expr instanceof Node\Expr\Array_) {
            foreach ($expr->items as $item) {
                $this->mark($item->value);
            }

            return;
        }

        while ($expr instanceof Node\Expr\ArrayDimFetch) {
            if (
                $expr->var instanceof Node\Expr\Variable
                && is_string($expr->var->name)
                && array_key_exists($expr->var->name, SuperglobalsHelper::HANDLED_SUPERGLOBALS)
            ) {
                $expr->setAttribute(self::VISITOR_KEY, true);
            }

            $expr = $expr->var;
        }
    }
}
