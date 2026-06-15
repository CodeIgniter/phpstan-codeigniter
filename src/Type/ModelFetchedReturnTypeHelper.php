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

use CodeIgniter\PHPStan\Database\Schema\CastFieldTypeResolver;
use CodeIgniter\PHPStan\Database\Schema\Column;
use CodeIgniter\PHPStan\Database\Schema\ColumnTypeResolver;
use CodeIgniter\PHPStan\Database\SchemaProvider;
use CodeIgniter\PHPStan\NodeVisitor\ModelReturnTypeTransformVisitor;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use stdClass;

/**
 * Resolves the type of a single fetched row for a model, honoring its `$returnType` (or the
 * `asArray()`/`asObject()` override) and shaping array rows from the live columns and `$casts`.
 */
final class ModelFetchedReturnTypeHelper
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly SchemaProvider $schemaProvider,
        private readonly ColumnTypeResolver $columnTypeResolver,
        private readonly CastFieldTypeResolver $castFieldTypeResolver,
    ) {}

    public function getFetchedReturnType(ClassReflection $classReflection, ?MethodCall $methodCall, Scope $scope): Type
    {
        $returnType = $this->resolveReturnType($classReflection, $methodCall, $scope);

        if ($returnType === 'object') {
            return new ObjectType(stdClass::class);
        }

        if ($returnType === 'array') {
            return $this->resolveArrayRowType($classReflection);
        }

        if ($this->reflectionProvider->hasClass($returnType)) {
            return new ObjectType($returnType);
        }

        return new ObjectWithoutClassType();
    }

    private function resolveReturnType(ClassReflection $classReflection, ?MethodCall $methodCall, Scope $scope): string
    {
        if ($methodCall !== null && $methodCall->hasAttribute(ModelReturnTypeTransformVisitor::RETURN_TYPE)) {
            $expr = $methodCall->getAttribute(ModelReturnTypeTransformVisitor::RETURN_TYPE);

            if ($expr instanceof Expr) {
                $strings = $scope->getType($expr)->getConstantStrings();

                if (count($strings) === 1) {
                    return $strings[0]->getValue();
                }
            }
        }

        $returnType = $classReflection->getNativeReflection()->getDefaultProperties()['returnType'] ?? 'array';

        return is_string($returnType) ? $returnType : 'array';
    }

    private function resolveArrayRowType(ClassReflection $classReflection): Type
    {
        $tableName = $classReflection->getNativeReflection()->getDefaultProperties()['table'] ?? null;

        $table = is_string($tableName) && $tableName !== '' ? $this->schemaProvider->get()->getTable($tableName) : null;

        if ($table === null) {
            return new ArrayType(new StringType(), new MixedType());
        }

        $casts        = $this->readStringMap($classReflection, 'casts');
        $castHandlers = $this->readStringMap($classReflection, 'castHandlers');
        $builder      = ConstantArrayTypeBuilder::createEmpty();

        foreach ($table->columns as $column) {
            $builder->setOffsetValueType(new ConstantStringType($column->name), $this->resolveFieldType($column, $casts, $castHandlers));
        }

        return $builder->getArray();
    }

    /**
     * @param array<string, string> $casts
     * @param array<string, string> $castHandlers
     */
    private function resolveFieldType(Column $column, array $casts, array $castHandlers): Type
    {
        if (isset($casts[$column->name])) {
            return $this->castFieldTypeResolver->resolve($casts[$column->name], $castHandlers);
        }

        return $this->columnTypeResolver->resolve($column);
    }

    /**
     * @return array<string, string>
     */
    private function readStringMap(ClassReflection $classReflection, string $property): array
    {
        $value = $classReflection->getNativeReflection()->getDefaultProperties()[$property] ?? [];

        if (! is_array($value)) {
            return [];
        }

        $map = [];

        foreach ($value as $key => $cast) {
            if (is_string($key) && is_string($cast)) {
                $map[$key] = $cast;
            }
        }

        return $map;
    }
}
