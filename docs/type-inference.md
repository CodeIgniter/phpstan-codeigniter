# Type Inference

All type inference features of this extension are summarised below:

## Dynamic Function Return Type Extensions

### ServicesReturnTypeExtension

**Class:** `CodeIgniter\PHPStan\Type\ServicesReturnTypeExtension`

This extension provides precise return types for the `service()` and `single_service()` functions.

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
