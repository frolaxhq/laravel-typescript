# API Resources

If your application uses [Laravel API Resources](https://laravel.com/docs/eloquent-resources) to wrap your models in a standard JSON response (e.g., `{ data: { ... } }`), `laravel-typescript` can automatically generate wrapper types for you.

## Enabling API Resource Wrappers

Enable this feature in your `config/typescript.php`:

```php
'writer' => [
    // ...
    'api_resources' => true,
],
```

## Generated Types

When enabled, the generator will produce an additional `Resource` interface for every model.

### Example

Suppose you have a `User` model. The generator produces the standard `User` interface as usual:

```typescript
export interface User {
  id: number;
  name: string;
  email: string;
}
```

With `api_resources` enabled, it also adds:

```typescript
export interface UserResource {
  data: User;
}
```

## Usage in Frontend

You can now use these resource types as the return type for your API fetching logic:

```typescript
async function fetchUser(id: number): Promise<UserResource> {
  const response = await fetch(`/api/users/${id}`);
  return response.json();
}

// Usage
const userResource = await fetchUser(1);
console.log(userResource.data.name);
```

This keeps your frontend code clean and typesafe, mirroring the exact structure returned by your Laravel backend.
