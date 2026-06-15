# Type Inference

All type inference features of this extension are summarised below:

## Dynamic Function Return Type Extensions

### ServicesReturnTypeExtension

**Class:** `CodeIgniter\PHPStan\Type\ServicesReturnTypeExtension`

This extension provides precise return types for the `service()` and `single_service()` functions.

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
>       - Acme\Blog\Config\Services
> ```

### FactoriesFunctionReturnTypeExtension

**Class:** `CodeIgniter\PHPStan\Type\FactoriesFunctionReturnTypeExtension`

This extension provides precise return types for the `config()` and `model()` functions, resolving the
class from the passed name or class string.

> [!NOTE]
> **Configuration:**
>
> You can instruct PHPStan to consider additional namespaces when resolving the passed name to a class.
>
> ```yml
> parameters:
>   codeigniter:
>     additionalConfigNamespaces:
>       - Acme\Blog\Config\
>     additionalModelNamespaces:
>       - Acme\Blog\Models\
> ```

## Dynamic Method Return Type Extensions

### ReflectionHelperMethodInvokerStaticReturnTypeExtension

**Class:** `CodeIgniter\PHPStan\Type\ReflectionHelperMethodInvokerReturnTypeExtension`

This extension provides precise return type for the method call to
`ReflectionHelper::getPrivateMethodInvoker()` (i.e., `$this->getPrivateMethodInvoker()`).
This is enabled by default for tests extending `CodeIgniter\Test\CIUnitTestCase`.

> [!NOTE]
> **Configuration:**
>
> If you are using `ReflectionHelper` outside of testing, you can still enjoy the precise return types by adding a
> service for the class using this trait. In your `phpstan.neon` (or `phpstan.dist.neon`), add the following to
> the _**services**_ schema:
>
> ```yml
> -
>  class: CodeIgniter\PHPStan\Type\ReflectionHelperMethodInvokerReturnTypeExtension
>  tags:
>    - phpstan.broker.dynamicMethodReturnTypeExtension
>  arguments:
>    class: <Fully qualified class name of class using ReflectionHelper>
>
> ```

### SuperglobalsMethodDynamicReturnTypeExtension

**Class:** `CodeIgniter\PHPStan\Type\SuperglobalsMethodDynamicReturnTypeExtension`

This extension provides precise return types for the following methods of `CodeIgniter\Superglobals`:
- `server()`
- `get()`
- `post()`
- `cookie()`
- `request()`
- `getGlobalArray()`

## Dynamic Static Method Return Type Extensions

### ReflectionHelperMethodInvokerStaticReturnTypeExtension

**Class:** `CodeIgniter\PHPStan\Type\ReflectionHelperMethodInvokerStaticReturnTypeExtension`

This extension provides precise return type for the static method call to
`ReflectionHelper::getPrivateMethodInvoker()` (i.e., `self::getPrivateMethodInvoker()`).
This is enabled by default for tests extending `CodeIgniter\Test\CIUnitTestCase`.

> [!NOTE]
> **Configuration:**
>
> If you are using `ReflectionHelper` outside of testing, you can still enjoy the precise return types by adding a
> service for the class using this trait. In your `phpstan.neon` (or `phpstan.dist.neon`), add the following to
> the _**services**_ schema:
>
> ```yml
> -
>  class: CodeIgniter\PHPStan\Type\ReflectionHelperMethodInvokerStaticReturnTypeExtension
>  tags:
>    - phpstan.broker.dynamicStaticMethodReturnTypeExtension
>  arguments:
>    class: <Fully qualified class name of class using ReflectionHelper>
>
> ```

### ServicesGetSharedInstanceReturnTypeExtension

**Class:** `CodeIgniter\PHPStan\Type\ServicesGetSharedInstanceReturnTypeExtension`

This extension provides precise return type for the static `getSharedInstance()` method of services class.

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
>       - Acme\Blog\Config\Services
> ```

### CacheFactoryGetHandlerReturnTypeExtension

**Class:** `CodeIgniter\PHPStan\Type\CacheFactoryGetHandlerReturnTypeExtension`

This extension provides precise return types for the static `CacheFactory::getHandler()` method, resolving
the handler class from the `validHandlers` of the passed `Config\Cache` and the requested handler and backup
handler names.

> [!NOTE]
> **Configuration:**
>
> By default, only the resolved primary handler is used as the return type. To also include the backup handler
> in the inferred return type, enable:
>
> ```yml
> parameters:
>   codeigniter:
>     addBackupHandlerAsReturnType: true
> ```
