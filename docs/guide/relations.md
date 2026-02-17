# Relations

`laravel-typescript` can automatically discover and type your Eloquent relations, enabling full end-to-end typesafety.

## Supported Relations

The following relation types are automatically detected:
- `HasOne` / `HasOneThrough`
- `BelongsTo`
- `HasMany` / `HasManyThrough` / `BelongsToMany`
- `MorphOne` / `MorphMany` / `MorphTo`
- `MorphToMany` / `MorphedByMany`

## Configuration

You can control how relations are generated in `config/typescript.php`:

```php
'relations' => [
    'enabled' => true,
    'optional' => false, // Make relation properties optional (T | undefined)
    'max_depth' => 1,    // Recursion limit for nested relations
],
```

### Max Depth

To prevent circular dependencies and giant interface trees, the `max_depth` setting limits how deep the generator will go.
- **Depth 0**: No relations are generated.
- **Depth 1**: Direct relations are generated.
- **Depth 2+**: Relations of relations are generated.

## Aggregate Properties

The generator also supports special Laravel aggregate properties:

### Counts

If you use `withCount(['posts'])`, you can have `posts_count: number` in your TypeScript type.

```php
'relations' => [
    'counts' => [
        'enabled' => true,
        'optional' => false,
    ],
],
```

### Exists

Support for `withExists(['posts'])` providing `posts_exists: boolean`.

### Sums

Support for `withSum(['posts', 'votes'])` providing `posts_sum_votes: number | null`.
