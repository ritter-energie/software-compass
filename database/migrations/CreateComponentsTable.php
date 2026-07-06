<?php

declare(strict_types=1);

namespace Database\Migrations;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CompoundStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\QueryStatements\OnDelete;

/**
 * Creates the `components` table (the central entity of the application)
 * and the `component_tags` pivot table used for free-form tagging.
 */
final class CreateComponentsTable implements MigratesUp, MigratesDown
{
    public string $name = '2026_07_02_000004_create_components_table';

    public function up(): QueryStatement
    {
        return new CompoundStatement(
            new CreateTableStatement('components')
                ->primary()
                ->varchar('name', 255)
                ->varchar('short_name', 100, nullable: true)
                ->varchar('slug', 255)
                ->belongsTo('components.component_type_id', 'component_types.id')
                ->belongsTo('components.status_id', 'component_statuses.id')
                ->belongsTo('components.criticality_id', 'criticality_levels.id', nullable: true)
                ->belongsTo('components.business_owner_id', 'people.id', nullable: true)
                ->belongsTo('components.technical_owner_id', 'people.id', nullable: true)
                ->belongsTo('components.deployment_location_id', 'deployment_locations.id', nullable: true)
                ->belongsTo('components.environment_id', 'environments.id', nullable: true)
                ->varchar('project_name', 255, nullable: true)
                ->date('started_on', nullable: true)
                ->text('purpose', nullable: true)
                ->text('description', nullable: true)
                ->varchar('documentation_url', 1000, nullable: true)
                ->varchar('repository_url', 1000, nullable: true)
                ->varchar('vendor', 255, nullable: true)
                ->text('lifecycle_notes', nullable: true)
                ->boolean('is_external', default: false)
                ->datetime('created_at')
                ->datetime('updated_at')
                ->unique('slug'),
            new CreateTableStatement('component_tags')
                ->belongsTo('component_tags.component_id', 'components.id', onDelete: OnDelete::CASCADE)
                ->belongsTo('component_tags.tag_id', 'tags.id', onDelete: OnDelete::CASCADE)
                ->raw('PRIMARY KEY (component_id, tag_id)'),
        );
    }

    public function down(): QueryStatement
    {
        return new CompoundStatement(
            new DropTableStatement('component_tags'),
            new DropTableStatement('components'),
        );
    }
}



