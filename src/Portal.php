<?php

namespace Thaumware\Portal;

use Thaumware\Portal\Core\PortalRegistry;
use Thaumware\Portal\Core\RelationLoader;

/**
 * Portal - Cross-service data relationships
 * 
 * Usage:
 *   Portal::install();  // Set up in bootstrap
 *   Portal::register('items', 'http://catalog:9110/api/items')
 *   Portal::attach($models)
 */
class Portal
{
    private static ?PortalRegistry $registry = null;
    private static ?RelationLoader $loader = null;
    private static bool $installed = false;

    private static ?Adapters\IlluminateAdapter $adapter = null;

    /**
     * Install Portal with custom adapter
     * Default: Illuminate (call Portal::install() in Laravel)
     */
    public static function install(?Adapters\IlluminateAdapter $adapter = null): void
    {
        if (self::$installed) {
            return;
        }

        $app = app();

        if ($adapter === null) {
            // Auto-resolve Illuminate if not provided
            $adapter = new Adapters\IlluminateAdapter(
                \Illuminate\Support\Facades\DB::getFacadeRoot(),
                \Illuminate\Support\Facades\Http::getFacadeRoot(),
                new class {
                public function uuid()
                {
                    return \Illuminate\Support\Str::uuid();
                }
                }
            );
        }

        self::$adapter = $adapter;

        $app->singleton(
            Contracts\StorageAdapter::class,
            fn() => $adapter
        );
        $app->singleton(
            Contracts\DataFetcher::class,
            fn() => $adapter
        );
        $app->singleton(PortalRegistry::class);
        $app->singleton(RelationLoader::class);

        // Create tables if needed
        self::ensureTables();

        self::$installed = true;
    }

    /**
     * Create Portal tables if they don't exist
     */
    private static function ensureTables(): void
    {
        $schema = \Illuminate\Support\Facades\Schema::getFacadeRoot();

        if (!$schema->hasTable('portal_origins')) {
            $schema->create('portal_origins', function ($table) {
                $table->uuid('id')->primary();
                $table->string('name')->unique();
                $table->string('direction');
                $table->string('type');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!$schema->hasTable('portals')) {
            $schema->create('portals', function ($table) {
                $table->uuid('id')->primary();
                $table->string('has_portal_id');
                $table->string('has_portal_type');
                $table->uuid('portal_origin_id');
                $table->string('external_id')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index('has_portal_id');
                $table->index('portal_origin_id');
            });
        }
    }

    private static function registry(): PortalRegistry
    {
        return self::$registry ??= app(PortalRegistry::class);
    }

    private static function loader(): RelationLoader
    {
        return self::$loader ??= app(RelationLoader::class);
    }

    /**
     * Register origin
     * 
     * @param string $name Unique name
     * @param string $source Table name or URL
     * @param string $type 'table' or 'http'
     */
    public static function register(string $name, string $source, string $type = 'table'): string
    {
        return self::registry()->register($name, $source, $type);
    }

    /**
     * Create relation
     * 
     * @param string $modelId Local model ID
     * @param string $modelType Model class name
     * @param string $originName Origin name
     * @param string $relatedId Related entity ID
     */
    public static function link(
        string $modelId,
        string $modelType,
        string $originName,
        string $relatedId,
        ?array $metadata = null
    ): void {
        self::registry()->link($modelId, $modelType, $originName, $relatedId, $metadata);
    }

    /**
     * Load relations into models (batch)
     * 
     * @param array|object $models Collection or array
     * @return array|object Same type as input
     */
    public static function attach($models)
    {
        return self::loader()->attach($models);
    }

    /**
     * Deactivate origin
     */
    public static function deactivate(string $originName): void
    {
        self::registry()->deactivate($originName);
    }
}

