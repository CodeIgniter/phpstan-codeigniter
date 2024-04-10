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

use CodeIgniter\Autoloader\Autoloader;
use CodeIgniter\Autoloader\FileLocatorInterface;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\CLI\Commands;
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

// from CodeIgniter\Config\Services
assertType(CacheInterface::class, service('cache'));
assertType(Commands::class, service('commands'));
assertType(CLIRequest::class, single_service('clirequest'));
assertType(Email::class, service('email'));
assertType(Exceptions::class, single_service('exceptions'));
assertType(Filters::class, service('filters'));
assertType(Iterator::class, service('iterator'));
assertType(Logger::class, single_service('logger'));
assertType(Pager::class, service('pager'));
assertType(Parser::class, service('parser'));
assertType(View::class, service('renderer'));
assertType('CodeIgniter\HTTP\CLIRequest|CodeIgniter\HTTP\IncomingRequest', service('request'));
assertType(ResponseInterface::class, service('response'));
assertType(Toolbar::class, service('toolbar'));
assertType(ValidationInterface::class, service('validation'));
assertType(Cell::class, service('viewcell'));
assertType('null', service('createRequest'));

// from CodeIgniter\Config\BaseService
assertType(Autoloader::class, single_service('autoloader'));
assertType(FileLocatorInterface::class, service('locator'));
assertType('null', service('__callStatic'));
assertType('null', service('serviceExists'));
assertType('null', service('reset'));
assertType('null', service('resetSingle'));
assertType('null', service('injectMock'));

// from OtherServices
// this should be overridden by OtherServices
assertType(stdClass::class, service('migrations'));
assertType(Closure::class, service('invoker'));

// gibberish
assertType('null', single_service('bar'));
assertType('null', single_service('timers'));

return [
    single_service('toBool'),
    service('noReturn'),
    service('returnNull'),
];
