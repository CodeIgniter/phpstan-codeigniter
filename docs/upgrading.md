# Upgrading from 1.x to 2.x

Version 2.x is a ground-up rewrite. The biggest addition is a live-database schema layer that infers
precise Model and Entity types, and the superglobals rules were split and renamed. This guide covers the
changes and how to migrate.

## Requirements

| Dependency      | 1.x       | 2.x                     |
| --------------- | --------- | ----------------------- |
| PHP             | `^8.2`    | `^8.2`                  |
| PHPStan         | `^2.0`    | `^2.2`                  |
| CodeIgniter 4   | `^4.6`    | `^4.7`                  |
| PHP extensions  | (none)    | `sqlite3` (new)         |

The new `sqlite3` extension powers the Model and Entity type inference. The extension materializes your
schema by running your migrations against a throwaway in-repo SQLite database, then reads the resulting
columns. Without `ext-sqlite3`, the schema-derived types are unavailable (PHPStan still runs, but Model and
Entity properties fall back to their framework types).

## What's new

### Model and Entity type inference

2.x derives types from your actual database schema instead of heuristics:

- `Model::find()`, `findAll()`, `first()`, and `findColumn()` return the precise row type for the model's
  `$returnType` (an entity, a shaped `array{...}` of columns, or a `stdClass`), honoring `asArray()`,
  `asObject()`, and the `select()` field list (aliases, `table.*`, and joined columns).
- Entity virtual properties are typed from the entity's `$casts` and `$dates` (including custom
  `$castHandlers`) layered over the backing column.
- `fake()` returns a single fabricated record of the model's return type.

See [type-inference.md](type-inference.md) for the full list.

These types are sharper than 1.x, so the upgrade may surface genuine type errors that were previously
invisible. For example, a `datetime`-cast property over a non-null column is now exactly `Time` (not
`Time|null`), which can reveal redundant null checks.

## Breaking changes

### Superglobals rules were renamed and split

The two 1.x superglobals rules became four, with new identifiers and reworded messages:

| 1.x                          | 2.x                                                                       |
| ---------------------------- | ------------------------------------------------------------------------- |
| `SuperglobalAccessRule`      | `SuperglobalsOffsetAccessRule` (`codeigniter.superglobalsOffsetAccess`)   |
| `SuperglobalAssignRule`      | `SuperglobalsOffsetAssignRule` (`codeigniter.superglobalsOffsetAssign`)   |
|                              | `SuperglobalsOffsetUnsetRule` (`codeigniter.superglobalsOffsetUnset`)     |
|                              | `SuperglobalsGlobalAssignRule` (`codeigniter.superglobalsGlobalAssign`)   |

Whole-array assignment such as `$_SERVER = [...]` is now reported by `SuperglobalsGlobalAssignRule`. Because
the identifiers and messages changed, any baseline entries for the old rules will no longer match and must be
regenerated.

Most of these errors are auto-fixable, so you can fix them in place instead of baselining them.
`SuperglobalsOffsetAccessRule` and `SuperglobalsOffsetAssignRule` are fully fixable, and
`SuperglobalsGlobalAssignRule` is partially fixable (single-element array assignments). Running PHPStan's
fixer (`vendor/bin/phpstan analyse --fix`) rewrites the direct access into the equivalent
`service('superglobals')` call, for example `$_SERVER['argv']` becomes
`service('superglobals')->server('argv')`.

### `model()`/`config()` with a class string is no longer discouraged

The 1.x `NoClassConstFetchOnFactoriesFunctions` rule (`codeigniter.factoriesClassConstFetch`) has been
removed. Passing `Foo::class` to `model()` or `config()` is no longer reported. Remove the corresponding
baseline entries.

## Configuration

Two parameters were added for the schema layer:

```yml
parameters:
  codeigniter:
    # Restrict schema introspection to a single namespace. By default every registered namespace is
    # scanned (your app plus installed packages), so a library analyzed on its own works out of the box.
    schemaNamespace: Acme\Blog

    # Where the schema cache is written. Defaults to the `tmp` directory of the working directory.
    schemaCacheDirectory: %currentWorkingDirectory%/writable/cache
```

Both are optional. The defaults work for a typical application.

## Migration steps

1. Bump the constraint:

   ```console
   composer require --dev "codeigniter/phpstan-codeigniter:^2.0"
   ```

2. Make sure the SQLite extension is available:

   ```console
   php -m | grep sqlite3
   ```

3. Run PHPStan and expect churn against your existing baseline: entries for the renamed superglobals rules
   and the removed `model()`/`config()` discouragement will no longer match, and the sharper Model/Entity
   types may add new errors.

4. Optionally, auto-rewrite the fixable superglobals access (see above) instead of baselining it:

   ```console
   vendor/bin/phpstan analyse --fix
   ```

   The `--fix` flag is experimental.

5. Review the newly reported errors before baselining. Some are genuine findings from the more precise
   inference, not noise.

6. Regenerate the baseline once you have triaged the real issues:

   ```console
   vendor/bin/phpstan analyse --generate-baseline
   ```

## Known limitations

- **Column types reflect SQLite affinity, not your production database.** The schema is materialized in
  SQLite, so a column's type follows SQLite's column affinity rather than your MySQL or Postgres type. A
  `BOOLEAN` column reads back as `int`, `DECIMAL` as `string`, and `ENUM`/`SET` as `string`. Add a `$casts`
  entry (for example `'is_active' => 'boolean'`) when you want the precise PHP type.
- **Models that set `$table` in the constructor are not mapped.** The entity-to-table bridge reads `$table`
  from the model's default property value. A model that assigns `$this->table` inside its constructor is not
  resolved, so its entity's non-cast properties fall back to `mixed`.
- **An explicit `@var` annotation overrides the inferred entity type.** A `/** @var SomeEntity $x */` on a
  fetched row replaces the cast-aware type with a plain `SomeEntity`, so its properties fall back to their
  framework types. Such annotations, often added when 1.x inference was wrong, are unnecessary in 2.x.
- **Only migrations build the schema.** Tables created outside migrations (for example, in test setup) are
  not introspected.
- **A non-constant `select()` degrades the shape.** When the `select()` argument is not a constant string,
  the row type falls back to `array<string, mixed>` rather than a precise shape.
