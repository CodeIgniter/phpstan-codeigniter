# Type Inference

All type inference features of this extension are summarised below:

## Dynamic Method Return Type Extensions

### SuperglobalsMethodDynamicReturnTypeExtension

**Class:** `CodeIgniter\PHPStan\Type\SuperglobalsMethodDynamicReturnTypeExtension`
**Enabled:** Yes

This extension provides precise return types for the following methods of `CodeIgniter\Superglobals`:
- `server()`
- `get()`
- `post()`
- `cookie()`
- `request()`
- `getGlobalArray()`
