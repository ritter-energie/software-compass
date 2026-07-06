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
 * Creates the `dependencies` table (interfaces / communication paths between
 * two components) and the `dependency_data_objects` pivot table.
 *
 * Note: the domain-level rule "source and target must differ" (see
 * `App\Domain\Dependency\Dependency`) is enforced in PHP, not via a CHECK
 * constraint, to keep behaviour consistent across MariaDB versions.
 */
final class CreateDependenciesTable implements MigratesUp, MigratesDown
{
    public string $name = '2026_07_02_000005_create_dependencies_table';

    public function up(): QueryStatement
    {
        return new CompoundStatement(
            new CreateTableStatement('dependencies')
                ->primary()
                ->integer('source_component_id')
                ->integer('target_component_id')
                ->foreignKey('dependencies.source_component_id', 'components.id')
                ->foreignKey('dependencies.target_component_id', 'components.id')
                ->belongsTo('dependencies.dependency_type_id', 'dependency_types.id')
                ->belongsTo('dependencies.protocol_id', 'communication_protocols.id', nullable: true)
                ->belongsTo('dependencies.status_id', 'component_statuses.id')
                ->belongsTo('dependencies.criticality_id', 'criticality_levels.id', nullable: true)
                ->belongsTo('dependencies.owner_id', 'people.id', nullable: true)
                ->varchar('name', 255)
                ->text('description', nullable: true)
                ->text('data_description', nullable: true)
                ->varchar('frequency', 255, nullable: true)
                ->varchar('direction', 50, default: 'source_to_target')
                ->varchar('authentication_method', 255, nullable: true)
                ->varchar('documentation_url', 1000, nullable: true)
                ->text('technical_notes', nullable: true)
                ->boolean('is_bidirectional', default: false)
                ->datetime('created_at')
                ->datetime('updated_at'),
            new CreateTableStatement('dependency_data_objects')
                ->belongsTo('dependency_data_objects.dependency_id', 'dependencies.id', onDelete: OnDelete::CASCADE)
                ->belongsTo('dependency_data_objects.data_object_id', 'data_objects.id', onDelete: OnDelete::CASCADE)
                ->raw('PRIMARY KEY (dependency_id, data_object_id)'),
        );
    }

    public function down(): QueryStatement
    {
        return new CompoundStatement(
            new DropTableStatement('dependency_data_objects'),
            new DropTableStatement('dependencies'),
        );
    }
}

