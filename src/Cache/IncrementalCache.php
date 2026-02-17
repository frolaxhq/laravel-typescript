<?php

declare(strict_types=1);

namespace Frolax\Typescript\Cache;

use Frolax\Typescript\Data\ModelReference;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * Handles incremental builds by fingerprinting models and caching results.
 * Only re-processes models whose source code or schema has changed.
 */
class IncrementalCache
{
    private const CACHE_PREFIX = 'laravel-typescript:';

    public function __construct(
        private readonly string $cacheStore = 'file',
        private readonly int $ttl = 86400, // 24 hours
    ) {}

    /**
     * Check whether a model needs re-processing.
     */
    public function isDirty(ModelReference $model): bool
    {
        $fingerprint = $this->fingerprint($model);
        $cached = $this->getCachedFingerprint($model);

        return $cached !== $fingerprint;
    }

    /**
     * Mark a model as processed with its current fingerprint.
     */
    public function markClean(ModelReference $model): void
    {
        $fingerprint = $this->fingerprint($model);
        Cache::store($this->cacheStore)->put(
            $this->cacheKey($model),
            $fingerprint,
            $this->ttl,
        );
    }

    /**
     * Get the cached result for a model.
     *
     * @return array<string, mixed>|null
     */
    public function getCachedResult(ModelReference $model): ?array
    {
        return Cache::store($this->cacheStore)->get(
            $this->resultCacheKey($model),
        );
    }

    /**
     * Cache a model's generation result.
     *
     * @param array<string, mixed> $result
     */
    public function cacheResult(ModelReference $model, array $result): void
    {
        Cache::store($this->cacheStore)->put(
            $this->resultCacheKey($model),
            $result,
            $this->ttl,
        );
    }

    /**
     * Clear all cached data.
     */
    public function flush(): void
    {
        // Clear all keys with our prefix
        // File cache does not support prefix clearing, so we flush the store
        Cache::store($this->cacheStore)->flush();
    }

    /**
     * Clear cache for a specific model.
     */
    public function forget(ModelReference $model): void
    {
        Cache::store($this->cacheStore)->forget($this->cacheKey($model));
        Cache::store($this->cacheStore)->forget($this->resultCacheKey($model));
    }

    /**
     * Generate a fingerprint for a model based on its source file hash.
     * The fingerprint changes when the model file is modified.
     */
    public function fingerprint(ModelReference $model): string
    {
        $components = [];

        // File content hash
        if ($model->filePath && File::exists($model->filePath)) {
            $components[] = md5_file($model->filePath);
        }

        // Class name (structural identity)
        $components[] = $model->className;

        // Connection name (schema may differ per connection)
        $components[] = $model->connection ?? 'default';

        return md5(implode('|', $components));
    }

    private function getCachedFingerprint(ModelReference $model): ?string
    {
        return Cache::store($this->cacheStore)->get($this->cacheKey($model));
    }

    private function cacheKey(ModelReference $model): string
    {
        return self::CACHE_PREFIX . 'fingerprint:' . md5($model->className);
    }

    private function resultCacheKey(ModelReference $model): string
    {
        return self::CACHE_PREFIX . 'result:' . md5($model->className);
    }
}
