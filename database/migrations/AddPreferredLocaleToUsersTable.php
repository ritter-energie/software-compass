<?php

declare(strict_types=1);

namespace Database\Migrations;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\VarcharStatement;

/** Adds a per-user UI language preference used by the frontend language selector. */
final class AddPreferredLocaleToUsersTable implements MigratesUp, MigratesDown
{
    public string $name = '2026_07_03_000009_add_preferred_locale_to_users_table';

    public function up(): QueryStatement
    {
        return (new AlterTableStatement('users'))
            ->add(new VarcharStatement('preferred_locale', 10, default: 'en'));
    }

    public function down(): QueryStatement
    {
        return (new AlterTableStatement('users'))
            ->dropColumn('preferred_locale');
    }
}

