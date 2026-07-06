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
 * Prepares the role model extension.
 *
 * MVP authentication stays HTTP Basic Auth, but these tables allow attaching
 * roles to users so authorization can be introduced incrementally.
 */
final class CreateRoleTables implements MigratesUp, MigratesDown
{
    public string $name = '2026_07_03_000010_create_role_tables';

    public function up(): QueryStatement
    {
        return new CompoundStatement(
            (new CreateTableStatement('roles'))
                ->primary()
                ->varchar('name', 100)
                ->text('description', nullable: true)
                ->datetime('created_at')
                ->datetime('updated_at')
                ->unique('name'),
            (new CreateTableStatement('user_roles'))
                ->primary()
                ->belongsTo('user_roles.user_id', 'users.id')
                ->belongsTo('user_roles.role_id', 'roles.id')
                ->datetime('created_at')
                ->datetime('updated_at')
                ->unique('user_id', 'role_id'),
        );
    }

    public function down(): QueryStatement
    {
        return new CompoundStatement(
            new DropTableStatement('user_roles'),
            new DropTableStatement('roles'),
        );
    }
}

