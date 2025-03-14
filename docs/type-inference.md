# Type Inference

All type inference capabilities of this extension are summarised below:

## Dynamic Static Method Return Type Extensions

### ReflectionHelperGetPrivateMethodInvokerReturnTypeExtension

This extension provides precise return type to `ReflectionHelper`'s static `getPrivateMethodInvoker()` method.
Since PHPStan's dynamic return type extensions work on classes, not traits, this extension is on by default
in test cases extending `CodeIgniter\Test\CIUnitTestCase`. To make this work, you should be calling the method
**statically**:

For example, we're accessing the private method `Foo::privateMethod()` which accepts a string parameter and returns bool.

**Before**
```php
public function testSomePrivateMethod(): void
{
    $method = self::getPrivateMethodInvoker(new Foo(), 'privateMethod');
    \PHPStan\dumpType($method); // Closure(mixed ...): mixed
}

```

**After**
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
