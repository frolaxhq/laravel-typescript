# Quick Start

Generating TypeScript definitions is simple. Once you've installed the package and published the config, you're ready to go.

## Generate Types

Run the following command to generate your TypeScript definitions:

```bash
php artisan typescript:generate
```

By default, this will scan your `app/Models` directory and generate a single `resources/js/types/generated/models.d.ts` file.

## Basic Usage

Suppose you have a `User` model:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['name', 'email'];
}
```

Running the generate command will produce:

```typescript
export interface User {
  id: number;
  name: string;
  email: string;
  created_at: string | null;
  updated_at: string | null;
}
```

## Adding Relations

If your model has relations, they'll be automatically detected:

```php
public function posts()
{
    return $this->hasMany(Post::class);
}
```

Result:

```typescript
export interface User {
  // ...
  posts: Post[];
}
```

## Customizing Output

You can change where files are generated and how they are structured in `config/typescript.php`:

```php
'output' => [
    'path' => resource_path('js/types'),
    'per_model_files' => true,
],
```

Setting `per_model_files` to `true` will generate a separate file for each model and a barrel export (`index.ts`).
