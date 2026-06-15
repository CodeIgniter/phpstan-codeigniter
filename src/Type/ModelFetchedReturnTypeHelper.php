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
use CodeIgniter\PHPStan\Database\Schema\Table;
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
use PHPStan\Type\ObjectShapeType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use stdClass;

/**
 * Resolves the type of a single fetched row for a model, honoring its `$returnType` (or the
 * `asArray()`/`asObject()` override), the `select()` field list, the live columns, and `$casts`.
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
            return $this->resolveObjectRowType($classReflection, $methodCall, $scope);
        }

        if ($returnType === 'array') {
            return $this->resolveArrayRowType($classReflection, $methodCall, $scope);
        }

        if ($this->reflectionProvider->hasClass($returnType)) {
            return $this->entityTypeWithModelCasts($classReflection, $returnType);
        }

        return new ObjectWithoutClassType();
    }

    /**
     * Types the entity with the producing model's `$casts`. The producing model is statically known here
     * (unlike in the entity reflection), so a model fetched through `asObject()` overrides the column types
     * its own model would otherwise apply. The entity's own `$casts` still win, as they run last in `__get()`.
     */
    private function entityTypeWithModelCasts(ClassReflection $modelReflection, string $entityClass): Type
    {
        $modelCasts = $this->readStringMap($modelReflection, 'casts');

        if ($modelCasts === []) {
            return new ObjectType($entityClass);
        }

        $entityReflection  = $this->reflectionProvider->getClass($entityClass);
        $entityCasts       = $this->readStringMap($entityReflection, 'casts');
        $datamap           = $this->readStringMap($entityReflection, 'datamap');
        $modelCastHandlers = $this->readStringMap($modelReflection, 'castHandlers');

        $tableName = $modelReflection->getNativeReflection()->getDefaultProperties()['table'] ?? null;
        $table     = is_string($tableName) && $tableName !== '' ? $this->schemaProvider->get()->getTable($tableName) : null;

        $overrides = [];

        foreach ($modelCasts as $column => $cast) {
            if (isset($entityCasts[$column])) {
                continue;
            }

            $schemaColumn       = $table?->getColumn($column);
            $overrides[$column] = $this->castFieldTypeResolver->resolve(
                $cast,
                $modelCastHandlers,
                $schemaColumn !== null && ! $schemaColumn->nullable,
            );
        }

        if ($overrides === []) {
            return new ObjectType($entityClass);
        }

        return new ModelCastEntityType($entityClass, $overrides, $datamap);
    }

    /**
     * Resolves the type of a single column's value, as returned element-wise by `findColumn()`.
     */
    public function getColumnFieldType(ClassReflection $classReflection, string $columnName): ?Type
    {
        $tableName = $classReflection->getNativeReflection()->getDefaultProperties()['table'] ?? null;

        $table = is_string($tableName) && $tableName !== '' ? $this->schemaProvider->get()->getTable($tableName) : null;

        if ($table === null) {
            return null;
        }

        return $this->fieldType(
            $columnName,
            $table->getColumn($columnName),
            $this->readStringMap($classReflection, 'casts'),
            $this->readStringMap($classReflection, 'castHandlers'),
        );
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

    private function resolveArrayRowType(ClassReflection $classReflection, ?MethodCall $methodCall, Scope $scope): Type
    {
        $fields = $this->resolveRowFieldTypes($classReflection, $methodCall, $scope);

        if ($fields === null) {
            return new ArrayType(new StringType(), new MixedType());
        }

        $builder = ConstantArrayTypeBuilder::createEmpty();

        foreach ($fields as $name => $type) {
            $builder->setOffsetValueType(new ConstantStringType($name), $type);
        }

        return $builder->getArray();
    }

    private function resolveObjectRowType(ClassReflection $classReflection, ?MethodCall $methodCall, Scope $scope): Type
    {
        $fields = $this->resolveRowFieldTypes($classReflection, $methodCall, $scope);

        if ($fields === null) {
            return new ObjectType(stdClass::class);
        }

        return new ObjectShapeType($fields, []);
    }

    /**
     * @return array<string, Type>|null Null when the row shape cannot be resolved.
     */
    private function resolveRowFieldTypes(ClassReflection $classReflection, ?MethodCall $methodCall, Scope $scope): ?array
    {
        $tableName = $classReflection->getNativeReflection()->getDefaultProperties()['table'] ?? null;

        $table = is_string($tableName) && $tableName !== '' ? $this->schemaProvider->get()->getTable($tableName) : null;

        if ($table === null) {
            return null;
        }

        $casts        = $this->readStringMap($classReflection, 'casts');
        $castHandlers = $this->readStringMap($classReflection, 'castHandlers');

        $selects = $methodCall !== null && $methodCall->hasAttribute(ModelReturnTypeTransformVisitor::SELECTS)
            ? $methodCall->getAttribute(ModelReturnTypeTransformVisitor::SELECTS)
            : null;

        if (! is_array($selects)) {
            return $this->columnsToFields($table, $casts, $castHandlers);
        }

        return $this->resolveSelectedFields($selects, $table, $casts, $castHandlers, $scope);
    }

    /**
     * @param array<mixed, mixed>   $selects
     * @param array<string, string> $casts
     * @param array<string, string> $castHandlers
     *
     * @return array<string, Type>|null
     */
    private function resolveSelectedFields(array $selects, Table $table, array $casts, array $castHandlers, Scope $scope): ?array
    {
        $fields = [];

        foreach ($selects as $expr) {
            if (! $expr instanceof Expr) {
                return null;
            }

            $strings = $scope->getType($expr)->getConstantStrings();

            if (count($strings) !== 1) {
                return null;
            }

            foreach ($this->splitSelect($strings[0]->getValue()) as $part) {
                $resolved = $this->resolveSelectPart($part, $table, $casts, $castHandlers);

                if ($resolved === null) {
                    return null;
                }

                foreach ($resolved as $name => $type) {
                    $fields[$name] = $type;
                }
            }
        }

        return $fields;
    }

    /**
     * @param array<string, string> $casts
     * @param array<string, string> $castHandlers
     *
     * @return array<string, Type>|null
     */
    private function resolveSelectPart(string $part, Table $modelTable, array $casts, array $castHandlers): ?array
    {
        if ($part === '*') {
            return $this->columnsToFields($modelTable, $casts, $castHandlers);
        }

        if (preg_match('/^(\w+)\.\*$/', $part, $matches) === 1) {
            $table = $this->schemaProvider->get()->getTable($matches[1]);

            return $table === null ? null : $this->columnsToFields($table, $casts, $castHandlers);
        }

        if (preg_match('/^(\w+)(?:\.(\w+))?(?:\s+(?:as\s+)?(\w+))?$/i', $part, $matches) === 1) {
            $qualified  = ($matches[2] ?? '') !== '';
            $columnName = $qualified ? $matches[2] : $matches[1];
            $outputName = ($matches[3] ?? '') !== '' ? $matches[3] : $columnName;
            $table      = $qualified ? $this->schemaProvider->get()->getTable($matches[1]) : $modelTable;

            if ($table === null) {
                return null;
            }

            return [$outputName => $this->fieldType($outputName, $table->getColumn($columnName), $casts, $castHandlers)];
        }

        if (preg_match('/\bas\s+(\w+)\s*$/i', $part, $matches) === 1) {
            return [$matches[1] => $this->fieldType($matches[1], null, $casts, $castHandlers)];
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function splitSelect(string $select): array
    {
        $parts   = [];
        $current = '';
        $depth   = 0;

        foreach (str_split($select === '' ? ' ' : $select) as $char) {
            if ($char === '(') {
                $depth++;
            } elseif ($char === ')') {
                $depth--;
            }

            if ($char === ',' && $depth === 0) {
                $parts[] = $current;
                $current = '';

                continue;
            }

            $current .= $char;
        }

        $parts[] = $current;

        return array_values(array_filter(array_map(trim(...), $parts), static fn (string $part): bool => $part !== ''));
    }

    /**
     * @param array<string, string> $casts
     * @param array<string, string> $castHandlers
     *
     * @return array<string, Type>
     */
    private function columnsToFields(Table $table, array $casts, array $castHandlers): array
    {
        $fields = [];

        foreach ($table->columns as $column) {
            $fields[$column->name] = $this->fieldType($column->name, $column, $casts, $castHandlers);
        }

        return $fields;
    }

    /**
     * @param array<string, string> $casts
     * @param array<string, string> $castHandlers
     */
    private function fieldType(string $name, ?Column $column, array $casts, array $castHandlers): Type
    {
        if (isset($casts[$name])) {
            return $this->castFieldTypeResolver->resolve($casts[$name], $castHandlers, $column !== null && ! $column->nullable);
        }

        return $column !== null ? $this->columnTypeResolver->resolve($column) : new MixedType();
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
