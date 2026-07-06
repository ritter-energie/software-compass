<?php

declare(strict_types=1);

namespace Database\Migrations;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\QueryStatements\OnDelete;

/** Creates optional many-to-many inheritance links between components. */
final class CreateComponentInheritanceTable implements MigratesUp, MigratesDown
{
    public string $name = '2026_07_06_000001_create_component_inheritance_table';

    public function up(): QueryStatement
    {
        return (new CreateTableStatement('component_inheritance'))
            ->belongsTo('component_inheritance.parent_component_id', 'components.id', onDelete: OnDelete::CASCADE)
            ->belongsTo('component_inheritance.child_component_id', 'components.id', onDelete: OnDelete::CASCADE)
            ->raw('PRIMARY KEY (parent_component_id, child_component_id)');
    }

    public function down(): QueryStatement
    {
        return new DropTableStatement('component_inheritance');
    }
}

