<?php

declare(strict_types=1);

namespace Database\Migrations;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CompoundStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

/**
 * Creates all simple lookup/reference tables used throughout the domain
 * (component types, statuses, criticality levels, environments, deployment
 * locations, dependency types, communication protocols, data objects and
 * tags). They are grouped into a single migration because none of them
 * depend on each other, and they are all populated by the lookup seeder.
 */
final class CreateLookupTables implements MigratesUp, MigratesDown
{
    public string $name = '2026_07_02_000003_create_lookup_tables';

    public function up(): QueryStatement
    {
        return new CompoundStatement(
            new CreateTableStatement('component_types')
                ->primary()
                ->varchar('name', 100)
                ->text('description', nullable: true)
                ->datetime('created_at')
                ->datetime('updated_at')
                ->unique('name'),
            new CreateTableStatement('component_statuses')
                ->primary()
                ->varchar('name', 100)
                ->text('description', nullable: true)
                ->integer('sort_order', default: 0)
                ->datetime('created_at')
                ->datetime('updated_at')
                ->unique('name'),
            new CreateTableStatement('criticality_levels')
                ->primary()
                ->varchar('name', 100)
                ->text('description', nullable: true)
                ->integer('sort_order', default: 0)
                ->datetime('created_at')
                ->datetime('updated_at')
                ->unique('name'),
            new CreateTableStatement('environments')
                ->primary()
                ->varchar('name', 100)
                ->text('description', nullable: true)
                ->datetime('created_at')
                ->datetime('updated_at')
                ->unique('name'),
            new CreateTableStatement('deployment_locations')
                ->primary()
                ->varchar('name', 255)
                ->varchar('location_type', 100)
                ->text('description', nullable: true)
                ->datetime('created_at')
                ->datetime('updated_at'),
            new CreateTableStatement('dependency_types')
                ->primary()
                ->varchar('name', 100)
                ->text('description', nullable: true)
                ->datetime('created_at')
                ->datetime('updated_at')
                ->unique('name'),
            new CreateTableStatement('communication_protocols')
                ->primary()
                ->varchar('name', 100)
                ->text('description', nullable: true)
                ->datetime('created_at')
                ->datetime('updated_at')
                ->unique('name'),
            new CreateTableStatement('data_objects')
                ->primary()
                ->varchar('name', 255)
                ->text('description', nullable: true)
                ->boolean('contains_personal_data', default: false)
                ->boolean('contains_sensitive_data', default: false)
                ->datetime('created_at')
                ->datetime('updated_at')
                ->unique('name'),
            new CreateTableStatement('tags')
                ->primary()
                ->varchar('name', 100)
                ->datetime('created_at')
                ->datetime('updated_at')
                ->unique('name'),
        );
    }

    public function down(): QueryStatement
    {
        return new CompoundStatement(
            new DropTableStatement('tags'),
            new DropTableStatement('data_objects'),
            new DropTableStatement('communication_protocols'),
            new DropTableStatement('dependency_types'),
            new DropTableStatement('deployment_locations'),
            new DropTableStatement('environments'),
            new DropTableStatement('criticality_levels'),
            new DropTableStatement('component_statuses'),
            new DropTableStatement('component_types'),
        );
    }
}

