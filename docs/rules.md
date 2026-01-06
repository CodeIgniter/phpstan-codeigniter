# Rules

All rules of this extension is summarised below:

## Superglobals

### SuperglobalsAccessRule

**Class:** `CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalsAccessRule`
**Enabled:** Yes

This rule forbids direct offset access on all superglobals (except `$_FILES` and `$_ENV`).
Instead, it recommends using the equivalent method getter in the `Superglobals` class.
