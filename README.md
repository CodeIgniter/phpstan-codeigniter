# CodeIgniter extensions and rules for PHPStan

[![Extension Tests](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-phpunit.yml/badge.svg)](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-phpunit.yml)
[![Coding Standards Check](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-coding-standards.yml/badge.svg)](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-coding-standards.yml)
[![PHPStan Static Analysis](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-phpstan.yml/badge.svg)](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-phpstan.yml)

* [PHPStan](https://phpstan.org/)
* [CodeIgniter](https://codeigniter.com/)

This extension provides the following features:

* [Type inference](docs/type-inference.md)

### Rules

* Checks if the string argument passed to `config()` or `model()` function is a valid class string extending
`CodeIgniter\Config\BaseConfig` or `CodeIgniter\Model`, respectively. This can be turned off by setting
`codeigniter.checkArgumentTypeOfFactories: false` in your `phpstan.neon`. For fine-grained control, you can
individually choose which factory function to disable using `codeigniter.checkArgumentTypeOfConfig` and
`codeigniter.checkArgumentTypeOfModel`. **NOTE:** Setting `codeigniter.checkArgumentTypeOfFactories: false` will effectively
bypass the two specific options.
* Checks if the string argument passed to `service()` or `single_service()` function is a valid service name. This can be turned off by setting `codeigniter.checkArgumentTypeOfServices: false` in your `phpstan.neon`.
* Disallows instantiating cache handlers using `new` and suggests to use the `CacheFactory` class instead.
* Disallows instantiating `FrameworkException` classes using `new`.
* Disallows direct re-assignment or access of `$_SERVER` and `$_GET` and suggests to use the `Superglobals` class instead.
* Disallows use of `::class` fetch on `config()` and `model()` and suggests to use the short form of the class instead.

## Installation

To use this extension, require it in [Composer](https://getcomposer.org/):

```
composer require --dev codeigniter/phpstan-codeigniter
```

If you also install [phpstan/extension-installer](https://github.com/phpstan/extension-installer) then you're all set!

<details>
	<summary>Manual installation</summary>

If you don't want to use `phpstan/extension-installer`, include extension.neon in your project's PHPStan config:

```yml
includes:
    - vendor/codeigniter/phpstan-codeigniter/extension.neon
```

</details>

## Contributing

Any contributions are welcome.

If you want to see a new rule or extension specific to CodeIgniter, please open a
[feature request](https://github.com/CodeIgniter/phpstan-codeigniter/issues/new?assignees=&labels=feature+request&projects=&template=feature_request.yml). If you can contribute the code yourself, please open a pull request instead.

Before reporting any bugs, please check if the bug occurs only if using this extension with PHPStan. If the bug is
reproducible in PHPStan alone, please open a bug report there instead. Thank you!

## License

PHPStan CodeIgniter is an open source library licensed under [MIT](LICENSE).
