# Rules

All rules of this extension is summarised below:

## Classes

### CacheHandlerInstantiationRule

**Class:** `CodeIgniter\PHPStan\Rules\Classes\CacheHandlerInstantiationRule`<br/>
Fixable: No

Simply instantiating cache handlers using `new` is incomplete. The public `initialize()` method
needs to be called on the instance so that the required initializations are done. This is usually
achieved by using `CacheFactory::getHandler()` or using the `cache()` function.

### CastHandlerRule

**Class:** `CodeIgniter\PHPStan\Rules\Classes\CastHandlerRule`<br/>
Fixable: No

This rule validates the `$casts` of an `Entity` or a model against its `$castHandlers`. A cast that is not a
framework built-in needs a handler, and CodeIgniter throws at runtime when it is missing or invalid. The rule
is reported on the offending entry, the `$casts` entry for an unregistered cast and the `$castHandlers` entry
for a missing or invalid handler, with one identifier per problem:

- `codeigniter.castHandlerNotRegistered` is a cast name with no entry in `$castHandlers`.
- `codeigniter.castHandlerNotFound` is a handler class that does not exist.
- `codeigniter.castHandlerInvalid` is a handler that implements neither `CodeIgniter\DataCaster\Cast\CastInterface`
  nor `CodeIgniter\Entity\Cast\CastInterface`.

Only the property defaults are read, so casts or handlers set in a constructor are not seen.

### FactoriesStaticMethodArgumentTypeRule

**Class:** `CodeIgniter\PHPStan\Rules\Classes\FactoriesStaticMethodArgumentTypeRule`<br/>
Fixable: No

This rule is the static counterpart of `FactoriesFunctionArgumentTypeRule`. It validates the constant alias
passed to `Factories::config()`, `Factories::models()` and `Factories::get('config'|'models', ...)`. It
reports an alias that does not resolve to a known class, and, when enabled, a resolved class that does not
extend `CodeIgniter\Config\BaseConfig` or `CodeIgniter\Model` respectively. A non-constant alias, another
component or another method is not checked. It uses the same identifiers and toggles as the function rule: it
is registered only when `codeigniter.checkArgumentTypeOfFactories` is `true`, and the instance checks follow
`codeigniter.checkArgumentTypeOfConfig` and `codeigniter.checkArgumentTypeOfModel`.

### FrameworkExceptionInstantiationRule

**Class:** `CodeIgniter\PHPStan\Rules\Classes\FrameworkExceptionInstantiationRule`<br/>
Fixable: No

This rule forbids creating the `CodeIgniter\Exceptions\FrameworkException` and its child classes
using the `new` keyword. Instead, it directs users to use one of their named constructor methods.

## Database

### ModelWriteAllowedFieldsRule

**Class:** `CodeIgniter\PHPStan\Rules\Database\ModelWriteAllowedFieldsRule`<br/>
Fixable: No

This rule reports a constant string key passed to `insert()`, `update()` or `save()` of a model, when the
key is not in the model's `$allowedFields`. With `$protectFields` on, the model silently drops such a key, so a
typo like `titel` writes nothing. The data can be an array literal or any array of a known shape. The rule is
skipped for a model that has `$protectFields = false` or an empty `$allowedFields`, since the allowed fields may
be set at runtime. Only the property defaults are read, so `setAllowedFields()` and `protect()` calls are not
seen.

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

## Language

### LanguageLineExistenceRule

**Class:** `CodeIgniter\PHPStan\Rules\Language\LanguageLineExistenceRule`<br/>
Fixable: No

This rule validates the constant line passed to `lang()` or `Language::getLine()` against your language
files. A variable typed as string literals, such as `@param 'App.title'|'App.footer' $line`, is checked for
each literal, and each unresolved literal is reported on its own. Lines are looked up for `en`, the default
locale and the supported locales of `Config\App`. The rule has two identifiers:

- `codeigniter.languageLineNotFound` reports a line whose language file exists but defines the key in none of
  the locales. CI would return the raw line as the message. A line whose file is not found at all is not
  reported, since the file may come from a namespace that the analyzer cannot see.
- `codeigniter.languageLineUntranslated` reports a line that `en` defines but a supported locale lacks.
  CI falls back to `en` at runtime, so this is opt-in through `codeigniter.reportUntranslatedLanguageLines`
  (default `false`).

```yml
parameters:
  codeigniter:
    reportUntranslatedLanguageLines: true
```

## Superglobals

### SuperglobalsGlobalAssignRule

**Class:** `CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalsGlobalAssignRule`<br/>
Fixable: Partial (only those single array assignments are fixable)

This rule forbids direct global assignment to all superglobals (except `$_ENV` and `$_SESSION`).
In case of assignment of arrays, it recommends instead using the equivalent method global getter
in `Superglobals` class.

### SuperglobalsOffsetAccessRule

**Class:** `CodeIgniter\PHPStan\Rules\Superglobals\SuperglobalsOffsetAccessRule`<br/>
Fixable: Yes (except multi-variable `isset()`, compound assignment, and increment or decrement)

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
