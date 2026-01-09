# Rules

All rules of this extension is summarised below:

## Superglobals

### SuperglobalsGlobalAssignRule

**Class:** `CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalsGlobalAssignRule`

This rule forbids direct global assignment to all superglobals (except `$_ENV` and `$_SESSION`).
In case of assignment of arrays, it recommends instead using the equivalent method global getter
in `Superglobals` class.

### SuperglobalsOffsetAccessRule

**Class:** `CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalsOffsetAccessRule`

This rule forbids direct offset access on all superglobals (except `$_FILES`, `$_ENV`, and `$_SESSION`).
Instead, it recommends using the equivalent method getter in the `Superglobals` class.

### SuperglobalsOffsetAssignRule

**Class:** `CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalsOffsetAssignRule`

This rule forbids direct offset assignment to all superglobals (except `$_FILES`, `$_ENV`, and `$_SESSION`).
Instead, it recommends using the equivalent method setter in the `Superglobals` class.

### SuperglobalsOffsetUnsetRule

**Class:** `CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalsOffsetUnsetRule`

This rule forbids direct offset unset to all superglobals (except `$_FILES`, `$_ENV`, and `$_SESSION`).
Instead, it recommends using the equivalent method unsetter in the `Superglobals` class.
