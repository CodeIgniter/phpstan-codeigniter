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

namespace CodeIgniter\PHPStan\Type;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NonAcceptingNeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use stdClass;

final class FakeFunctionReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
    /**
     * @var array<string, class-string<Type>>
     */
    private static array $notStringFormattedFields = [
        'success' => BooleanType::class,
        'user_id' => IntegerType::class,
    ];

    /**
     * @var array<string, class-string<Type>>
     */
    private static array $typeInterpolations = [
        'bool' => BooleanType::class,
        'int'  => IntegerType::class,
    ];

    /**
     * @var list<string>
     */
    private array $dateFields = [];

    /**
     * @param array<string, string> $notStringFormattedFieldsArray
     */
    public function __construct(
        private readonly FactoriesReturnTypeHelper $factoriesReturnTypeHelper,
        private readonly ReflectionProvider $reflectionProvider,
        array $notStringFormattedFieldsArray
    ) {
        foreach ($notStringFormattedFieldsArray as $field => $type) {
            if (! isset(self::$typeInterpolations[$type])) {
                continue;
            }

            self::$notStringFormattedFields[$field] = self::$typeInterpolations[$type];
        }
    }

    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return $functionReflection->getName() === 'fake';
    }

    public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): ?Type
    {
        $arguments = $functionCall->getArgs();

        if ($arguments === []) {
            return null;
        }

        $modelType = $this->factoriesReturnTypeHelper->check($scope->getType($arguments[0]->value), 'model');

        if (! $modelType->isObject()->yes()) {
            return new NonAcceptingNeverType();
        }

        $classReflections = $modelType->getObjectClassReflections();

        if (count($classReflections) !== 1) {
            return $modelType; // ObjectWithoutClassType
        }

        $classReflection = current($classReflections);

        $returnType = $this->getNativeStringPropertyValue($classReflection, $scope, 'returnType');

        if ($returnType === 'object') {
            return new ObjectType(stdClass::class);
        }

        if ($returnType === 'array') {
            return $this->getArrayReturnType($classReflection, $scope);
        }

        if ($this->reflectionProvider->hasClass($returnType)) {
            return new ObjectType($returnType);
        }

        return new ObjectWithoutClassType();
    }

    private function getArrayReturnType(ClassReflection $classReflection, Scope $scope): Type
    {
        $this->fillDateFields($classReflection, $scope);
        $fieldsTypes = $this->getNativePropertyType($classReflection, $scope, 'allowedFields')->getConstantArrays();

        if ($fieldsTypes === []) {
            return new ConstantArrayType([], []);
        }

        $fields = array_filter(array_map(
            static fn (Type $type) => current($type->getConstantStrings()),
            current($fieldsTypes)->getValueTypes()
        ));

        return new ConstantArrayType(
            $fields,
            array_map(function (ConstantStringType $fieldType) use ($classReflection, $scope): Type {
                $field = $fieldType->getValue();

                if (array_key_exists($field, self::$notStringFormattedFields)) {
                    $type = self::$notStringFormattedFields[$field];

                    return new $type();
                }

                if (
                    in_array($field, $this->dateFields, true)
                    && $this->getNativeStringPropertyValue($classReflection, $scope, 'dateFormat') === 'int'
                ) {
                    return new IntegerType();
                }

                return new StringType();
            }, $fields)
        );
    }

    private function fillDateFields(ClassReflection $classReflection, Scope $scope): void
    {
        foreach (['createdAt', 'updatedAt', 'deletedAt'] as $property) {
            if ($classReflection->hasNativeProperty($property)) {
                $this->dateFields[] = $this->getNativeStringPropertyValue($classReflection, $scope, $property);
            }
        }
    }

    private function getNativePropertyType(ClassReflection $classReflection, Scope $scope, string $property): Type
    {
        if (! $classReflection->hasNativeProperty($property)) {
            throw new ShouldNotHappenException(sprintf('Native property %s::$%s does not exist.', $classReflection->getDisplayName(), $property));
        }

        return $scope->getType($classReflection->getNativeProperty($property)->getNativeReflection()->getDefaultValueExpression());
    }

    private function getNativeStringPropertyValue(ClassReflection $classReflection, Scope $scope, string $property): string
    {
        $propertyType = $this->getNativePropertyType($classReflection, $scope, $property)->getConstantStrings();
        assert(count($propertyType) === 1);

        return current($propertyType)->getValue();
    }
}
