# CodeIgniter extensions and rules for PHPStan

[![Extension Tests](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-phpunit.yml/badge.svg)](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-phpunit.yml)
[![Coding Standards Check](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-coding-standards.yml/badge.svg)](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-coding-standards.yml)
[![PHPStan Static Analysis](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-phpstan.yml/badge.svg)](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-phpstan.yml)

* [PHPStan](https://phpstan.org/)
* [CodeIgniter](https://codeigniter.com/)

This extension provides the following features:

### Type Inference

* Provides precise return types for `config()` and `model()` functions.
* Provides precise return types for `service()` and `single_service()` functions.

### Rules

* Checks if the string argument passed to `config()` or `model()` function is a valid class string extending `CodeIgniter\Config\BaseConfig` or `CodeIgniter\Model`, respectively. This can be turned off by setting `codeigniter.checkArgumentTypeOfFactories: false` in your `phpstan.neon`.
* Checks if the string argument passed to `service()` or `single_service()` function is a valid service name. This can be turned off by setting `codeigniter.checkArgumentTypeOfServices: false` in your `phpstan.neon`.
* Disallows instantiating cache handlers using `new` and suggests to use the `CacheFactory` class instead.
* Disallows instantiating `FrameworkException` classes using `new`.

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

## Configuration

This extension adds the default namespace for `config()` and `model()` functions as `Config\` and `App\Models\`, respectively,
when searching for possible classes. If your application uses other namespaces, you can configure this extension
in your `phpstan.neon` to recognize those namespaces:

```yml
parameters:
  codeigniter:
    additionalConfigNamespaces:
      - Acme\Blog\Config\
      - Foo\Bar\Config\
    additionalModelNamespaces:
      - Acme\Blog\Models\

```

For the `service()` and `single_service()` functions, you can instruct PHPStan to consider your own
services factory classes. **Please note that it should be a valid class extending `CodeIgniter\Config\BaseService`!**

```yml
parameters:
  codeigniter:
    additionalServices:
      - Acme\Blog\Config\ServiceFactory
```

## Caveats

1. The behavior of factories functions relative to how they load classes is based on codeigniter4/framework v4.4. If you are
  relying on the behavior of < v4.4, this may not work out for you.

## Contributing

Any contributions are welcome.

If you want to see a new rule or extension specific to CodeIgniter, please open a
[feature request](https://github.com/CodeIgniter/phpstan-codeigniter/issues/new?assignees=&labels=feature+request&projects=&template=feature_request.yml). If you can contribute the code yourself, please open a pull request instead.

Before reporting any bugs, please check if the bug occurs only if using this extension with PHPStan. If the bug is
reproducible in PHPStan alone, please open a bug report there instead. Thank you!

## License

PHPStan CodeIgniter is an open source library licensed under [MIT](LICENSE).
