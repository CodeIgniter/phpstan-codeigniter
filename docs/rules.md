# Rules

All rules of this extension is summarised below:

## Classes

### CacheHandlerInstantiationRule

**Class:** `CodeIgniter\PHPStan\Rules\Classes\CacheHandlerInstantiationRule`<br/>
Fixable: No

Simply instantiating cache handlers using `new` is incomplete. The public `initialize()` method
needs to be called on the instance so that the required initializations are done. This is usually
achieved by using `CacheFactory::getHandler()` or using the `cache()` function.

### FrameworkExceptionInstantiationRule

**Class:** `CodeIgniter\PHPStan\Rules\Classes\FrameworkExceptionInstantiationRule`<br/>
Fixable: No

This rule forbids creating the `CodeIgniter\Exceptions\FrameworkException` and its child classes
using the `new` keyword. Instead, it directs users to use one of their named constructor methods.

## Functions

### FactoriesFunctionArgumentTypeRule

**Class:** `CodeIgniter\PHPStan\Rules\Functions\FactoriesFunctionArgumentTypeRule`<br/>
Fixable: No

This rule validates the class string passed to either the `config()` or `model()` function. It reports
a passed string that does not resolve to a known class, and, when enabled, a resolved class that does not
extend `CodeIgniter\Config\BaseConfig` or `CodeIgniter\Model` respectively. The rule is registered only
when `codeigniter.checkArgumentTypeOfFactories` is `true`, and the per-function instance checks are
toggled by `codeigniter.checkArgumentTypeOfConfig` and `codeigniter.checkArgumentTypeOfModel`.

### ServicesFunctionArgumentTypeRule

**Class:** `CodeIgniter\PHPStan\Rules\Functions\ServicesFunctionArgumentTypeRule`<br/>
Fixable: No

This rule validates the service method name passed to either `service()` or `single_service()` function
if coming from a valid Services class. It also validates the return if a valid object instance. The rule is
registered only when `codeigniter.checkArgumentTypeOfServices` is `true`.

## Superglobals

### SuperglobalsGlobalAssignRule

**Class:** `CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalsGlobalAssignRule`<br/>
Fixable: Partial (only those single array assignments are fixable)

This rule forbids direct global assignment to all superglobals (except `$_ENV` and `$_SESSION`).
In case of assignment of arrays, it recommends instead using the equivalent method global getter
in `Superglobals` class.

### SuperglobalsOffsetAccessRule

**Class:** `CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalsOffsetAccessRule`<br/>
Fixable: Yes

This rule forbids direct offset access on all superglobals (except `$_FILES`, `$_ENV`, and `$_SESSION`).
Instead, it recommends using the equivalent method getter in the `Superglobals` class.

### SuperglobalsOffsetAssignRule

**Class:** `CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalsOffsetAssignRule`<br/>
Fixable: Yes

This rule forbids direct offset assignment to all superglobals (except `$_FILES`, `$_ENV`, and `$_SESSION`).
Instead, it recommends using the equivalent method setter in the `Superglobals` class.

### SuperglobalsOffsetUnsetRule

**Class:** `CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalsOffsetUnsetRule`<br/>
Fixable: No

This rule forbids direct offset unset to all superglobals (except `$_FILES`, `$_ENV`, and `$_SESSION`).
Instead, it recommends using the equivalent method unsetter in the `Superglobals` class.
