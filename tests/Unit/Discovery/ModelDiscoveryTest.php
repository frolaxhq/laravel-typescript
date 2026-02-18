<?php

namespace Frolax\Typescript\Tests\Unit\Discovery;

use Frolax\Typescript\Data\GenerationConfig;
use Frolax\Typescript\Discovery\ModelDiscovery;
use Frolax\Typescript\Tests\TestCase;

class ModelDiscoveryTest extends TestCase
{
    private string $fixturePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixturePath = __DIR__ . '/Fixtures';
        
        // Mock base path to point to fixtures
        $this->app->setBasePath($this->fixturePath);

        // Register autoloader for fixture models
        spl_autoload_register(function ($class) {
            $prefix = 'App\\';
            $base_dir = $this->fixturePath . '/App/';
            
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                // Check Other namespace
                $prefix = 'Other\\';
                $base_dir = $this->fixturePath . '/Other/';
                $len = strlen($prefix);
                if (strncmp($prefix, $class, $len) !== 0) {
                    return;
                }
            }

            $relative_class = substr($class, $len);
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

            if (file_exists($file)) {
                require $file;
            }
        });
    }

    protected function tearDown(): void
    {
        // Unregister autoloader - anonymous function removal is tricky but this is fine for tests
        // spl_autoload_unregister(...);
        parent::tearDown();
    }

    public function test_it_discovers_models_from_composer_paths_when_auto_discover_is_true()
    {
        $config = new GenerationConfig(
            autoDiscover: true,
            paths: [], // No manual paths
        );

        $discovery = new ModelDiscovery();
        $models = $discovery->discover($config);
        
        $this->assertNotEmpty($models);
        
        $classNames = $models->pluck('className')->toArray();
        $this->assertContains('App\Models\User', $classNames);
        $this->assertContains('App\Models\Post', $classNames);
        $this->assertContains('Other\Models\Tag', $classNames);
    }

    public function test_it_does_not_discover_when_auto_discover_is_false()
    {
        // When paths is empty, it defaults to app_path('Models')
        // In our mock, app_path('Models') resolves to proper App models because base_path is mocked.
        // So we must explicitly set a non-existent or empty path to verify auto-discover is OFF.
        
        $config = new GenerationConfig(
            autoDiscover: false,
            paths: [__DIR__ . '/Fixtures/Empty'],
        );

        if (!is_dir(__DIR__ . '/Fixtures/Empty')) {
            mkdir(__DIR__ . '/Fixtures/Empty');
        }

        $discovery = new ModelDiscovery();
        $models = $discovery->discover($config);

        $this->assertEmpty($models);
        rmdir(__DIR__ . '/Fixtures/Empty');
    }

    public function test_it_merges_auto_discovered_paths_with_configured_paths()
    {
        $config = new GenerationConfig(
            autoDiscover: true,
            paths: [__DIR__ . '/Fixtures/Other/Models'],
        );

        $discovery = new ModelDiscovery();
        $models = $discovery->discover($config);

        // Should contain all models + unique check so no duplicates
        $classNames = $models->pluck('className')->toArray();
        
        $this->assertContains('App\Models\User', $classNames);
        $this->assertContains('App\Models\Post', $classNames);
        $this->assertContains('Other\Models\Tag', $classNames);
        
        // Verify uniqueness
        $this->assertCount(count(array_unique($classNames)), $classNames);
    }
}
