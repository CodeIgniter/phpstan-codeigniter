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

namespace CodeIgniter\PHPStan\Helpers;

use CodeIgniter\Config\DotEnv;
use CodeIgniter\Superglobals;
use InvalidArgumentException;
use PHPStan\Analyser\Scope;

final class SuperglobalsHelper
{
    public const HANDLED_SUPERGLOBALS = [
        '_SERVER'  => 'server',
        '_GET'     => 'get',
        '_POST'    => 'post',
        '_COOKIE'  => 'cookie',
        '_FILES'   => 'files',
        '_REQUEST' => 'request',
    ];
    public const SERVER_ITEMS_WITH_NON_STRING_TYPE = [
        'argv'               => 'array<array-key, mixed>',
        'argc'               => 'int',
        'REQUEST_TIME'       => 'int',
        'REQUEST_TIME_FLOAT' => 'float',
    ];

    public function isHandledSuperglobal(string $name, Scope $scope): bool
    {
        if (! array_key_exists($name, self::HANDLED_SUPERGLOBALS)) {
            return false;
        }

        if ($scope->getFunction() === null) {
            return false; // ignore uses in root level (not inside function or method)
        }

        return ! $scope->isInClass() || ! in_array($scope->getClassReflection()->getName(), [
            DotEnv::class, // `service()` is not yet loaded here
            Superglobals::class,
        ], true);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getMethodGetter(string $name): ?string
    {
        return match ($name) {
            '_SERVER'  => 'server',
            '_GET'     => 'get',
            '_POST'    => 'post',
            '_COOKIE'  => 'cookie',
            '_FILES'   => null,
            '_REQUEST' => 'request',
            default    => throw new InvalidArgumentException(sprintf('Superglobal $%s is not handled.', $name)),
        };
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getMethodGlobalGetter(string $name): string
    {
        return match ($name) {
            '_SERVER'  => 'getServerArray',
            '_GET'     => 'getGetArray',
            '_POST'    => 'getPostArray',
            '_COOKIE'  => 'getCookieArray',
            '_FILES'   => 'getFilesArray',
            '_REQUEST' => 'getRequestArray',
            default    => throw new InvalidArgumentException(sprintf('Superglobal $%s is not handled.', $name)),
        };
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getMethodSetter(string $name): ?string
    {
        return match ($name) {
            '_SERVER'  => 'setServer',
            '_GET'     => 'setGet',
            '_POST'    => 'setPost',
            '_COOKIE'  => 'setCookie',
            '_FILES'   => null,
            '_REQUEST' => 'setRequest',
            default    => throw new InvalidArgumentException(sprintf('Superglobal $%s is not handled.', $name)),
        };
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getMethodGlobalSetter(string $name): string
    {
        return match ($name) {
            '_SERVER'  => 'setServerArray',
            '_GET'     => 'setGetArray',
            '_POST'    => 'setPostArray',
            '_COOKIE'  => 'setCookieArray',
            '_FILES'   => 'setFilesArray',
            '_REQUEST' => 'setRequestArray',
            default    => throw new InvalidArgumentException(sprintf('Superglobal $%s is not handled.', $name)),
        };
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getMethodUnsetter(string $name): ?string
    {
        return match ($name) {
            '_SERVER'  => 'unsetServer',
            '_GET'     => 'unsetGet',
            '_POST'    => 'unsetPost',
            '_COOKIE'  => 'unsetCookie',
            '_FILES'   => null,
            '_REQUEST' => 'unsetRequest',
            default    => throw new InvalidArgumentException(sprintf('Superglobal $%s is not handled.', $name)),
        };
    }
}
