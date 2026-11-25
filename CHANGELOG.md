# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

The changelog of the releases before v2.0.0 is [published in the Releases section](https://github.com/CodeIgniter/phpstan-codeigniter/releases).

## Unreleased

## [2.4.1](https://github.com/CodeIgniter/phpstan-codeigniter/compare/v2.4.0...v2.4.1) - 2026-11-25

### Added

- Type the `encrypted`, `encrypted-json` and `encrypted-json-array` casts of CodeIgniter 4.8.

### Fixed

- Fix a `null` `Config\Migrations` or `Config\App` when a parallel worker, such as Rector's, loads the config class while PHPStan traps the autoloader (#55).

## [2.4.0](https://github.com/CodeIgniter/phpstan-codeigniter/compare/v2.3.0...v2.4.0) - 2026-11-20

### Added

- Add `CastHandlerRule` to report a `$casts` entry of an entity or model that has no handler (`codeigniter.castHandlerNotRegistered`), a handler class that does not exist (`codeigniter.castHandlerNotFound`) and a handler that implements neither `CastInterface` (`codeigniter.castHandlerInvalid`).
- Add `MigrationNotAppliedRule` to report a migration that failed on the SQLite schema connection (`codeigniter.migrationNotApplied`). A migration pinned to another database group is not reported.

### Changed

- Unknown column, row field, cast element and entity property types are now an explicit `mixed`, so `checkImplicitMixed: true` no longer reports their uses (#54). They print in full, for example `array<mixed, mixed>|null` instead of `array|null`.
- The result cache is now invalidated when a migration, a language file or a model changes, and only the files that depend on it are analysed again.
- The schema cache format changed, so the schema is rebuilt once after upgrading.

### Fixed

- Fall back to the `@property` tags of an entity for a property that the extension cannot type (#53).

## [2.3.0](https://github.com/CodeIgniter/phpstan-codeigniter/compare/v2.2.3...v2.3.0) - 2026-11-19

### Added

- Add return type extensions for `lang()` and `Language::getLine()` that narrow to `string` or `list<string>` (non-empty where known) from the language files.
- Add `LanguageLineExistenceRule` to report a language line that no locale defines, and, with `codeigniter.reportUntranslatedLanguageLines`, a line that a supported locale lacks.
- Add a return type extension for `dot_array_search()` that returns the leaf type of a constant array shape for a constant index.
- Add a return type extension for `env()` that returns `bool|string|null` joined with the default type.
- Narrow `IncomingRequest::getJsonVar()` for a null or array index.
- Add `ModelWriteAllowedFieldsRule` to report a key passed to `insert()`, `update()` or `save()` that is not in the model's `$allowedFields`.
- Add `FactoriesStaticMethodArgumentTypeRule` to validate the alias passed to `Factories::config()`, `Factories::models()` and `Factories::get()`.

## [2.2.3](https://github.com/CodeIgniter/phpstan-codeigniter/compare/v2.2.2...v2.2.3) - 2026-11-09

### Fixed

- Fix `SuperglobalsOffsetAccessRule` auto-fixes that would cause a PHP fatal error: `isset()` on a superglobal offset is now rewritten to `!== null`, and no fix is applied for multi-variable `isset()`, compound assignment, increment or decrement.

## [2.2.2](https://github.com/CodeIgniter/phpstan-codeigniter/compare/v2.2.1...v2.2.2) - 2026-11-04

### Fixed

- Suggest `single_service()` for superglobals reads so that they fetch the live value. Auto-fixing now also considers the variable context so that a fix does not crash PHP.

## [2.2.1](https://github.com/CodeIgniter/phpstan-codeigniter/compare/v2.2.0...v2.2.1) - 2026-11-04

### Changed

- Support PHPStan's extension attributes natively. This requires PHPStan v2.3.5.

## [2.2.0](https://github.com/CodeIgniter/phpstan-codeigniter/compare/v2.1.0...v2.2.0) - 2026-10-06

### Added

- Static `Factories` loaders. `Factories::config()`, `Factories::models()` and `Factories::get()` now return the requested class, the same as the `config()` and `model()` functions. A class string resolves for any component. A short alias such as `Factories::config('App')` resolves through the config and model namespaces, including `additionalConfigNamespaces` and `additionalModelNamespaces`.
- `Request::getServer()` returns `array<string, mixed>` with no index. A constant key returns `string|null`, or the actual type for `argv`, `argc`, `REQUEST_TIME` and `REQUEST_TIME_FLOAT`.
- `IncomingRequest` input accessors. `getGet()`, `getPost()`, `getCookie()`, `getPostGet()`, `getGetPost()` and `getRawInputVar()` return `array<string, mixed>` for a null or array index. A constant string index returns `array<mixed>|string|null`, except `getRawInputVar()`, which keeps its declared type. A filter argument also keeps the declared type.

### Changed

- Support PHPStan 2.3. The minimum stays at PHPStan 2.2.

### Fixed

- Fix a crash under PHPStan 2.3. Rector and other tools that ask PHPStan for native types stopped with `assert(count($classTypes) === 1)` on model fetch chains such as `$this->select('name')->asArray()->find()`. The model fetch extension now falls back to the declared return type when the receiver has no known class.
- Fix model fetches on a union of models. `find()`, `findAll()`, `first()` and `findColumn()` called on a union of models, for example `($flag ? $users : $jobs)->find(1)`, now return the union of the row types of every model. Previously they used the type of the first model only.
- Fix the schema build in parallel runs. Migrations are discovered once per run, and building a cold schema cache is locked, so parallel PHPStan workers no longer all run the migrations.

## [2.1.0](https://github.com/CodeIgniter/phpstan-codeigniter/compare/v2.0.1...v2.1.0) - 2026-06-15

### Added

- Type entities fetched with `asObject()` by the casts of the producing model.

## [2.0.1](https://github.com/CodeIgniter/phpstan-codeigniter/compare/v2.0.0...v2.0.1) - 2026-06-15

### Fixed

- Apply the casts of the model to the entity property types.

## [2.0.0](https://github.com/CodeIgniter/phpstan-codeigniter/compare/v1.6.0...v2.0.0) - 2026-06-15

This release is a ground-up rewrite of the extension. Read the [upgrade guide](docs/upgrading.md) before upgrading.

### Added

- Model and entity type inference driven by the database schema, which is materialized by running the migrations against a temporary SQLite database (closes #17, #19 and #25).
  - `Model::find()`, `findAll()`, `first()` and `findColumn()` return the precise row type for the `$returnType` of the model (an entity, a shaped `array{...}` of columns, or a `stdClass`), honoring `asArray()`, `asObject()` and the `select()` field list (aliases, `table.*` and joined columns).
  - Entity virtual properties are typed from the `$casts` and `$dates` of the entity (including custom `$castHandlers`) layered over the backing database column.
  - `fake()` returns a single fabricated record of the return type of the model.

### Changed

- **Breaking:** require PHP `^8.2`, PHPStan `^2.2`, CodeIgniter 4 `^4.7` and the `sqlite3` PHP extension.
- **Breaking:** split the superglobals rules into `SuperglobalsOffsetAccess`, `SuperglobalsOffsetAssign`, `SuperglobalsOffsetUnset` and `SuperglobalsGlobalAssign`, which also catches whole-array assignment. Most of them are auto-fixable with `vendor/bin/phpstan analyse --fix`.
- **Breaking:** the rule identifiers and messages changed, so existing baseline entries no longer match and must be regenerated.

### Removed

- Remove the rule that discouraged `::class` in `config()` and `model()`.
