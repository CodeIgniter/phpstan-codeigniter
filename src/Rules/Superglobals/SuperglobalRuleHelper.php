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

namespace CodeIgniter\PHPStan\Rules\Superglobals;

use InvalidArgumentException;

final class SuperglobalRuleHelper
{
    public function isHandledSuperglobal(string $name): bool
    {
        return in_array($name, ['_SERVER', '_GET'], true);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getSuperglobalMethodSetter(string $name): string
    {
        switch ($name) {
            case '_SERVER':
                return 'setServer';

            case '_GET':
                return 'setGet';

            default:
                throw new InvalidArgumentException(sprintf('Unhandled superglobal: "%s".', $name));
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getSuperglobalMethodGetter(string $name): string
    {
        switch ($name) {
            case '_SERVER':
                return 'server';

            case '_GET':
                return 'get';

            default:
                throw new InvalidArgumentException(sprintf('Unhandled superglobal: "%s".', $name));
        }
    }
}
