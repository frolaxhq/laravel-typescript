# Standalone Types

Sometimes you need to define TypeScript interfaces or types that aren't tied to a specific Eloquent model, or you want to define a shared structure that multiple models use.

`laravel-typescript` allows you to define these **Standalone Types** directly in your configuration file.

## Defining Standalone Types

In `config/typescript.php`, you can use the `standalone` mapping under the `mappings` section:

```php
'mappings' => [
    // ...
    'standalone' => [
        'Image' => '{ original: string; thumbnail: string }',
        'Address' => [
            'street' => 'string',
            'city' => 'string',
            'zip' => 'string',
        ],
    ],
],
```

### Format Support

You can provide the definition as a string or an associative array:
- **String**: Useful for raw TypeScript definitions or type aliases.
- **Array**: Useful for defining structures in a PHP-friendly way.

## Referencing in Models

Once defined, you can reference these types in your model accessors using the `@return` docblock:

```php
/**
 * @return Image
 */
protected function avatar(): Attribute
{
    return Attribute::make(get: fn () => $this->getAvatarData());
}
```

The generator will automatically detect that `Image` is a standalone type and use it in the generated interface:

```typescript
export interface Image { original: string; thumbnail: string }

export interface User {
  // ...
  avatar: Image;
}
```

## How it works

The generator intelligently wraps your definitions:
- If the definition doesn't start with `interface` or `type`, it will wrap it.
- If it starts with `{`, it becomes an `export interface`.
- Otherwise, it becomes an `export type`.
- If you provide a full `export interface MyType { ... }` string, it will be used as-is.

## Output Location

- **Single File Mode**: Standalone types are appended at the end of the file.
- **Per Model Mode**: Standalone types are gathered into a dedicated `types.ts` file in your output directory and included in the barrel export (`index.ts`).
