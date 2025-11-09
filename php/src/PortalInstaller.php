<?php

namespace Thaumware\Portal;

use Thaumware\Portal\Contracts\StorageAdapter;

/**
 * Framework-agnostic installer
 * Each framework adapts this to its own schema builder
 */
class PortalInstaller
{
    public function __construct(private StorageAdapter $storage)
    {
    }

    /**
     * Check if portal tables exist
     */
    public function isInstalled(): bool
    {
        // If we can find an origin, schema exists
        try {
            $this->storage->findOriginByName('_check');
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Get the schema definition (for framework adapters)
     * Each adapter implements this in its own way
     */
    public function getSchema(): array
    {
        return [
            'portal_origins' => [
                'id' => 'uuid primary',
                'name' => 'string unique',
                'direction' => 'string',
                'type' => 'string',
                'is_active' => 'boolean default:true',
                'created_at' => 'timestamp',
                'updated_at' => 'timestamp',
                'deleted_at' => 'timestamp nullable',
            ],
            'portals' => [
                'id' => 'uuid primary',
                'has_portal_id' => 'string',
                'has_portal_type' => 'string',
                'portal_origin_id' => 'uuid',
                'external_id' => 'string nullable',
                'metadata' => 'json nullable',
                'created_at' => 'timestamp',
                'updated_at' => 'timestamp',
                'deleted_at' => 'timestamp nullable',
                'index' => ['has_portal_id', 'portal_origin_id'],
            ],
        ];
    }
}
