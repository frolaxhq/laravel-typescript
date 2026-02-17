<?php

declare(strict_types=1);

use Frolax\Typescript\Cache\IncrementalCache;
use Frolax\Typescript\Data\ModelReference;

describe('IncrementalCache', function () {
    beforeEach(function () {
        $this->cache = new IncrementalCache(cacheStore: 'array');
    });

    it('detects dirty model on first run', function () {
        $model = new ModelReference(
            className: 'App\\Models\\User',
            shortName: 'User',
            filePath: __DIR__ . '/../../Fixtures/Models/User.php',
        );

        expect($this->cache->isDirty($model))->toBeTrue();
    });

    it('detects clean model after marking', function () {
        $model = new ModelReference(
            className: 'App\\Models\\User',
            shortName: 'User',
            filePath: __DIR__ . '/../../Fixtures/Models/User.php',
        );

        $this->cache->markClean($model);

        expect($this->cache->isDirty($model))->toBeFalse();
    });

    it('detects dirty after forget', function () {
        $model = new ModelReference(
            className: 'App\\Models\\User',
            shortName: 'User',
            filePath: __DIR__ . '/../../Fixtures/Models/User.php',
        );

        $this->cache->markClean($model);
        $this->cache->forget($model);

        expect($this->cache->isDirty($model))->toBeTrue();
    });

    it('caches and retrieves result', function () {
        $model = new ModelReference(
            className: 'App\\Models\\User',
            shortName: 'User',
            filePath: __DIR__ . '/../../Fixtures/Models/User.php',
        );

        $result = ['shortName' => 'User', 'properties' => ['id', 'name']];
        $this->cache->cacheResult($model, $result);

        expect($this->cache->getCachedResult($model))->toBe($result);
    });

    it('returns null for uncached result', function () {
        $model = new ModelReference(
            className: 'App\\Models\\Post',
            shortName: 'Post',
            filePath: __DIR__ . '/../../Fixtures/Models/Post.php',
        );

        expect($this->cache->getCachedResult($model))->toBeNull();
    });

    it('generates stable fingerprint', function () {
        $model = new ModelReference(
            className: 'App\\Models\\User',
            shortName: 'User',
            filePath: __DIR__ . '/../../Fixtures/Models/User.php',
        );

        $fp1 = $this->cache->fingerprint($model);
        $fp2 = $this->cache->fingerprint($model);

        expect($fp1)->toBe($fp2);
    });

    it('generates different fingerprint for different models', function () {
        $user = new ModelReference(
            className: 'App\\Models\\User',
            shortName: 'User',
            filePath: __DIR__ . '/../../Fixtures/Models/User.php',
        );

        $post = new ModelReference(
            className: 'App\\Models\\Post',
            shortName: 'Post',
            filePath: __DIR__ . '/../../Fixtures/Models/Post.php',
        );

        expect($this->cache->fingerprint($user))->not->toBe($this->cache->fingerprint($post));
    });

    it('flushes all cache', function () {
        $model = new ModelReference(
            className: 'App\\Models\\User',
            shortName: 'User',
            filePath: __DIR__ . '/../../Fixtures/Models/User.php',
        );

        $this->cache->markClean($model);
        $this->cache->cacheResult($model, ['test' => true]);

        $this->cache->flush();

        expect($this->cache->isDirty($model))->toBeTrue();
        expect($this->cache->getCachedResult($model))->toBeNull();
    });
});
