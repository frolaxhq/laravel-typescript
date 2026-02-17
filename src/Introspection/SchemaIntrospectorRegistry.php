<?php

declare(strict_types=1);

namespace Frolax\Typescript\Introspection;

use Frolax\Typescript\Contracts\SchemaIntrospectorContract;
use Frolax\Typescript\Exceptions\IntrospectionException;
use Illuminate\Database\Eloquent\Model;

/**
 * Registry of schema introspectors, auto-selects based on DB driver.
 */
class SchemaIntrospectorRegistry
{
    /** @var array<int, SchemaIntrospectorContract> */
    private array $introspectors = [];

    public function __construct()
    {
        // Register defaults - order matters: first registered = first checked
        $this->introspectors[] = new LaravelSchemaIntrospector; // Checked first
        $this->introspectors[] = new FallbackSchemaIntrospector; // Always last (catch-all)
    }

    /**
     * Register a schema introspector (higher priority = checked first).
     */
    public function register(SchemaIntrospectorContract $introspector): void
    {
        // Prepend so later registrations take priority (except fallback)
        array_unshift($this->introspectors, $introspector);
    }

    /**
     * Get the best introspector for the given connection/driver.
     */
    public function getForConnection(?string $connection = null): SchemaIntrospectorContract
    {
        $driver = $this->resolveDriver($connection);

        foreach ($this->introspectors as $introspector) {
            if ($introspector->supports($driver)) {
                return $introspector;
            }
        }

        throw new IntrospectionException(
            "No schema introspector available for driver '{$driver}'."
        );
    }

    /**
     * Get the best introspector for the given model.
     */
    public function getForModel(Model $model): SchemaIntrospectorContract
    {
        return $this->getForConnection($model->getConnectionName());
    }

    /**
     * Resolve the database driver name for a connection.
     */
    private function resolveDriver(?string $connection): string
    {
        $connection = $connection ?? config('database.default');

        return config("database.connections.{$connection}.driver", 'unknown');
    }
}
