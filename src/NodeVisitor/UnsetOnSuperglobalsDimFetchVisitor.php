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

final class UnsetOnSuperglobalsDimFetchVisitor extends NodeVisitorAbstract
{
    public const VISITOR_KEY = 'unset_on_superglobals_dim_fetch';

    /**
     * @return null
     */
    public function enterNode(Node $node)
    {
        if (! $node instanceof Node\Stmt\Unset_) {
            return null;
        }

        foreach ($node->vars as $i => $var) {
            if (! $var instanceof Node\Expr\ArrayDimFetch) {
                continue;
            }

            if (! $var->var instanceof Node\Expr\Variable) {
                continue;
            }

            $varName = $var->var->name;

            if (! is_string($varName)) {
                continue;
            }

            if (! array_key_exists($varName, SuperglobalsHelper::HANDLED_SUPERGLOBALS)) {
                continue;
            }

            $var->setAttribute(self::VISITOR_KEY, true);
        }

        return null;
    }
}
