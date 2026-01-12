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

namespace CodeIgniter\PHPStan\Tests\Fixtures;

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\CLI\Commands;
use CodeIgniter\Config\Services;
use CodeIgniter\Debug\Exceptions;
use CodeIgniter\Debug\Iterator;
use CodeIgniter\Debug\Toolbar;
use CodeIgniter\Email\Email;
use CodeIgniter\Filters\Filters;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Log\Logger;
use CodeIgniter\Pager\Pager;
use CodeIgniter\Validation\ValidationInterface;
use CodeIgniter\View\Cell;
use CodeIgniter\View\Parser;
use CodeIgniter\View\View;

use function PHPStan\Testing\assertType;

final class SharedServices extends Services
{
    public static function forTesting(): void
    {
        assertType(CacheInterface::class, self::getSharedInstance('cache'));
        assertType(Commands::class, self::getSharedInstance('commands'));
        assertType(CLIRequest::class, self::getSharedInstance('clirequest'));
        assertType(Email::class, self::getSharedInstance('email'));
        assertType(Exceptions::class, self::getSharedInstance('exceptions'));
        assertType(Filters::class, self::getSharedInstance('filters'));
        assertType(Iterator::class, self::getSharedInstance('iterator'));
        assertType(Logger::class, self::getSharedInstance('logger'));
        assertType(Pager::class, self::getSharedInstance('pager'));
        assertType(Parser::class, self::getSharedInstance('parser'));
        assertType(View::class, self::getSharedInstance('renderer'));
        assertType('CodeIgniter\HTTP\CLIRequest|CodeIgniter\HTTP\IncomingRequest', self::getSharedInstance('request'));
        assertType(ResponseInterface::class, self::getSharedInstance('response'));
        assertType(Toolbar::class, self::getSharedInstance('toolbar'));
        assertType(ValidationInterface::class, self::getSharedInstance('validation'));
        assertType(Cell::class, self::getSharedInstance('viewcell'));
        assertType('null', self::getSharedInstance('createRequest'));
    }
}
