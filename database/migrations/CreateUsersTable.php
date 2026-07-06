<?php

declare(strict_types=1);

namespace Database\Migrations;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

/**
 * Creates the `users` table, which stores session login credentials.
 *
 * Users sign in with the email address of their linked `people` record.
 */
final class CreateUsersTable implements MigratesUp, MigratesDown
{
    public string $name = '2026_07_02_000002_create_users_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('users')
            ->primary()
            ->varchar('password_hash', 255)
            ->belongsTo('users.person_id', 'people.id', nullable: true)
            ->boolean('is_active', default: true)
            ->datetime('created_at')
            ->datetime('updated_at')
            ->unique('person_id');
    }

    public function down(): QueryStatement
    {
        return new DropTableStatement('users');
    }
}

