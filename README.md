# CodeIgniter extensions and rules for PHPStan

[![Extension Tests](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-phpunit.yml/badge.svg)](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-phpunit.yml)
[![Coding Standards Check](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-coding-standards.yml/badge.svg)](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-coding-standards.yml)
[![PHPStan Static Analysis](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-phpstan.yml/badge.svg)](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-phpstan.yml)

* [PHPStan](https://phpstan.org/)
* [CodeIgniter](https://codeigniter.com/)

This extension provides the following features:

* Provides correct return types for `config()` and `model()` functions.

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
