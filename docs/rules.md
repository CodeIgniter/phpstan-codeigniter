# Rules

All rules of this extension is summarised below:

## Superglobals

### SuperglobalsOffsetAccessRule

**Class:** `CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalsOffsetAccessRule`

This rule forbids direct offset access on all superglobals (except `$_FILES`, `$_ENV`, and `$_SESSION`).
Instead, it recommends using the equivalent method getter in the `Superglobals` class.

### SuperglobalsOffsetAssignRule

**Class:** `CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalsOffsetAssignRule`

This rule forbids direct offset assignment to all superglobals (except `$_FILES`, `$_ENV`, and `$_SESSION`).
Instead, it recommends using the equivalent method setter in the `Superglobals` class.
