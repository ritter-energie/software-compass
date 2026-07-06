<?php

declare(strict_types=1);

namespace Database\Migrations;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

/**
 * Creates the `audit_logs` table. Entries are written whenever components,
 * dependencies, journeys or governance reviews are created, changed or
 * deleted.
 */
final class CreateAuditLogsTable implements MigratesUp, MigratesDown
{
    public string $name = '2026_07_02_000008_create_audit_logs_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('audit_logs')
            ->primary()
            ->varchar('entity_type', 255)
            ->integer('entity_id', unsigned: true)
            ->varchar('action', 100)
            ->json('old_values', nullable: true)
            ->json('new_values', nullable: true)
            ->belongsTo('audit_logs.changed_by', 'people.id', nullable: true)
            ->datetime('created_at');
    }

    public function down(): QueryStatement
    {
        return new DropTableStatement('audit_logs');
    }
}

