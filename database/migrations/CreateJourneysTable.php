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
 * Creates the `journeys`, `journey_steps` and `journey_step_components`
 * tables that model Customer Journeys and how components support them.
 */
final class CreateJourneysTable implements MigratesUp, MigratesDown
{
    public string $name = '2026_07_02_000006_create_journeys_table';

    public function up(): QueryStatement
    {
        return new CompoundStatement(
            new CreateTableStatement('journeys')
                ->primary()
                ->varchar('name', 255)
                ->varchar('slug', 255)
                ->text('description', nullable: true)
                ->belongsTo('journeys.owner_id', 'people.id', nullable: true)
                ->belongsTo('journeys.status_id', 'component_statuses.id')
                ->integer('sort_order', default: 0)
                ->datetime('created_at')
                ->datetime('updated_at')
                ->unique('slug'),
            new CreateTableStatement('journey_steps')
                ->primary()
                ->belongsTo('journey_steps.journey_id', 'journeys.id', onDelete: OnDelete::CASCADE)
                ->varchar('name', 255)
                ->text('description', nullable: true)
                ->integer('sort_order')
                ->datetime('created_at')
                ->datetime('updated_at'),
            new CreateTableStatement('journey_step_components')
                ->primary()
                ->belongsTo('journey_step_components.journey_step_id', 'journey_steps.id', onDelete: OnDelete::CASCADE)
                ->belongsTo('journey_step_components.component_id', 'components.id', onDelete: OnDelete::CASCADE)
                ->varchar('role_in_step', 100, default: 'supporting')
                ->text('notes', nullable: true)
                ->datetime('created_at')
                ->datetime('updated_at'),
        );
    }

    public function down(): QueryStatement
    {
        return new CompoundStatement(
            new DropTableStatement('journey_step_components'),
            new DropTableStatement('journey_steps'),
            new DropTableStatement('journeys'),
        );
    }
}

