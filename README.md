# CodeIgniter extensions and rules for PHPStan

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
