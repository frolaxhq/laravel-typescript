# Accessors & Mutators

`laravel-typescript` has deep support for both traditional and modern Laravel accessors.

## Traditional Accessors

The generator automatically detects methods following the `get...Attribute` pattern:

```php
public function getFullNameAttribute(): string
{
    return "{$this->first_name} {$this->last_name}";
}
```

Result:

```typescript
export interface User {
  // ...
  full_name: string;
}
```

## Modern Attributes (Laravel 9+)

Support for the `Attribute` class is built-in. The return type of the accessor is used as the TypeScript type.

```php
use Illuminate\Database\Eloquent\Casts\Attribute;

protected function isVerified(): Attribute
{
    return Attribute::make(get: fn () => $this->email_verified_at !== null);
}
```

Result:

```typescript
export interface User {
  // ...
  is_verified: boolean;
}
```

## Performance Note

For modern Attributes, the generator uses reflection to inspect the closure's return type. If you haven't specified a return type hint in your PHP code, it will attempt to extract it from the `@return` docblock. If that fails, it defaults to `any` or `unknown`.

## Visibility

The generator scans both `public` and `protected` methods to find your accessors, ensuring that internal-only accessors used for logic are correctly typed in your frontend.
