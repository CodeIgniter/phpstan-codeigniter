# Rules

All rules of this extension is summarised below:

## Functions

### ServicesFunctionArgumentTypeRule

**Class:** `CodeIgniter\PHPStan\Rules\Functions\ServicesFunctionArgumentTypeRule`<br/>
Fixable: No

This rule validates the service method name passed to either `service()` or `single_service()` function
if coming from a valid Services class. It also validates the return if a valid object instance.

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
