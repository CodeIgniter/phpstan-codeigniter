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

namespace CodeIgniter\PHPStan\Tests\Rules;

function test_services_argument_type(): void
{
    service('logger'); // valid
    service('non_existent_service'); // invalid

    $serviceName = 'logger';
    service($serviceName); // valid

    single_service('createRequest'); // invalid
}

/**
 * @param 'email'|'superglobals' $service
 */
function test_services_union_argument_type(string $service): void
{
    service($service); // valid
}

/**
 * @param 'bar'|'baz' $service
 */
function test_services_union_argument_type_invalid(string $service): void
{
    service($service); // invalid
}

function test_services_unknown_argument_string_type(string $service): void
{
    service($service); // skip checking
}

function test_services_invalid_return_types(): void
{
    service('boolreturn'); // invalid bool return type
    service('intreturn'); // invalid int return type
    service('voidreturn'); // invalid void return type
}
