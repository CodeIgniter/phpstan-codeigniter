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

use CodeIgniter\Commands\Utilities\ConfigCheck;
use CodeIgniter\Commands\Utilities\Environment;
use CodeIgniter\PHPStan\NodeVisitor\ConsecutiveAssignsVisitor;
use CodeIgniter\PHPStan\Type\ReflectionHelperMethodInvokerStaticReturnTypeExtension;
use CodeIgniter\PHPStan\Type\ServicesFunctionReturnTypeExtension;
use CodeIgniter\Test\CIUnitTestCase;

use function PHPStan\Testing\assertType;

/**
 * @internal
 */
final class ReflectionHelperMethodInvokerTest extends CIUnitTestCase
{
    public function testOnFirstClassCallable(): void
    {
        assertType(
            'Closure(object|string, string): (Closure(mixed ...$args=): mixed)',
            $this->getPrivateMethodInvoker(...),
        );
    }

    public function testObjectAsObjectType(): void
    {
        assertType('Closure(): void', self::getPrivateMethodInvoker($this, 'testOnFirstClassCallable'));

        $object = new ConsecutiveAssignsVisitor();
        assertType('Closure(PhpParser\Node): null', self::getPrivateMethodInvoker($object, 'enterNode'));
        assertType(
            'Closure(array<PhpParser\Node>): (array<PhpParser\Node>|null)',
            $this->getPrivateMethodInvoker($object, 'afterTraverse'),
        );

        $object = new Environment(service('logger'), service('commands'));
        assertType(
            'Closure(array<int|string, string|null>): int',
            $this->getPrivateMethodInvoker($object, 'run'),
        );
        assertType('Closure(string): bool', $this->getPrivateMethodInvoker($object, 'writeNewEnvironmentToEnvFile'));

        $object = new ConfigCheck(service('logger'), service('commands'));
        assertType(
            'Closure(array<int|string, string|null>): int',
            $this->getPrivateMethodInvoker($object, 'run'),
        );
        assertType('Closure(object): string', $this->getPrivateMethodInvoker($object, 'getVarDump'));
        assertType('Closure(object): string', $this->getPrivateMethodInvoker($object, 'getKintD'));
    }

    public function testClassStringAsObjectType(): void
    {
        assertType('Closure(): never', $this->getPrivateMethodInvoker(self::class, 'testOnFirstClassCallable'));

        $object = ConsecutiveAssignsVisitor::class;
        assertType('Closure(PhpParser\Node): never', $this->getPrivateMethodInvoker($object, 'enterNode'));
        assertType(
            'Closure(array<PhpParser\Node>): never',
            $this->getPrivateMethodInvoker($object, 'afterTraverse'),
        );

        $object = ReflectionHelperMethodInvokerStaticReturnTypeExtension::class;
        assertType(
            'Closure(CodeIgniter\PHPStan\Helpers\ReflectionHelperPrivateInvokerHelper, class-string): never',
            $this->getPrivateMethodInvoker($object, '__construct'),
        );
        assertType(
            'Closure(PHPStan\Reflection\MethodReflection): never',
            $this->getPrivateMethodInvoker($object, 'isStaticMethodSupported'),
        );
        assertType(
            'Closure(PHPStan\Reflection\MethodReflection, PhpParser\Node\Expr\StaticCall, PHPStan\Analyser\Scope): never',
            $this->getPrivateMethodInvoker($object, 'getTypeFromStaticMethodCall'),
        );
    }

    public function testOnNamedArgumentCall(): void
    {
        $object = new ConsecutiveAssignsVisitor();
        assertType(
            'Closure(PhpParser\Node): null',
            $this->getPrivateMethodInvoker(method: 'enterNode', obj: $object),
        );
        assertType(
            'Closure(array<PhpParser\Node>): (array<PhpParser\Node>|null)',
            $this->getPrivateMethodInvoker(obj: $object, method: 'afterTraverse'),
        );
    }

    public function testReturnIsNever(): void
    {
        assertType('*NEVER*', $this->getPrivateMethodInvoker('NotClass', 'foo'));
        assertType('*NEVER*', $this->getPrivateMethodInvoker($this, 'inexistentMethod'));
    }

    public function testOnString(string $object): void
    {
        assertType(
            'Closure(mixed ...): mixed',
            $this->getPrivateMethodInvoker($object, '__construct'),
        );
    }

    /**
     * @param class-string $object
     */
    public function testOnClassString(string $object): void
    {
        assertType(
            'Closure(mixed ...): mixed',
            $this->getPrivateMethodInvoker($object, '__construct'),
        );
    }

    /**
     * @param class-string<ConfigCheck> $class
     */
    public function testOnGenericClassString(string $class): void
    {
        assertType(
            'Closure(Psr\Log\LoggerInterface, CodeIgniter\CLI\Commands): never',
            $this->getPrivateMethodInvoker($class, '__construct'),
        );
    }

    public function testOnObject(object $object): void
    {
        assertType(
            'Closure(mixed ...): mixed',
            $this->getPrivateMethodInvoker($object, '__construct'),
        );
    }

    /**
     * @param $this $object
     */
    public function testOnObjectWithClassType(object $object): void
    {
        assertType(
            'Closure(non-empty-string): $this',
            $this->getPrivateMethodInvoker($object, '__construct'),
        );
    }

    /**
     * @param class-string<ServicesFunctionReturnTypeExtension>|ConfigCheck $object
     */
    public function testOnUnionOfObjects(object|string $object): void
    {
        assertType(
            sprintf(
                '(%s)|(%s)',
                'Closure(CodeIgniter\PHPStan\Helpers\ServicesReturnTypeHelper): never',
                'Closure(Psr\Log\LoggerInterface, CodeIgniter\CLI\Commands): CodeIgniter\Commands\Utilities\ConfigCheck',
            ),
            $this->getPrivateMethodInvoker($object, '__construct'),
        );
    }

    /**
     * @param 'NotClass'|class-string<Environment> $object
     */
    public function testOnUnionOfStringObjectsWithOneNonClass(string $object): void
    {
        assertType(
            'Closure(Psr\Log\LoggerInterface, CodeIgniter\CLI\Commands): never',
            $this->getPrivateMethodInvoker($object, '__construct'),
        );
    }

    /**
     * @param '__construct'|'testReturnIsNever' $method
     */
    public function testOnUnionOfMethods(string $method): void
    {
        assertType(
            '(Closure(): void)|(Closure(non-empty-string): $this)',
            $this->getPrivateMethodInvoker($this, $method),
        );
    }

    public function testOnVariadicArguments(): void
    {
        $anon = new class () {
            public function testing(string $a, int $b, bool $c, string ...$d): void {}
        };

        assertType(
            'Closure(string, int, bool, string ...): void',
            $this->getPrivateMethodInvoker($anon, 'testing'),
        );
    }
}
