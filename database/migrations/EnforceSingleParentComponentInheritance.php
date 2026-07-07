<?php

declare(strict_types=1);

namespace Database\Migrations;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CompoundStatement;
use Tempest\Database\QueryStatements\RawStatement;

/**
 * Enforces that a child component can have at most one parent component.
 */
final class EnforceSingleParentComponentInheritance implements MigratesUp, MigratesDown
{
    public string $name = '2026_07_07_000001_enforce_single_parent_component_inheritance';

    public function up(): QueryStatement
    {
        return new CompoundStatement(
            new RawStatement(
                'DELETE ci FROM component_inheritance ci
                 JOIN component_inheritance keep_ci
                   ON ci.child_component_id = keep_ci.child_component_id
                  AND ci.parent_component_id > keep_ci.parent_component_id',
            ),
            new RawStatement(
                'CREATE UNIQUE INDEX component_inheritance_child_component_unique
                 ON component_inheritance (child_component_id)',
            ),
        );
    }

    public function down(): QueryStatement
    {
        return new RawStatement('DROP INDEX component_inheritance_child_component_unique ON component_inheritance');
    }
}
