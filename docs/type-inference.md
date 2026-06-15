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

### FakeFunctionReturnTypeExtension

**Class:** `CodeIgniter\PHPStan\Type\FakeFunctionReturnTypeExtension`

This extension provides the precise return type for the `fake()` function, typing it as a single fabricated
record of the given model's return type (an entity, a shaped array, or a `stdClass`). The model may be passed
as a class string, a model name, or a model instance.

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

### ModelFindReturnTypeExtension

**Class:** `CodeIgniter\PHPStan\Type\ModelFindReturnTypeExtension`

This extension provides precise return types for the `find()`, `findAll()`, `first()`, and `findColumn()`
methods of `CodeIgniter\Model` subclasses.

A fetched row is typed from the model's `$returnType`:
- an entity instance (whose properties are typed by the entity extension below, with the producing model's
  `$casts` layered on so an `asObject(SomeEntity::class)` fetch reflects that model's casts, not only the
  entity's own model's),
- a shaped array built from the table's columns and the model's `$casts`, or
- a `stdClass` with those same fields.

The row type also honors an `asArray()`/`asObject()` override and a `select()` field list earlier in the call
chain. A `select()` supports `column`, `table.column`, `as` aliases, and `table.*`, resolving qualified
references (including joined tables) against the introspected schema. A non-constant or unparseable `select()`
falls back to a generic array.

Each method then wraps that row type:
- `find()`: a single row or `null` for a scalar id, a list of rows for an array of ids or no argument.
- `findAll()`: a list of rows.
- `first()`: a single row or `null`.
- `findColumn()`: a list of the selected column's values, or `null`.

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

## Properties Class Reflection Extensions

### EntityPropertiesClassReflectionExtension

**Class:** `CodeIgniter\PHPStan\Reflection\EntityPropertiesClassReflectionExtension`

This extension types the virtual properties of `CodeIgniter\Entity\Entity` subclasses. For each property it
applies, in order, the entity's `$dates`, the entity's `$casts`, then the `$casts` of the model whose
`$returnType` is the entity (CodeIgniter applies those before hydrating the entity), and finally the type of
the backing database column. Custom `$castHandlers` are resolved by reflecting their `get()` method, and the
backing column is found through the model's table. Properties that match none of these resolve to `mixed`.

> [!NOTE]
> **Configuration:**
>
> The Model and Entity column types are derived from a live schema, built by running your migrations against a
> throwaway SQLite database (this requires the `sqlite3` PHP extension). By default every registered namespace
> is scanned (your app plus installed packages). To restrict the schema to a single namespace, set:
>
> ```yml
> parameters:
>   codeigniter:
>     schemaNamespace: Acme\Blog
> ```
