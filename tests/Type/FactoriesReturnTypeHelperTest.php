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

namespace CodeIgniter\PHPStan\Tests\Type;

use CodeIgniter\PHPStan\Type\FactoriesReturnTypeHelper;
use Config\App;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Testing\PHPStanTestCase;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
#[Group('Unit')]
final class FactoriesReturnTypeHelperTest extends PHPStanTestCase
{
    public static function provideCheckOfReturnTypeCases(): iterable
    {
        yield 'null type returns null type' => [new NullType(), new NullType()];

        yield 'boolean type returns null type' => [new NullType(), new ConstantBooleanType(true)];

        yield 'non class string returns null type' => [new NullType(), new ConstantStringType('Bar')];

        yield 'constant class string' => [new ObjectType(App::class), new ConstantStringType(App::class, true)];

        yield 'class string' => [new ObjectWithoutClassType(), new ClassStringType()];

        yield 'union type' => [new UnionType([new NullType(), new ObjectType(App::class)]), new UnionType([new ConstantStringType('Bar'), new ConstantStringType(App::class, true)])];
    }

    public static function provideCheckUsingReflectionProviderCases(): iterable
    {
        yield 'short class name' => [new ObjectType(App::class), new ConstantStringType('App')];

        yield 'module config' => [new ObjectType('Acme\Blog\Config\Bar'), new ConstantStringType('Bar')];

        yield 'module model' => [new ObjectType('Acme\Blog\Models\Foo'), new ConstantStringType('Foo'), 'model'];
    }

    #[DataProvider('provideCheckOfReturnTypeCases')]
    public function testCheckOfReturnType(Type $expectedType, Type $inputType, string $function = 'config'): void
    {
        $actualType = $this->createFactoriesReturnTypeHelper()->check($inputType, $function);
        self::assertInstanceOf($expectedType::class, $actualType);

        $expected = $expectedType->describe(VerbosityLevel::precise());
        $actual   = $actualType->describe(VerbosityLevel::precise());
        self::assertSame($expected, $actual);
    }

    #[DataProvider('provideCheckUsingReflectionProviderCases')]
    public function testCheckUsingReflectionProvider(Type $expectedType, Type $inputType, string $function = 'config'): void
    {
        // this is needed to prevent errors arising only in random executions
        // this populates ReflectionProviderStaticAccessor::registerInstance()
        self::createReflectionProvider();

        /** @var MockObject&ReflectionProvider $reflectionProvider */
        $reflectionProvider = $this->createMock(ReflectionProvider::class);
        $reflectionProvider
            ->method('hasClass')
            ->willReturnMap([
                ['Config\App', true],
                ['Config\Bar', false],
                ['App\Models\Foo', false],
                ['Acme\Blog\Config\Bar', true],
                ['Acme\Blog\Models\Foo', true],
            ]);

        $actualType = $this->createFactoriesReturnTypeHelper($reflectionProvider)->check($inputType, $function);
        self::assertInstanceOf($expectedType::class, $actualType);

        $expected = $expectedType->describe(VerbosityLevel::precise());
        $actual   = $actualType->describe(VerbosityLevel::precise());
        self::assertSame($expected, $actual);
    }

    private function createFactoriesReturnTypeHelper(?ReflectionProvider $reflectionProvider = null): FactoriesReturnTypeHelper
    {
        return new FactoriesReturnTypeHelper(
            $reflectionProvider ?? self::createReflectionProvider(),
            ['Acme\Blog\Config'],
            ['Acme\Blog\Models']
        );
    }
}
