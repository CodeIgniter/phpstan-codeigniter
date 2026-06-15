# PHPStan CodeIgniter4 extension

[![Extension Tests](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-phpunit.yml/badge.svg)](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-phpunit.yml)
[![Coding Standards Check](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-coding-standards.yml/badge.svg)](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-coding-standards.yml)
[![PHPStan Static Analysis](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-phpstan.yml/badge.svg)](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-phpstan.yml)

* [PHPStan](https://phpstan.org/)
* [CodeIgniter4](https://codeigniter.com/)

## Description

This PHPStan extension provides type inference support and rules for `CodeIgniter4`.

* [Rules](docs/rules.md)
* [Type inference](docs/type-inference.md)
* [Upgrading from 1.x to 2.x](docs/upgrading.md)

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

## Bootstrapping CodeIgniter

This extension boots CodeIgniter during analysis so it can read your configuration, services, and database
schema. Point PHPStan at a bootstrap file that loads the framework. CodeIgniter's own test bootstrap works
out of the box:

```yml
parameters:
    bootstrapFiles:
        - vendor/codeigniter4/framework/system/Test/bootstrap.php
```

The Model and Entity type inference additionally materializes your schema by running your migrations against
a temporary SQLite database, so the `sqlite3` PHP extension is required (it is declared in this package's
`composer.json`). The schema is cached under the working directory's `tmp` folder by default. See the
[type inference docs](docs/type-inference.md) for how to point it at a specific namespace.

## Contributing

Any contributions are welcome.

If you want to see a new rule or extension specific to CodeIgniter, please open a
[feature request](https://github.com/CodeIgniter/phpstan-codeigniter/issues/new?assignees=&labels=feature+request&projects=&template=feature_request.yml). If you can contribute the code yourself, please open a pull request instead.

Before reporting any bugs, please check if the bug occurs only if using this extension with PHPStan. If the bug is
reproducible in PHPStan alone, please open a bug report there instead. Thank you!

## License

PHPStan CodeIgniter is an open source library licensed under [MIT](LICENSE).
