<?php

declare(strict_types=1);

namespace Database\Migrations;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

/**
 * Creates the `people` table.
 *
 * People represent business owners, technical owners, interface owners and
 * journey owners. They are intentionally kept separate from `users` (login
 * accounts), because not every person needs to log in, and not every
 * account necessarily maps to a single real-world person.
 */
final class CreatePeopleTable implements MigratesUp, MigratesDown
{
    public string $name = '2026_07_02_000001_create_people_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('people')
            ->primary()
            ->varchar('name', 255)
            ->varchar('email', 255, nullable: true)
            ->varchar('department', 255, nullable: true)
            ->varchar('role_title', 255, nullable: true)
            ->boolean('is_active', default: true)
            ->datetime('created_at')
            ->datetime('updated_at');
    }

    public function down(): QueryStatement
    {
        return new DropTableStatement('people');
    }
}
