<?php

namespace Thaumware\Portal;

/**
 * Portal schema definition (framework-agnostic)
 */
class Schema
{
    public static function tables(): array
    {
        return [
            'portal_origins' => [
                'id' => ['type' => 'uuid', 'primary' => true],
                'name' => ['type' => 'string', 'unique' => true],
                'direction' => ['type' => 'string'],
                'type' => ['type' => 'string'],
                'is_active' => ['type' => 'boolean', 'default' => true],
                'created_at' => ['type' => 'timestamp'],
                'updated_at' => ['type' => 'timestamp'],
                'deleted_at' => ['type' => 'timestamp', 'nullable' => true],
            ],
            'portals' => [
                'id' => ['type' => 'uuid', 'primary' => true],
                'has_portal_id' => ['type' => 'string', 'index' => true],
                'has_portal_type' => ['type' => 'string'],
                'portal_origin_id' => ['type' => 'uuid', 'index' => true],
                'external_id' => ['type' => 'string', 'nullable' => true],
                'metadata' => ['type' => 'json', 'nullable' => true],
                'created_at' => ['type' => 'timestamp'],
                'updated_at' => ['type' => 'timestamp'],
                'deleted_at' => ['type' => 'timestamp', 'nullable' => true],
            ],
        ];
    }
}
