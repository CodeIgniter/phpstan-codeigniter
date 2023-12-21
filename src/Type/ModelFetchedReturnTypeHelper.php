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

use CodeIgniter\PHPStan\NodeVisitor\ModelReturnTypeTransformVisitor;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use stdClass;

final class ModelFetchedReturnTypeHelper
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;

    /**
     * @var array<string, class-string<Type>>
     */
    private static array $notStringFormattedFields = [
        'active'      => BooleanType::class,
        'force_reset' => BooleanType::class,
        'id'          => IntegerType::class,
        'success'     => BooleanType::class,
        'user_id'     => IntegerType::class,
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
        ReflectionProvider $reflectionProvider,
        array $notStringFormattedFieldsArray
    ) {
        $this->reflectionProvider = $reflectionProvider;

        foreach ($notStringFormattedFieldsArray as $field => $type) {
            if (! isset(self::$typeInterpolations[$type])) {
                continue;
            }

            self::$notStringFormattedFields[$field] = self::$typeInterpolations[$type];
        }
    }

    public function getFetchedReturnType(ClassReflection $classReflection, ?MethodCall $methodCall, Scope $scope): Type
    {
        $returnType = $this->getNativeStringPropertyValue($classReflection, $scope, ModelReturnTypeTransformVisitor::RETURN_TYPE);

        if ($methodCall !== null && $methodCall->hasAttribute(ModelReturnTypeTransformVisitor::RETURN_TYPE)) {
            /** @var Expr $returnExpr */
            $returnExpr = $methodCall->getAttribute(ModelReturnTypeTransformVisitor::RETURN_TYPE);
            $returnType = $this->getStringValueFromExpr($returnExpr, $scope);
        }

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
        $fieldsTypes = $scope->getType(
            $classReflection->getNativeProperty('allowedFields')->getNativeReflection()->getDefaultValueExpression()
        )->getConstantArrays();

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

    private function getNativeStringPropertyValue(ClassReflection $classReflection, Scope $scope, string $property): string
    {
        if (! $classReflection->hasNativeProperty($property)) {
            throw new ShouldNotHappenException(sprintf('Native property %s::$%s does not exist.', $classReflection->getDisplayName(), $property));
        }

        return $this->getStringValueFromExpr(
            $classReflection->getNativeProperty($property)->getNativeReflection()->getDefaultValueExpression(),
            $scope
        );
    }

    private function getStringValueFromExpr(Expr $expr, Scope $scope): string
    {
        $exprType = $scope->getType($expr)->getConstantStrings();
        assert(count($exprType) === 1);

        return current($exprType)->getValue();
    }
}
