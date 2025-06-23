# Type Inference

All type inference capabilities of this extension are summarised below:

## Dynamic Function Return Type Extensions

### FactoriesFunctionReturnTypeExtension

This extension provides precise return types for the `config()` and `model()` functions.

**Before:**
```php
\PHPStan\dumpType(config('bar')); // BaseConfig|null
\PHPStan\dumpType(config('App')); // BaseConfig|null
\PHPStan\dumpType(model(BarModel::class)); // Model|null

```

**After:**
```php
\PHPStan\dumpType(config('bar')); // null
\PHPStan\dumpType(config('App')); // Config\App
\PHPStan\dumpType(model(BarModel::class)); // CodeIgniter\PHPStan\Tests\Fixtures\Type\BarModel

```

> [!NOTE]
> **Configuration:**
>
> This extension adds the default namespace for `config()` and `model()` functions as `Config\`
> and `App\Models\`, respectively, when searching for possible classes. If your application uses
> other namespaces, you can configure this extension in your `phpstan.neon` to recognize those namespaces:
>
> ```yml
> parameters:
>   codeigniter:
>     additionalConfigNamespaces:
>       - Acme\Blog\Config\
>       - Foo\Bar\Config\
>     additionalModelNamespaces:
>       - Acme\Blog\Models\
>
> ```

### FakeFunctionReturnTypeExtension

This extension provides precise return type for the `fake()` function.

**Before:**
```php
\PHPStan\dumpType(fake('baz')); // array|object
\PHPStan\dumpType(fake(BarModel::class)); // array|object
\PHPStan\dumpType(fake(UserModel::class)); // array|object
\PHPStan\dumpType(fake(UserIdentityModel::class)); // array|object
\PHPStan\dumpType(fake(LoginModel::class)); // array|object
\PHPStan\dumpType(fake(TokenLoginModel::class)); // array|object
\PHPStan\dumpType(fake(GroupModel::class)); // array|object

```

**After:**
```php
\PHPStan\dumpType(fake('baz')); // never
\PHPStan\dumpType(fake(BarModel::class)); // stdClass
\PHPStan\dumpType(fake(UserModel::class)); // CodeIgniter\Shield\Entities\User
\PHPStan\dumpType(fake(UserIdentityModel::class)); // CodeIgniter\Shield\Entities\UserIdentity
\PHPStan\dumpType(fake(LoginModel::class)); // CodeIgniter\Shield\Entities\Login
\PHPStan\dumpType(fake(TokenLoginModel::class)); // CodeIgniter\Shield\Entities\Login
\PHPStan\dumpType(fake(GroupModel::class)); // array{user_id: int, group: string, created_at: string}

```

> [!NOTE]
> **Configuration:**
>
> When the model passed to `fake()` has the property `$returnType` set to `array`, this extension will give
> a precise array shape based on the allowed fields of the model. Most of the time, the formatted fields are
> strings. If not a string, you can indicate the format return type for the particular field.
>
> ```yml
> parameters:
>   codeigniter:
>     notStringFormattedFields: # key-value pair of field => format
>       success: bool
>       user_id: int
> ```

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

> [!NOTE]
> **Configuration:**
>
> You can instruct PHPStan to consider your own services factory classes.
> **Please note that it should be a valid class extending `CodeIgniter\Config\BaseService`!**
>
> ```yml
> parameters:
>   codeigniter:
>     additionalServices:
>       - Acme\Blog\Config\ServiceFactory
> ```

## Dynamic Method Return Type Extension

### ModelFindReturnTypeExtension

This extension provides precise return types for `CodeIgniter\Model`'s `find()`, `findAll()`, and `first()` methods.
This also allows dynamic return type transformation of `CodeIgniter\Model` when `asArray()` or `asObject()` is called.

## Dynamic Static Method Return Type Extensions

### CacheFactoryHandlerReturnTypeExtension

This extension provides precise return type to `CacheFactory::getHandler()` static method.

**Before:**
```php
\PHPStan\dumpType(CacheFactory::getHandler(new Cache())); // CodeIgniter\Cache\CacheInterface
\PHPStan\dumpType(CacheFactory::getHandler(new Cache(), 'redis')); // CodeIgniter\Cache\CacheInterface
```

**After:**
```php
\PHPStan\dumpType(CacheFactory::getHandler(new Cache())); // CodeIgniter\Cache\Handlers\FileHandler
\PHPStan\dumpType(CacheFactory::getHandler(new Cache(), 'redis')); // CodeIgniter\Cache\Handlers\RedisHandler
```

> [!NOTE]
> **Configuration:**
>
> By default, this extension only considers the primary handler as the return type. If that fails (e.g. the handler
> is not defined in the Cache config's `$validHandlers` array), then this will return the backup handler as
> return type. If you want to return both primary and backup handlers as return type, you can set this:
>
> ```yml
> parameters:
>   codeigniter:
>     addBackupHandlerAsReturnType: true
> ```
>
> This setting will give the return type as a benevolent union of the primary and backup handler types.
>
> ```php
> \PHPStan\dumpType(CacheFactory::getHandler(new Cache())); // (FileHandler|DummyHandler)
> \PHPStan\dumpType(CacheFactory::getHandler(new Cache(), 'redis', 'file')); // (FileHandler|RedisHandler)
> ```

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
> **Configuration:**
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

> [!NOTE]
> **Configuration:**
>
> You can instruct PHPStan to consider your own services factory classes.
> **Please note that it should be a valid class extending `CodeIgniter\Config\BaseService`!**
>
> ```yml
> parameters:
>   codeigniter:
>     additionalServices:
>       - Acme\Blog\Config\ServiceFactory
> ```
