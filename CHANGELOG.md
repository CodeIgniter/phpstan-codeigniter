# Changelog

All notable changes to this library will be documented in this file.

This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v1.0.1](https://github.com/CodeIgniter/phpstan-codeigniter/compare/v1.0.0...v1.0.1) - 2023-08-30

### Fixed

* Add changelog
* Fix wrong namespace of tests
* Add missing trailing comma

## [v1.0.0](https://github.com/CodeIgniter/phpstan-codeigniter/releases/tag/v1.0.0) - 2023-08-27

### Initial release

This PHPStan extension provides the following features:

#### Type Inference

* Provides precise return types for `config()` and `model()` functions.
* Provides precise return types for `service()` and `single_service()` functions.

#### Rules

* Checks if the string argument passed to `config()` or `model()` function is a valid class string extending `CodeIgniter\Config\BaseConfig` or `CodeIgniter\Model`, respectively. This can be turned off by setting `codeigniter.checkArgumentTypeOfFactories: false` in your `phpstan.neon`.
* Checks if the string argument passed to `service()` or `single_service()` function is a valid service name. This can be turned off by setting `codeigniter.checkArgumentTypeOfServices: false` in your `phpstan.neon`.
* Disallows instantiating cache handlers using `new` and suggests using the `CacheFactory` class instead.
* Disallows instantiating `FrameworkException` classes using `new`.
* Disallows direct re-assignment or access of `$_SERVER` and `$_GET` and suggests using the `Superglobals` class instead.
