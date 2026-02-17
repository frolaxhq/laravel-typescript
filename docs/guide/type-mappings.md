# Type Mappings

`laravel-typescript` comes with sensible defaults for mapping database and PHP types to TypeScript, but you have full control over these mappings.

## Default Mappings

| PHP/DB Type | TypeScript Type |
| --- | --- |
| `string`, `char`, `text` | `string` |
| `int`, `integer`, `bigint`, `float`, `double` | `number` |
| `bool`, `boolean` | `boolean` |
| `date`, `datetime`, `timestamp` | `string` |
| `json`, `array` | `any` |

## Custom Type Mappings

You can override any type mapping in your `config/typescript.php`:

```php
'mappings' => [
    'custom' => [
        'point' => '{ lat: number; lng: number }',
        'money' => 'string',
        'decimal' => 'number',
    ],
],
```

## Global Type Overrides

### Timestamps as Date

By default, timestamps are generated as `string`. You can change this globally to `Date`:

```php
'mappings' => [
    'timestamps_as_date' => true,
],
```

### Decimals as String

If you use high-precision decimals and want to avoid precision loss in JavaScript, you can map them all to `string`:

```php
'mappings' => [
    'decimals_as_string' => true,
],
```
