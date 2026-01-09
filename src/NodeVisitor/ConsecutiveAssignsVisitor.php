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
use PhpParser\NodeVisitorAbstract;

final class ConsecutiveAssignsVisitor extends NodeVisitorAbstract
{
    public const VISITOR_KEY = 'consecutive_assigns';

    public function enterNode(Node $node): null
    {
        if (! $node instanceof Node\Expr\Assign) {
            return null;
        }

        $expr = $node->expr;

        if ($expr instanceof Node\Expr\Assign) {
            $node->setAttribute(self::VISITOR_KEY, true);
        }

        while (true) {
            if (! $expr instanceof Node\Expr\Assign) {
                break;
            }

            $expr->setAttribute(self::VISITOR_KEY, true);
            $expr = $expr->expr;
        }

        return null;
    }
}
