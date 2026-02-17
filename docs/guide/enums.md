# Enums

`laravel-typescript` can automatically generate TypeScript enums or types from your PHP enums used in models.

## Supported Styles

You can customize how enums are output in your `config/typescript.php`:

```php
'writer' => [
    // ...
    'enum_style' => 'const_object', // or 'ts_enum', 'union'
],
```

### const_object (Recommended)

Generates a constant object and a type alias. This is the most flexible approach and works well with many frontend utilities.

```typescript
export const UserRole = {
    Admin: 'admin',
    User: 'user',
} as const;

export type UserRole = typeof UserRole[keyof typeof UserRole];
```

### ts_enum

Generates a standard TypeScript enum.

```typescript
export enum UserRole {
    Admin = 'admin',
    User = 'user',
}
```

### union

Generates a simple string (or number) union type.

```typescript
export type UserRole = 'admin' | 'user';
```

## Discovery

The generator automatically finds enums that are:
- Used as type hints in model accessors.
- Defined in the `$casts` property of your model.

## Output Structure

In `per_model_files` mode, enums are placed in a dedicated `enums/` subdirectory to keep your types organized.
