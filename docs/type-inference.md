# Type Inference

All type inference features of this extension are summarised below:

## Dynamic Function Return Type Extensions

### ServicesReturnTypeExtension

**Class:** `CodeIgniter\PHPStan\Type\ServicesReturnTypeExtension`

This extension provides precise return types for the `service()` and `single_service()` functions.

## Dynamic Method Return Type Extensions

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

### ServicesGetSharedInstanceReturnTypeExtension

**Class:** `CodeIgniter\PHPStan\Type\ServicesGetSharedInstanceReturnTypeExtension`

This extension provides precise return type for the static `getSharedInstance()` method of services class.
