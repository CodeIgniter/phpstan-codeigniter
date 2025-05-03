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

namespace CodeIgniter\PHPStan\Tests\Fixtures\Type;

use CodeIgniter\Commands\Utilities\ConfigCheck;
use CodeIgniter\Commands\Utilities\Environment;
use CodeIgniter\PHPStan\NodeVisitor\ModelReturnTypeTransformVisitor;
use CodeIgniter\PHPStan\Type\FactoriesFunctionReturnTypeExtension;
use CodeIgniter\PHPStan\Type\ServicesFunctionReturnTypeExtension;
use CodeIgniter\Test\CIUnitTestCase;

use function PHPStan\Testing\assertType;

/**
 * @internal
 */
final class ReflectionHelperGetPrivateMethodInvokerTest extends CIUnitTestCase
{
    public function testOnFirstClassCallable(): void
    {
        assertType(
            'Closure(object|string, string): (Closure(mixed ...$args=): mixed)',
            self::getPrivateMethodInvoker(...),
        );
    }

    public function testObjectAsObjectType(): void
    {
        assertType('Closure(): void', self::getPrivateMethodInvoker($this, 'testOnFirstClassCallable'));

        $object = new ModelReturnTypeTransformVisitor();
        assertType('Closure(PhpParser\Node): null', self::getPrivateMethodInvoker($object, 'enterNode'));
        assertType(
            'Closure(array<PhpParser\Node>): (array<PhpParser\Node>|null)',
            self::getPrivateMethodInvoker($object, 'afterTraverse'),
        );

        $object = new Environment(service('logger'), service('commands'));
        assertType(
            'Closure(array<int|string, string|null>): int',
            self::getPrivateMethodInvoker($object, 'run'),
        );
        assertType('Closure(string): bool', self::getPrivateMethodInvoker($object, 'writeNewEnvironmentToEnvFile'));

        $object = new ConfigCheck(service('logger'), service('commands'));
        assertType(
            'Closure(array<int|string, string|null>): int',
            self::getPrivateMethodInvoker($object, 'run'),
        );
        assertType('Closure(object): string', self::getPrivateMethodInvoker($object, 'getVarDump'));
        assertType('Closure(object): string', self::getPrivateMethodInvoker($object, 'getKintD'));
    }

    public function testClassStringAsObjectType(): void
    {
        assertType('Closure(): never', self::getPrivateMethodInvoker(self::class, 'testOnFirstClassCallable'));

        $object = ModelReturnTypeTransformVisitor::class;
        assertType('Closure(PhpParser\Node): never', self::getPrivateMethodInvoker($object, 'enterNode'));
        assertType(
            'Closure(array<PhpParser\Node>): never',
            self::getPrivateMethodInvoker($object, 'afterTraverse'),
        );

        $object = FactoriesFunctionReturnTypeExtension::class;
        assertType(
            'Closure(CodeIgniter\PHPStan\Type\FactoriesReturnTypeHelper): never',
            self::getPrivateMethodInvoker($object, '__construct'),
        );
        assertType(
            'Closure(PHPStan\Reflection\FunctionReflection): never',
            self::getPrivateMethodInvoker($object, 'isFunctionSupported'),
        );
        assertType(
            'Closure(PHPStan\Reflection\FunctionReflection, PhpParser\Node\Expr\FuncCall, PHPStan\Analyser\Scope): never',
            self::getPrivateMethodInvoker($object, 'getTypeFromFunctionCall'),
        );
    }

    public function testOnNamedArgumentCall(): void
    {
        $object = new ModelReturnTypeTransformVisitor();
        assertType(
            'Closure(PhpParser\Node): null',
            self::getPrivateMethodInvoker(method: 'enterNode', obj: $object),
        );
        assertType(
            'Closure(array<PhpParser\Node>): (array<PhpParser\Node>|null)',
            self::getPrivateMethodInvoker(obj: $object, method: 'afterTraverse'),
        );
    }

    public function testReturnIsNever(): void
    {
        assertType('*NEVER*', self::getPrivateMethodInvoker('NotClass', 'foo'));
        assertType('*NEVER*', self::getPrivateMethodInvoker($this, 'inexistentMethod'));
    }

    public function testOnString(string $object): void
    {
        assertType(
            'Closure(mixed ...): mixed',
            self::getPrivateMethodInvoker($object, '__construct'),
        );
    }

    /**
     * @param class-string $object
     */
    public function testOnClassString(string $object): void
    {
        assertType(
            'Closure(mixed ...): mixed',
            self::getPrivateMethodInvoker($object, '__construct'),
        );
    }

    /**
     * @param class-string<ConfigCheck> $class
     */
    public function testOnGenericClassString(string $class): void
    {
        assertType(
            'Closure(Psr\Log\LoggerInterface, CodeIgniter\CLI\Commands): never',
            self::getPrivateMethodInvoker($class, '__construct'),
        );
    }

    public function testOnObject(object $object): void
    {
        assertType(
            'Closure(mixed ...): mixed',
            self::getPrivateMethodInvoker($object, '__construct'),
        );
    }

    /**
     * @param $this $object
     */
    public function testOnObjectWithClassType(object $object): void
    {
        assertType(
            'Closure(non-empty-string): $this',
            self::getPrivateMethodInvoker($object, '__construct'),
        );
    }

    /**
     * @param class-string<ServicesFunctionReturnTypeExtension>|self $object
     */
    public function testOnUnionOfObjects(object|string $object): void
    {
        assertType(
            sprintf(
                '%s|%s',
                '(Closure(CodeIgniter\PHPStan\Type\ServicesReturnTypeHelper): never)',
                '(Closure(non-empty-string): CodeIgniter\PHPStan\Tests\Fixtures\Type\ReflectionHelperGetPrivateMethodInvokerTest)',
            ),
            self::getPrivateMethodInvoker($object, '__construct'),
        );
    }

    /**
     * @param 'NotClass'|class-string<Environment> $object
     */
    public function testOnUnionOfStringObjectsWithOneNonClass(string $object): void
    {
        assertType(
            'Closure(Psr\Log\LoggerInterface, CodeIgniter\CLI\Commands): never',
            self::getPrivateMethodInvoker($object, '__construct'),
        );
    }

    /**
     * @param '__construct'|'testReturnIsNever' $method
     */
    public function testOnUnionOfMethods(string $method): void
    {
        assertType(
            '(Closure(): void)|(Closure(non-empty-string): $this)',
            self::getPrivateMethodInvoker($this, $method),
        );
    }

    public function testOnVariadicArguments(): void
    {
        $anon = new class () {
            public function testing(string $a, int $b, bool $c, string ...$d): void {}
        };

        assertType(
            'Closure(string, int, bool, string ...): void',
            self::getPrivateMethodInvoker($anon, 'testing'),
        );
    }
}
