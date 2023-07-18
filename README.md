# CodeIgniter extensions and rules for PHPStan

[![Coverage Status](https://coveralls.io/repos/github/CodeIgniter/phpstan-codeigniter/badge.svg?branch=1.x)](https://coveralls.io/github/CodeIgniter/phpstan-codeigniter?branch=1.x)
[![Extension Tests](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-phpunit.yml/badge.svg)](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-phpunit.yml)
[![Coding Standards Check](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-coding-standards.yml/badge.svg)](https://github.com/CodeIgniter/phpstan-codeigniter/actions/workflows/test-coding-standards.yml)

* [PHPStan](https://phpstan.org/)
* [CodeIgniter](https://codeigniter.com/)

This extension provides the following features:

## Installation

To use this extension, require it in [Composer](https://getcomposer.org/):

```
composer require --dev codeigniter/phpstan-codeigniter
```

If you also install [phpstan/extension-installer](https://github.com/phpstan/extension-installer) then you're all set!

<details>
	<summary>Manual installation</summary>

If you don't want to use `phpstan/extension-installer`, include extension.neon in your project's PHPStan config:

```
includes:
    - vendor/codeigniter/phpstan-codeigniter/extension.neon
```

</details>
