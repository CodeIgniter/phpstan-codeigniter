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
    public const VISITOR_KEY    = 'variable_context_on_superglobals_dim_fetch';
    public const CONTEXT_ISSET  = 'isset';
    public const CONTEXT_WRITE  = 'write';
    public const CONTEXT_NESTED = 'nested';
    public const ISSET_KEY      = 'isset_of_superglobals_dim_fetch';

    /**
     * @return null
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Expr\Isset_) {
            foreach ($node->vars as $var) {
                if (! $this->isSuperglobalFetch($var)) {
                    continue;
                }

                $var->setAttribute(self::VISITOR_KEY, self::CONTEXT_ISSET);

                if (count($node->vars) === 1) {
                    $var->setAttribute(self::ISSET_KEY, $node);
                }
            }
        } elseif ($node instanceof Node\Expr\AssignOp || $node instanceof Node\Expr\AssignRef) {
            $this->mark($node->var, self::CONTEXT_WRITE);

            if ($node instanceof Node\Expr\AssignRef) {
                $this->mark($node->expr, self::CONTEXT_WRITE);
            }
        } elseif (
            $node instanceof Node\Expr\PreInc
            || $node instanceof Node\Expr\PreDec
            || $node instanceof Node\Expr\PostInc
            || $node instanceof Node\Expr\PostDec
        ) {
            $this->mark($node->var, self::CONTEXT_WRITE);
        } elseif ($node instanceof Node\Stmt\Foreach_) {
            $this->mark($node->valueVar, self::CONTEXT_WRITE);

            if ($node->keyVar !== null) {
                $this->mark($node->keyVar, self::CONTEXT_WRITE);
            }
        } elseif ($node instanceof Node\Expr\Assign && $node->var instanceof Node\Expr\Array_) {
            $this->mark($node->var, self::CONTEXT_WRITE);
        }

        return null;
    }

    private function mark(Node\Expr $expr, string $context): void
    {
        if ($expr instanceof Node\Expr\Array_) {
            foreach ($expr->items as $item) {
                $this->mark($item->value, $context);
            }

            return;
        }

        while ($expr instanceof Node\Expr\ArrayDimFetch) {
            if ($this->isSuperglobalFetch($expr)) {
                $expr->setAttribute(self::VISITOR_KEY, $context);
            }

            $expr    = $expr->var;
            $context = self::CONTEXT_NESTED;
        }
    }

    private function isSuperglobalFetch(Node\Expr $expr): bool
    {
        return $expr instanceof Node\Expr\ArrayDimFetch
            && $expr->var instanceof Node\Expr\Variable
            && is_string($expr->var->name)
            && array_key_exists($expr->var->name, SuperglobalsHelper::HANDLED_SUPERGLOBALS);
    }
}
