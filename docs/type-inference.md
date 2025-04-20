# Type Inference

All type inference capabilities of this extension are summarised below:

## Dynamic Function Return Type Extensions

### ServicesFunctionReturnTypeExtension

This extension provides precise return types for the `service()` and `single_service()` functions.

**Before:**
```php
\PHPStan\dumpType(service('cache')); // object|null

```

**After:**
```php
\PHPStan\dumpType(service('cache')); // CodeIgniter\Cache\CacheInterface
```

## Dynamic Static Method Return Type Extensions

### ReflectionHelperGetPrivateMethodInvokerReturnTypeExtension

This extension provides precise return type to `ReflectionHelper`'s static `getPrivateMethodInvoker()` method.
Since PHPStan's dynamic return type extensions work on classes, not traits, this extension is on by default
in test cases extending `CodeIgniter\Test\CIUnitTestCase`. To make this work, you should be calling the method
**statically**:

For example, we're accessing the private method:
```php
class Foo
{
    private static function privateMethod(string $value): bool
    {
        return true;
    }
}

```

**Before:**
```php
public function testSomePrivateMethod(): void
{
    $method = self::getPrivateMethodInvoker(new Foo(), 'privateMethod');
    \PHPStan\dumpType($method); // Closure(mixed ...): mixed
}

```

**After:**
```php
public function testSomePrivateMethod(): void
{
    $method = self::getPrivateMethodInvoker(new Foo(), 'privateMethod');
    \PHPStan\dumpType($method); // Closure(string): bool
}

```

> [!NOTE]
>
> If you are using `ReflectionHelper` outside of testing, you can still enjoy the precise return types by adding a
> service for the class using this trait. In your `phpstan.neon` (or `phpstan.neon.dist`), add the following to
> the _**services**_ schema:
>
> ```yml
> -
>  class: CodeIgniter\PHPStan\Type\ReflectionHelperGetPrivateMethodInvokerReturnTypeExtension
>  tags:
>    - phpstan.broker.dynamicStaticMethodReturnTypeExtension
>  arguments:
>    class: <Fully qualified class name of class using ReflectionHelper>
>
> ```

### ServicesGetSharedInstanceReturnTypeExtension

This extension provides precise return type for the protected static method `getSharedInstance()` of `Services`.

**Before:**
```php
<?php
class MyService extends \Config\Services
{
    public static function bar(bool $getShared = true): Bar
    {
        if ($getShared) {
            \PHPStan\dumpType(static::getSharedInstance('bar')); // object
            return static::getSharedInstance('bar');
        }

        return new Bar();
    }
}

```

**After:**
```php
<?php
class MyService extends \Config\Services
{
    public static function bar(bool $getShared = true): Bar
    {
        if ($getShared) {
            \PHPStan\dumpType(static::getSharedInstance('bar')); // Bar
            return static::getSharedInstance('bar');
        }

        return new Bar();
    }
}

```
